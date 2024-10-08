<?php

namespace Axytos\KaufAufRechnung\Core\Model\OrderStateMachine\Context;

use Axytos\KaufAufRechnung\Core\Model\AxytosOrderCommandFacade;
use Axytos\KaufAufRechnung\Core\Model\AxytosOrderEventEmitter;
use Axytos\KaufAufRechnung\Core\Model\OrderStateMachine\OrderStateContextInterface;
use Axytos\KaufAufRechnung\Core\Model\OrderStateMachine\OrderStateMachine;
use Axytos\KaufAufRechnung\Core\Plugin\Abstractions\PluginOrderInterface;

abstract class AbstractStateContext implements OrderStateContextInterface
{
    /**
     * @var OrderStateMachine
     */
    private $stateMachine;

    /**
     * @var PluginOrderInterface
     */
    private $pluginOrder;

    /**
     * @var AxytosOrderCommandFacade
     */
    private $commandFacade;

    /**
     * @var AxytosOrderEventEmitter
     */
    private $eventEmitter;

    protected function __construct(
        OrderStateMachine $stateMachine,
        PluginOrderInterface $pluginOrder,
        AxytosOrderCommandFacade $commandFacade,
        AxytosOrderEventEmitter $eventEmitter
    ) {
        $this->stateMachine = $stateMachine;
        $this->pluginOrder = $pluginOrder;
        $this->commandFacade = $commandFacade;
        $this->eventEmitter = $eventEmitter;
    }

    /**
     * @return PluginOrderInterface
     */
    public function getPluginOrder()
    {
        return $this->pluginOrder;
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
        $this->stateMachine->changeState($newState);
    }

    /**
     * @param string $name
     *
     * @return mixed
     */
    public function getStateValue($name)
    {
        return $this->stateMachine->getStateValue($name);
    }

    /**
     * @param string $name
     * @param mixed  $value
     *
     * @return void
     */
    public function setStateValue($name, $value)
    {
        $this->stateMachine->setStateValue($name, $value);
    }

    /**
     * @param string $name
     *
     * @return void
     */
    public function unsetStateValue($name)
    {
        $this->stateMachine->unsetStateValue($name);
    }

    /**
     * @return string
     *
     * @phpstan-return \Axytos\ECommerce\Clients\Invoice\ShopActions::*
     */
    public function checkoutPrecheck()
    {
        return $this->commandFacade->checkoutPrecheck($this->pluginOrder);
    }

    /**
     * @return void
     */
    public function checkoutConfirm()
    {
        $this->commandFacade->checkoutConfirm($this->pluginOrder);
    }

    /**
     * @return void
     */
    public function reportCancel()
    {
        $this->commandFacade->reportCancel($this->pluginOrder);
    }

    /**
     * @return void
     */
    public function reportUncancel()
    {
        $this->commandFacade->reportUncancel($this->pluginOrder);
    }

    /**
     * @return void
     */
    public function reportCreateInvoice()
    {
        $this->commandFacade->reportCreateInvoice($this->pluginOrder);
    }

    /**
     * @return void
     */
    public function reportRefund()
    {
        $this->commandFacade->reportRefund($this->pluginOrder);
    }

    /**
     * @return void
     */
    public function reportShipping()
    {
        $this->commandFacade->reportShipping($this->pluginOrder);
    }

    /**
     * @return void
     */
    public function reportTrackingInformation()
    {
        $this->commandFacade->reportTrackingInformation($this->pluginOrder);
    }

    /**
     * @return void
     */
    public function reportUpdate()
    {
        $this->commandFacade->reportUpdate($this->pluginOrder);
    }

    /**
     * @return bool
     */
    public function hasBeenPaid()
    {
        return $this->commandFacade->hasBeenPaid($this->pluginOrder);
    }

    /**
     * @param string $eventName
     *
     * @phpstan-param \Axytos\KaufAufRechnung\Core\Abstractions\Model\AxytosOrderEvents::* $eventName
     *
     * @return void
     */
    public function emit($eventName)
    {
        $this->eventEmitter->emit($eventName);
    }
}
