<?php

namespace Axytos\KaufAufRechnung\Core\Model\OrderStateMachine;

use Axytos\ECommerce\Clients\ErrorReporting\ErrorReportingClientInterface;
use Axytos\KaufAufRechnung\Core\Model\AxytosOrderCommandFacade;
use Axytos\KaufAufRechnung\Core\Model\AxytosOrderEventEmitter;
use Axytos\KaufAufRechnung\Core\Model\OrderStateMachine\Context\CheckoutStateContext;
use Axytos\KaufAufRechnung\Core\Model\OrderStateMachine\Context\SyncCriticalChangesStateContext;
use Axytos\KaufAufRechnung\Core\Model\OrderStateMachine\Context\SyncPaymentStatusStateContext;
use Axytos\KaufAufRechnung\Core\Model\OrderStateMachine\Context\SyncUncriticalChangesStateContext;
use Axytos\KaufAufRechnung\Core\Model\OrderStateMachine\States\CanceledState;
use Axytos\KaufAufRechnung\Core\Model\OrderStateMachine\States\CheckoutConfirmedState;
use Axytos\KaufAufRechnung\Core\Model\OrderStateMachine\States\CheckoutFailedState;
use Axytos\KaufAufRechnung\Core\Model\OrderStateMachine\States\CheckoutRejectedState;
use Axytos\KaufAufRechnung\Core\Model\OrderStateMachine\States\CompletelyCanceledState;
use Axytos\KaufAufRechnung\Core\Model\OrderStateMachine\States\CompletelyPaidState;
use Axytos\KaufAufRechnung\Core\Model\OrderStateMachine\States\CompletelyRefundedState;
use Axytos\KaufAufRechnung\Core\Model\OrderStateMachine\States\InvoicedState;
use Axytos\KaufAufRechnung\Core\Model\OrderStateMachine\States\UncheckedState;
use Axytos\KaufAufRechnung\Core\Plugin\Abstractions\Database\DatabaseTransactionFactoryInterface;
use Axytos\KaufAufRechnung\Core\Plugin\Abstractions\Logging\LoggerAdapterInterface;
use Axytos\KaufAufRechnung\Core\Plugin\Abstractions\PluginOrderInterface;

class OrderStateMachine
{
    /**
     * @var OrderStateInterface
     */
    private $state;

    /**
     * @var array<string, mixed>
     */
    private $stateData;

    /**
     * @var PluginOrderInterface
     */
    private $pluginOrder;

    /**
     * @var ErrorReportingClientInterface
     */
    private $errorReportingClient;

    /**
     * @var DatabaseTransactionFactoryInterface
     */
    private $databaseTransactionFactory;

    /**
     * @var AxytosOrderCommandFacade
     */
    private $commandFacade;

    /**
     * @var AxytosOrderEventEmitter
     */
    private $eventEmitter;

    /**
     * @var LoggerAdapterInterface
     */
    private $logger;

    public function __construct(
        PluginOrderInterface $pluginOrder,
        ErrorReportingClientInterface $errorReportingClient,
        DatabaseTransactionFactoryInterface $databaseTransactionFactory,
        AxytosOrderCommandFacade $commandFacade,
        AxytosOrderEventEmitter $eventEmitter,
        LoggerAdapterInterface $logger
    ) {
        $this->pluginOrder = $pluginOrder;
        $this->errorReportingClient = $errorReportingClient;
        $this->databaseTransactionFactory = $databaseTransactionFactory;
        $this->commandFacade = $commandFacade;
        $this->eventEmitter = $eventEmitter;
        $this->logger = $logger;

        $this->restoreState();
    }

    /**
     * @return void
     */
    public function checkout()
    {
        try {
            $context = new CheckoutStateContext(
                $this,
                $this->pluginOrder,
                $this->commandFacade,
                $this->eventEmitter
            );
            $this->state->setContext($context);
            $this->state->checkout();
        } catch (\Throwable $th) {
            $this->errorReportingClient->reportError($th);
            $this->logger->error($th->getMessage());
        } catch (\Exception $th) { // @phpstan-ignore-line because of php5 compatibility
            $this->errorReportingClient->reportError($th);
            $this->logger->error($th->getMessage());
        }
    }

