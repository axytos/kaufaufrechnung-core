<?php

namespace Axytos\KaufAufRechnung\Core\Tests\Integration;

use Axytos\KaufAufRechnung\Core\Plugin\Abstractions\PluginOrderInterface;
use Axytos\KaufAufRechnung\Core\Plugin\Abstractions\Database\DatabaseTransactionFactoryInterface;
use Axytos\KaufAufRechnung\Core\Plugin\Abstractions\Database\DatabaseTransactionInterface;
use Axytos\KaufAufRechnung\Core\Plugin\Abstractions\Information\BasketUpdateInformationInterface;
use Axytos\KaufAufRechnung\Core\Plugin\Abstractions\Information\CancelInformationInterface;
use Axytos\KaufAufRechnung\Core\Plugin\Abstractions\Information\CheckoutInformationInterface;
use Axytos\KaufAufRechnung\Core\Plugin\Abstractions\Information\InvoiceInformationInterface;
use Axytos\KaufAufRechnung\Core\Plugin\Abstractions\Information\PaymentInformationInterface;
use Axytos\KaufAufRechnung\Core\Plugin\Abstractions\Information\RefundInformationInterface;
use Axytos\KaufAufRechnung\Core\Plugin\Abstractions\Information\ShippingInformationInterface;
use Axytos\KaufAufRechnung\Core\Plugin\Abstractions\Information\TrackingInformationInterface;
use PHPUnit\Framework\MockObject\MockObject;

trait AxytosMockModelFactoryTrait
{
    /**
     * @return DatabaseTransactionFactoryInterface&MockObject
     */
    private function createDatabaseTransactionFactoryMock()
    {
        /** @var DatabaseTransactionFactoryInterface&MockObject */
        $databaseTransactionFactory = $this->createMock(DatabaseTransactionFactoryInterface::class);
        $databaseTransactionFactory->method('create')->willReturn($this->createMock(DatabaseTransactionInterface::class));
        return $databaseTransactionFactory;
    }

