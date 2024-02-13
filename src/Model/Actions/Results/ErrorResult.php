<?php

namespace Axytos\KaufAufRechnung\Core\Model\Actions\Results;

class ErrorResult extends AbstractActionResult
{
    /**
     * @var \Throwable
     */
    private $error;

    /**
     * @param \Throwable $error
     */
    public function __construct($error)
    {
        $this->error = $error;
    }

    /**
     * @return string
     */
    public function getResultCode()
    {
        return 'ERROR';
    }

    /**
     * @return \Axytos\KaufAufRechnung\Core\Abstractions\Model\Actions\ActionResultMessageInterface[]
     */
    public function getResult()
    {
        return [
            new ActionResultMessage('Exception-Type', get_class($this->error)),
            new ActionResultMessage('Message', $this->error->getMessage()),
        ];
    }
}
