<?php

namespace Axytos\KaufAufRechnung\Core\Tests\Unit\Model\Commands;

use Axytos\ECommerce\Clients\Invoice\InvoiceClientInterface;
use Axytos\ECommerce\Clients\Invoice\ShopActions;
use Axytos\KaufAufRechnung\Core\Model\Commands\CheckoutPrecheckCommand;
use Axytos\KaufAufRechnung\Core\Plugin\Abstractions\Information\CheckoutInformationInterface;
use Axytos\KaufAufRechnung\Core\Plugin\Abstractions\Logging\LoggerAdapterInterface;
use Axytos\KaufAufRechnung\Core\Plugin\Abstractions\PluginOrderInterface;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class CheckoutPrecheckCommandTest extends TestCase
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
     * @var CheckoutPrecheckCommand
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

        $this->pluginOrder
            ->method('checkoutInformation')
            ->willReturn($this->createMock(CheckoutInformationInterface::class))
        ;

        $this->sut = new CheckoutPrecheckCommand(
            $this->pluginOrder,
            $this->invoiceClient,
            $this->createMock(LoggerAdapterInterface::class)
        );
    }

    /**
     * @return void
     */
    public function test_execute_prechecks_order_with_change_payment_method()
    {
        $this->invoiceClient
            ->expects($this->once())
            ->method('precheck')
            ->willReturn(ShopActions::CHANGE_PAYMENT_METHOD)
        ;

        $this->sut->execute();

        $this->assertEquals(ShopActions::CHANGE_PAYMENT_METHOD, $this->sut->getShopAction());
    }

    /**
     * @return void
     */
    public function test_execute_prechecks_order_with_complete_order()
    {
        $this->invoiceClient
            ->expects($this->once())
            ->method('precheck')
            ->willReturn(ShopActions::COMPLETE_ORDER)
        ;

        $this->sut->execute();

        $this->assertEquals(ShopActions::COMPLETE_ORDER, $this->sut->getShopAction());
    }
}