    /**
     * @return void
     */
    public function syncCriticalChanges()
    {
        $transaction = $this->databaseTransactionFactory->create();
        try {
            $transaction->begin();

            $context = new SyncCriticalChangesStateContext(
                $this,
                $this->pluginOrder,
                $this->commandFacade,
                $this->eventEmitter
            );
            $this->state->setContext($context);
            $this->state->syncCriticalChanges();

            $transaction->commit();
        } catch (\Throwable $th) {
            $this->errorReportingClient->reportError($th);
            $this->logger->error($th->getMessage());
            $transaction->rollback();
        } catch (\Exception $th) { // @phpstan-ignore-line because of php5 compatibility
            $this->errorReportingClient->reportError($th);
            $this->logger->error($th->getMessage());
            $transaction->rollback();
        }
    }

    /**
     * @return void
     */
    public function syncUncriticalChanges()
    {
        $context = new SyncUncriticalChangesStateContext(
            $this,
            $this->pluginOrder,
            $this->commandFacade,
            $this->eventEmitter,
            $this->databaseTransactionFactory,
            $this->errorReportingClient
        );
        $this->state->setContext($context);
        $this->state->syncUncriticalChanges();
    }

    /**
     * @return void
     */
    public function syncPaymentStatus()
    {
        $context = new SyncPaymentStatusStateContext(
            $this,
            $this->pluginOrder,
            $this->commandFacade,
            $this->eventEmitter
        );
        $this->state->setContext($context);
        $this->state->syncPaymentStatus();
    }

    /**
     * @return string|null
     *
     * @phpstan-return \Axytos\KaufAufRechnung\Core\Abstractions\Model\AxytosOrderCheckoutAction::*|null
     */
    public function getCheckoutAction()
    {
        return $this->state->getCheckoutAction();
    }

    /**
     * @param string $newState
     *
     * @phpstan-param \Axytos\KaufAufRechnung\Core\Model\OrderStateMachine\OrderStates::* $newState
     *
     * @return void
     */
    public function changeState($newState)
    {
        $this->logger->debug('Transitioning to state: ' . $newState);

        $context = $this->state->getContext();
        $this->state->onExit();
        $this->state = $this->createState($newState);
        $this->state->setContext($context);
        $this->state->onEnter();
        $this->pluginOrder->saveState($newState, serialize($this->stateData));
    }

    /**
     * @param string $name
     *
     * @return mixed
     */
    public function getStateValue($name)
    {
        return isset($this->stateData[$name]) ? $this->stateData[$name] : null;
    }

    /**
     * @param string $name
     * @param mixed  $value
     *
     * @return void
     */
    public function setStateValue($name, $value)
    {
        $this->stateData[$name] = $value;
    }

    /**
     * @param string $name
     *
     * @return void
     */
    public function unsetStateValue($name)
    {
        unset($this->stateData[$name]);
    }

    /**
     * @return OrderStateInterface
     */
    public function getCurrentState()
    {
        return $this->state;
    }

    /**
     * @return void
     */
    private function restoreState()
    {
        $stateInfo = $this->pluginOrder->loadState();
        if (null !== $stateInfo) {
            $this->state = $this->createState($stateInfo->getName());
            $stateData = $stateInfo->getData();
            $stateData = is_string($stateData) && '' !== $stateData ? unserialize($stateData) : [];
            $this->stateData = is_array($stateData) ? $stateData : [];
        } else {
            $this->state = new UncheckedState();
            $this->stateData = [];
        }
    }

    /**
     * @param string $name
     *
     * @return OrderStateInterface
     */
    private function createState($name)
    {
        switch ($name) {
            case OrderStates::CHECKOUT_CONFIRMED:
                return new CheckoutConfirmedState();
            case OrderStates::CHECKOUT_REJECTED:
                return new CheckoutRejectedState();
            case OrderStates::CHECKOUT_FAILED:
                return new CheckoutFailedState();
            case OrderStates::INVOICED:
                return new InvoicedState();
            case OrderStates::CANCELED:
                return new CanceledState();
            case OrderStates::COMPLETELY_PAID:
                return new CompletelyPaidState();
            case OrderStates::COMPLETELY_REFUNDED:
                return new CompletelyRefundedState();
            case OrderStates::COMPLETELY_CANCELED:
                return new CompletelyCanceledState();
            default:
                return new UncheckedState();
        }
    }
}
