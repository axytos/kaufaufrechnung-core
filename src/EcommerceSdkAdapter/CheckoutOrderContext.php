<?php

namespace Axytos\KaufAufRechnung\Core\EcommerceSdkAdapter;

use Axytos\KaufAufRechnung\Core\Plugin\Abstractions\Information\CheckoutInformationInterface;

class CheckoutOrderContext extends TemporaryOrderContext
{
    /**
     * @var \Axytos\KaufAufRechnung\Core\Plugin\Abstractions\Information\CheckoutInformationInterface
     */
    private $checkoutInformation;

    public function __construct(
        CheckoutInformationInterface $checkoutInformation
    ) {
        $this->checkoutInformation = $checkoutInformation;
    }

    public function getPreCheckResponseData()
    {
        return $this->checkoutInformation->getPreCheckResponseData();
    }

    public function setPreCheckResponseData($data)
    {
        $this->checkoutInformation->savePreCheckResponseData($data);
    }

    public function getOrderNumber()
    {
        /** @phpstan-ignore-next-line */
        return $this->checkoutInformation->getOrderNumber();
    }

    /**
     * @return \Axytos\ECommerce\DataTransferObjects\CustomerDataDto
     */
    public function getPersonalData()
    {
        $customer = $this->checkoutInformation->getCustomer();

        $dto = new \Axytos\ECommerce\DataTransferObjects\CustomerDataDto();
        $dto->externalCustomerId = $customer->getCustomerNumber();
        $dto->dateOfBirth = $customer->getDateOfBirth();
        $dto->email = $customer->getEmailAddress();

        if (!is_null($customer->getCompanyName())) {
            $dto->company = new \Axytos\ECommerce\DataTransferObjects\CompanyDto();
            $dto->company->name = $customer->getCompanyName();
        }

        return $dto;
    }

    /**
     * @return \Axytos\ECommerce\DataTransferObjects\InvoiceAddressDto
     */
    public function getInvoiceAddress()
    {
        $invoiceAddress = $this->checkoutInformation->getInvoiceAddress();

        $dto = new \Axytos\ECommerce\DataTransferObjects\InvoiceAddressDto();
        $dto->company = $invoiceAddress->getCompanyName();
        $dto->salutation = $invoiceAddress->getSalutation();
        $dto->firstname = $invoiceAddress->getFirstName();
        $dto->lastname = $invoiceAddress->getLastName();
        $dto->zipCode = $invoiceAddress->getZipCode();
        $dto->city = $invoiceAddress->getCityName();
        $dto->region = $invoiceAddress->getRegionName();
        $dto->country = $invoiceAddress->getCountryCode();
        $dto->vatId = $invoiceAddress->getVATId();
        $dto->addressLine1 = $invoiceAddress->getStreet();
        $dto->addressLine2 = $invoiceAddress->getAdditionalAddressLine2();
        $dto->addressLine3 = $invoiceAddress->getAdditionalAddressLine3();
        $dto->addressLine4 = $invoiceAddress->getAdditionalAddressLine4();
        return $dto;
    }

    /**
     * @return \Axytos\ECommerce\DataTransferObjects\DeliveryAddressDto
     */
    public function getDeliveryAddress()
    {
        $deliveryAddress = $this->checkoutInformation->getDeliveryAddress();

        $dto = new \Axytos\ECommerce\DataTransferObjects\DeliveryAddressDto();
        $dto->company = $deliveryAddress->getCompanyName();
        $dto->salutation = $deliveryAddress->getSalutation();
        $dto->firstname = $deliveryAddress->getFirstName();
        $dto->lastname = $deliveryAddress->getLastName();
        $dto->zipCode = $deliveryAddress->getZipCode();
        $dto->city = $deliveryAddress->getCityName();
        $dto->region = $deliveryAddress->getRegionName();
        $dto->country = $deliveryAddress->getCountryCode();
        $dto->vatId = $deliveryAddress->getVATId();
        $dto->addressLine1 = $deliveryAddress->getStreet();
        $dto->addressLine2 = $deliveryAddress->getAdditionalAddressLine2();
        $dto->addressLine3 = $deliveryAddress->getAdditionalAddressLine3();
        $dto->addressLine4 = $deliveryAddress->getAdditionalAddressLine4();

        return $dto;
    }

    /**
     * @return \Axytos\ECommerce\DataTransferObjects\BasketDto
     */
    public function getBasket()
    {
        $basket = $this->checkoutInformation->getBasket();

        $dto = new \Axytos\ECommerce\DataTransferObjects\BasketDto();
        $dto->netTotal = $basket->getNetTotal();
        $dto->grossTotal = $basket->getGrossTotal();
        $dto->currency = $basket->getCurrency();

        $positions = array_map([$this, 'createBasketPositionDto'], $basket->getPositions());
        $dto->positions = new \Axytos\ECommerce\DataTransferObjects\BasketPositionDtoCollection(...$positions);

        return $dto;
    }

    /**
     * @param \Axytos\KaufAufRechnung\Core\Plugin\Abstractions\Information\Checkout\BasketPositionInterface $basketPosition
     * @return \Axytos\ECommerce\DataTransferObjects\BasketPositionDto
     */
    private function createBasketPositionDto($basketPosition)
    {
        $dto = new \Axytos\ECommerce\DataTransferObjects\BasketPositionDto();
        $dto->productId = $basketPosition->getProductNumber();
        $dto->productName = $basketPosition->getProductName();
        $dto->quantity = $basketPosition->getQuantity();
        $dto->taxPercent = $basketPosition->getTaxPercent();
        $dto->netPricePerUnit = $basketPosition->getNetPricePerUnit();
        $dto->netPositionTotal = $basketPosition->getNetPositionTotal();
        $dto->grossPricePerUnit = $basketPosition->getGrossPricePerUnit();
        $dto->grossPositionTotal = $basketPosition->getGrossPositionTotal();
        return $dto;
    }
}