    /**
     *
     * @return PluginOrderInterface&MockObject
     */
    private function createPluginOrderMock()
    {
        /** @var PluginOrderInterface&MockObject */
        $pluginOrder = $this->createMock(PluginOrderInterface::class);
        /** @var BasketUpdateInformationInterface&MockObject */
        $basketUpdateInformation = $this->createMock(BasketUpdateInformationInterface::class);
        /** @var CancelInformationInterface&MockObject */
        $cancelInformation = $this->createMock(CancelInformationInterface::class);
        /** @var CheckoutInformationInterface&MockObject */
        $checkoutInformation = $this->createMock(CheckoutInformationInterface::class);
        /** @var InvoiceInformationInterface&MockObject */
        $invoiceInformation = $this->createMock(InvoiceInformationInterface::class);
        /** @var PaymentInformationInterface&MockObject */
        $paymentInformation = $this->createMock(PaymentInformationInterface::class);
        /** @var RefundInformationInterface&MockObject */
        $refundInformation = $this->createMock(RefundInformationInterface::class);
        /** @var ShippingInformationInterface&MockObject */
        $shippingInformation = $this->createMock(ShippingInformationInterface::class);
        /** @var TrackingInformationInterface&MockObject */
        $trackingInformation = $this->createMock(TrackingInformationInterface::class);

        $customer = $this->createCustomerMock();
        $checkoutInvoiceAddress = $this->createCheckoutInvoiceAddressMock();
        $checkoutDeliveryAddress = $this->createCheckoutDeliveryAddressMock();
        $checkoutBasket = $this->createCheckoutBasket();
        $reportInvoiceBasket = $this->createReportInvoiceBasket();
        $reportRefundBasket = $this->createReportRefundBasket();
        $reportShippingBasket = $this->createReportShippingBasket();
        $reportTrackingInformationDeliveryAddress = $this->createReportTrackingInformationDeliveryAddress();
        $reportBasketUpdatesBasket = $this->createReportBasketUpdatesBasket();

        $orderNumber = $this->createOrderNumber();
        $invoiceNumber = $this->createInvoiceNumber();

        $pluginOrder->method('getOrderNumber')->willReturn($orderNumber);
        $pluginOrder->method('basketUpdateInformation')->willReturn($basketUpdateInformation);
        $pluginOrder->method('cancelInformation')->willReturn($cancelInformation);
        $pluginOrder->method('checkoutInformation')->willReturn($checkoutInformation);
        $pluginOrder->method('invoiceInformation')->willReturn($invoiceInformation);
        $pluginOrder->method('paymentInformation')->willReturn($paymentInformation);
        $pluginOrder->method('refundInformation')->willReturn($refundInformation);
        $pluginOrder->method('shippingInformation')->willReturn($shippingInformation);
        $pluginOrder->method('trackingInformation')->willReturn($trackingInformation);

        $checkoutInformation->method('getOrderNumber')->willReturn($orderNumber);
        $checkoutInformation->method('getCustomer')->willReturn($customer);
        $checkoutInformation->method('getInvoiceAddress')->willReturn($checkoutInvoiceAddress);
        $checkoutInformation->method('getDeliveryAddress')->willReturn($checkoutDeliveryAddress);
        $checkoutInformation->method('getBasket')->willReturn($checkoutBasket);

        $invoiceInformation->method('getOrderNumber')->willReturn($orderNumber);
        $invoiceInformation->method('getInvoiceNumber')->willReturn($invoiceNumber);
        $invoiceInformation->method('getBasket')->willReturn($reportInvoiceBasket);

        $refundInformation->method('getOrderNumber')->willReturn($orderNumber);
        $refundInformation->method('getInvoiceNumber')->willReturn($invoiceNumber);
        $refundInformation->method('getBasket')->willReturn($reportRefundBasket);

        $shippingInformation->method('getOrderNumber')->willReturn($orderNumber);
        $shippingInformation->method('getShippingBasketPositions')->willReturn($reportShippingBasket);

        $trackingInformation->method('getOrderNumber')->willReturn($orderNumber);
        $trackingInformation->method('getDeliveryAddress')->willReturn($reportTrackingInformationDeliveryAddress);
        $trackingInformation->method('getTrackingIds')->willReturn(['a1', 'x4', 'd22']);
        $trackingInformation->method('getDeliveryWeight')->willReturn(159.43);
        $trackingInformation->method('getDeliveryMethod')->willReturn('BHL');

        $basketUpdateInformation->method('getOrderNumber')->willReturn($orderNumber);
        $basketUpdateInformation->method('getBasket')->willReturn($reportBasketUpdatesBasket);

        return $pluginOrder;
    }

    /**
     * @return \Axytos\KaufAufRechnung\Core\Plugin\Abstractions\Information\Checkout\CustomerInterface&MockObject
     */
    private function createCustomerMock()
    {
        /** @var \Axytos\KaufAufRechnung\Core\Plugin\Abstractions\Information\Checkout\CustomerInterface&MockObject */
        $customer = $this->createMock(\Axytos\KaufAufRechnung\Core\Plugin\Abstractions\Information\Checkout\CustomerInterface::class);
        $customer->method('getCustomerNumber')->willReturn('123456879');
        $customer->method('getDateOfBirth')->willReturn(\DateTimeImmutable::createFromFormat('Y-m-d', '1990-01-01'));
        $customer->method('getEmailAddress')->willReturn('max.mustermann@test.com');
        $customer->method('getCompanyName')->willReturn('MusterCompany');
        return $customer;
    }

