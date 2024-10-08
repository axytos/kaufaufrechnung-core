<?php

namespace Axytos\KaufAufRechnung\Core\Tests\Unit\Model\Commands;

use Axytos\ECommerce\Clients\ErrorReporting\ErrorReportingClientInterface;
use Axytos\ECommerce\Clients\Invoice\InvoiceClientInterface;
use Axytos\FinancialServices\OpenAPI\Client\ApiException;
use Axytos\KaufAufRechnung\Core\Model\Commands\HasBeenPaidCommand;
use Axytos\KaufAufRechnung\Core\Plugin\Abstractions\Information\PaymentInformationInterface;
use Axytos\KaufAufRechnung\Core\Plugin\Abstractions\Logging\LoggerAdapterInterface;
use Axytos\KaufAufRechnung\Core\Plugin\Abstractions\PluginOrderInterface;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class HasBeenPaidCommandTest extends TestCase
{
    /**
     * @var PluginOrderInterface&MockObject
     */
    private $pluginOrder;

    /**
     * @var InvoiceClientInterface&MockObject
     */
    private $invoiceClient;

    /**
     * @var ErrorReportingClientInterface&MockObject
     */
    private $errorReportingClient;

    /**
     * @var HasBeenPaidCommand
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
        $this->pluginOrder = $this->createMock(PluginOrderInterface::class);
        $this->invoiceClient = $this->createMock(InvoiceClientInterface::class);
        $this->errorReportingClient = $this->createMock(ErrorReportingClientInterface::class);

        $this->pluginOrder
            ->method('paymentInformation')
            ->willReturn($this->createMock(PaymentInformationInterface::class))
        ;

        $this->sut = new HasBeenPaidCommand(
            $this->pluginOrder,
            $this->invoiceClient,
            $this->createMock(LoggerAdapterInterface::class),
            $this->errorReportingClient
        );
    }

    /**
     * @return void
     */
    public function test_has_been_paid_returns_true()
    {
        $this->invoiceClient->method('hasBeenPaid')->willReturn(true);

        $this->sut->execute();

        $this->assertTrue($this->sut->hasBeenPaid());
    }

    /**
     * @return void
     */
    public function test_has_been_paid_returns_false()
    {
        $this->invoiceClient->method('hasBeenPaid')->willReturn(false);

        $this->sut->execute();

        $this->assertFalse($this->sut->hasBeenPaid());
    }

    /**
     * @return void
     */
    public function test_has_been_paid_returns_false_on_client_error()
    {
        $this->invoiceClient
            ->method('hasBeenPaid')
            ->willThrowException(new ApiException('', 400))
        ;

        $this->sut->execute();

        $this->assertFalse($this->sut->hasBeenPaid());
    }

    /**
     * @return void
     */
    public function test_execute_rethrows_exception_on_server_error()
    {
        $this->invoiceClient
            ->method('hasBeenPaid')
            ->willThrowException(new ApiException('', 500))
        ;

        $this->expectException(ApiException::class);

        $this->sut->execute();
    }
}
