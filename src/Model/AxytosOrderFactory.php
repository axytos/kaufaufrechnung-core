<?php

namespace Axytos\KaufAufRechnung\Core\Model;

use Axytos\ECommerce\Clients\ErrorReporting\ErrorReportingClientInterface;
use Axytos\KaufAufRechnung\Core\Model\OrderStateMachine\OrderStateMachine;
use Axytos\KaufAufRechnung\Core\Plugin\Abstractions\Logging\LoggerAdapterInterface;
use Axytos\KaufAufRechnung\Core\Plugin\Abstractions\Database\DatabaseTransactionFactoryInterface;

class AxytosOrderFactory
{
    /**
     * @var \Axytos\ECommerce\Clients\ErrorReporting\ErrorReportingClientInterface
     */
    private $errorReportingClient;

    /**
     * @var \Axytos\KaufAufRechnung\Core\Plugin\Abstractions\Database\DatabaseTransactionFactoryInterface
     */
    private $databaseTransactionFactory;

    /**
     * @var \Axytos\KaufAufRechnung\Core\Model\AxytosOrderCommandFacade
     */
    private $commandFacade;

    /**
     * @var \Axytos\KaufAufRechnung\Core\Plugin\Abstractions\Logging\LoggerAdapterInterface
     */
    private $logger;

    public function __construct(
        ErrorReportingClientInterface $errorReportingClient,
        DatabaseTransactionFactoryInterface $databaseTransactionFactory,
        AxytosOrderCommandFacade $commandFacade,
        LoggerAdapterInterface $logger
    ) {
        $this->errorReportingClient = $errorReportingClient;
        $this->databaseTransactionFactory = $databaseTransactionFactory;
        $this->commandFacade = $commandFacade;
        $this->logger = $logger;
    }

    /**
     * @param \Axytos\KaufAufRechnung\Core\Plugin\Abstractions\PluginOrderInterface $pluginOrder
     * @return \Axytos\KaufAufRechnung\Core\Model\AxytosOrder
     */
    public function create($pluginOrder)
    {
        $eventEmitter = new AxytosOrderEventEmitter();
        $stateMachine = new OrderStateMachine(
            $pluginOrder,
            $this->errorReportingClient,
            $this->databaseTransactionFactory,
            $this->commandFacade,
            $eventEmitter,
            $this->logger
        );
        return new AxytosOrder(
            $eventEmitter,
            $stateMachine
        );
    }

    /**
     * @param \Axytos\KaufAufRechnung\Core\Plugin\Abstractions\PluginOrderInterface[] $pluginOrders
     * @return \Axytos\KaufAufRechnung\Core\Model\AxytosOrder[]
     */
    public function createMany($pluginOrders)
    {
        return array_map([$this, 'create'], $pluginOrders);
    }
}
