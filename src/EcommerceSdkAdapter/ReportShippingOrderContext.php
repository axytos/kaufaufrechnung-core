<?php

namespace Axytos\KaufAufRechnung\Core\EcommerceSdkAdapter;

use Axytos\ECommerce\DataTransferObjects\ShippingBasketPositionDto;
use Axytos\ECommerce\DataTransferObjects\ShippingBasketPositionDtoCollection;
use Axytos\KaufAufRechnung\Core\Plugin\Abstractions\Information\ShippingInformationInterface;

class ReportShippingOrderContext extends TemporaryOrderContext
{
    /**
     * @var \Axytos\KaufAufRechnung\Core\Plugin\Abstractions\Information\ShippingInformationInterface
     */
    private $shippingInformation;

    public function __construct(
        ShippingInformationInterface $shippingInformation
    ) {
        $this->shippingInformation = $shippingInformation;
    }

    public function getOrderNumber()
    {
        /** @phpstan-ignore-next-line */
        return $this->shippingInformation->getOrderNumber();
    }

    public function getShippingBasketPositions()
    {
        $positions = array_map(
            [$this, 'createShippingBasketPositionDto'],
            $this->shippingInformation->getShippingBasketPositions()
        );
        return new ShippingBasketPositionDtoCollection(...$positions);
    }

    /**
     * @param \Axytos\KaufAufRechnung\Core\Plugin\Abstractions\Information\Shipping\BasketPositionInterface $position
     * @return ShippingBasketPositionDto
     */
    private function createShippingBasketPositionDto($position)
    {
        $dto = new ShippingBasketPositionDto();
        $dto->productId = $position->getProductNumber();
        $dto->quantity = $position->getQuantity();
        return $dto;
    }
}
