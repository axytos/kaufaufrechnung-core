<?php

namespace Axytos\KaufAufRechnung\Core\EcommerceSdkAdapter;

use Axytos\KaufAufRechnung\Core\Plugin\Abstractions\Information\InvoiceInformationInterface;

class ReportInvoiceOrderContext extends TemporaryOrderContext
{
    /**
     * @var \Axytos\KaufAufRechnung\Core\Plugin\Abstractions\Information\InvoiceInformationInterface
     */
    private $invoiceInformation;

    public function __construct(
        InvoiceInformationInterface $invoiceInformation
    ) {
        $this->invoiceInformation = $invoiceInformation;
    }

    public function getOrderNumber()
    {
        /** @phpstan-ignore-next-line */
        return $this->invoiceInformation->getOrderNumber();
    }

    public function getOrderInvoiceNumber()
    {
        /** @phpstan-ignore-next-line */
        return $this->invoiceInformation->getInvoiceNumber();
    }

    /**
     * @return \Axytos\ECommerce\DataTransferObjects\CreateInvoiceBasketDto
     */
    public function getCreateInvoiceBasket()
    {
        $basket = $this->invoiceInformation->getBasket();

        $dto = new \Axytos\ECommerce\DataTransferObjects\CreateInvoiceBasketDto();
        $dto->netTotal = $basket->getNetTotal();
        $dto->grossTotal = $basket->getGrossTotal();

        $positions = array_map([$this, 'createBasketPositionDto'], $basket->getPositions());
        $dto->positions = new \Axytos\ECommerce\DataTransferObjects\CreateInvoiceBasketPositionDtoCollection(...$positions);

        $taxGroups = array_map([$this, 'createTaxGroupDto'], $basket->getTaxGroups());
        $dto->taxGroups = new \Axytos\ECommerce\DataTransferObjects\CreateInvoiceTaxGroupDtoCollection(...$taxGroups);

        return $dto;
    }

    /**
     * @param \Axytos\KaufAufRechnung\Core\Plugin\Abstractions\Information\Invoice\BasketPositionInterface $basketPosition
     * @return \Axytos\ECommerce\DataTransferObjects\CreateInvoiceBasketPositionDto
     */
    private function createBasketPositionDto($basketPosition)
    {
        $dto = new \Axytos\ECommerce\DataTransferObjects\CreateInvoiceBasketPositionDto();
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



    /**
     * @param \Axytos\KaufAufRechnung\Core\Plugin\Abstractions\Information\Invoice\TaxGroupInterface $taxGroup
     * @return \Axytos\ECommerce\DataTransferObjects\CreateInvoiceTaxGroupDto
     */
    private function createTaxGroupDto($taxGroup)
    {
        $dto = new \Axytos\ECommerce\DataTransferObjects\CreateInvoiceTaxGroupDto();
        $dto->taxPercent = $taxGroup->getTaxPercent();
        $dto->valueToTax = $taxGroup->getValueToTax();
        $dto->total = $taxGroup->getTotal();
        return $dto;
    }
}
