<?php

namespace Axytos\KaufAufRechnung\Core;

use Axytos\ECommerce\Clients\ErrorReporting\ErrorReportingClientInterface;
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

    /**
     * @var \Axytos\ECommerce\Clients\ErrorReporting\ErrorReportingClientInterface
     */
    private $errorReportingClient;

    public function __construct(
        OrderSyncRepositoryInterface $orderSyncRepository,
        AxytosOrderFactory $axytosOrderFactory,
        LoggerAdapterInterface $logger,
        ErrorReportingClientInterface $errorReportingClient
    ) {
        $this->orderSyncRepository = $orderSyncRepository;
        $this->axytosOrderFactory = $axytosOrderFactory;
        $this->logger = $logger;
        $this->errorReportingClient = $errorReportingClient;
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

        /** @var \Axytos\KaufAufRechnung\Core\Plugin\Abstractions\PluginOrderInterface[] */
        $syncableOrders = array_slice($pluginOrders, 0, $batchSize);

        $this->logger->info('OrderSyncWorker: ' . count($syncableOrders) . ' to sync.');

        foreach ($syncableOrders as $syncableOrder) {
            try {
                $this->logger->info('OrderSyncWorker: syncing order ' . $syncableOrder->getOrderNumber());
                $axytosOrder = $this->axytosOrderFactory->create($syncableOrder);
                $axytosOrder->sync();
            } catch (\Throwable $th) {
                $this->logger->error('OrderSyncWorker: error syncing order ' . $syncableOrder->getOrderNumber() . ': ' . $th->getMessage());
                $this->errorReportingClient->reportError($th);
            }
        }

        if (is_int($batchSize) && count($pluginOrders) > $batchSize) {
            return strval($pluginOrders[$batchSize]->getOrderNumber());
        } else {
            return null;
        }
    }
}
