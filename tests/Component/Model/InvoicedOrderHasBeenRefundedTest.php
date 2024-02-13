<?php

namespace Axytos\KaufAufRechnung\Core\Tests\Component\Model;

use Axytos\KaufAufRechnung\Core\Model\OrderStateMachine\OrderStates;
use Axytos\KaufAufRechnung\Core\Model\OrderStateMachine\States\CompletelyRefundedState;
use Axytos\KaufAufRechnung\Core\Model\OrderStateMachine\States\InvoicedState;

class InvoicedOrderHasBeenRefundedTest extends AxytosOrderTestCase
{
    /**
     * @return string
     * @phpstan-return \Axytos\KaufAufRechnung\Core\Model\OrderStateMachine\OrderStates::*
     */
    protected function initialState()
    {
        return OrderStates::INVOICED;
    }

    /**
     * @return void
     */
    public function test_sync_transitions_to_completely_refunded_state()
    {
        $this->pluginOrder->method('hasBeenShipped')->willReturn(true);
        $this->pluginOrder->method('hasNewTrackingInformation')->willReturn(true);
        $this->pluginOrder->method('hasBeenRefunded')->willReturn(true);

        $this->commandFacade
            ->expects($this->once())
            ->method('reportRefund');
        $this->commandFacade
            ->expects($this->never())
            ->method('reportShipping');
        $this->commandFacade
            ->expects($this->never())
            ->method('reportTrackingInformation');

        $this->sut->sync();

        $this->assertInstanceOf(CompletelyRefundedState::class, $this->sut->getCurrentState());
    }

    /**
     * @return void
     */
    public function test_sync_stays_in_invoiced_state()
    {
        $this->pluginOrder->method('hasBeenShipped')->willReturn(true);
        $this->pluginOrder->method('hasNewTrackingInformation')->willReturn(true);
        $this->pluginOrder->method('hasBeenRefunded')->willReturn(false);

        $this->commandFacade
            ->expects($this->never())
            ->method('reportRefund');
        $this->commandFacade
            ->expects($this->once())
            ->method('reportShipping');
        $this->commandFacade
            ->expects($this->once())
            ->method('reportTrackingInformation');

        $this->sut->sync();

        $this->assertInstanceOf(InvoicedState::class, $this->sut->getCurrentState());
    }
}
