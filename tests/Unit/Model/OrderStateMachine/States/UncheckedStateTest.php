<?php

namespace Axytos\KaufAufRechnung\Core\Tests\Unit\Model\OrderStateMachine\States;

use Axytos\ECommerce\Clients\Invoice\ShopActions;
use Axytos\KaufAufRechnung\Core\Abstractions\Model\AxytosOrderCheckoutAction;
use Axytos\KaufAufRechnung\Core\Abstractions\Model\AxytosOrderEvents;
use Axytos\KaufAufRechnung\Core\Model\OrderStateMachine\OrderStateContextInterface;
use Axytos\KaufAufRechnung\Core\Model\OrderStateMachine\OrderStates;
use Axytos\KaufAufRechnung\Core\Model\OrderStateMachine\States\UncheckedState;
use Axytos\KaufAufRechnung\Core\Plugin\Abstractions\PluginOrderInterface;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class UncheckedStateTest extends TestCase
{
    /**
     * @var OrderStateContextInterface&MockObject
     */
    private $context;

    /**
     * @var PluginOrderInterface&MockObject
     */
    private $pluginOrder;

    /**
     * @var string[]
     *
     * @phpstan-var array<\Axytos\KaufAufRechnung\Core\Abstractions\Model\AxytosOrderEvents::*>
     */
    private $emittedEvents;

    /**
     * @var UncheckedState
     */
    private $sut;

    /**
     * @before
     *
     * @return void
     */
    #[Before]
    public function beforeEach()
    {
        $this->context = $this->createMock(OrderStateContextInterface::class);
        $this->pluginOrder = $this->createMock(PluginOrderInterface::class);

        $this->context
            ->method('getPluginOrder')
            ->willReturn($this->pluginOrder)
        ;

        $this->emittedEvents = [];
        $this->context
            ->method('emit')
            ->willReturnCallback(function ($eventName) {
                $this->emittedEvents[] = $eventName;

                return null;
            })
        ;

        $this->sut = new UncheckedState();
        $this->sut->setContext($this->context);
    }

    /**
     * @return void
     */
    public function test_checkout_invokes_precheck_then_confirm()
    {
        $this->context
            ->expects($this->once())
            ->method('checkoutPrecheck')
            ->willReturn(ShopActions::COMPLETE_ORDER)
        ;
        $this->context
            ->expects($this->once())
            ->method('checkoutConfirm')
        ;
        $this->context
            ->expects($this->once())
            ->method('changeState')
            ->with(OrderStates::CHECKOUT_CONFIRMED)
        ;

        $this->sut->checkout(false);

        $this->assertEquals([
            AxytosOrderEvents::CHECKOUT_BEFORE_CHECK,
            AxytosOrderEvents::CHECKOUT_AFTER_ACCEPTED,
            AxytosOrderEvents::CHECKOUT_AFTER_CONFIRMED,
        ], $this->emittedEvents);
    }

    /**
     * @return void
     */
    public function test_checkout_rejects_order_if_precheck_is_unsuccessful()
    {
        $this->context
            ->expects($this->once())
            ->method('checkoutPrecheck')
            ->willReturn(ShopActions::CHANGE_PAYMENT_METHOD)
        ;
        $this->context
            ->expects($this->never())
            ->method('checkoutConfirm')
        ;
        $this->context
            ->expects($this->once())
            ->method('changeState')
            ->with(OrderStates::CHECKOUT_REJECTED)
        ;

        $this->sut->checkout(false);

        $this->assertEquals([
            AxytosOrderEvents::CHECKOUT_BEFORE_CHECK,
            AxytosOrderEvents::CHECKOUT_AFTER_REJECTED,
        ], $this->emittedEvents);
    }

    /**
     * @return void
     */
    public function test_checkout_fails_order_if_precheck_throws()
    {
        $this->context
            ->expects($this->once())
            ->method('checkoutPrecheck')
            ->willThrowException(new \Exception('simulator error'))
        ;
        $this->context
            ->expects($this->never())
            ->method('checkoutConfirm')
        ;
        $this->context
            ->expects($this->once())
            ->method('changeState')
            ->with(OrderStates::CHECKOUT_FAILED)
        ;

        $this->expectException(\Exception::class);

        try {
            $this->sut->checkout(false);
        } finally {
            $this->assertEquals([
                AxytosOrderEvents::CHECKOUT_BEFORE_CHECK,
                AxytosOrderEvents::CHECKOUT_AFTER_FAILED,
            ], $this->emittedEvents);
        }
    }

    /**
     * @return void
     */
    public function test_checkout_fails_order_if_confirm_throws()
    {
        $this->context
            ->expects($this->once())
            ->method('checkoutPrecheck')
            ->willReturn(ShopActions::COMPLETE_ORDER)
        ;
        $this->context
            ->expects($this->once())
            ->method('checkoutConfirm')
            ->willThrowException(new \Exception('simulated error'))
        ;
        $this->context
            ->expects($this->once())
            ->method('changeState')
            ->with(OrderStates::CHECKOUT_FAILED)
        ;

        $this->expectException(\Exception::class);

        try {
            $this->sut->checkout(false);
        } finally {
            $this->assertEquals([
                AxytosOrderEvents::CHECKOUT_BEFORE_CHECK,
                AxytosOrderEvents::CHECKOUT_AFTER_ACCEPTED,
                AxytosOrderEvents::CHECKOUT_AFTER_FAILED,
            ], $this->emittedEvents);
        }
    }

    /**
     * @return void
     */
    public function test_get_checkout_action_always_returns_change_payment_method()
    {
        $this->assertEquals(
            AxytosOrderCheckoutAction::CHANGE_PAYMENT_METHOD,
            $this->sut->getCheckoutAction()
        );
    }
}
