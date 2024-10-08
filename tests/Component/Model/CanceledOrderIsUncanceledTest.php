<?php

namespace Axytos\KaufAufRechnung\Core\Tests\Component\Model;

use Axytos\KaufAufRechnung\Core\Model\OrderStateMachine\OrderStates;
use Axytos\KaufAufRechnung\Core\Model\OrderStateMachine\States\CanceledState;
use Axytos\KaufAufRechnung\Core\Model\OrderStateMachine\States\CheckoutConfirmedState;

/**
 * @internal
 */
class CanceledOrderIsUncanceledTest extends AxytosOrderTestCase
{
    /**
     * @return string
     *
     * @phpstan-return \Axytos\KaufAufRechnung\Core\Model\OrderStateMachine\OrderStates::*
     */
    protected function initialState()
    {
        return OrderStates::CANCELED;
    }

    /**
     * @return array<string, mixed>
     */
    protected function initialStateData()
    {
        return [
            CanceledState::CANCEL_DATE => time(),
        ];
    }

    /**
     * @return void
     */
    public function test_sync_does_nothing_if_order_is_still_canceled()
    {
        $this->pluginOrder
            ->method('hasBeenCanceled')
            ->willReturn(true)
        ;

        $this->commandFacade
            ->expects($this->never())
            ->method('reportUncancel')
        ;

        $this->sut->sync();

        $this->assertInstanceOf(CanceledState::class, $this->sut->getCurrentState());
    }

    /**
     * @return void
     */
    public function test_sync_transitions_to_confirmed_if_order_is_not_canceled()
    {
        $this->pluginOrder
            ->method('hasBeenCanceled')
            ->willReturn(false)
        ;

        $this->commandFacade
            ->expects($this->once())
            ->method('reportUncancel')
        ;

        $this->sut->sync();

        $this->assertInstanceOf(CheckoutConfirmedState::class, $this->sut->getCurrentState());
    }
}
