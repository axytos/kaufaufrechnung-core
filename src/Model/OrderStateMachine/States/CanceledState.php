<?php

namespace Axytos\KaufAufRechnung\Core\Model\OrderStateMachine\States;

use Axytos\KaufAufRechnung\Core\Model\OrderStateMachine\OrderStates;

class CanceledState extends AbstractState
{
    const CANCEL_DATE = 'CANCEL_DATE';
    const CANCEL_RETENTION_DAYS = 30;

    /**
     * @return void
     */
    public function syncCriticalChanges()
    {
        $pluginOrder = $this->context->getPluginOrder();

        if (!$pluginOrder->hasBeenCanceled()) {
            $this->transitionToConfirmed();

            return;
        }

        if ($this->daysSinceCanceled() >= self::CANCEL_RETENTION_DAYS) {
            $this->transitionToCompletelyCanceled();

            return;
        }
    }

    /**
     * @return void
     */
    public function onEnter()
    {
        $this->context->setStateValue(self::CANCEL_DATE, time());
    }

    /**
     * @return void
     */
    public function onExit()
    {
        $this->context->unsetStateValue(self::CANCEL_DATE);
    }

    /**
     * @return int
     */
    private function daysSinceCanceled()
    {
        $cancelDate = $this->context->getStateValue(self::CANCEL_DATE);
        if (!is_int($cancelDate)) {
            // transition to self to reset cancel date
            $this->context->changeState(OrderStates::CANCELED);

            return 0;
        }

        $now = time();
        $secondsPassed = $now - $cancelDate;
        $secondsPerDay = 86400;

        return $secondsPassed / $secondsPerDay;
    }

    /**
     * @return void
     */
    private function transitionToConfirmed()
    {
        $this->context->reportUncancel();
        $this->context->changeState(OrderStates::CHECKOUT_CONFIRMED);
    }

    /**
     * @return void
     */
    private function transitionToCompletelyCanceled()
    {
        $this->context->changeState(OrderStates::COMPLETELY_CANCELED);
    }
}
