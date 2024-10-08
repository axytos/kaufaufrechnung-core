<?php

namespace Axytos\KaufAufRechnung\Core\Model\OrderStateMachine\States;

use Axytos\KaufAufRechnung\Core\Abstractions\Model\AxytosOrderCheckoutAction;

class CheckoutFailedState extends AbstractState
{
    /**
     * @return string|null
     *
     * @phpstan-return \Axytos\KaufAufRechnung\Core\Abstractions\Model\AxytosOrderCheckoutAction::*|null
     */
    public function getCheckoutAction()
    {
        return AxytosOrderCheckoutAction::CHANGE_PAYMENT_METHOD;
    }
}
