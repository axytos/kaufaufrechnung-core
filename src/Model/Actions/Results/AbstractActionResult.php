<?php

namespace Axytos\KaufAufRechnung\Core\Model\Actions\Results;

use Axytos\KaufAufRechnung\Core\Abstractions\Model\Actions\ActionResultInterface;

abstract class AbstractActionResult implements ActionResultInterface
{
    /**
     * @return \Axytos\KaufAufRechnung\Core\Abstractions\Model\Actions\ActionResultMessageInterface[]
     */
    public function getResult()
    {
        return [];
    }

    /**
     * @return int
     */
    public function getHttpStatusCode()
    {
        return 200;
    }

    /**
     * @return mixed
     */
    public function jsonSerialize()
    {
        return [
            'resultCode' => $this->getResultCode(),
            'result' => $this->getResult(),
        ];
    }
}
