<?php

namespace Axytos\KaufAufRechnung\Core\EcommerceSdkAdapter;

use Axytos\KaufAufRechnung\Core\Plugin\Abstractions\Information\CancelInformationInterface;

class ReportCancelOrderContext extends TemporaryOrderContext
{
    /**
     * @var CancelInformationInterface
     */
    private $cancelInformation;

    public function __construct(
        CancelInformationInterface $cancelInformation
    ) {
        $this->cancelInformation = $cancelInformation;
    }

    /**
     * @return string
     */
    public function getOrderNumber()
    {
        return strval($this->cancelInformation->getOrderNumber());
    }
}
