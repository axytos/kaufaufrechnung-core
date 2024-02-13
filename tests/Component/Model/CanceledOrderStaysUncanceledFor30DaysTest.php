<?php

namespace Axytos\KaufAufRechnung\Core\Tests\Component\Model;

use Axytos\KaufAufRechnung\Core\Model\OrderStateMachine\OrderStates;
use Axytos\KaufAufRechnung\Core\Model\OrderStateMachine\States\CanceledState;
use Axytos\KaufAufRechnung\Core\Model\OrderStateMachine\States\CheckoutConfirmedState;
use Axytos\KaufAufRechnung\Core\Model\OrderStateMachine\States\CompletelyCanceledState;

class CanceledOrderStaysUncanceledFor30DaysTest extends AxytosOrderTestCase
{
    /**
     * @return string
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
            CanceledState::CANCEL_DATE => time() - 2678400, // 31 days
        ];
    }

    /**
     * @return void
     */
    public function test_sync_TransitionsToCompletelyCanceled()
    {
        $this->pluginOrder
            ->method('hasBeenCanceled')
            ->willReturn(true);

        $this->commandFacade->expects($this->never())->method('reportUncancel');

        $this->sut->sync();

        $this->assertInstanceOf(CompletelyCanceledState::class, $this->sut->getCurrentState());
    }

    /**
     * @return void
     */
    public function test_sync_TransitionsToConfirmedIfUncanceledAnyways()
    {
        $this->pluginOrder
            ->method('hasBeenCanceled')
            ->willReturn(false);

        $this->commandFacade->expects($this->once())->method('reportUncancel');

        $this->sut->sync();

        $this->assertInstanceOf(CheckoutConfirmedState::class, $this->sut->getCurrentState());
    }
}
