<?php

namespace Axytos\KaufAufRechnung\Core\Tests\Unit\Model\Commands;

use Axytos\ECommerce\Clients\Invoice\InvoiceClientInterface;
use Axytos\KaufAufRechnung\Core\Model\Commands\CheckoutConfirmCommand;
use Axytos\KaufAufRechnung\Core\Plugin\Abstractions\Information\CheckoutInformationInterface;
use Axytos\KaufAufRechnung\Core\Plugin\Abstractions\Logging\LoggerAdapterInterface;
use Axytos\KaufAufRechnung\Core\Plugin\Abstractions\PluginOrderInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CheckoutConfirmCommandTest extends TestCase
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
     * @var CheckoutConfirmCommand
     */
    private $sut;

    /**
     * @before
     * @return void
     */
    public function beforeEach()
    {
        $this->pluginOrder = $this->createMock(PluginOrderInterface::class);
        $this->invoiceClient = $this->createMock(InvoiceClientInterface::class);

        $this->pluginOrder
            ->method('checkoutInformation')
            ->willReturn($this->createMock(CheckoutInformationInterface::class));

        $this->sut = new CheckoutConfirmCommand(
            $this->pluginOrder,
            $this->invoiceClient,
            $this->createMock(LoggerAdapterInterface::class)
        );
    }

    /**
     * @return void
     */
    public function test_execute_confirmsOrder()
    {
        $this->invoiceClient
            ->expects($this->once())
            ->method('confirmOrder');

        $this->sut->execute();
    }
}
