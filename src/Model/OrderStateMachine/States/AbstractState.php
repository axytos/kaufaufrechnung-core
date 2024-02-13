<?php

namespace Axytos\KaufAufRechnung\Core\Model\OrderStateMachine\States;

use Axytos\KaufAufRechnung\Core\Model\OrderStateMachine\OrderStateInterface;

abstract class AbstractState implements OrderStateInterface
{
    /**
     * @var \Axytos\KaufAufRechnung\Core\Model\OrderStateMachine\OrderStateContextInterface
     */
    protected $context;

    /**
     * @param \Axytos\KaufAufRechnung\Core\Model\OrderStateMachine\OrderStateContextInterface $context
     * @return void
     */
    public function setContext($context)
    {
        $this->context = $context;
    }

    public function getContext()
    {
        return $this->context;
    }

    /**
     * @return void
     */
    public function checkout()
    {
    }

    /**
     * @return void
     */
    public function syncCriticalChanges()
    {
    }

    /**
     * @return void
     */
    public function syncUncriticalChanges()
    {
    }

    /**
     * @return void
     */
    public function syncPaymentStatus()
    {
    }

    /**
     * @return string|null
     * @phpstan-return \Axytos\KaufAufRechnung\Core\Abstractions\Model\AxytosOrderCheckoutAction::*|null
     */
    public function getCheckoutAction()
    {
        return null;
    }

    /**
     * @return void
     */
    public function onEnter()
    {
    }

    /**
     * @return void
     */
    public function onExit()
    {
    }
}
