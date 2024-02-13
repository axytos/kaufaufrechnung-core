<?php

namespace Axytos\KaufAufRechnung\Core\Model\OrderStateMachine;

abstract class OrderStates
{
    const CHECKOUT_CONFIRMED = 'CHECKOUT_CONFIRMED';
    const CHECKOUT_FAILED = 'CHECKOUT_FAILED';
    const CHECKOUT_REJECTED = 'CHECKOUT_REJECTED';
    const INVOICED = 'INVOICED';
    const CANCELED = 'CANCELED';
    const COMPLETELY_CANCELED = 'COMPLETELY_CANCELED';
    const COMPLETELY_PAID = 'COMPLETELY_PAID';
    const COMPLETELY_REFUNDED = 'COMPLETELY_REFUNDED';
}
