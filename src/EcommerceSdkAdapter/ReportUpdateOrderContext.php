<?php

namespace Axytos\KaufAufRechnung\Core\EcommerceSdkAdapter;

use Axytos\KaufAufRechnung\Core\Plugin\Abstractions\Information\BasketUpdateInformationInterface;

class ReportUpdateOrderContext extends TemporaryOrderContext
{
    /**
     * @var BasketUpdateInformationInterface
     */
    private $basketUpdateInformation;

    public function __construct(
        BasketUpdateInformationInterface $basketUpdateInformation
    ) {
        $this->basketUpdateInformation = $basketUpdateInformation;
    }

    public function getOrderNumber()
    {
        /** @phpstan-ignore-next-line */
        return $this->basketUpdateInformation->getOrderNumber();
    }

    public function getBasket()
    {
        $basket = $this->basketUpdateInformation->getBasket();

        $dto = new \Axytos\ECommerce\DataTransferObjects\BasketDto();
        $dto->netTotal = $basket->getNetTotal();
        $dto->grossTotal = $basket->getGrossTotal();
        $dto->currency = $basket->getCurrency();

        $positions = array_map([$this, 'createBasketPositionDto'], $basket->getPositions());
        $dto->positions = new \Axytos\ECommerce\DataTransferObjects\BasketPositionDtoCollection(...$positions);

        return $dto;
    }

    /**
     * @param \Axytos\KaufAufRechnung\Core\Plugin\Abstractions\Information\BasketUpdate\BasketPositionInterface $basketPosition
     *
     * @return \Axytos\ECommerce\DataTransferObjects\BasketPositionDto
     */
    private function createBasketPositionDto($basketPosition)
    {
        $dto = new \Axytos\ECommerce\DataTransferObjects\BasketPositionDto();
        $dto->productId = $basketPosition->getProductNumber();
        $dto->productName = $basketPosition->getProductName();
        $dto->productCategory = $basketPosition->getProductCategory();
        $dto->quantity = $basketPosition->getQuantity();
        $dto->taxPercent = $basketPosition->getTaxPercent();
        $dto->netPricePerUnit = $basketPosition->getNetPricePerUnit();
        $dto->netPositionTotal = $basketPosition->getNetPositionTotal();
        $dto->grossPricePerUnit = $basketPosition->getGrossPricePerUnit();
        $dto->grossPositionTotal = $basketPosition->getGrossPositionTotal();

        return $dto;
    }
}
