<?php

namespace Axytos\KaufAufRechnung\Core\EcommerceSdkAdapter;

use Axytos\KaufAufRechnung\Core\Plugin\Abstractions\Information\TrackingInformationInterface;

class ReportTrackingInformationOrderContext extends TemporaryOrderContext
{
    /**
     * @var TrackingInformationInterface
     */
    private $trackingInformation;

    public function __construct(
        TrackingInformationInterface $trackingInformation
    ) {
        $this->trackingInformation = $trackingInformation;
    }

    public function getOrderNumber()
    {
        /** @phpstan-ignore-next-line */
        return $this->trackingInformation->getOrderNumber();
    }

    /**
     * @return \Axytos\ECommerce\DataTransferObjects\DeliveryAddressDto
     */
    public function getDeliveryAddress()
    {
        $deliveryAddress = $this->trackingInformation->getDeliveryAddress();

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
     * @return float
     */
    public function getDeliveryWeight()
    {
        return $this->trackingInformation->getDeliveryWeight();
    }

    /**
     * @return string[]
     */
    public function getTrackingIds()
    {
        return $this->trackingInformation->getTrackingIds();
    }

    /**
     * @return string
     */
    public function getLogistician()
    {
        return $this->trackingInformation->getDeliveryMethod();
    }
}
