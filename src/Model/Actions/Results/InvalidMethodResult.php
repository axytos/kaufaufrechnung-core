<?php

namespace Axytos\KaufAufRechnung\Core\Model\Actions\Results;

class InvalidMethodResult extends AbstractActionResult
{
    /**
     * @var string
     */
    private $method;

    /**
     * @param string $method
     */
    public function __construct($method)
    {
        $this->method = $method;
    }

    /**
     * @return string
     */
    public function getResultCode()
    {
        return 'INVALID_METHOD';
    }

    /**
     * @return \Axytos\KaufAufRechnung\Core\Abstractions\Model\Actions\ActionResultMessageInterface[]
     */
    public function getResult()
    {
        return [
            new ActionResultMessage('Method', $this->method),
        ];
    }

    /**
     * @return int
     */
    public function getHttpStatusCode()
    {
        return 405;
    }
}
