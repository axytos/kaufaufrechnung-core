<?php

namespace Axytos\KaufAufRechnung\Core\EcommerceSdkAdapter;

use Axytos\KaufAufRechnung\Core\Plugin\Abstractions\Information\PaymentInformationInterface;

class FetchPaymentStatusOrderContext extends TemporaryOrderContext
{
    /**
     * @var \Axytos\KaufAufRechnung\Core\Plugin\Abstractions\Information\PaymentInformationInterface
     */
    private $paymentInformation;

    public function __construct(
        PaymentInformationInterface $paymentInformation
    ) {
        $this->paymentInformation = $paymentInformation;
    }

    public function getOrderNumber()
    {
        /** @phpstan-ignore-next-line */
        return $this->paymentInformation->getOrderNumber();
    }
}
