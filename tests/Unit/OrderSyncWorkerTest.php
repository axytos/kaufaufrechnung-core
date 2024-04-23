<?php

namespace Axytos\KaufAufRechnung\Core\Tests\Unit;

use Axytos\KaufAufRechnung\Core\Model\AxytosOrder;
use Axytos\KaufAufRechnung\Core\OrderSyncWorker;
use Axytos\KaufAufRechnung\Core\Plugin\Abstractions\Logging\LoggerAdapterInterface;
use Axytos\KaufAufRechnung\Core\Model\AxytosOrderFactory;
use Axytos\KaufAufRechnung\Core\Plugin\Abstractions\OrderSyncRepositoryInterface;
use Axytos\KaufAufRechnung\Core\Plugin\Abstractions\PluginOrderInterface;
use AxytosKaufAufRechnungShopware5\Adapter\PluginOrder;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class OrderSyncWorkerTest extends TestCase
{
    /**
     * @var \Axytos\KaufAufRechnung\Core\Plugin\Abstractions\OrderSyncRepositoryInterface&MockObject
     */
    private $orderSyncRepository;

    /**
     * @var \Axytos\KaufAufRechnung\Core\Model\AxytosOrderFactory&MockObject
     */
    private $axytosOrderFactory;

    /**
     * @var \Axytos\KaufAufRechnung\Core\OrderSyncWorker
     */
    private $sut;

    /**
     * @before
     * @return void
     */
    #[Before]
    public function beforeEach()
    {
        $this->orderSyncRepository = $this->createMock(OrderSyncRepositoryInterface::class);
        $this->axytosOrderFactory = $this->createMock(AxytosOrderFactory::class);

        $this->sut = new OrderSyncWorker(
            $this->orderSyncRepository,
            $this->axytosOrderFactory,
            $this->createMock(LoggerAdapterInterface::class)
        );
    }

    /**
     * @return void
     */
    public function test_sync_executes_command_for_each_order()
    {
        /** @var PluginOrderInterface[]&MockObject[] */
        $ordersToSync = [
            $this->createMock(PluginOrderInterface::class),
            $this->createMock(PluginOrderInterface::class),
            $this->createMock(PluginOrderInterface::class),
            $this->createMock(PluginOrderInterface::class),
            $this->createMock(PluginOrderInterface::class),
        ];

        $executionCounts = [
            'sync' => 0
        ];

        $this->orderSyncRepository
            ->expects($this->once())
            ->method('getOrdersByStates')
            ->with(OrderSyncWorker::SYNCABLE_STATES)
            ->willReturn($ordersToSync);

        $this->axytosOrderFactory->method('createMany')->willReturnCallback(function ($array) use (&$executionCounts) {
            return array_map(function () use (&$executionCounts) {
                /** @var AxytosOrder&MockObject */
                $axytosOrder = $this->createMock(AxytosOrder::class);
                $axytosOrder->method('sync')->willReturnCallback(function () use (&$executionCounts) {
                    $executionCounts['sync']++;
                });
                return $axytosOrder;
            }, $array);
        });


        $this->sut->sync();

        $this->assertEquals(5, $executionCounts['sync']);
    }

    /**
     * @return void
     */
    public function test_sync_passesParametersToRepository()
    {
        $batchSize = 42;
        $startToken = 'start-token';
        $expectedNextToken = 'next-token';

        /** @var PluginOrder&MockObject */
        $finalOrder = $this->createMock(PluginOrder::class);
        $pluginOrders = array_fill(0, $batchSize, $this->createMock(PluginOrder::class));
        $pluginOrders[] = $finalOrder;
        /** @var AxytosOrder&MockObject */
        $axytosOrder = $this->createMock(AxytosOrder::class);
        $axytosOrders = array_fill(0, $batchSize, $axytosOrder);

        $this->orderSyncRepository
            ->expects($this->once())
            ->method('getOrdersByStates')
            ->with(OrderSyncWorker::SYNCABLE_STATES, $batchSize + 1, $startToken)
            ->willReturn($pluginOrders);

        $this->axytosOrderFactory
            ->method('createMany')
            ->with(array_slice($pluginOrders, 0, $batchSize))
            ->willReturn($axytosOrders);

        $finalOrder
            ->method('getOrderNumber')
            ->willReturn($expectedNextToken);

        $axytosOrder
            ->expects($this->exactly($batchSize))
            ->method('sync');

        $result = $this->sut->sync($batchSize, $startToken);

        $this->assertEquals($expectedNextToken, $result);
    }

    /**
     * @return void
     */
    public function test_sync_returnsNoNextTokenWhenDone()
    {
        $expectedBatchSize = 42;

        $pluginOrders = array_fill(0, 10, $this->createMock(PluginOrder::class));
        /** @var AxytosOrder&MockObject */
        $axytosOrder = $this->createMock(AxytosOrder::class);
        $axytosOrders = array_fill(0, 10, $axytosOrder);

        $this->orderSyncRepository
            ->expects($this->once())
            ->method('getOrdersByStates')
            ->with(OrderSyncWorker::SYNCABLE_STATES, $expectedBatchSize + 1)
            ->willReturn($pluginOrders);

        $this->axytosOrderFactory
            ->method('createMany')
            ->with($pluginOrders)
            ->willReturn($axytosOrders);

        $axytosOrder
            ->expects($this->exactly(10))
            ->method('sync');

        $result = $this->sut->sync($expectedBatchSize);

        $this->assertNull($result);
    }
}
