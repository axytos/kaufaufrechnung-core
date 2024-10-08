<?php

namespace Axytos\KaufAufRechnung\Core\Model;

use Axytos\ECommerce\Clients\ErrorReporting\ErrorReportingClientInterface;
use Axytos\ECommerce\Clients\Invoice\InvoiceClientInterface;
use Axytos\KaufAufRechnung\Core\Model\Commands\CheckoutConfirmCommand;
use Axytos\KaufAufRechnung\Core\Model\Commands\CheckoutPrecheckCommand;
use Axytos\KaufAufRechnung\Core\Model\Commands\HasBeenPaidCommand;
use Axytos\KaufAufRechnung\Core\Model\Commands\ReportCancelCommand;
use Axytos\KaufAufRechnung\Core\Model\Commands\ReportInvoiceCommand;
use Axytos\KaufAufRechnung\Core\Model\Commands\ReportRefundCommand;
use Axytos\KaufAufRechnung\Core\Model\Commands\ReportShippingCommand;
use Axytos\KaufAufRechnung\Core\Model\Commands\ReportTrackingInformationCommand;
use Axytos\KaufAufRechnung\Core\Model\Commands\ReportUncancelCommand;
use Axytos\KaufAufRechnung\Core\Model\Commands\ReportUpdateCommand;
use Axytos\KaufAufRechnung\Core\Plugin\Abstractions\Logging\LoggerAdapterInterface;
use Axytos\KaufAufRechnung\Core\Plugin\Abstractions\PluginOrderInterface;

class AxytosOrderCommandFacade
{
    /**
     * @var InvoiceClientInterface
     */
    private $invoiceClient;

    /**
     * @var ErrorReportingClientInterface
     */
    private $errorReportingClient;

    /**
     * @var LoggerAdapterInterface
     */
    private $logger;

    public function __construct(
        InvoiceClientInterface $invoiceClient,
        ErrorReportingClientInterface $errorReportingClient,
        LoggerAdapterInterface $logger
    ) {
        $this->invoiceClient = $invoiceClient;
        $this->errorReportingClient = $errorReportingClient;
        $this->logger = $logger;
    }

    /**
     * @param PluginOrderInterface $pluginOrder
     *
     * @return string
     *
     * @phpstan-return \Axytos\ECommerce\Clients\Invoice\ShopActions::*
     */
    public function checkoutPrecheck($pluginOrder)
    {
        $command = new CheckoutPrecheckCommand(
            $pluginOrder,
            $this->invoiceClient,
            $this->logger
        );

        $command->execute();

        return $command->getShopAction();
    }

    /**
     * @param PluginOrderInterface $pluginOrder
     *
     * @return void
     */
    public function checkoutConfirm($pluginOrder)
    {
        $command = new CheckoutConfirmCommand(
            $pluginOrder,
            $this->invoiceClient,
            $this->logger
        );

        $command->execute();
    }

    /**
     * @param PluginOrderInterface $pluginOrder
     *
     * @return void
     */
    public function reportCancel($pluginOrder)
    {
        $command = new ReportCancelCommand(
            $pluginOrder,
            $this->invoiceClient,
            $this->logger,
            $this->errorReportingClient
        );

        $command->execute();
    }

    /**
     * @param PluginOrderInterface $pluginOrder
     *
     * @return void
     */
    public function reportUncancel($pluginOrder)
    {
        $command = new ReportUncancelCommand(
            $pluginOrder,
            $this->invoiceClient,
            $this->logger,
            $this->errorReportingClient
        );

        $command->execute();
    }

    /**
     * @param PluginOrderInterface $pluginOrder
     *
     * @return void
     */
    public function reportCreateInvoice($pluginOrder)
    {
        $command = new ReportInvoiceCommand(
            $pluginOrder,
            $this->invoiceClient,
            $this->logger,
            $this->errorReportingClient
        );

        $command->execute();
    }

    /**
     * @param PluginOrderInterface $pluginOrder
     *
     * @return void
     */
    public function reportRefund($pluginOrder)
    {
        $command = new ReportRefundCommand(
            $pluginOrder,
            $this->invoiceClient,
            $this->logger,
            $this->errorReportingClient
        );

        $command->execute();
    }

    /**
     * @param PluginOrderInterface $pluginOrder
     *
     * @return void
     */
    public function reportShipping($pluginOrder)
    {
        $command = new ReportShippingCommand(
            $pluginOrder,
            $this->invoiceClient,
            $this->logger,
            $this->errorReportingClient
        );

        $command->execute();
    }

    /**
     * @param PluginOrderInterface $pluginOrder
     *
     * @return void
     */
    public function reportTrackingInformation($pluginOrder)
    {
        $command = new ReportTrackingInformationCommand(
            $pluginOrder,
            $this->invoiceClient,
            $this->logger,
            $this->errorReportingClient
        );

        $command->execute();
    }

    /**
     * @param PluginOrderInterface $pluginOrder
     *
     * @return void
     */
    public function reportUpdate($pluginOrder)
    {
        $command = new ReportUpdateCommand(
            $pluginOrder,
            $this->invoiceClient,
            $this->logger,
            $this->errorReportingClient
        );

        $command->execute();
    }

    /**
     * @param PluginOrderInterface $pluginOrder
     *
     * @return bool
     */
    public function hasBeenPaid($pluginOrder)
    {
        $command = new HasBeenPaidCommand(
            $pluginOrder,
            $this->invoiceClient,
            $this->logger,
            $this->errorReportingClient
        );

        $command->execute();

        return $command->hasBeenPaid();
    }
}
