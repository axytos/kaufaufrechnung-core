<?php

namespace Axytos\KaufAufRechnung\Core\Tests\Unit\Model\Commands;

use Axytos\ECommerce\Clients\ErrorReporting\ErrorReportingClientInterface;
use Axytos\ECommerce\Clients\Invoice\InvoiceClientInterface;
use Axytos\FinancialServices\OpenAPI\Client\ApiException;
use Axytos\KaufAufRechnung\Core\Model\Commands\ReportShippingCommand;
use Axytos\KaufAufRechnung\Core\Plugin\Abstractions\Information\ShippingInformationInterface;
use Axytos\KaufAufRechnung\Core\Plugin\Abstractions\Logging\LoggerAdapterInterface;
use Axytos\KaufAufRechnung\Core\Plugin\Abstractions\PluginOrderInterface;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ReportShippingCommandTest extends TestCase
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
     * @var ReportShippingCommand
     */
    private $sut;

    /**
     * @before
     * @return void
     */
    #[Before]
    public function beforeEach()
    {
        $this->pluginOrder = $this->createMock(PluginOrderInterface::class);
        $this->invoiceClient = $this->createMock(InvoiceClientInterface::class);
        $this->errorReportingClient = $this->createMock(ErrorReportingClientInterface::class);

        $this->pluginOrder
            ->method('shippingInformation')
            ->willReturn($this->createMock(ShippingInformationInterface::class));

        $this->sut = new ReportShippingCommand(
            $this->pluginOrder,
            $this->invoiceClient,
            $this->createMock(LoggerAdapterInterface::class),
            $this->errorReportingClient
        );
    }

    /**
     * @return void
     */
    public function test_execute_reports_shipping()
    {
        $this->invoiceClient
            ->expects($this->once())
            ->method('reportShipping');

        $this->sut->execute();
    }

    /**
     * @return void
     */
    public function test_execute_succeeds_and_reports_error_on_client_error()
    {
        $this->invoiceClient
            ->method('reportShipping')
            ->willThrowException(new ApiException("", 400));

        $this->errorReportingClient
            ->expects($this->once())
            ->method('reportError')
            ->with($this->isInstanceOf(ApiException::class));

        $this->sut->execute();
    }

    /**
     * @return void
     */
    public function test_execute_rethrows_exception_on_server_error()
    {
        $this->invoiceClient
            ->method('reportShipping')
            ->willThrowException(new ApiException("", 500));

        $this->expectException(ApiException::class);

        $this->sut->execute();
    }
}
