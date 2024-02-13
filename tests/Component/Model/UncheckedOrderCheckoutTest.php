<?php

namespace Axytos\KaufAufRechnung\Core\Tests\Component\Model;

use Axytos\ECommerce\Clients\Invoice\ShopActions;
use Axytos\KaufAufRechnung\Core\Abstractions\Model\AxytosOrderEvents;
use Axytos\KaufAufRechnung\Core\Model\OrderStateMachine\States\CheckoutConfirmedState;
use Axytos\KaufAufRechnung\Core\Model\OrderStateMachine\States\CheckoutFailedState;
use Axytos\KaufAufRechnung\Core\Model\OrderStateMachine\States\CheckoutRejectedState;

class UncheckedOrderCheckoutTest extends AxytosOrderTestCase
{
    /**
     * @return string
     * @phpstan-return \Axytos\KaufAufRechnung\Core\Model\OrderStateMachine\OrderStates::*
     */
    protected function initialState()
    {
        /** @phpstan-ignore-next-line because any unknown state is equal to unchecked */
        return '';
    }

    /**
     * @return void
     */
    public function test_checkout_runsPrecheckAndConfirmAndTransitionsToConfirmed()
    {
        $this->commandFacade
            ->expects($this->once())
            ->method('checkoutPrecheck')
            ->willReturn(ShopActions::COMPLETE_ORDER);
        $this->commandFacade
            ->expects($this->once())
            ->method('checkoutConfirm');

        $this->expectEventEmitted(AxytosOrderEvents::CHECKOUT_BEFORE_CHECK);
        $this->expectEventEmitted(AxytosOrderEvents::CHECKOUT_AFTER_ACCEPTED);
        $this->expectEventEmitted(AxytosOrderEvents::CHECKOUT_AFTER_CONFIRMED);

        $this->sut->checkout();

        $this->assertInstanceOf(CheckoutConfirmedState::class, $this->sut->getCurrentState());
    }

    /**
     * @return void
     */
    public function test_checkout_runsPrecheckAndTransitionsToRejectedIfPrecheckRejects()
    {
        $this->commandFacade
            ->expects($this->once())
            ->method('checkoutPrecheck')
            ->willReturn(ShopActions::CHANGE_PAYMENT_METHOD);
        $this->commandFacade
            ->expects($this->never())
            ->method('checkoutConfirm');

        $this->expectEventEmitted(AxytosOrderEvents::CHECKOUT_BEFORE_CHECK);
        $this->expectEventEmitted(AxytosOrderEvents::CHECKOUT_AFTER_REJECTED);

        $this->sut->checkout();

        $this->assertInstanceOf(CheckoutRejectedState::class, $this->sut->getCurrentState());
    }

    /**
     * @return void
     */
    public function test_checkout_runsPrecheckAndTransitionsToFailedIfPrecheckFails()
    {
        $this->commandFacade
            ->expects($this->once())
            ->method('checkoutPrecheck')
            ->willThrowException(new \Exception("simulated error"));
        $this->commandFacade
            ->expects($this->never())
            ->method('checkoutConfirm');

        $this->expectEventEmitted(AxytosOrderEvents::CHECKOUT_BEFORE_CHECK);
        $this->expectEventEmitted(AxytosOrderEvents::CHECKOUT_AFTER_FAILED);

        $this->sut->checkout();

        $this->assertInstanceOf(CheckoutFailedState::class, $this->sut->getCurrentState());
    }

    /**
     * @return void
     */
    public function test_checkout_runsConfirmAndTransitionsToFailedIfConfirmFails()
    {
        $this->commandFacade
            ->expects($this->once())
            ->method('checkoutPrecheck')
            ->willReturn(ShopActions::COMPLETE_ORDER);
        $this->commandFacade
            ->expects($this->once())
            ->method('checkoutConfirm')
            ->willThrowException(new \Exception("simulated error"));

        $this->expectEventEmitted(AxytosOrderEvents::CHECKOUT_BEFORE_CHECK);
        $this->expectEventEmitted(AxytosOrderEvents::CHECKOUT_AFTER_ACCEPTED);
        $this->expectEventEmitted(AxytosOrderEvents::CHECKOUT_AFTER_FAILED);

        $this->sut->checkout();

        $this->assertInstanceOf(CheckoutFailedState::class, $this->sut->getCurrentState());
    }
}
