<?php

namespace Axytos\KaufAufRechnung\Core\Model\OrderStateMachine\States;

use Axytos\KaufAufRechnung\Core\Model\OrderStateMachine\OrderStates;

class InvoicedState extends AbstractState
{
    /**
     * @return void
     */
    public function syncCriticalChanges()
    {
        $pluginOrder = $this->context->getPluginOrder();

        if ($this->context->hasBeenPaid()) {
            $pluginOrder->saveHasBeenPaid();
            $this->context->changeState(OrderStates::COMPLETELY_PAID);
        } else if ($pluginOrder->hasBeenRefunded()) {
            $this->context->reportRefund();
            $this->context->changeState(OrderStates::COMPLETELY_REFUNDED);
        }
    }

    /**
     * @return void
     */
    public function syncUncriticalChanges()
    {
        $this->reportShipping();
        $this->reportTrackingInformation();
    }

    /**
     * @return void
     */
    public function syncPaymentStatus()
    {
        if ($this->context->hasBeenPaid()) {
            $pluginOrder = $this->context->getPluginOrder();
            $pluginOrder->saveHasBeenPaid();
            $this->context->changeState(OrderStates::COMPLETELY_PAID);
        }
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
}