    /**
     *
     * @return \Axytos\KaufAufRechnung\Core\Plugin\Abstractions\Information\Checkout\InvoiceAddressInterface&MockObject
     */
    private function createCheckoutInvoiceAddressMock()
    {
        /** @var \Axytos\KaufAufRechnung\Core\Plugin\Abstractions\Information\Checkout\InvoiceAddressInterface&MockObject */
        $invoiceAddress = $this->createMock(\Axytos\KaufAufRechnung\Core\Plugin\Abstractions\Information\Checkout\InvoiceAddressInterface::class);
        $invoiceAddress->method('getCompanyName')->willReturn('Musterfabrik');
        $invoiceAddress->method('getSalutation')->willReturn('Herr');
        $invoiceAddress->method('getFirstName')->willReturn('Max');
        $invoiceAddress->method('getLastName')->willReturn('Mustermann');
        $invoiceAddress->method('getZipCode')->willReturn('11111'); // required for risk suit mocking
        $invoiceAddress->method('getCityName')->willReturn('Musterhausen');
        $invoiceAddress->method('getRegionName')->willReturn('Musterland');
        $invoiceAddress->method('getCountryCode')->willReturn('DE');
        $invoiceAddress->method('getVATId')->willReturn('55555');
        $invoiceAddress->method('getStreet')->willReturn('Musterstraße 1a');
        $invoiceAddress->method('getAdditionalAddressLine3')->willReturn('Trakt C');
        $invoiceAddress->method('getAdditionalAddressLine4')->willReturn('Zimmer 111');
        return $invoiceAddress;
    }

    /**
     *
     * @return \Axytos\KaufAufRechnung\Core\Plugin\Abstractions\Information\Checkout\DeliveryAddressInterface&MockObject
     */
    private function createCheckoutDeliveryAddressMock()
    {
        /** @var \Axytos\KaufAufRechnung\Core\Plugin\Abstractions\Information\Checkout\DeliveryAddressInterface&MockObject */
        $deliveryAddress = $this->createMock(\Axytos\KaufAufRechnung\Core\Plugin\Abstractions\Information\Checkout\DeliveryAddressInterface::class);
        $deliveryAddress->method('getCompanyName')->willReturn('Musterfabrik');
        $deliveryAddress->method('getSalutation')->willReturn('Herr');
        $deliveryAddress->method('getFirstName')->willReturn('Max');
        $deliveryAddress->method('getLastName')->willReturn('Mustermann');
        $deliveryAddress->method('getZipCode')->willReturn('11111'); // required for risk suit mocking
        $deliveryAddress->method('getCityName')->willReturn('Musterhausen');
        $deliveryAddress->method('getRegionName')->willReturn('Musterland');
        $deliveryAddress->method('getCountryCode')->willReturn('DE');
        $deliveryAddress->method('getVATId')->willReturn('55555');
        $deliveryAddress->method('getStreet')->willReturn('Musterstraße 1a');
        $deliveryAddress->method('getAdditionalAddressLine3')->willReturn('Trakt C');
        $deliveryAddress->method('getAdditionalAddressLine4')->willReturn('Zimmer 111');
        return $deliveryAddress;
    }

    /**
     * @return \Axytos\KaufAufRechnung\Core\Plugin\Abstractions\Information\Checkout\BasketInterface&MockObject
     */
    private function createCheckoutBasket()
    {
        /** @var \Axytos\KaufAufRechnung\Core\Plugin\Abstractions\Information\Checkout\BasketInterface&MockObject */
        $basket = $this->createMock(\Axytos\KaufAufRechnung\Core\Plugin\Abstractions\Information\Checkout\BasketInterface::class);
        /** @var \Axytos\KaufAufRechnung\Core\Plugin\Abstractions\Information\Checkout\BasketPositionInterface&MockObject */
        $basketPosition = $this->createMock(\Axytos\KaufAufRechnung\Core\Plugin\Abstractions\Information\Checkout\BasketPositionInterface::class);

        $basket->method('getNetTotal')->willReturn(100.0);
        $basket->method('getGrossTotal')->willReturn(111.0);
        $basket->method('getCurrency')->willReturn('EUR');
        $basket->method('getPositions')->willReturn([$basketPosition]);

        $basketPosition->method('getProductNumber')->willReturn('prod1');
        $basketPosition->method('getProductName')->willReturn('Test Product');
        $basketPosition->method('getQuantity')->willReturn(10);
        $basketPosition->method('getTaxPercent')->willReturn(0.11);
        $basketPosition->method('getNetPricePerUnit')->willReturn(10.0);
        $basketPosition->method('getGrossPricePerUnit')->willReturn(11.1);
        $basketPosition->method('getNetPositionTotal')->willReturn(100.0);
        $basketPosition->method('getGrossPositionTotal')->willReturn(111.0);

        return $basket;
    }

