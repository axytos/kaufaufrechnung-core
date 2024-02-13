<?php

namespace Axytos\KaufAufRechnung\Core\Model\Commands;

use Axytos\ECommerce\Clients\Invoice\InvoiceClientInterface;
use Axytos\KaufAufRechnung\Core\EcommerceSdkAdapter\CheckoutOrderContext;
use Axytos\KaufAufRechnung\Core\Model\AxytosOrderCommandInterface;
use Axytos\KaufAufRechnung\Core\Plugin\Abstractions\Information\Checkout\CheckoutAdapterInterface;
use Axytos\KaufAufRechnung\Core\Plugin\Abstractions\Logging\LoggerAdapterInterface;
use Axytos\KaufAufRechnung\Core\Plugin\Abstractions\PluginOrderInterface;

class CheckoutConfirmCommand implements AxytosOrderCommandInterface
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

    public function __construct(
        PluginOrderInterface $order,
        InvoiceClientInterface $invoiceClient,
        LoggerAdapterInterface $logger
    ) {
        $this->order = $order;
        $this->invoiceClient = $invoiceClient;
        $this->logger = $logger;
    }

    /**
     * @return void
     */
    public function execute()
    {
        $this->logger->info('Order: ' . $this->order->getOrderNumber() . ' | CheckoutConfirm started');

        $checkoutOrderContext = new CheckoutOrderContext($this->order->checkoutInformation());
        $this->invoiceClient->confirmOrder($checkoutOrderContext);

        $this->logger->info('Order: ' . $this->order->getOrderNumber() . ' | CheckoutConfirm finished');
    }
}
