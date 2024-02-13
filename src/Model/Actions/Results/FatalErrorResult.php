<?php

namespace Axytos\KaufAufRechnung\Core\Model\Actions\Results;

class FatalErrorResult extends AbstractActionResult
{
    /**
     * @return string
     */
    public function getResultCode()
    {
        return 'FATAL_ERROR';
    }

    /**
     * @return \Axytos\KaufAufRechnung\Core\Abstractions\Model\Actions\ActionResultMessageInterface[]
     */
    public function getResult()
    {
        return [
            new ActionResultMessage('Message', 'Check the API exception logs for more details'),
        ];
    }

    /**
     * @return int
     */
    public function getHttpStatusCode()
    {
        return 500;
    }
}
