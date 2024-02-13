<?php

namespace Axytos\KaufAufRechnung\Core\Model\Actions\Results;

class SuccessResult extends AbstractActionResult
{
    /**
     * @var string|null
     */
    private $nextToken;

    /**
     * @param string|null $nextToken
     */
    public function __construct($nextToken = null)
    {
        $this->nextToken = $nextToken;
    }

    /**
     * @return string
     */
    public function getResultCode()
    {
        return 'OK';
    }

    /**
     * @return \Axytos\KaufAufRechnung\Core\Abstractions\Model\Actions\ActionResultMessageInterface[]
     */
    public function getResult()
    {
        /** @var \Axytos\KaufAufRechnung\Core\Abstractions\Model\Actions\ActionResultMessageInterface[] */
        $result = [];
        if (is_string($this->nextToken)) {
            $result[] = new ActionResultMessage("nextToken", $this->nextToken);
        }
        return $result;
    }
}
