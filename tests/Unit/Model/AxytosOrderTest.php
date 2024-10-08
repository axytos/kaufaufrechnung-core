<?php

namespace Axytos\KaufAufRechnung\Core\Tests\Unit\Model;

use Axytos\ECommerce\Clients\ErrorReporting\ErrorReportingClientInterface;
use Axytos\KaufAufRechnung\Core\Abstractions\Model\AxytosOrderCheckoutAction;
use Axytos\KaufAufRechnung\Core\Model\AxytosOrderCommandFacade;
use Axytos\KaufAufRechnung\Core\Model\AxytosOrderFactory;
use Axytos\KaufAufRechnung\Core\Model\OrderStateMachine\OrderStates;
use Axytos\KaufAufRechnung\Core\Plugin\Abstractions\Database\DatabaseTransactionFactoryInterface;
use Axytos\KaufAufRechnung\Core\Plugin\Abstractions\Logging\LoggerAdapterInterface;
use Axytos\KaufAufRechnung\Core\Plugin\Abstractions\Model\AxytosOrderStateInfo;
use Axytos\KaufAufRechnung\Core\Plugin\Abstractions\PluginOrderInterface;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class AxytosOrderTest extends TestCase
{
    /**
     * @var \Axytos\KaufAufRechnung\Core\Plugin\Abstractions\PluginOrderInterface&MockObject
     */
    private $pluginOrder;

    /**
     * @var \Axytos\ECommerce\Clients\ErrorReporting\ErrorReportingClientInterface&MockObject
     */
    private $errorReportingClient;

    /**
     * @var AxytosOrderFactory
     */
    private $orderFactory;

    /**
     * @before
     *
     * @return void
     */
    #[Before]
    public function beforeEach()
    {
        $this->errorReportingClient = $this->createMock(ErrorReportingClientInterface::class);

        $this->pluginOrder = $this->createMock(PluginOrderInterface::class);

        $this->orderFactory = new AxytosOrderFactory(
            $this->errorReportingClient,
            $this->createMock(DatabaseTransactionFactoryInterface::class),
            $this->createMock(AxytosOrderCommandFacade::class),
            $this->createMock(LoggerAdapterInterface::class)
        );
    }

    /**
     * @dataProvider getOrderCheckoutAction_test_cases
     *
     * @param string $orderState
     * @param string $expected
     *
     * @phpstan-param \Axytos\KaufAufRechnung\Core\Model\OrderStateMachine\OrderStates::* $orderState
     * @phpstan-param \Axytos\KaufAufRechnung\Core\Abstractions\Model\AxytosOrderCheckoutAction::* $expected
     *
     * @return void
     */
    #[DataProvider('getOrderCheckoutAction_test_cases')]
    public function test_get_order_checkout_action($orderState, $expected)
    {
        $this->pluginOrder
            ->method('loadState')
            ->willReturn(new AxytosOrderStateInfo($orderState))
        ;

        $sut = $this->orderFactory->create($this->pluginOrder);
        $actual = $sut->getOrderCheckoutAction();

        $this->assertEquals($expected, $actual);
    }

    /**
     * @return array<array<mixed>>
     */
    public static function getOrderCheckoutAction_test_cases()
    {
        return [
            ['' /* unchecked */, AxytosOrderCheckoutAction::CHANGE_PAYMENT_METHOD],
            [OrderStates::CHECKOUT_REJECTED, AxytosOrderCheckoutAction::CHANGE_PAYMENT_METHOD],
            [OrderStates::CHECKOUT_CONFIRMED, AxytosOrderCheckoutAction::COMPLETE_CHECKOUT],
            [OrderStates::CHECKOUT_FAILED, AxytosOrderCheckoutAction::CHANGE_PAYMENT_METHOD],
        ];
    }

    /**
     * @dataProvider getOrderCheckoutAction_error_cases
     *
     * @param string $orderState
     *
     * @phpstan-param \Axytos\KaufAufRechnung\Core\Model\OrderStateMachine\OrderStates::* $orderState
     *
     * @return void
     */
    #[DataProvider('getOrderCheckoutAction_error_cases')]
    public function test_get_order_checkout_action_throws_for_unsupported_states($orderState)
    {
        $this->pluginOrder
            ->method('loadState')
            ->willReturn(new AxytosOrderStateInfo($orderState))
        ;

        $this->expectException(\Exception::class);

        $sut = $this->orderFactory->create($this->pluginOrder);
        $sut->getOrderCheckoutAction();
    }

    /**
     * @return array<array<mixed>>
     */
    public static function getOrderCheckoutAction_error_cases()
    {
        return [
            [OrderStates::INVOICED],
            [OrderStates::CANCELED],
            [OrderStates::COMPLETELY_CANCELED],
            [OrderStates::COMPLETELY_PAID],
            [OrderStates::COMPLETELY_REFUNDED],
        ];
    }
}
