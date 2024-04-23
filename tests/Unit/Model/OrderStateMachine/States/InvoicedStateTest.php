<?php

namespace Axytos\KaufAufRechnung\Core\Tests\Unit\Model\OrderStateMachine\States;

use Axytos\KaufAufRechnung\Core\Model\OrderStateMachine\OrderStateContextInterface;
use Axytos\KaufAufRechnung\Core\Model\OrderStateMachine\OrderStates;
use Axytos\KaufAufRechnung\Core\Model\OrderStateMachine\States\InvoicedState;
use Axytos\KaufAufRechnung\Core\Plugin\Abstractions\PluginOrderInterface;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class InvoicedStateTest extends TestCase
{
    /**
     * @var \Axytos\KaufAufRechnung\Core\Model\OrderStateMachine\States\InvoicedState
     */
    private $sut;

    /**
     * @before
     * @return void
     */
    #[Before]
    public function beforeEach()
    {
        $this->sut = new InvoicedState();
    }

    /**
     * @return void
     */
    public function test_syncCriticalChanges_completelyRefundedReportsRefundedOrder()
    {
        /** @var PluginOrderInterface&MockObject */
        $pluginOrder = $this->createMock(PluginOrderInterface::class);

        /** @var OrderStateContextInterface&MockObject */
        $context = $this->createMock(OrderStateContextInterface::class);
        $context->method('getPluginOrder')->willReturn($pluginOrder);

        $pluginOrder->method('hasBeenRefunded')->willReturn(true);
        $context->method('hasBeenPaid')->willReturn(false);

        $pluginOrder->expects($this->never())->method('saveHasBeenPaid');
        $context->expects($this->once())->method('reportRefund');
        $context->expects($this->once())->method('changeState')->with(OrderStates::COMPLETELY_REFUNDED);

        $this->sut->setContext($context);
        $this->sut->syncCriticalChanges();
    }

    /**
     * @return void
     */
    public function test_syncCriticalChanges_completely_paid()
    {
        /** @var PluginOrderInterface&MockObject */
        $pluginOrder = $this->createMock(PluginOrderInterface::class);

        /** @var OrderStateContextInterface&MockObject */
        $context = $this->createMock(OrderStateContextInterface::class);
        $context->method('getPluginOrder')->willReturn($pluginOrder);

        $pluginOrder->method('hasBeenRefunded')->willReturn(false);
        $context->method('hasBeenPaid')->willReturn(true);

        $pluginOrder->expects($this->once())->method('saveHasBeenPaid');
        $context->expects($this->never())->method('reportRefund');
        $context->expects($this->once())->method('changeState')->with(OrderStates::COMPLETELY_PAID);

        $this->sut->setContext($context);
        $this->sut->syncCriticalChanges();
    }

    /**
     * @return void
     */
    public function test_syncCriticalChanges_updatesStatusOfPaidOrderEvenIfRefunded()
    {
        /** @var PluginOrderInterface&MockObject */
        $pluginOrder = $this->createMock(PluginOrderInterface::class);
        $pluginOrder->method('hasBeenRefunded')->willReturn(true);

        /** @var OrderStateContextInterface&MockObject */
        $context = $this->createMock(OrderStateContextInterface::class);
        $context->method('getPluginOrder')->willReturn($pluginOrder);
        $context->method('hasBeenPaid')->willReturn(true);

        $pluginOrder->expects($this->once())->method('saveHasBeenPaid');
        $context->expects($this->never())->method('reportRefund');
        $context->expects($this->once())->method('changeState')->with(OrderStates::COMPLETELY_PAID);

        $this->sut->setContext($context);
        $this->sut->syncCriticalChanges();
    }

    /**
     * @return void
     */
    public function test_syncCriticalChanges_doesNothingIfNeitherPaidNorRefunded()
    {
        /** @var PluginOrderInterface&MockObject */
        $pluginOrder = $this->createMock(PluginOrderInterface::class);

        /** @var OrderStateContextInterface&MockObject */
        $context = $this->createMock(OrderStateContextInterface::class);
        $context->method('getPluginOrder')->willReturn($pluginOrder);

        $pluginOrder->expects($this->never())->method('saveHasBeenPaid');
        $context->expects($this->never())->method('reportRefund');
        $context->expects($this->never())->method('changeState');

        $this->sut->setContext($context);
        $this->sut->syncCriticalChanges();
    }

    /**
     * @return void
     */
    public function test_syncUncriticalChanges()
    {
        /** @var PluginOrderInterface&MockObject */
        $pluginOrder = $this->createMock(PluginOrderInterface::class);
        $pluginOrder->method('hasShippingReported')->willReturn(false);
        $pluginOrder->method('hasBeenShipped')->willReturn(true);
        $pluginOrder->method('hasNewTrackingInformation')->willReturn(true);

        /** @var OrderStateContextInterface&MockObject */
        $context = $this->createMock(OrderStateContextInterface::class);
        $context->method('getPluginOrder')->willReturn($pluginOrder);

        $context->expects($this->once())->method('reportShipping');
        $context->expects($this->once())->method('reportTrackingInformation');

        $this->sut->setContext($context);
        $this->sut->syncUncriticalChanges();
    }

    /**
     * @return void
     */
    public function test_syncUncriticalChanges_onlySyncsRelevantChanges()
    {
        /** @var PluginOrderInterface&MockObject */
        $pluginOrder = $this->createMock(PluginOrderInterface::class);
        $pluginOrder->method('hasShippingReported')->willReturn(true);
        $pluginOrder->method('hasBeenShipped')->willReturn(true);
        $pluginOrder->method('hasNewTrackingInformation')->willReturn(false);

        /** @var OrderStateContextInterface&MockObject */
        $context = $this->createMock(OrderStateContextInterface::class);
        $context->method('getPluginOrder')->willReturn($pluginOrder);

        $context->expects($this->never())->method('reportShipping');
        $context->expects($this->never())->method('reportTrackingInformation');

        $this->sut->setContext($context);
        $this->sut->syncUncriticalChanges();
    }

    /**
     * @return void
     */
    public function test_syncPaymentStatus_updatesPaymentStatus()
    {
        /** @var PluginOrderInterface&MockObject */
        $pluginOrder = $this->createMock(PluginOrderInterface::class);

        /** @var OrderStateContextInterface&MockObject */
        $context = $this->createMock(OrderStateContextInterface::class);
        $context->method('getPluginOrder')->willReturn($pluginOrder);
        $context->method('hasBeenPaid')->willReturn(true);

        $pluginOrder->expects($this->once())->method('saveHasBeenPaid');
        $context->expects($this->once())->method('changeState')->with(OrderStates::COMPLETELY_PAID);

        $this->sut->setContext($context);
        $this->sut->syncPaymentStatus();
    }

    /**
     * @return void
     */
    public function test_syncPaymentStatus_doesNothingIfPaymentStatusDidNotChange()
    {
        /** @var PluginOrderInterface&MockObject */
        $pluginOrder = $this->createMock(PluginOrderInterface::class);

        /** @var OrderStateContextInterface&MockObject */
        $context = $this->createMock(OrderStateContextInterface::class);
        $context->method('hasBeenPaid')->willReturn(false);
        $context->method('getPluginOrder')->willReturn($pluginOrder);

        $pluginOrder->expects($this->never())->method('saveHasBeenPaid');
        $context->expects($this->never())->method('changeState');

        $this->sut->setContext($context);
        $this->sut->syncPaymentStatus();
    }
}
