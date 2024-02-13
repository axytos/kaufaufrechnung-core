<?php

namespace Axytos\KaufAufRechnung\Core\Tests\Unit\Model;

use Axytos\ECommerce\Clients\ErrorReporting\ErrorReportingClientInterface;
use Axytos\ECommerce\Clients\Invoice\InvoiceClientInterface;
use Axytos\KaufAufRechnung\Core\Abstractions\Model\AxytosOrderEvents;
use Axytos\KaufAufRechnung\Core\Model\AxytosOrder;
use Axytos\KaufAufRechnung\Core\Model\AxytosOrderCommandFacade;
use Axytos\KaufAufRechnung\Core\Model\AxytosOrderFactory;
use Axytos\KaufAufRechnung\Core\Plugin\Abstractions\Logging\LoggerAdapterInterface;
use Axytos\KaufAufRechnung\Core\Plugin\Abstractions\Database\DatabaseTransactionFactoryInterface;
use Axytos\KaufAufRechnung\Core\Plugin\Abstractions\Database\DatabaseTransactionInterface;
use Axytos\KaufAufRechnung\Core\Plugin\Abstractions\PluginOrderInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AxytosOrderTestCase extends TestCase
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
     * @var AxytosOrder
     */
    protected $sut;

    /**
     * @var string[]
     */
    protected $emittedEvents;

    /**
     * @before
     * @return void
     */
    public function beforeEach()
    {
        $this->invoiceClient = $this->createMock(InvoiceClientInterface::class);
        $this->databaseTransactionFactory = $this->createMock(DatabaseTransactionFactoryInterface::class);

        $this->databaseTransaction = $this->createMock(DatabaseTransactionInterface::class);

        $this->errorReportingClient = $this->createMock(ErrorReportingClientInterface::class);

        $orderFactory = new AxytosOrderFactory(
            $this->errorReportingClient,
            $this->databaseTransactionFactory,
            $this->createMock(AxytosOrderCommandFacade::class),
            $this->createMock(LoggerAdapterInterface::class)
        );

        $this->pluginOrder = $this->createMock(PluginOrderInterface::class);

        $this->sut = $orderFactory->create($this->pluginOrder);

        $this->fakeSaveOrderCheckStateMemory();
        $this->setUpDatabaseTransaction();
        $this->setUpEventListeners();
    }

    /**
     * @return void
     */
    protected function fakeSaveOrderCheckStateMemory()
    {
        $memory = null;

        $this->pluginOrder->method('saveOrderCheckState')->willReturnCallback(function ($orderCheckState) use (&$memory) {
            $memory = $orderCheckState;
        });
        $this->pluginOrder->method('getOrderCheckState')->willReturnCallback(function () use (&$memory) {
            return $memory;
        });
    }

    /**
     * @return void
     */
    protected function setUpDatabaseTransaction()
    {
        $this->databaseTransactionFactory->method('create')->willReturn($this->databaseTransaction);
    }

    /**
     * @return void
     */
    protected function setUpEventListeners()
    {
        $this->emittedEvents = [];
        $catcher = function ($eventName) {
            array_push($this->emittedEvents, $eventName);
        };
        $this->sut->subscribeEventListener(AxytosOrderEvents::CHECKOUT_BEFORE_CHECK, $catcher);
        $this->sut->subscribeEventListener(AxytosOrderEvents::CHECKOUT_AFTER_REJECTED, $catcher);
        $this->sut->subscribeEventListener(AxytosOrderEvents::CHECKOUT_AFTER_ACCEPTED, $catcher);
        $this->sut->subscribeEventListener(AxytosOrderEvents::CHECKOUT_AFTER_CONFIRMED, $catcher);
        $this->sut->subscribeEventListener(AxytosOrderEvents::CHECKOUT_AFTER_FAILED, $catcher);
    }

    /**
     * @param string $eventName
     * @phpstan-param \Axytos\KaufAufRechnung\Core\Abstractions\Model\AxytosOrderEvents::* $eventName
     * @return void
     */
    protected function assertAxytosOrderEventEmitted($eventName)
    {
        $this->assertTrue(in_array($eventName, $this->emittedEvents, true), "Failed asserting $eventName was emitted.");
    }

    /**
     * @param string $eventName
     * @phpstan-param \Axytos\KaufAufRechnung\Core\Abstractions\Model\AxytosOrderEvents::* $eventName
     * @return void
     */
    protected function givenAxytosOrderEventListenerError($eventName)
    {
        $this->sut->subscribeEventListener($eventName, function () {
            throw new \Exception();
        });
    }
}
