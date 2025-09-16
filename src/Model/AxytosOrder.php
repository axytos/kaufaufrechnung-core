<?php

namespace Axytos\KaufAufRechnung\Core\Model;

use Axytos\KaufAufRechnung\Core\Abstractions\Model\AxytosOrderInterface;
use Axytos\KaufAufRechnung\Core\Model\OrderStateMachine\OrderStateMachine;

class AxytosOrder implements AxytosOrderInterface
{
    /**
     * @var AxytosOrderEventEmitter
     */
    private $eventEmitter;

    /**
     * @var OrderStateMachine
     */
    private $stateMachine;

    public function __construct(
        AxytosOrderEventEmitter $eventEmitter,
        OrderStateMachine $stateMachine
    ) {
        $this->eventEmitter = $eventEmitter;
        $this->stateMachine = $stateMachine;
    }

    /**
     * @param string   $eventName
     * @param callable $eventListener
     *
     * @phpstan-param \Axytos\KaufAufRechnung\Core\Abstractions\Model\AxytosOrderEvents::* $eventName
     *
     * @return void
     */
    public function subscribeEventListener($eventName, $eventListener)
    {
        $this->eventEmitter->subscribe($eventName, $eventListener);
    }

    /**
     * @return string
     *
     * @phpstan-return \Axytos\KaufAufRechnung\Core\Abstractions\Model\AxytosOrderCheckoutAction::*
     */
    public function getOrderCheckoutAction()
    {
        $checkoutAction = $this->stateMachine->getCheckoutAction();
        if (null === $checkoutAction) {
            throw new \Exception('No checkout action defined for current order state');
        }

        return $checkoutAction;
    }

    /**
     * @param bool $skipPrecheck
     *
     * @return void
     */
    public function checkout($skipPrecheck = true)
    {
        $this->stateMachine->checkout($skipPrecheck);
    }

    /**
     * @return void
     */
    public function sync()
    {
        $this->stateMachine->syncCriticalChanges();
        $this->stateMachine->syncUncriticalChanges();
    }

    /**
     * @return void
     */
    public function syncPaymentStatus()
    {
        $this->stateMachine->syncPaymentStatus();
    }

    /**
     * @return \Axytos\KaufAufRechnung\Core\Model\OrderStateMachine\OrderStateInterface
     */
    public function getCurrentState()
    {
        return $this->stateMachine->getCurrentState();
    }
}
