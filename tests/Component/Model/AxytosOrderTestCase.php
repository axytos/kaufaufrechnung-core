<?php

namespace Axytos\KaufAufRechnung\Core\Tests\Component\Model;

use Axytos\ECommerce\Clients\ErrorReporting\ErrorReportingClientInterface;
use Axytos\ECommerce\Clients\Invoice\InvoiceClientInterface;
use Axytos\KaufAufRechnung\Core\Model\AxytosOrder;
use Axytos\KaufAufRechnung\Core\Model\AxytosOrderCommandFacade;
use Axytos\KaufAufRechnung\Core\Model\AxytosOrderCommandInterface;
use Axytos\KaufAufRechnung\Core\Model\AxytosOrderFactory;
use Axytos\KaufAufRechnung\Core\Plugin\Abstractions\Database\DatabaseTransactionFactoryInterface;
use Axytos\KaufAufRechnung\Core\Plugin\Abstractions\Database\DatabaseTransactionInterface;
use Axytos\KaufAufRechnung\Core\Plugin\Abstractions\Logging\LoggerAdapterInterface;
use Axytos\KaufAufRechnung\Core\Plugin\Abstractions\Model\AxytosOrderStateInfo;
use Axytos\KaufAufRechnung\Core\Plugin\Abstractions\PluginOrderInterface;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

abstract class AxytosOrderTestCase extends TestCase
{
    /**
     * @var InvoiceClientInterface&MockObject
     */
    protected $invoiceClient;

    /**
     * @var PluginOrderInterface&MockObject
     */
    protected $pluginOrder;

    /**
     * @var DatabaseTransactionFactoryInterface&MockObject
     */
    protected $databaseTransactionFactory;

    /**
     * @var DatabaseTransactionInterface&MockObject
     */
    protected $databaseTransaction;

    /**
     * @var ErrorReportingClientInterface&MockObject
     */
    protected $errorReportingClient;

    /**
     * @var \Axytos\KaufAufRechnung\Core\Model\AxytosOrderCommandFacade&MockObject
     */
    protected $commandFacade;

    /**
     * @var AxytosOrder
     */
    protected $sut;

    /**
     * @var string[]
     */
    protected $emittedEvents;

    /**
     * @before
     *
     * @return void
     */
    #[Before]
    public function beforeEach()
    {
        $this->pluginOrder = $this->createMock(PluginOrderInterface::class);
        $this->invoiceClient = $this->createMock(InvoiceClientInterface::class);
        $this->databaseTransactionFactory = $this->createMock(DatabaseTransactionFactoryInterface::class);
        $this->databaseTransaction = $this->createMock(DatabaseTransactionInterface::class);
        $this->errorReportingClient = $this->createMock(ErrorReportingClientInterface::class);
        $this->commandFacade = $this->createMock(AxytosOrderCommandFacade::class);

        $orderFactory = new AxytosOrderFactory(
            $this->errorReportingClient,
            $this->databaseTransactionFactory,
            $this->commandFacade,
            $this->createMock(LoggerAdapterInterface::class)
        );

        $this->setUpPluginOrder();
        $this->setUpDatabaseTransaction();

        $this->sut = $orderFactory->create($this->pluginOrder);
    }

    /**
     * @return string
     *
     * @phpstan-return \Axytos\KaufAufRechnung\Core\Model\OrderStateMachine\OrderStates::*
     */
    abstract protected function initialState();

    /**
     * @return array<string,mixed>
     */
    protected function initialStateData()
    {
        return [];
    }

    /**
     * @return void
     */
    protected function setUpPluginOrder()
    {
        $this->pluginOrder->method('loadState')->willReturn(new AxytosOrderStateInfo(
            $this->initialState(),
            serialize($this->initialStateData())
        ));
    }

    /**
     * @return void
     */
    protected function setUpDatabaseTransaction()
    {
        $this->databaseTransactionFactory->method('create')->willReturn($this->databaseTransaction);
    }

    /**
     * @param string $eventName
     *
     * @phpstan-param \Axytos\KaufAufRechnung\Core\Abstractions\Model\AxytosOrderEvents::* $eventName
     *
     * @param int|null $expectedEmitCount
     *
     * @return void
     */
    protected function expectEventEmitted($eventName, $expectedEmitCount = null)
    {
        /** @var MockObject */
        $callbackMock = $this->createMock(AxytosOrderCommandInterface::class);
        $callbackMock
            ->expects(null !== $expectedEmitCount ? $this->exactly($expectedEmitCount) : $this->once())
            ->method('execute')
        ;

        /** @phpstan-ignore-next-line */
        $this->sut->subscribeEventListener($eventName, [$callbackMock, 'execute']);
    }
}
