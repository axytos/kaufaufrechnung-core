<?php

namespace Axytos\KaufAufRechnung\Core\Model\Actions\Results;

class InvalidDataResult extends AbstractActionResult
{
    /**
     * @var string
     */
    private $reason;

    /**
     * @var string|null
     */
    private $property;

    /**
     * @param string      $reason
     * @param string|null $property
     */
    public function __construct($reason, $property = null)
    {
        $this->reason = $reason;
        $this->property = $property;
    }

    /**
     * @return string
     */
    public function getResultCode()
    {
        return 'INVALID_DATA';
    }

    /**
     * @return \Axytos\KaufAufRechnung\Core\Abstractions\Model\Actions\ActionResultMessageInterface[]
     */
    public function getResult()
    {
        $result = [];
        if (!is_null($this->property)) {
            $result[] = new ActionResultMessage('Property', $this->property);
        }
        $result[] = new ActionResultMessage('Reason', $this->reason);

        return $result;
    }

    /**
     * @return int
     */
    public function getHttpStatusCode()
    {
        return 400;
    }
}
