<?php

namespace Axytos\KaufAufRechnung\Core\Model;

use Axytos\ECommerce\Clients\ErrorReporting\ErrorReportingClientInterface;
use Axytos\KaufAufRechnung\Core\Model\OrderStateMachine\OrderStateMachine;
use Axytos\KaufAufRechnung\Core\Plugin\Abstractions\Database\DatabaseTransactionFactoryInterface;
use Axytos\KaufAufRechnung\Core\Plugin\Abstractions\Logging\LoggerAdapterInterface;

class AxytosOrderFactory
{
    /**
     * @var ErrorReportingClientInterface
     */
    private $errorReportingClient;

    /**
     * @var DatabaseTransactionFactoryInterface
     */
    private $databaseTransactionFactory;

    /**
     * @var AxytosOrderCommandFacade
     */
    private $commandFacade;

    /**
     * @var LoggerAdapterInterface
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
     *
     * @return AxytosOrder
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
     *
     * @return \Axytos\KaufAufRechnung\Core\Model\AxytosOrder[]
     */
    public function createMany($pluginOrders)
    {
        return array_map([$this, 'create'], $pluginOrders);
    }
}
