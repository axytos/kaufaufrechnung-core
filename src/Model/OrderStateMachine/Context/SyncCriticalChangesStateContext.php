<?php

namespace Axytos\KaufAufRechnung\Core\Model\OrderStateMachine\Context;

use Axytos\KaufAufRechnung\Core\Model\AxytosOrderCommandFacade;
use Axytos\KaufAufRechnung\Core\Model\AxytosOrderEventEmitter;
use Axytos\KaufAufRechnung\Core\Model\OrderStateMachine\OrderStateMachine;
use Axytos\KaufAufRechnung\Core\Plugin\Abstractions\PluginOrderInterface;

class SyncCriticalChangesStateContext extends AbstractStateContext
{
    public function __construct(
        OrderStateMachine $stateMachine,
        PluginOrderInterface $pluginOrder,
        AxytosOrderCommandFacade $commandFacade,
        AxytosOrderEventEmitter $eventEmitter
    ) {
        parent::__construct($stateMachine, $pluginOrder, $commandFacade, $eventEmitter);
    }
}
