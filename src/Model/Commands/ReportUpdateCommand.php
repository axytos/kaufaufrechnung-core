<?php

namespace Axytos\KaufAufRechnung\Core\Model\Commands;

use Axytos\ECommerce\Clients\ErrorReporting\ErrorReportingClientInterface;
use Axytos\ECommerce\Clients\Invoice\InvoiceClientInterface;
use Axytos\FinancialServices\OpenAPI\Client\ApiException;
use Axytos\KaufAufRechnung\Core\EcommerceSdkAdapter\ReportUpdateOrderContext;
use Axytos\KaufAufRechnung\Core\Model\AxytosOrderCommandInterface;
use Axytos\KaufAufRechnung\Core\Plugin\Abstractions\Logging\LoggerAdapterInterface;
use Axytos\KaufAufRechnung\Core\Plugin\Abstractions\PluginOrderInterface;

class ReportUpdateCommand implements AxytosOrderCommandInterface
{
    /**
     * @var \Axytos\KaufAufRechnung\Core\Plugin\Abstractions\PluginOrderInterface
     */
    private $order;

    /**
     * @var \Axytos\ECommerce\Clients\Invoice\InvoiceClientInterface
     */
    private $invoiceClient;

    /**
     * @var \Axytos\KaufAufRechnung\Core\Plugin\Abstractions\Logging\LoggerAdapterInterface
     */
    private $logger;

    /**
     * @var \Axytos\ECommerce\Clients\ErrorReporting\ErrorReportingClientInterface
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
        $this->logger->info('Order: ' . $this->order->getOrderNumber() . ' | ReportUpdate started');

        try {
            $this->invoiceClient->updateOrder(new ReportUpdateOrderContext($this->order->basketUpdateInformation()));
        } catch (ApiException $exception) {
            if ($exception->getCode() >= 400 && $exception->getCode() < 500) {
                $this->errorReportingClient->reportError($exception);
                $this->logger->warning('Order: ' . $this->order->getOrderNumber() . ' | ' . $exception);
            } else {
                throw $exception;
            }
        }

        $this->logger->info('Order: ' . $this->order->getOrderNumber() . ' | ReportUpdate finished');
    }
}
