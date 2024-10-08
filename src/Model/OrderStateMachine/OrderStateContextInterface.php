<?php

namespace Axytos\KaufAufRechnung\Core\Model\OrderStateMachine;

interface OrderStateContextInterface
{
    /**
     * @return \Axytos\KaufAufRechnung\Core\Plugin\Abstractions\PluginOrderInterface
     */
    public function getPluginOrder();

    /**
     * @param string $newState
     *
     * @phpstan-param \Axytos\KaufAufRechnung\Core\Model\OrderStateMachine\OrderStates::* $newState
     *
     * @return void
     */
    public function changeState($newState);

    /**
     * @param string $name
     *
     * @return mixed
     */
    public function getStateValue($name);

    /**
     * @param string $name
     * @param mixed  $value
     *
     * @return void
     */
    public function setStateValue($name, $value);

    /**
     * @param string $name
     *
     * @return void
     */
    public function unsetStateValue($name);

    /**
     * @return string
     *
     * @phpstan-return \Axytos\ECommerce\Clients\Invoice\ShopActions::*
     */
    public function checkoutPrecheck();

    /**
     * @return void
     */
    public function checkoutConfirm();

    /**
     * @return void
     */
    public function reportCancel();

    /**
     * @return void
     */
    public function reportUncancel();

    /**
     * @return void
     */
    public function reportCreateInvoice();

    /**
     * @return void
     */
    public function reportRefund();

    /**
     * @return void
     */
    public function reportShipping();

    /**
     * @return void
     */
    public function reportTrackingInformation();

    /**
     * @return void
     */
    public function reportUpdate();

    /**
     * @return bool
     */
    public function hasBeenPaid();

    /**
     * @param string $eventName
     *
     * @phpstan-param \Axytos\KaufAufRechnung\Core\Abstractions\Model\AxytosOrderEvents::* $eventName
     *
     * @return void
     */
    public function emit($eventName);
}
