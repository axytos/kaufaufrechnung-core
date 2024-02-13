<?php

namespace Axytos\KaufAufRechnung\Core\Model\Commands;

use Axytos\ECommerce\Clients\Invoice\InvoiceClientInterface;
use Axytos\ECommerce\Clients\Invoice\ShopActions;
use Axytos\KaufAufRechnung\Core\EcommerceSdkAdapter\CheckoutOrderContext;
use Axytos\KaufAufRechnung\Core\Model\AxytosOrderCommandInterface;
use Axytos\KaufAufRechnung\Core\Plugin\Abstractions\Logging\LoggerAdapterInterface;
use Axytos\KaufAufRechnung\Core\Plugin\Abstractions\PluginOrderInterface;

class CheckoutPrecheckCommand implements AxytosOrderCommandInterface
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
     * @var string
     * @phpstan-var \Axytos\ECommerce\Clients\Invoice\ShopActions::*
     */
    private $shopAction = ShopActions::CHANGE_PAYMENT_METHOD;

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
        $this->logger->info('Order: ' . $this->order->getOrderNumber() . ' | CheckoutPrecheck started');

        $checkoutOrderContext = new CheckoutOrderContext($this->order->checkoutInformation());

        /** @phpstan-ignore-next-line */
        $this->shopAction = $this->invoiceClient->precheck($checkoutOrderContext);

        $this->logger->info('Order: ' . $this->order->getOrderNumber() . ' | CheckoutPrecheck finished');
    }

    /**
     * @return string
     * @phpstan-return \Axytos\ECommerce\Clients\Invoice\ShopActions::*
     */
    public function getShopAction()
    {
        return $this->shopAction;
    }
}
