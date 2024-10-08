<?php

namespace Axytos\KaufAufRechnung\Core\Model\Commands;

use Axytos\ECommerce\Clients\ErrorReporting\ErrorReportingClientInterface;
use Axytos\ECommerce\Clients\Invoice\InvoiceClientInterface;
use Axytos\FinancialServices\OpenAPI\Client\ApiException;
use Axytos\KaufAufRechnung\Core\EcommerceSdkAdapter\ReportTrackingInformationOrderContext;
use Axytos\KaufAufRechnung\Core\Model\AxytosOrderCommandInterface;
use Axytos\KaufAufRechnung\Core\Plugin\Abstractions\Logging\LoggerAdapterInterface;
use Axytos\KaufAufRechnung\Core\Plugin\Abstractions\PluginOrderInterface;

class ReportTrackingInformationCommand implements AxytosOrderCommandInterface
{
    /**
     * @var PluginOrderInterface
     */
    private $order;

    /**
     * @var InvoiceClientInterface
     */
    private $invoiceClient;

    /**
     * @var LoggerAdapterInterface
     */
    private $logger;

    /**
     * @var ErrorReportingClientInterface
     */
    private $errorReportingClient;

    public function __construct(
        PluginOrderInterface $order,
        InvoiceClientInterface $invoiceClient,
        LoggerAdapterInterface $logger,
        ErrorReportingClientInterface $errorReportingClient
    ) {
        $this->order = $order;
        $this->invoiceClient = $invoiceClient;
        $this->logger = $logger;
        $this->errorReportingClient = $errorReportingClient;
    }

    /**
     * @return void
     */
    public function execute()
    {
        $this->logger->info('Order: ' . $this->order->getOrderNumber() . ' | ReportTrackingInformation started');

        try {
            $trackingInformation = new ReportTrackingInformationOrderContext($this->order->trackingInformation());
            $this->logger->info('TrackingIds: ' . implode(',', $trackingInformation->getTrackingIds()));
            $this->logger->info('Logistician: ' . $trackingInformation->getLogistician());
            $this->invoiceClient->trackingInformation($trackingInformation);
        } catch (ApiException $exception) {
            if ($exception->getCode() >= 400 && $exception->getCode() < 500) {
                $this->errorReportingClient->reportError($exception);
                $this->logger->warning('Order: ' . $this->order->getOrderNumber() . ' | ' . $exception);
            } else {
                throw $exception;
            }
        }

        $this->logger->info('Order: ' . $this->order->getOrderNumber() . ' | ReportTrackingInformation finished');
    }
}
