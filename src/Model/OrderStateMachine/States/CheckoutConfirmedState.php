<?php

namespace Axytos\KaufAufRechnung\Core\Model\OrderStateMachine\States;

use Axytos\KaufAufRechnung\Core\Abstractions\Model\AxytosOrderCheckoutAction;
use Axytos\KaufAufRechnung\Core\Model\OrderStateMachine\OrderStates;

class CheckoutConfirmedState extends AbstractState
{
    /**
     * @return void
     */
    public function syncCriticalChanges()
    {
        $pluginOrder = $this->context->getPluginOrder();

        if ($pluginOrder->hasBeenCanceled()) {
            $this->transitionToCanceled();

            return;
        }

        if ($pluginOrder->hasBeenInvoiced()) {
            $this->transitionToInvoiced();

            return;
        }
    }

    /**
     * @return void
     */
    public function syncUncriticalChanges()
    {
        $this->reportUpdate();
        $this->reportShipping();
        $this->reportTrackingInformation();
    }

    /**
     * @return string|null
     *
     * @phpstan-return \Axytos\KaufAufRechnung\Core\Abstractions\Model\AxytosOrderCheckoutAction::*|null
     */
    public function getCheckoutAction()
    {
        return AxytosOrderCheckoutAction::COMPLETE_CHECKOUT;
    }

    /**
     * @return void
     */
    private function transitionToCanceled()
    {
        $this->context->reportCancel();
        $this->context->changeState(OrderStates::CANCELED);
    }

    /**
     * @return void
     */
    private function transitionToInvoiced()
    {
        $this->reportUpdate();
        $this->context->reportCreateInvoice();
        $this->context->changeState(OrderStates::INVOICED);
    }

    /**
     * @return void
     */
    private function reportShipping()
    {
        $pluginOrder = $this->context->getPluginOrder();

        if ($pluginOrder->hasShippingReported()) {
            return;
        }

        if (!$pluginOrder->hasBeenShipped()) {
            return;
        }

        $this->context->reportShipping();

        $pluginOrder->saveHasShippingReported();
    }

    /**
     * @return void
     */
    private function reportTrackingInformation()
    {
        $pluginOrder = $this->context->getPluginOrder();

        if (!$pluginOrder->hasNewTrackingInformation()) {
            return;
        }

        $this->context->reportTrackingInformation();

        $pluginOrder->saveNewTrackingInformation();
    }

    /**
     * @return void
     */
    private function reportUpdate()
    {
        $pluginOrder = $this->context->getPluginOrder();

        if (!$pluginOrder->hasBasketUpdates()) {
            return;
        }

        $this->context->reportUpdate();

        $pluginOrder->saveBasketUpdatesReported();
    }
}
