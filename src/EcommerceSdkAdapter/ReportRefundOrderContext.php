<?php

namespace Axytos\KaufAufRechnung\Core\EcommerceSdkAdapter;

use Axytos\KaufAufRechnung\Core\Plugin\Abstractions\Information\RefundInformationInterface;

class ReportRefundOrderContext extends TemporaryOrderContext
{
    /**
     * @var RefundInformationInterface
     */
    private $refundInformation;

    public function __construct(
        RefundInformationInterface $refundInformation
    ) {
        $this->refundInformation = $refundInformation;
    }

    public function getOrderNumber()
    {
        /** @phpstan-ignore-next-line */
        return $this->refundInformation->getOrderNumber();
    }

    public function getOrderInvoiceNumber()
    {
        /** @phpstan-ignore-next-line */
        return $this->refundInformation->getInvoiceNumber();
    }

    /**
     * @return \Axytos\ECommerce\DataTransferObjects\RefundBasketDto
     */
    public function getRefundBasket()
    {
        $basket = $this->refundInformation->getBasket();

        $dto = new \Axytos\ECommerce\DataTransferObjects\RefundBasketDto();
        $dto->netTotal = $basket->getNetTotal();
        $dto->grossTotal = $basket->getGrossTotal();

        $positions = array_map([$this, 'createBasketPositionDto'], $basket->getPositions());
        $dto->positions = new \Axytos\ECommerce\DataTransferObjects\RefundBasketPositionDtoCollection(...$positions);

        $taxGroups = array_map([$this, 'createTaxGroupDto'], $basket->getTaxGroups());
        $dto->taxGroups = new \Axytos\ECommerce\DataTransferObjects\RefundBasketTaxGroupDtoCollection(...$taxGroups);

        return $dto;
    }

    /**
     * @param \Axytos\KaufAufRechnung\Core\Plugin\Abstractions\Information\Refund\BasketPositionInterface $basketPosition
     *
     * @return \Axytos\ECommerce\DataTransferObjects\RefundBasketPositionDto
     */
    private function createBasketPositionDto($basketPosition)
    {
        $dto = new \Axytos\ECommerce\DataTransferObjects\RefundBasketPositionDto();
        $dto->productId = $basketPosition->getProductNumber();
        $dto->netRefundTotal = $basketPosition->getNetRefundTotal();
        $dto->grossRefundTotal = $basketPosition->getGrossRefundTotal();

        return $dto;
    }

    /**
     * @param \Axytos\KaufAufRechnung\Core\Plugin\Abstractions\Information\Refund\TaxGroupInterface $taxGroup
     *
     * @return \Axytos\ECommerce\DataTransferObjects\RefundBasketTaxGroupDto
     */
    private function createTaxGroupDto($taxGroup)
    {
        $dto = new \Axytos\ECommerce\DataTransferObjects\RefundBasketTaxGroupDto();
        $dto->taxPercent = $taxGroup->getTaxPercent();
        $dto->valueToTax = $taxGroup->getValueToTax();
        $dto->total = $taxGroup->getTotal();

        return $dto;
    }
}
