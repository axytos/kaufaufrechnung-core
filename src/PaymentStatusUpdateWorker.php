<?php

namespace Axytos\KaufAufRechnung\Core;

use Axytos\ECommerce\Clients\ErrorReporting\ErrorReportingClientInterface;
use Axytos\ECommerce\Clients\Invoice\InvoiceClientInterface;
use Axytos\KaufAufRechnung\Core\Model\AxytosOrderFactory;
use Axytos\KaufAufRechnung\Core\Plugin\Abstractions\Logging\LoggerAdapterInterface;
use Axytos\KaufAufRechnung\Core\Plugin\Abstractions\OrderSyncRepositoryInterface;

class PaymentStatusUpdateWorker
{
    /**
     * @var OrderSyncRepositoryInterface
     */
    private $orderSyncRepository;

    /**
     * @var LoggerAdapterInterface
     */
    private $logger;

    /**
     * @var InvoiceClientInterface
     */
    private $invoiceClient;

    /**
     * @var ErrorReportingClientInterface
     */
    private $errorReportingClient;

    /**
     * @var AxytosOrderFactory
     */
    private $axytosOrderFactory;

    public function __construct(
        OrderSyncRepositoryInterface $orderSyncRepository,
        AxytosOrderFactory $axytosOrderFactory,
        LoggerAdapterInterface $logger,
        InvoiceClientInterface $invoiceClient,
        ErrorReportingClientInterface $errorReportingClient
    ) {
        $this->orderSyncRepository = $orderSyncRepository;
        $this->logger = $logger;
        $this->invoiceClient = $invoiceClient;
        $this->errorReportingClient = $errorReportingClient;

        $this->axytosOrderFactory = $axytosOrderFactory;
    }

    /**
     * @param string $paymentId
     *
     * @return void
     */
    public function updatePaymentStatus($paymentId)
    {
        try {
            $this->logger->info('PaymentStatusUpdateWorker started | ' . $paymentId);

            $axytosOrder = $this->fetchAxytosOrderForPayment($paymentId);
            $axytosOrder->syncPaymentStatus();

            $this->logger->info('PaymentStatusUpdateWorker finished | ' . $paymentId);
        } catch (\Throwable $th) {
            $this->errorReportingClient->reportError($th);
            $this->logger->error('PaymentStatusUpdateWorker failed | ' . $paymentId . ' | ' . $th);
        } catch (\Exception $th) { // @phpstan-ignore-line because of php5 compatibility
            $this->errorReportingClient->reportError($th);
            $this->logger->error('PaymentStatusUpdateWorker failed | ' . $paymentId . ' | ' . $th);
        }
    }

    /**
     * @param string $paymentId
     *
     * @return Model\AxytosOrder
     */
    private function fetchAxytosOrderForPayment($paymentId)
    {
        $pluginOrder = $this->fetchPluginOrderForPayment($paymentId);

        return $this->axytosOrderFactory->create($pluginOrder);
    }

    /**
     * @param string $paymentId
     *
     * @return Plugin\Abstractions\PluginOrderInterface
     */
    private function fetchPluginOrderForPayment($paymentId)
    {
        $orderNumber = $this->fetchOrderNumberForPayment($paymentId);
        $pluginOrder = $this->orderSyncRepository->getOrderByOrderNumber($orderNumber);

        if (is_null($pluginOrder)) {
            throw new \Exception('PaymentStatusUpdateWorker was not able to find order | ' . $orderNumber);
        }

        return $pluginOrder;
    }

    /**
     * @param string $paymentId
     *
     * @return string
     */
    private function fetchOrderNumberForPayment($paymentId)
    {
        $invoiceOrderPaymentUpdate = $this->invoiceClient->getInvoiceOrderPaymentUpdate($paymentId);

        return $invoiceOrderPaymentUpdate->orderId;
    }
}
