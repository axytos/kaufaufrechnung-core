<?php

namespace Axytos\KaufAufRechnung\Core\Tests\Integration;

use Axytos\ECommerce\AxytosECommerceClient;
use Axytos\ECommerce\Tests\Integration\Providers\ApiHostProvider;
use Axytos\ECommerce\Tests\Integration\Providers\ApiKeyProvider;
use Axytos\ECommerce\Tests\Integration\Providers\FallbackModeConfiguration;
use Axytos\ECommerce\Tests\Integration\Providers\PaymentMethodConfiguration;
use Axytos\ECommerce\Tests\Integration\Providers\UserAgentInfoProvider;
use Axytos\ECommerce\Clients\ErrorReporting\ErrorReportingClientInterface;
use Axytos\KaufAufRechnung\Core\Model\AxytosOrder;
use Axytos\KaufAufRechnung\Core\Model\AxytosOrderCommandFacade;
use Axytos\KaufAufRechnung\Core\Model\AxytosOrderFactory;
use Axytos\KaufAufRechnung\Core\Plugin\Abstractions\Information\CheckoutInformationInterface;
use Axytos\KaufAufRechnung\Core\Plugin\Abstractions\Logging\LoggerAdapterInterface;
use Axytos\KaufAufRechnung\Core\Plugin\Abstractions\Model\AxytosOrderStateInfo;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class IntegrationTestCase extends TestCase
{
    use AxytosMockModelFactoryTrait;

    /**
     * @var AxytosOrder
     */
    protected $sut;

    /**
     * @var string
     */
    private $lastStateName;

    /**
     * @var mixed
     */
    private $preCheckResponse;

    /**
     * @var bool
     */
    private $hasBeenCanceled;

    /**
     * @var bool
     */
    private $hasBeenInvoiced;

    /**
     * @var bool
     */
    private $hasBeenRefunded;

    /**
     * @var bool
     */
    private $hasBeenShipped;

    /**
     * @var bool
     */
    private $hasNewTrackingInformation;

    /**
     * @var bool
     */
    private $hasBasketUpdates;

    /**
     * @var bool
     */
    private $shippingReportedSaved;

    /**
     * @var bool
     */
    private $newTrackingInformationSaved;

    /**
     * @var bool
     */
    private $basketUpdatesReportedSaved;

    /**
     * @var bool
     */
    private $basketFrozen;

    /**
     * @before
     * @return void
     */
    #[Before]
    public function beforeEach()
    {
        $invoiceClient = new AxytosECommerceClient(
            new ApiHostProvider(),
            new ApiKeyProvider(),
            new PaymentMethodConfiguration(),
            new FallbackModeConfiguration(),
            new UserAgentInfoProvider(),
            $this->createMock(\Axytos\ECommerce\Logging\LoggerAdapterInterface::class)
        );

        $commandFacade = new AxytosOrderCommandFacade(
            $invoiceClient,
            $this->createMock(ErrorReportingClientInterface::class),
            $this->createMock(LoggerAdapterInterface::class)
        );

        $axytosOrderFactory = new AxytosOrderFactory(
            $this->createMock(ErrorReportingClientInterface::class),
            $this->createDatabaseTransactionFactoryMock(),
            $commandFacade,
            $this->createMock(LoggerAdapterInterface::class)
        );

        $this->hasBeenCanceled = false;
        $this->hasBeenInvoiced = false;
        $this->hasBeenRefunded = false;
        $this->hasBeenShipped = false;
        $this->hasNewTrackingInformation = false;
        $this->hasBasketUpdates = false;

        $this->shippingReportedSaved = false;
        $this->newTrackingInformationSaved = false;
        $this->basketUpdatesReportedSaved = false;

        $this->basketFrozen = false;

        $pluginOrder = $this->createPluginOrderMock();
        /** @var CheckoutInformationInterface&MockObject */
        $checkoutInformation = $pluginOrder->checkoutInformation();

        $checkoutInformation->method('savePreCheckResponseData')->willReturnCallback([$this, 'savePreCheckResponseData']);
        $checkoutInformation->method('getPreCheckResponseData')->willReturnCallback([$this, 'loadPreCheckResponseData']);
        $pluginOrder->method('saveState')->willReturnCallback([$this, 'saveState']);
        $pluginOrder->method('loadState')->willReturnCallback([$this, 'loadState']);
        $pluginOrder->method('hasBeenCanceled')->willReturnCallback([$this, 'hasBeenCanceled']);
        $pluginOrder->method('hasBeenInvoiced')->willReturnCallback([$this, 'hasBeenInvoiced']);
        $pluginOrder->method('hasBeenRefunded')->willReturnCallback([$this, 'hasBeenRefunded']);
        $pluginOrder->method('hasBeenShipped')->willReturnCallback([$this, 'hasBeenShipped']);
        $pluginOrder->method('hasNewTrackingInformation')->willReturnCallback([$this, 'hasNewTrackingInformation']);
        $pluginOrder->method('hasBasketUpdates')->willReturnCallback([$this, 'hasBasketUpdates']);

        $pluginOrder->method('saveHasShippingReported')->willReturnCallback([$this, 'saveHasShippingReported']);
        $pluginOrder->method('saveNewTrackingInformation')->willReturnCallback([$this, 'saveNewTrackingInformation']);
        $pluginOrder->method('saveBasketUpdatesReported')->willReturnCallback([$this, 'saveBasketUpdatesReported']);
        $pluginOrder->method('freezeBasket')->willReturnCallback([$this, 'freezeBasket']);

        $this->sut = $axytosOrderFactory->create($pluginOrder);
    }

    /**
     * @param string $expectedStateClassName
     * @phpstan-param class-string<\Axytos\KaufAufRechnung\Core\Model\OrderStateMachine\OrderStateInterface> $expectedStateClassName
     * @return void
     */
    public function thenAssertStateIs($expectedStateClassName)
    {
        $this->assertInstanceOf($expectedStateClassName, $this->sut->getCurrentState());
    }

    /**
     * @param bool $expectedValue
     * @return void
     */
    public function thenAssertShippingReportedSaved($expectedValue)
    {
        $this->assertEquals($expectedValue, $this->shippingReportedSaved);
    }

    /**
     * @param bool $expectedValue
     * @return void
     */
    public function thenAssertNewTrackingInformationSaved($expectedValue)
    {
        $this->assertEquals($expectedValue, $this->newTrackingInformationSaved);
    }

    /**
     * @param bool $expectedValue
     * @return void
     */
    public function thenAssertBasketUpdateReportedSaved($expectedValue)
    {
        $this->assertEquals($expectedValue, $this->basketUpdatesReportedSaved);
    }

    /**
     * @param bool $expectedValue
     * @return void
     */
    public function thenAssertBasketFrozen($expectedValue)
    {
        $this->assertEquals($expectedValue, $this->basketFrozen);
    }

    /**
     * @param mixed $data
     * @return void
     */
    public function savePreCheckResponseData($data)
    {
        $this->preCheckResponse = $data;
    }

    /**
     * @return mixed
     */
    public function loadPreCheckResponseData()
    {
        return $this->preCheckResponse;
    }

    /**
     * @param string $stateName
     * @return void
     */
    public function saveState($stateName)
    {
        $this->lastStateName = $stateName;
    }

    /**
     * @return AxytosOrderStateInfo
     */
    public function loadState()
    {
        return new AxytosOrderStateInfo($this->lastStateName, null);
    }

    /**
     * @return bool
     */
    public function hasBeenCanceled()
    {
        return $this->hasBeenCanceled;
    }

    /**
     * @param bool $value
     * @return void
     */
    public function givenHasBeenCanceled($value)
    {
        $this->hasBeenCanceled = $value;
    }

    /**
     * @return bool
     */
    public function hasBeenInvoiced()
    {
        return $this->hasBeenInvoiced;
    }

    /**
     * @param bool $value
     * @return void
     */
    public function givenHasBeenInvoiced($value)
    {
        $this->hasBeenInvoiced = $value;
    }

    /**
     * @return bool
     */
    public function hasBeenRefunded()
    {
        return $this->hasBeenRefunded;
    }

    /**
     * @param bool $value
     * @return void
     */
    public function givenHasBeenRefunded($value)
    {
        $this->hasBeenRefunded = $value;
    }

    /**
     * @return bool
     */
    public function hasBeenShipped()
    {
        return $this->hasBeenShipped;
    }

    /**
     * @param bool $value
     * @return void
     */
    public function givenHasBeenShipped($value)
    {
        $this->hasBeenShipped = $value;
    }

    /**
     * @return bool
     */
    public function hasNewTrackingInformation()
    {
        return $this->hasNewTrackingInformation;
    }

    /**
     * @param bool $value
     * @return void
     */
    public function givenHasNewTrackingInformation($value)
    {
        $this->hasNewTrackingInformation = $value;
    }

    /**
     * @return bool
     */
    public function hasBasketUpdates()
    {
        return $this->hasBasketUpdates;
    }

    /**
     * @param bool $value
     * @return void
     */
    public function givenHasBasketUpdates($value)
    {
        $this->hasBasketUpdates = $value;
    }

    /**
     * @return void
     */
    public function saveHasShippingReported()
    {
        $this->shippingReportedSaved = true;
    }

    /**
     * @return void
     */
    public function saveNewTrackingInformation()
    {
        $this->newTrackingInformationSaved = true;
    }

    /**
     * @return void
     */
    public function saveBasketUpdatesReported()
    {
        $this->basketUpdatesReportedSaved = true;
    }

    /**
     * @return void
     */
    public function freezeBasket()
    {
        $this->basketFrozen = true;
    }
}
