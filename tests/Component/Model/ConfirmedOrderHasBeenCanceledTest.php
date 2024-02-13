<?php

namespace Axytos\KaufAufRechnung\Core\Tests\Component\Model;

use Axytos\KaufAufRechnung\Core\Model\OrderStateMachine\OrderStates;
use Axytos\KaufAufRechnung\Core\Model\OrderStateMachine\States\CanceledState;
use Axytos\KaufAufRechnung\Core\Model\OrderStateMachine\States\CheckoutConfirmedState;
use Axytos\KaufAufRechnung\Core\Model\OrderStateMachine\States\InvoicedState;
use PHPUnit\Framework\MockObject\IncompatibleReturnValueException;
use PHPUnit\Framework\InvalidArgumentException;
use PHPUnit\Framework\MockObject\MethodCannotBeConfiguredException;
use PHPUnit\Framework\MockObject\MethodNameAlreadyConfiguredException;
use SebastianBergmann\RecursionContext\InvalidArgumentException as RecursionContextInvalidArgumentException;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\ExpectationFailedException;

class ConfirmedOrderHasBeenCanceledTest extends AxytosOrderTestCase
{
    /**
     * @return string
     * @phpstan-return \Axytos\KaufAufRechnung\Core\Model\OrderStateMachine\OrderStates::*
     */
    protected function initialState()
    {
        return OrderStates::CHECKOUT_CONFIRMED;
    }

    /**
     * @return void
     */
    public function test_sync_staysInConfirmedStateAndSyncsUncriticalsIfOrderIsNotCanceled()
    {
        $this->pluginOrder
            ->method('hasBeenCanceled')
            ->willReturn(false);
        $this->pluginOrder
            ->method('hasBasketUpdates')
            ->willReturn(true);
        $this->pluginOrder
            ->method('hasShippingReported')
            ->willReturn(false);
        $this->pluginOrder
            ->method('hasBeenShipped')
            ->willReturn(true);
        $this->pluginOrder
            ->method('hasNewTrackingInformation')
            ->willReturn(true);

        $this->commandFacade
            ->expects($this->never())
            ->method('reportCreateInvoice');
        $this->commandFacade
            ->expects($this->once())
            ->method('reportUpdate');
        $this->commandFacade
            ->expects($this->once())
            ->method('reportShipping');
        $this->commandFacade
            ->expects($this->once())
            ->method('reportTrackingInformation');

        $this->sut->sync();

        $this->assertInstanceOf(CheckoutConfirmedState::class, $this->sut->getCurrentState());
    }

    /**
     * @return void
     */
    public function test_sync_transitionsToCanceledWhenOrderHasBeenCanceled()
    {
        $this->pluginOrder
            ->method('hasBeenCanceled')
            ->willReturn(true);

        $this->commandFacade
            ->expects($this->once())
            ->method('reportCancel');
        $this->commandFacade
            ->expects($this->never())
            ->method('reportUpdate');
        $this->commandFacade
            ->expects($this->never())
            ->method('reportShipping');
        $this->commandFacade
            ->expects($this->never())
            ->method('reportTrackingInformation');

        $this->sut->sync();

        $this->assertInstanceOf(CanceledState::class, $this->sut->getCurrentState());
    }
}
