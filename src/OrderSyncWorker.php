<?php

namespace Axytos\KaufAufRechnung\Core;

use Axytos\KaufAufRechnung\Core\Model\AxytosOrderFactory;
use Axytos\KaufAufRechnung\Core\Model\OrderStateMachine\OrderStates;
use Axytos\KaufAufRechnung\Core\Plugin\Abstractions\Logging\LoggerAdapterInterface;
use Axytos\KaufAufRechnung\Core\Plugin\Abstractions\OrderSyncRepositoryInterface;

class OrderSyncWorker
{
    const SYNCABLE_STATES = [
        OrderStates::CHECKOUT_CONFIRMED,
        OrderStates::INVOICED,
        OrderStates::CANCELED,
    ];

    /**
     * @var \Axytos\KaufAufRechnung\Core\Plugin\Abstractions\OrderSyncRepositoryInterface
     */
    private $orderSyncRepository;

    /**
     * @var \Axytos\KaufAufRechnung\Core\Plugin\Abstractions\Logging\LoggerAdapterInterface
     */
    private $logger;

    /**
     * @var \Axytos\KaufAufRechnung\Core\Model\AxytosOrderFactory
     */
    private $axytosOrderFactory;

    public function __construct(
        OrderSyncRepositoryInterface $orderSyncRepository,
        AxytosOrderFactory $axytosOrderFactory,
        LoggerAdapterInterface $logger
    ) {
        $this->orderSyncRepository = $orderSyncRepository;
        $this->axytosOrderFactory = $axytosOrderFactory;
        $this->logger = $logger;
    }

    /**
     * @param int|null $batchSize
     * @param string|null $firstOrderId
     * @return string|null
     */
    public function sync($batchSize = null, $firstOrderId = null)
    {
        if (is_null($batchSize)) {
            $this->logger->info('OrderSyncWorker started');
        } else {
            $this->logger->info("OrderSyncWorker started (batch size: $batchSize, first order: $firstOrderId)");
        }

        $nextToken = $this->processSync($batchSize, $firstOrderId);

        $this->logger->info('OrderSyncWorker finished');
        return $nextToken;
    }

    /**
     * @param int|null $batchSize
     * @param string|null $firstOrderId
     * @return string|null
     */
    private function processSync($batchSize, $firstOrderId)
    {
        $pluginOrders = $this->orderSyncRepository->getOrdersByStates(
            self::SYNCABLE_STATES,
            is_int($batchSize) ? $batchSize + 1 : null,
            $firstOrderId
        );

        //reset array keys to 0,1,...,n-1
        $pluginOrders = array_values($pluginOrders);

        $syncableOrders = array_slice($pluginOrders, 0, $batchSize);
        $axytosOrders = $this->axytosOrderFactory->createMany($syncableOrders);

        $this->logger->info('OrderSyncWorker: ' . count($axytosOrders) . ' to sync.');

        foreach ($axytosOrders as $axytosOrder) {
            $axytosOrder->sync();
        }

        if (is_int($batchSize) && count($pluginOrders) > $batchSize) {
            return strval($pluginOrders[$batchSize]->getOrderNumber());
        } else {
            return null;
        }
    }
}
