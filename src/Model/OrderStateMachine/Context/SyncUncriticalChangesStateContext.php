<?php

namespace Axytos\KaufAufRechnung\Core\Model\OrderStateMachine\Context;

use Axytos\ECommerce\Clients\ErrorReporting\ErrorReportingClientInterface;
use Axytos\KaufAufRechnung\Core\Model\AxytosOrderCommandFacade;
use Axytos\KaufAufRechnung\Core\Model\AxytosOrderEventEmitter;
use Axytos\KaufAufRechnung\Core\Model\OrderStateMachine\OrderStateMachine;
use Axytos\KaufAufRechnung\Core\Plugin\Abstractions\Database\DatabaseTransactionFactoryInterface;
use Axytos\KaufAufRechnung\Core\Plugin\Abstractions\PluginOrderInterface;

class SyncUncriticalChangesStateContext extends AbstractStateContext
{
    /**
     * @var AxytosOrderCommandFacade
     */
    private $commandFacade;

    /**
     * @var DatabaseTransactionFactoryInterface
     */
    private $databaseTransactionFactory;

    /**
     * @var ErrorReportingClientInterface
     */
    private $errorReportingClient;

    public function __construct(
        OrderStateMachine $stateMachine,
        PluginOrderInterface $pluginOrder,
        AxytosOrderCommandFacade $commandFacade,
        AxytosOrderEventEmitter $eventEmitter,
        DatabaseTransactionFactoryInterface $databaseTransactionFactory,
        ErrorReportingClientInterface $errorReportingClient
    ) {
        parent::__construct($stateMachine, $pluginOrder, $commandFacade, $eventEmitter);
        $this->commandFacade = $commandFacade;
        $this->databaseTransactionFactory = $databaseTransactionFactory;
        $this->errorReportingClient = $errorReportingClient;
    }

    /**
     * @return void
     */
    public function reportShipping()
    {
        $this->executeInTransaction([$this->commandFacade, 'reportShipping']);
    }

    /**
     * @return void
     */
    public function reportTrackingInformation()
    {
        $this->executeInTransaction([$this->commandFacade, 'reportTrackingInformation']);
    }

    /**
     * @return void
     */
    public function reportUpdate()
    {
        $this->executeInTransaction([$this->commandFacade, 'reportUpdate']);
    }

    /**
     * @param callable $callable
     *
     * @return void
     */
    private function executeInTransaction($callable)
    {
        try {
            $transaction = $this->databaseTransactionFactory->create();
            $this->executeInTransactionInternal($callable, $transaction);
        } catch (\Throwable $th) {
            $this->errorReportingClient->reportError($th);
        } catch (\Exception $th) { // @phpstan-ignore-line because of php5 compatibility
            $this->errorReportingClient->reportError($th);
        }
    }

    /**
     * @param callable                                                                               $callable
     * @param \Axytos\KaufAufRechnung\Core\Plugin\Abstractions\Database\DatabaseTransactionInterface $transaction
     *
     * @return void
     */
    private function executeInTransactionInternal($callable, $transaction)
    {
        try {
            $transaction->begin();
            call_user_func($callable, $this->getPluginOrder());
            $transaction->commit();
        } catch (\Throwable $th) {
            $this->errorReportingClient->reportError($th);
            $transaction->rollback();
        } catch (\Exception $th) { // @phpstan-ignore-line because of php5 compatibility
            $this->errorReportingClient->reportError($th);
            $transaction->rollback();
        }
    }
}
