<?php

namespace Axytos\KaufAufRechnung\Core\Tests\Unit;

use Axytos\ECommerce\Clients\ErrorReporting\ErrorReportingClientInterface;
use Axytos\ECommerce\Clients\Invoice\InvoiceClientInterface;
use Axytos\ECommerce\Clients\Invoice\InvoiceOrderPaymentUpdate;
use Axytos\ECommerce\Clients\Invoice\PaymentStatus;
use Axytos\KaufAufRechnung\Core\Model\AxytosOrder;
use Axytos\KaufAufRechnung\Core\Model\AxytosOrderFactory;
use Axytos\KaufAufRechnung\Core\PaymentStatusUpdateWorker;
use Axytos\KaufAufRechnung\Core\Plugin\Abstractions\Logging\LoggerAdapterInterface;
use Axytos\KaufAufRechnung\Core\Plugin\Abstractions\OrderSyncRepositoryInterface;
use Axytos\KaufAufRechnung\Core\Plugin\Abstractions\PluginOrderInterface;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class PaymentStatusUpdateWorkerTest extends TestCase
{
    /**
     * @var \Axytos\KaufAufRechnung\Core\Plugin\Abstractions\OrderSyncRepositoryInterface&MockObject
     */
    private $orderSyncRepository;

    /**
     * @var \Axytos\ECommerce\Clients\Invoice\InvoiceClientInterface&MockObject
     */
    private $invoiceClient;

    /**
     * @var \Axytos\ECommerce\Clients\ErrorReporting\ErrorReportingClientInterface&MockObject
     */
    private $errorReportingClient;

    /**
     * @var \Axytos\KaufAufRechnung\Core\Model\AxytosOrderFactory&MockObject
     */
    private $axytosOrderFactory;

    /**
     * @var PaymentStatusUpdateWorker
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
        $this->orderSyncRepository = $this->createMock(OrderSyncRepositoryInterface::class);
        $this->invoiceClient = $this->createMock(InvoiceClientInterface::class);
        $this->errorReportingClient = $this->createMock(ErrorReportingClientInterface::class);
        $this->axytosOrderFactory = $this->createMock(AxytosOrderFactory::class);

        $this->sut = new PaymentStatusUpdateWorker(
            $this->orderSyncRepository,
            $this->axytosOrderFactory,
            $this->createMock(LoggerAdapterInterface::class),
            $this->invoiceClient,
            $this->errorReportingClient
        );
    }

    /**
     * @dataProvider updatePaymentStatus_test_cases
     *
     * @param string $paymentStatus
     * @param bool   $orderExists
     * @param int    $expectedInvocations
     *
     * @return void
     */
    #[DataProvider('updatePaymentStatus_test_cases')]
    public function test_update_payment_status_updates_order_status_for_payment_status($paymentStatus, $orderExists, $expectedInvocations)
    {
        $testPaymentId = 'payment-id';
        $testOrderId = 'order-id';
        $invoiceOrderPaymentUpdate = new InvoiceOrderPaymentUpdate();
        $invoiceOrderPaymentUpdate->orderId = $testOrderId;
        $invoiceOrderPaymentUpdate->paymentStatus = $paymentStatus;

        /** @var PluginOrderInterface&MockObject */
        $pluginOrder = $this->createMock(PluginOrderInterface::class);

        /** @var AxytosOrder&MockObject */
        $axytosOrder = $this->createMock(AxytosOrder::class);

        $this->axytosOrderFactory
            ->method('create')
            ->with($pluginOrder)
            ->willReturn($axytosOrder)
        ;

        $this->invoiceClient
            ->method('getInvoiceOrderPaymentUpdate')
            ->with($testPaymentId)
            ->willReturn($invoiceOrderPaymentUpdate)
        ;
        if ($orderExists) {
            $this->orderSyncRepository
                ->method('getOrderByOrderNumber')
                ->with($testOrderId)
                ->willReturn($pluginOrder)
            ;
        } else {
            $this->orderSyncRepository
                ->method('getOrderByOrderNumber')
                ->with($testOrderId)
                ->willReturn(null)
            ;
        }

        $axytosOrder
            ->expects($this->exactly($expectedInvocations))
            ->method('syncPaymentStatus')
        ;

        $this->sut->updatePaymentStatus($testPaymentId);
    }

    /**
     * @return array<array<mixed>>
     */
    public static function updatePaymentStatus_test_cases()
    {
        return [
            [PaymentStatus::UNPAID, false, 0],
            [PaymentStatus::UNPAID, true, 1],
            [PaymentStatus::PARTIALLY_PAID, false, 0],
            [PaymentStatus::PARTIALLY_PAID, true, 1],
            [PaymentStatus::PAID, false, 0],
            [PaymentStatus::PAID, true, 1],
            [PaymentStatus::OVERPAID, false, 0],
            [PaymentStatus::OVERPAID, true, 1],
        ];
    }

    /**
     * @return void
     */
    public function ignore_test_updatePaymentStatus_reportsErrorOnExceptions()
    {
        $testException = new \Exception('Test Exception');

        $this->invoiceClient
            ->method('getInvoiceOrderPaymentUpdate')
            ->willThrowException($testException)
        ;
        $this->errorReportingClient
            ->expects($this->once())
            ->method('reportError')
            ->with($testException)
        ;

        $this->sut->updatePaymentStatus('payment-id');
    }
}