    /**
     *
     * @return \Axytos\KaufAufRechnung\Core\Plugin\Abstractions\Information\Invoice\BasketInterface&MockObject
     */
    private function createReportInvoiceBasket()
    {
        /** @var \Axytos\KaufAufRechnung\Core\Plugin\Abstractions\Information\Invoice\BasketInterface&MockObject */
        $basket = $this->createMock(\Axytos\KaufAufRechnung\Core\Plugin\Abstractions\Information\Invoice\BasketInterface::class);
        /** @var \Axytos\KaufAufRechnung\Core\Plugin\Abstractions\Information\Invoice\BasketPositionInterface&MockObject */
        $basketPosition = $this->createMock(\Axytos\KaufAufRechnung\Core\Plugin\Abstractions\Information\Invoice\BasketPositionInterface::class);
        /** @var \Axytos\KaufAufRechnung\Core\Plugin\Abstractions\Information\Invoice\TaxGroupInterface&MockObject */
        $taxGroup = $this->createMock(\Axytos\KaufAufRechnung\Core\Plugin\Abstractions\Information\Invoice\TaxGroupInterface::class);

        $basket->method('getNetTotal')->willReturn(100.0);
        $basket->method('getGrossTotal')->willReturn(111.0);
        $basket->method('getPositions')->willReturn([$basketPosition]);
        $basket->method('getTaxGroups')->willReturn([$taxGroup]);
        $basketPosition->method('getProductNumber')->willReturn('prod1');
        $basketPosition->method('getProductName')->willReturn('Test Product');
        $basketPosition->method('getQuantity')->willReturn(10);
        $basketPosition->method('getTaxPercent')->willReturn(0.11);
        $basketPosition->method('getNetPricePerUnit')->willReturn(10.0);
        $basketPosition->method('getGrossPricePerUnit')->willReturn(11.1);
        $basketPosition->method('getNetPositionTotal')->willReturn(100.0);
        $basketPosition->method('getGrossPositionTotal')->willReturn(111.0);
        $taxGroup->method('getTaxPercent')->willReturn(0.11);
        $taxGroup->method('getValueToTax')->willReturn(100.0);
        $taxGroup->method('getTotal')->willReturn(111.0);

        return $basket;
    }

    /**
     *
     * @return \Axytos\KaufAufRechnung\Core\Plugin\Abstractions\Information\Refund\BasketInterface&MockObject
     */
    private function createReportRefundBasket()
    {
        /** @var \Axytos\KaufAufRechnung\Core\Plugin\Abstractions\Information\Refund\BasketInterface&MockObject */
        $basket = $this->createMock(\Axytos\KaufAufRechnung\Core\Plugin\Abstractions\Information\Refund\BasketInterface::class);
        /** @var \Axytos\KaufAufRechnung\Core\Plugin\Abstractions\Information\Refund\BasketPositionInterface&MockObject */
        $basketPosition = $this->createMock(\Axytos\KaufAufRechnung\Core\Plugin\Abstractions\Information\Refund\BasketPositionInterface::class);
        /** @var \Axytos\KaufAufRechnung\Core\Plugin\Abstractions\Information\Refund\TaxGroupInterface&MockObject */
        $taxGroup = $this->createMock(\Axytos\KaufAufRechnung\Core\Plugin\Abstractions\Information\Refund\TaxGroupInterface::class);

        $basket->method('getNetTotal')->willReturn(100.0);
        $basket->method('getGrossTotal')->willReturn(111.0);
        $basket->method('getPositions')->willReturn([$basketPosition]);
        $basket->method('getTaxGroups')->willReturn([$taxGroup]);
        $basketPosition->method('getProductNumber')->willReturn('prod1');
        $basketPosition->method('getNetRefundTotal')->willReturn(100.0);
        $basketPosition->method('getGrossRefundTotal')->willReturn(111.0);
        $taxGroup->method('getTaxPercent')->willReturn(0.11);
        $taxGroup->method('getValueToTax')->willReturn(100.0);
        $taxGroup->method('getTotal')->willReturn(111.0);

        return $basket;
    }

