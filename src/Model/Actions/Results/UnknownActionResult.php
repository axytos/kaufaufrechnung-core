<?php

namespace Axytos\KaufAufRechnung\Core\Model\Actions\Results;

class UnknownActionResult extends AbstractActionResult
{
    /**
     * @var string
     */
    private $action;

    /**
     * @param string $action
     */
    public function __construct($action)
    {
        $this->action = $action;
    }

    /**
     * @return string
     */
    public function getResultCode()
    {
        return 'UNKNOWN_ACTION';
    }

    /**
     * @return \Axytos\KaufAufRechnung\Core\Abstractions\Model\Actions\ActionResultMessageInterface[]
     */
    public function getResult()
    {
        return [
            new ActionResultMessage('Action', $this->action),
        ];
    }

    /**
     * @return int
     */
    public function getHttpStatusCode()
    {
        return 400;
    }
}
