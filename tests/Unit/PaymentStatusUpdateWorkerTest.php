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
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Rule\InvocationOrder;
use PHPUnit\Framework\TestCase;

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
     * @var \Axytos\KaufAufRechnung\Core\PaymentStatusUpdateWorker
     */
    private $sut;

    /**
     * @before
     * @return void
     */
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
     * @param string $paymentStatus
     * @param bool $orderExists
     * @param InvocationOrder $expectedInvocations
     * @return void
     */
    public function test_updatePaymentStatus_updatesOrderStatusForPaymentStatus($paymentStatus, $orderExists, $expectedInvocations)
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
            ->willReturn($axytosOrder);

        $this->invoiceClient
            ->method('getInvoiceOrderPaymentUpdate')
            ->with($testPaymentId)
            ->willReturn($invoiceOrderPaymentUpdate);
        if ($orderExists) {
            $this->orderSyncRepository
                ->method('getOrderByOrderNumber')
                ->with($testOrderId)
                ->willReturn($pluginOrder);
        } else {
            $this->orderSyncRepository
                ->method('getOrderByOrderNumber')
                ->with($testOrderId)
                ->willReturn(null);
        }

        $axytosOrder
            ->expects($expectedInvocations)
            ->method('syncPaymentStatus');

        $this->sut->updatePaymentStatus($testPaymentId);
    }

    /**
     * @return void
     */
    public function ignore_test_updatePaymentStatus_reportsErrorOnExceptions()
    {
        $testException = new \Exception("Test Exception");

        $this->invoiceClient
            ->method('getInvoiceOrderPaymentUpdate')
            ->willThrowException($testException);
        $this->errorReportingClient
            ->expects($this->once())
            ->method('reportError')
            ->with($testException);

        $this->sut->updatePaymentStatus('payment-id');
    }

    /**
     * @return array<array<mixed>>
     */
    public function updatePaymentStatus_test_cases()
    {
        return [
            [PaymentStatus::UNPAID, false, $this->never()],
            [PaymentStatus::UNPAID, true, $this->once()],
            [PaymentStatus::PARTIALLY_PAID, false, $this->never()],
            [PaymentStatus::PARTIALLY_PAID, true, $this->once()],
            [PaymentStatus::PAID, false, $this->never()],
            [PaymentStatus::PAID, true, $this->once()],
            [PaymentStatus::OVERPAID, false, $this->never()],
            [PaymentStatus::OVERPAID, true, $this->once()],
        ];
    }
}
