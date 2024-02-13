<?php

namespace Axytos\KaufAufRechnung\Core\Model\Actions\Results;

use Axytos\KaufAufRechnung\Core\Abstractions\Model\Actions\ActionResultMessageInterface;

class ActionResultMessage implements ActionResultMessageInterface
{
    /**
     * @var string
     */
    private $message;

    /**
     * @var string|null
     */
    private $details;

    /**
     * @param string $message
     * @param string|null $details
     */
    public function __construct($message, $details = null)
    {
        $this->message = $message;
        $this->details = $details;
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @return string|null
     */
    public function getDetails()
    {
        return $this->details;
    }

    /**
     * @return mixed
     */
    public function jsonSerialize()
    {
        return [
            'message' => $this->message,
            'details' => $this->details,
        ];
    }
}
