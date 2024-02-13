<?php

namespace Axytos\KaufAufRechnung\Core\Tests\Integration;

use Axytos\KaufAufRechnung\Core\Model\OrderStateMachine\States\CanceledState;
use Axytos\KaufAufRechnung\Core\Model\OrderStateMachine\States\CheckoutConfirmedState;
use Axytos\KaufAufRechnung\Core\Model\OrderStateMachine\States\CompletelyRefundedState;
use Axytos\KaufAufRechnung\Core\Model\OrderStateMachine\States\InvoicedState;

class AxytosOrderIntegrationTest extends IntegrationTestCase
{
    /**
     * @return void
     */
    public function test_checkout()
    {
        $this->sut->checkout();

        $this->thenAssertStateIs(CheckoutConfirmedState::class);
        $this->thenAssertBasketFrozen(true);
    }

    /**
     * @return void
     */
    public function test_reportCancel()
    {
        $this->sut->checkout();
        $this->thenAssertStateIs(CheckoutConfirmedState::class);
        $this->thenAssertBasketFrozen(true);

        $this->givenHasBeenCanceled(true);
        $this->sut->sync();
        $this->thenAssertStateIs(CanceledState::class);
    }

    /**
     * @return void
     */
    public function test_reportUncancel()
    {
        $this->sut->checkout();
        $this->thenAssertStateIs(CheckoutConfirmedState::class);
        $this->thenAssertBasketFrozen(true);

        $this->givenHasBeenCanceled(true);
        $this->sut->sync();
        $this->thenAssertStateIs(CanceledState::class);

        $this->givenHasBeenCanceled(false);
        $this->sut->sync();
        $this->thenAssertStateIs(CheckoutConfirmedState::class);
    }

    /**
     * @return void
     */
    public function test_reportCreateInvoice()
    {
        $this->sut->checkout();
        $this->thenAssertStateIs(CheckoutConfirmedState::class);
        $this->thenAssertBasketFrozen(true);

        $this->givenHasBeenInvoiced(true);
        $this->sut->sync();
        $this->thenAssertStateIs(InvoicedState::class);
    }

    /**
     * @return void
     */
    public function test_reportCreateInvoice_with_updates()
    {
        $this->sut->checkout();
        $this->thenAssertStateIs(CheckoutConfirmedState::class);
        $this->thenAssertBasketFrozen(true);

        $this->givenHasBeenInvoiced(true);
        $this->givenHasBasketUpdates(true);
        $this->sut->sync();
        $this->thenAssertStateIs(InvoicedState::class);
        $this->thenAssertBasketUpdateReportedSaved(true);
    }

    /**
     * @return void
     */
    public function test_reportRefund()
    {
        $this->sut->checkout();
        $this->thenAssertStateIs(CheckoutConfirmedState::class);
        $this->thenAssertBasketFrozen(true);

        $this->givenHasBeenInvoiced(true);
        $this->sut->sync();
        $this->thenAssertStateIs(InvoicedState::class);

        $this->givenHasBeenRefunded(true);
        $this->sut->sync();
        $this->thenAssertStateIs(CompletelyRefundedState::class);
    }

    /**
     * @return void
     */
    public function test_reportShipping()
    {
        $this->sut->checkout();
        $this->thenAssertStateIs(CheckoutConfirmedState::class);
        $this->thenAssertBasketFrozen(true);

        $this->givenHasBeenShipped(true);
        $this->sut->sync();
        $this->thenAssertStateIs(CheckoutConfirmedState::class);
        $this->thenAssertShippingReportedSaved(true);
    }

    /**
     * @return void
     */
    public function test_reportShipping_when_invoiced()
    {
        $this->sut->checkout();
        $this->thenAssertStateIs(CheckoutConfirmedState::class);
        $this->thenAssertBasketFrozen(true);

        $this->givenHasBeenInvoiced(true);
        $this->sut->sync();
        $this->thenAssertStateIs(InvoicedState::class);

        $this->givenHasBeenShipped(true);
        $this->sut->sync();
        $this->thenAssertStateIs(InvoicedState::class);
        $this->thenAssertShippingReportedSaved(true);
    }

    /**
     * @return void
     */
    public function test_reportTrackingInformation()
    {
        $this->sut->checkout();
        $this->thenAssertStateIs(CheckoutConfirmedState::class);
        $this->thenAssertBasketFrozen(true);

        $this->givenHasNewTrackingInformation(true);
        $this->sut->sync();
        $this->thenAssertStateIs(CheckoutConfirmedState::class);
        $this->thenAssertNewTrackingInformationSaved(true);
    }

    /**
     * @return void
     */
    public function test_reportTrackingInformation_when_invoiced()
    {
        $this->sut->checkout();
        $this->thenAssertStateIs(CheckoutConfirmedState::class);
        $this->thenAssertBasketFrozen(true);

        $this->givenHasBeenInvoiced(true);
        $this->sut->sync();
        $this->thenAssertStateIs(InvoicedState::class);

        $this->givenHasNewTrackingInformation(true);
        $this->sut->sync();
        $this->thenAssertStateIs(InvoicedState::class);
        $this->thenAssertNewTrackingInformationSaved(true);
    }

    /**
     * @return void
     */
    public function test_reportUpdate()
    {
        $this->sut->checkout();
        $this->thenAssertStateIs(CheckoutConfirmedState::class);
        $this->thenAssertBasketFrozen(true);

        $this->givenHasBasketUpdates(true);
        $this->sut->sync();
        $this->thenAssertStateIs(CheckoutConfirmedState::class);
        $this->thenAssertBasketUpdateReportedSaved(true);
    }
}
