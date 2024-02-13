<?php

namespace Axytos\KaufAufRechnung\Core\Tests\Unit\Model\OrderStateMachine\States;

use Axytos\KaufAufRechnung\Core\Model\OrderStateMachine\OrderStateContextInterface;
use Axytos\KaufAufRechnung\Core\Model\OrderStateMachine\OrderStates;
use Axytos\KaufAufRechnung\Core\Model\OrderStateMachine\States\CheckoutConfirmedState;
use Axytos\KaufAufRechnung\Core\Plugin\Abstractions\PluginOrderInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Rule\InvocationOrder;
use PHPUnit\Framework\TestCase;

class CheckoutConfirmedStateTest extends TestCase
{
    /**
     * @var OrderStateContextInterface&MockObject
     */
    private $context;

    /**
     * @var PluginOrderInterface&MockObject
     */
    private $pluginOrder;

    /**
     * @var CheckoutConfirmedState
     */
    private $sut;

    /**
     * @before
     * @return void
     */
    public function beforeEach()
    {
        $this->context = $this->createMock(OrderStateContextInterface::class);
        $this->pluginOrder = $this->createMock(PluginOrderInterface::class);

        $this->context
            ->method('getPluginOrder')
            ->willReturn($this->pluginOrder);

        $this->sut = new CheckoutConfirmedState();
        $this->sut->setContext($this->context);
    }

    /**
     * @dataProvider syncCriticalChanges_testData
     * @param bool $invoiced
     * @param bool $cancelled
     * @param string|null $transitionTo
     * @return void
     */
    public function test_syncCriticalChanges_transitionsToCorrectState($invoiced, $cancelled, $transitionTo)
    {
        $this->pluginOrder
            ->method('hasBeenInvoiced')
            ->willReturn($invoiced);
        $this->pluginOrder
            ->method('hasBeenCanceled')
            ->willReturn($cancelled);

        if (!is_null($transitionTo)) {
            $this->context
                ->expects($this->once())
                ->method('changeState')
                ->with($transitionTo);
        } else {
            $this->context
                ->expects($this->never())
                ->method('changeState');
        }

        $this->sut->syncCriticalChanges();
    }

    /**
     * @return mixed[]
     */
    public function syncCriticalChanges_testData()
    {
        return [
            'not invoiced and not canceled -> will do nothing' => [false, false, null],
            'not invoiced and canceled -> transition to canceled' => [false, true, OrderStates::CANCELED],
            'invoiced and not canceled -> transition to invoiced' => [true, false, OrderStates::INVOICED],
            'invoiced and canceled -> transition to canceled' => [true, true, OrderStates::CANCELED],
        ];
    }

    /**
     * @dataProvider syncUncriticalChanges_reportsUpdate_testData
     * @param bool $hasBasketUpdates
     * @param InvocationOrder $reportInvocationCount
     * @param InvocationOrder $saveInvocationCount
     * @return void
     */
    public function test_syncUncriticalChanges_reportsUpdate($hasBasketUpdates, $reportInvocationCount, $saveInvocationCount)
    {
        $this->pluginOrder
            ->method('hasBasketUpdates')
            ->willReturn($hasBasketUpdates);

        $this->context
            ->expects($reportInvocationCount)
            ->method('reportUpdate');
        $this->pluginOrder
            ->expects($saveInvocationCount)
            ->method('saveBasketUpdatesReported');

        $this->sut->syncUncriticalChanges();
    }

    /**
     * @return mixed[]
     */
    public function syncUncriticalChanges_reportsUpdate_testData()
    {
        return [
            'does not have basket updates -> does nothing' => [false, $this->never(), $this->never()],
            'has basket updates -> reports update' => [true, $this->once(), $this->once()],
        ];
    }

    /**
     * @dataProvider syncUncriticalChanges_reportsShipping_testData
     * @param bool $hasBeenShipped
     * @param bool $hasShippingReported
     * @param InvocationOrder $reportInvocationCount
     * @param InvocationOrder $saveInvocationCount
     * @return void
     */
    public function test_syncUncriticalChanges_reportsShipping($hasBeenShipped, $hasShippingReported, $reportInvocationCount, $saveInvocationCount)
    {
        $this->pluginOrder
            ->method('hasBeenShipped')
            ->willReturn($hasBeenShipped);
        $this->pluginOrder
            ->method('hasShippingReported')
            ->willReturn($hasShippingReported);

        $this->context
            ->expects($reportInvocationCount)
            ->method('reportShipping');
        $this->pluginOrder
            ->expects($saveInvocationCount)
            ->method('saveHasShippingReported');

        $this->sut->syncUncriticalChanges();
    }

    /**
     * @return mixed[]
     */
    public function syncUncriticalChanges_reportsShipping_testData()
    {
        return [
            'not shipped and not reported -> does nothing' => [false, false, $this->never(), $this->never()],
            'not shipped and reported -> does nothing' => [false, true, $this->never(), $this->never()],
            'shipped and not reported -> reports shipping' => [true, false, $this->once(), $this->once()],
            'shipped and reported -> does nothing' => [true, true, $this->never(), $this->never()],
        ];
    }

    /**
     * @dataProvider syncUncriticalChanges_reportsTracking_testData
     * @param bool $hasNewTrackingInformation
     * @param InvocationOrder $reportInvocationCount
     * @param InvocationOrder $saveInvocationCount
     * @return void
     */
    public function test_syncUncriticalChanges_reportsTracking($hasNewTrackingInformation, $reportInvocationCount, $saveInvocationCount)
    {
        $this->pluginOrder
            ->method('hasNewTrackingInformation')
            ->willReturn($hasNewTrackingInformation);

        $this->context
            ->expects($reportInvocationCount)
            ->method('reportTrackingInformation');
        $this->pluginOrder
            ->expects($saveInvocationCount)
            ->method('saveNewTrackingInformation');

        $this->sut->syncUncriticalChanges();
    }

    /**
     * @return mixed[]
     */
    public function syncUncriticalChanges_reportsTracking_testData()
    {
        return [
            'does not have tracking updates -> does nothing' => [false, $this->never(), $this->never()],
            'has tracking updates -> reports update' => [true, $this->once(), $this->once()],
        ];
    }
}