    /**
     *
     * @return (\Axytos\KaufAufRechnung\Core\Plugin\Abstractions\Information\Shipping\BasketPositionInterface&MockObject)[]
     */
    private function createReportShippingBasket()
    {
        /** @var \Axytos\KaufAufRechnung\Core\Plugin\Abstractions\Information\Shipping\BasketPositionInterface&MockObject */
        $position = $this->createMock(\Axytos\KaufAufRechnung\Core\Plugin\Abstractions\Information\Shipping\BasketPositionInterface::class);

        $position->method('getProductNumber')->willReturn('654321');
        $position->method('getQuantity')->willReturn('3');

        return [$position];
    }

    /**
     * @return \Axytos\KaufAufRechnung\Core\Plugin\Abstractions\Information\Tracking\DeliveryAddressInterface&MockObject
     */
    private function createReportTrackingInformationDeliveryAddress()
    {
        /** @var \Axytos\KaufAufRechnung\Core\Plugin\Abstractions\Information\Tracking\DeliveryAddressInterface&MockObject */
        $address = $this->createMock(\Axytos\KaufAufRechnung\Core\Plugin\Abstractions\Information\Tracking\DeliveryAddressInterface::class);

        $address->method('getCompanyName')->willReturn('Musterfabrik');
        $address->method('getSalutation')->willReturn('Herr');
        $address->method('getFirstName')->willReturn('Max');
        $address->method('getLastName')->willReturn('Mustermann');
        $address->method('getZipCode')->willReturn('11111'); // required for risk suit mocking
        $address->method('getCityName')->willReturn('Musterhausen');
        $address->method('getRegionName')->willReturn('Musterland');
        $address->method('getCountryCode')->willReturn('DE');
        $address->method('getVATId')->willReturn('55555');
        $address->method('getStreet')->willReturn('Musterstraße 1a');
        $address->method('getAdditionalAddressLine3')->willReturn('Trakt C');
        $address->method('getAdditionalAddressLine4')->willReturn('Zimmer 111');

        return $address;
    }

    /**
     *
     * @return \Axytos\KaufAufRechnung\Core\Plugin\Abstractions\Information\BasketUpdate\BasketInterface&MockObject
     */
    private function createReportBasketUpdatesBasket()
    {
        /** @var \Axytos\KaufAufRechnung\Core\Plugin\Abstractions\Information\BasketUpdate\BasketInterface&MockObject */
        $basket = $this->createMock(\Axytos\KaufAufRechnung\Core\Plugin\Abstractions\Information\BasketUpdate\BasketInterface::class);
        /** @var \Axytos\KaufAufRechnung\Core\Plugin\Abstractions\Information\BasketUpdate\BasketPositionInterface&MockObject */
        $basketPosition = $this->createMock(\Axytos\KaufAufRechnung\Core\Plugin\Abstractions\Information\BasketUpdate\BasketPositionInterface::class);

        $basket->method('getNetTotal')->willReturn(100.0);
        $basket->method('getGrossTotal')->willReturn(111.0);
        $basket->method('getCurrency')->willReturn("EUR");
        $basket->method('getPositions')->willReturn([$basketPosition]);
        $basketPosition->method('getProductNumber')->willReturn('prod1');
        $basketPosition->method('getProductName')->willReturn('Test Product');
        $basketPosition->method('getProductCategory')->willReturn('Testing');
        $basketPosition->method('getQuantity')->willReturn(10);
        $basketPosition->method('getTaxPercent')->willReturn(0.11);
        $basketPosition->method('getNetPricePerUnit')->willReturn(10.0);
        $basketPosition->method('getGrossPricePerUnit')->willReturn(11.1);
        $basketPosition->method('getNetPositionTotal')->willReturn(100.0);
        $basketPosition->method('getGrossPositionTotal')->willReturn(111.0);

        return $basket;
    }

    /**
     * @return string
     */
    private function createOrderNumber()
    {
        return uniqid('integration-test-order-number-');
    }

    /**
     * @return string
     */
    private function createInvoiceNumber()
    {
        return uniqid('integration-test-invoice-number-');
    }
}
