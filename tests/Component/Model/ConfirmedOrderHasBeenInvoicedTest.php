<?php

namespace Axytos\KaufAufRechnung\Core\Tests\Component\Model;

use Axytos\KaufAufRechnung\Core\Model\OrderStateMachine\OrderStates;
use Axytos\KaufAufRechnung\Core\Model\OrderStateMachine\States\CheckoutConfirmedState;
use Axytos\KaufAufRechnung\Core\Model\OrderStateMachine\States\InvoicedState;

/**
 * @internal
 */
class ConfirmedOrderHasBeenInvoicedTest extends AxytosOrderTestCase
{
    /**
     * @return string
     *
     * @phpstan-return \Axytos\KaufAufRechnung\Core\Model\OrderStateMachine\OrderStates::*
     */
    protected function initialState()
    {
        return OrderStates::CHECKOUT_CONFIRMED;
    }

    /**
     * @return void
     */
    public function test_sync_stays_in_confirmed_state_and_syncs_uncriticals_if_order_is_not_invoiced()
    {
        $this->pluginOrder
            ->method('hasBeenInvoiced')
            ->willReturn(false)
        ;
        $this->pluginOrder
            ->method('hasBasketUpdates')
            ->willReturn(true)
        ;
        $this->pluginOrder
            ->method('hasShippingReported')
            ->willReturn(false)
        ;
        $this->pluginOrder
            ->method('hasBeenShipped')
            ->willReturn(true)
        ;
        $this->pluginOrder
            ->method('hasNewTrackingInformation')
            ->willReturn(true)
        ;

        $this->commandFacade
            ->expects($this->never())
            ->method('reportCreateInvoice')
        ;
        $this->commandFacade
            ->expects($this->once())
            ->method('reportUpdate')
        ;
        $this->commandFacade
            ->expects($this->once())
            ->method('reportShipping')
        ;
        $this->commandFacade
            ->expects($this->once())
            ->method('reportTrackingInformation')
        ;

        $this->sut->sync();

        $this->assertInstanceOf(CheckoutConfirmedState::class, $this->sut->getCurrentState());
    }

    /**
     * @return void
     */
    public function test_sync_transitions_to_invoiced_when_order_has_been_invoiced()
    {
        $this->pluginOrder
            ->method('hasBeenInvoiced')
            ->willReturn(true)
        ;
        $this->pluginOrder
            ->method('hasBasketUpdates')
            ->willReturn(true)
        ;
        $this->pluginOrder
            ->method('hasShippingReported')
            ->willReturn(false)
        ;
        $this->pluginOrder
            ->method('hasBeenShipped')
            ->willReturn(true)
        ;
        $this->pluginOrder
            ->method('hasNewTrackingInformation')
            ->willReturn(true)
        ;

        $this->commandFacade
            ->expects($this->once())
            ->method('reportUpdate')
        ;
        $this->commandFacade
            ->expects($this->once())
            ->method('reportCreateInvoice')
        ;
        $this->commandFacade
            ->expects($this->once())
            ->method('reportShipping')
        ;
        $this->commandFacade
            ->expects($this->once())
            ->method('reportTrackingInformation')
        ;

        $this->sut->sync();

        $this->assertInstanceOf(InvoicedState::class, $this->sut->getCurrentState());
    }
}
