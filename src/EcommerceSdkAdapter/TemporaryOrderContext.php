<?php

namespace Axytos\KaufAufRechnung\Core\EcommerceSdkAdapter;

use Axytos\ECommerce\Clients\Invoice\InvoiceOrderContextInterface;
use DateTimeImmutable;

class TemporaryOrderContext implements InvoiceOrderContextInterface
{
    /**
     * @return string
     */
    public function getOrderNumber()
    {
        return '';
    }
    /**
     * @return string
     */
    public function getOrderInvoiceNumber()
    {
        return '';
    }
    /**
     * @return \DateTimeInterface
     */
    public function getOrderDateTime()
    {
        return new DateTimeImmutable();
    }
    /**
     * @return \Axytos\ECommerce\DataTransferObjects\CustomerDataDto
     */
    public function getPersonalData()
    {
        return new \Axytos\ECommerce\DataTransferObjects\CustomerDataDto();
    }
    /**
     * @return \Axytos\ECommerce\DataTransferObjects\InvoiceAddressDto
     */
    public function getInvoiceAddress()
    {
        return new \Axytos\ECommerce\DataTransferObjects\InvoiceAddressDto();
    }
    /**
     * @return \Axytos\ECommerce\DataTransferObjects\DeliveryAddressDto
     */
    public function getDeliveryAddress()
    {
        return new \Axytos\ECommerce\DataTransferObjects\DeliveryAddressDto();
    }
    /**
     * @return \Axytos\ECommerce\DataTransferObjects\BasketDto
     */
    public function getBasket()
    {
        return new \Axytos\ECommerce\DataTransferObjects\BasketDto();
    }
    /**
     * @return \Axytos\ECommerce\DataTransferObjects\RefundBasketDto
     */
    public function getRefundBasket()
    {
        return new \Axytos\ECommerce\DataTransferObjects\RefundBasketDto();
    }
    /**
     * @return \Axytos\ECommerce\DataTransferObjects\CreateInvoiceBasketDto
     */
    public function getCreateInvoiceBasket()
    {
        return new \Axytos\ECommerce\DataTransferObjects\CreateInvoiceBasketDto();
    }
    /**
     * @return \Axytos\ECommerce\DataTransferObjects\ShippingBasketPositionDtoCollection
     */
    public function getShippingBasketPositions()
    {
        return new \Axytos\ECommerce\DataTransferObjects\ShippingBasketPositionDtoCollection();
    }
    /**
     * @return mixed[]
     */
    public function getPreCheckResponseData()
    {
        return [];
    }
    /**
     * @param mixed[] $data
     * @return void
     */
    public function setPreCheckResponseData($data)
    {
    }
    /**
     * @return \Axytos\ECommerce\DataTransferObjects\ReturnPositionModelDtoCollection
     */
    public function getReturnPositions()
    {
        return new \Axytos\ECommerce\DataTransferObjects\ReturnPositionModelDtoCollection();
    }
    /**
     * @return float
     */
    public function getDeliveryWeight()
    {
        return 0.0;
    }
    /**
     * @return string[]
     */
    public function getTrackingIds()
    {
        return [];
    }
    /**
     * @return string
     */
    public function getLogistician()
    {
        return '';
    }
}
