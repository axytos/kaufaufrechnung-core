<?php

namespace Axytos\KaufAufRechnung\Core\Tests\Unit\Model\Commands;

use Axytos\ECommerce\Clients\ErrorReporting\ErrorReportingClientInterface;
use Axytos\ECommerce\Clients\Invoice\InvoiceClientInterface;
use Axytos\FinancialServices\OpenAPI\Client\ApiException;
use Axytos\KaufAufRechnung\Core\Model\Commands\ReportUpdateCommand;
use Axytos\KaufAufRechnung\Core\Plugin\Abstractions\Information\BasketUpdateInformationInterface;
use Axytos\KaufAufRechnung\Core\Plugin\Abstractions\Logging\LoggerAdapterInterface;
use Axytos\KaufAufRechnung\Core\Plugin\Abstractions\PluginOrderInterface;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ReportUpdateCommandTest extends TestCase
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
     * @var ReportUpdateCommand
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
            ->method('basketUpdateInformation')
            ->willReturn($this->createMock(BasketUpdateInformationInterface::class));

        $this->sut = new ReportUpdateCommand(
            $this->pluginOrder,
            $this->invoiceClient,
            $this->createMock(LoggerAdapterInterface::class),
            $this->errorReportingClient
        );
    }

    /**
     * @return void
     */
    public function test_execute_reports_basket_update()
    {
        $this->invoiceClient
            ->expects($this->once())
            ->method('updateOrder');

        $this->sut->execute();
    }

    /**
     * @return void
     */
    public function test_execute_succeeds_and_reports_error_on_client_error()
    {
        $this->invoiceClient
            ->method('updateOrder')
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
            ->method('updateOrder')
            ->willThrowException(new ApiException("", 500));

        $this->expectException(ApiException::class);

        $this->sut->execute();
    }
}
