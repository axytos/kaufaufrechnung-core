<?php

namespace Axytos\KaufAufRechnung\Core\Tests\Component\Model;

use Axytos\KaufAufRechnung\Core\Model\OrderStateMachine\OrderStates;
use Axytos\KaufAufRechnung\Core\Model\OrderStateMachine\States\CompletelyPaidState;
use Axytos\KaufAufRechnung\Core\Model\OrderStateMachine\States\InvoicedState;

/**
 * @internal
 */
class InvoicedOrderHasBeenPaidTest extends AxytosOrderTestCase
{
    /**
     * @return string
     *
     * @phpstan-return \Axytos\KaufAufRechnung\Core\Model\OrderStateMachine\OrderStates::*
     */
    protected function initialState()
    {
        return OrderStates::INVOICED;
    }

    /**
     * @return void
     */
    public function test_sync_transitions_to_completely_paid_state()
    {
        $this->pluginOrder->method('hasBeenShipped')->willReturn(true);
        $this->pluginOrder->method('hasNewTrackingInformation')->willReturn(true);
        $this->commandFacade->method('hasBeenPaid')->willReturn(true);

        $this->pluginOrder
            ->expects($this->once())
            ->method('saveHasBeenPaid')
        ;
        $this->commandFacade
            ->expects($this->never())
            ->method('reportShipping')
        ;
        $this->commandFacade
            ->expects($this->never())
            ->method('reportTrackingInformation')
        ;

        $this->sut->sync();

        $this->assertInstanceOf(CompletelyPaidState::class, $this->sut->getCurrentState());
    }

    /**
     * @return void
     */
    public function test_sync_stays_in_invoiced_state()
    {
        $this->pluginOrder->method('hasBeenShipped')->willReturn(true);
        $this->pluginOrder->method('hasNewTrackingInformation')->willReturn(true);
        $this->commandFacade->method('hasBeenPaid')->willReturn(false);

        $this->pluginOrder
            ->expects($this->never())
            ->method('saveHasBeenPaid')
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

    /**
     * @return void
     */
    public function test_sync_payment_status_transitions_to_completely_paid_state()
    {
        $this->commandFacade->method('hasBeenPaid')->willReturn(true);

        $this->pluginOrder
            ->expects($this->once())
            ->method('saveHasBeenPaid')
        ;

        $this->sut->syncPaymentStatus();

        $this->assertInstanceOf(CompletelyPaidState::class, $this->sut->getCurrentState());
    }

    /**
     * @return void
     */
    public function test_sync_payment_status_stays_in_invoiced_state()
    {
        $this->commandFacade->method('hasBeenPaid')->willReturn(false);

        $this->pluginOrder
            ->expects($this->never())
            ->method('saveHasBeenPaid')
        ;

        $this->sut->syncPaymentStatus();

        $this->assertInstanceOf(InvoicedState::class, $this->sut->getCurrentState());
    }
}
