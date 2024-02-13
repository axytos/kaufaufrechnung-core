<?php

namespace Axytos\KaufAufRechnung\Core\Model\Actions\Results;

class InvalidClientSecretResult extends AbstractActionResult
{
    /**
     * @return string
     */
    public function getResultCode()
    {
        return 'INVALID_CLIENT_SECRET';
    }

    /**
     * @return int
     */
    public function getHttpStatusCode()
    {
        return 401;
    }
}
