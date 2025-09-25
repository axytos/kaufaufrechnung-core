<?php

namespace Axytos\KaufAufRechnung\Core\Model;

class AxytosOrderEventEmitter
{
    /**
     * @var array<string,callable[]>
     */
    private $eventListeners = [];

    /**
     * @param string   $eventName
     * @param callable $eventListener
     *
     * @phpstan-param \Axytos\KaufAufRechnung\Core\Abstractions\Model\AxytosOrderEvents::* $eventName
     *
     * @return void
     */
    public function subscribe($eventName, $eventListener)
    {
        if (!array_key_exists($eventName, $this->eventListeners)) {
            $this->eventListeners[$eventName] = [];
        }

        array_push($this->eventListeners[$eventName], $eventListener);
    }

    /**
     * @param string $eventName
     *
     * @return void
     */
    public function emit($eventName)
    {
        if (!array_key_exists($eventName, $this->eventListeners)) {
            return;
        }

        foreach ($this->eventListeners[$eventName] as $eventListener) {
            call_user_func($eventListener, $eventName);
        }
    }
}
