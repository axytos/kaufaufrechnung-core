<?php

namespace Axytos\KaufAufRechnung\Core\Model\OrderStateMachine;

interface OrderStateInterface
{
    /**
     * @param \Axytos\KaufAufRechnung\Core\Model\OrderStateMachine\OrderStateContextInterface $context
     * @return void
     */
    public function setContext($context);

    /**
     * @return \Axytos\KaufAufRechnung\Core\Model\OrderStateMachine\OrderStateContextInterface
     */
    public function getContext();

    /**
     * @return void
     */
    public function checkout();

    /**
     * @return void
     */
    public function syncCriticalChanges();

    /**
     * @return void
     */
    public function syncUncriticalChanges();

    /**
     * @return void
     */
    public function syncPaymentStatus();

    /**
     * @return string|null
     * @phpstan-return \Axytos\KaufAufRechnung\Core\Abstractions\Model\AxytosOrderCheckoutAction::*|null
     */
    public function getCheckoutAction();

    /**
     * @return void
     */
    public function onEnter();

    /**
     * @return void
     */
    public function onExit();
}
