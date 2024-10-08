<?php

namespace Axytos\KaufAufRechnung\Core\Tests\Unit\Model\Actions;

use Axytos\KaufAufRechnung\Core\Model\Actions\ActionExecutor;
use Axytos\KaufAufRechnung\Core\Model\Actions\Results\InvalidClientSecretResult;
use Axytos\KaufAufRechnung\Core\Model\Actions\Results\InvalidDataResult;
use Axytos\KaufAufRechnung\Core\Model\Actions\Results\SuccessResult;
use Axytos\KaufAufRechnung\Core\Model\Actions\Results\UnknownActionResult;
use Axytos\KaufAufRechnung\Core\OrderSyncWorker;
use Axytos\KaufAufRechnung\Core\Plugin\Abstractions\Configuration\ClientSecretProviderInterface;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class ActionExecutorTest extends TestCase
{
    /**
     * @var ClientSecretProviderInterface&MockObject
     */
    private $clientSecretProvider;

    /**
     * @var OrderSyncWorker&MockObject
     */
    private $orderSyncWorker;

    /**
     * @var ActionExecutor
     */
    private $sut;

    /**
     * @before
     *
     * @return void
     */
    #[Before]
    public function beforeEach()
    {
        $this->clientSecretProvider = $this->createMock(ClientSecretProviderInterface::class);
        $this->orderSyncWorker = $this->createMock(OrderSyncWorker::class);

        $this->sut = new ActionExecutor($this->clientSecretProvider, $this->orderSyncWorker);
    }

    /**
     * @return void
     */
    public function test_execute_action_returns_invalid_secret_result_if_secrets_are_not_equal()
    {
        $this->clientSecretProvider
            ->method('getClientSecret')
            ->willReturn('expected-secret')
        ;

        $result = $this->sut->executeAction('invalid-secret', 'action');

        $this->assertInstanceOf(InvalidClientSecretResult::class, $result);
    }

    /**
     * @return void
     */
    public function test_execute_action_returns_invalid_secret_result_if_secret_is_not_configured()
    {
        $this->clientSecretProvider
            ->method('getClientSecret')
            ->willReturn(null)
        ;

        /** @phpstan-ignore-next-line */
        $result = $this->sut->executeAction(null, 'action');

        $this->assertInstanceOf(InvalidClientSecretResult::class, $result);
    }

    /**
     * @return void
     */
    public function test_execute_action_returns_invalid_secret_result_if_secret_is_empty()
    {
        $this->clientSecretProvider
            ->method('getClientSecret')
            ->willReturn('')
        ;

        $result = $this->sut->executeAction('', 'action');

        $this->assertInstanceOf(InvalidClientSecretResult::class, $result);
    }

    /**
     * @return void
     */
    public function test_execute_action_returns_unknown_action_if_action_has_no_handler()
    {
        $expectedSecret = 'expected-secret';
        $this->clientSecretProvider
            ->method('getClientSecret')
            ->willReturn($expectedSecret)
        ;

        /** @var UnknownActionResult */
        $result = $this->sut->executeAction($expectedSecret, 'unknown-action');

        $this->assertInstanceOf(UnknownActionResult::class, $result);
        $this->assertCount(1, $result->getResult());
        $this->assertEquals('unknown-action', $result->getResult()[0]->getDetails());
    }

    /**
     * @return void
     */
    public function test_execute_action_invokes_order_sync_handler_for_order_sync_action()
    {
        $expectedSecret = 'expected-secret';
        $this->clientSecretProvider
            ->method('getClientSecret')
            ->willReturn($expectedSecret)
        ;

        $this->orderSyncWorker
            ->expects($this->once())
            ->method('sync')
            ->with(null, null)
            ->willReturn(null)
        ;

        $result = $this->sut->executeAction($expectedSecret, 'order-sync');

        $this->assertInstanceOf(SuccessResult::class, $result);
        $this->assertEmpty($result->getResult());
    }

    /**
     * @return void
     */
    public function test_execute_action_invokes_order_sync_handler_case_insensitive()
    {
        $expectedSecret = 'expected-secret';
        $this->clientSecretProvider
            ->method('getClientSecret')
            ->willReturn($expectedSecret)
        ;

        $this->orderSyncWorker
            ->expects($this->once())
            ->method('sync')
            ->with(null, null)
            ->willReturn(null)
        ;

        $result = $this->sut->executeAction($expectedSecret, 'oRdEr-SyNc');

        $this->assertInstanceOf(SuccessResult::class, $result);
        $this->assertEmpty($result->getResult());
    }

    /**
     * @return void
     */
    public function test_execute_action_invokes_order_sync_handler_with_parameters()
    {
        $expectedSecret = 'expected-secret';
        $expectedBatchSize = 42;
        $expectedStartToken = 'expected-start-token';
        $this->clientSecretProvider
            ->method('getClientSecret')
            ->willReturn($expectedSecret)
        ;

        $this->orderSyncWorker
            ->expects($this->once())
            ->method('sync')
            ->with($expectedBatchSize, $expectedStartToken)
            ->willReturn(null)
        ;

        $result = $this->sut->executeAction($expectedSecret, 'order-sync', [
            'batchSize' => $expectedBatchSize,
            'startToken' => $expectedStartToken,
        ]);

        $this->assertInstanceOf(SuccessResult::class, $result);
        $this->assertEmpty($result->getResult());
    }

    /**
     * @return void
     */
    public function test_execute_action_returns_invalid_data_if_batch_size_parameter_has_invalid_type()
    {
        $expectedSecret = 'expected-secret';
        $this->clientSecretProvider
            ->method('getClientSecret')
            ->willReturn($expectedSecret)
        ;

        $this->orderSyncWorker
            ->expects($this->never())
            ->method('sync')
        ;

        $result = $this->sut->executeAction($expectedSecret, 'order-sync', [
            'batchSize' => 'invalid',
        ]);

        $this->assertInstanceOf(InvalidDataResult::class, $result);
    }

    /**
     * @return void
     */
    public function test_execute_action_returns_invalid_data_if_start_token_parameter_has_invalid_type()
    {
        $expectedSecret = 'expected-secret';
        $this->clientSecretProvider
            ->method('getClientSecret')
            ->willReturn($expectedSecret)
        ;

        $this->orderSyncWorker
            ->expects($this->never())
            ->method('sync')
        ;

        $result = $this->sut->executeAction($expectedSecret, 'order-sync', [
            'startToken' => 123,
        ]);

        $this->assertInstanceOf(InvalidDataResult::class, $result);
    }

    /**
     * @return void
     */
    public function test_execute_action_returns_next_token_in_result_if_returned_by_sync()
    {
        $expectedSecret = 'expected-secret';
        $expectedNextToken = 'next-token';
        $this->clientSecretProvider
            ->method('getClientSecret')
            ->willReturn($expectedSecret)
        ;

        $this->orderSyncWorker
            ->expects($this->once())
            ->method('sync')
            ->willReturn($expectedNextToken)
        ;

        $result = $this->sut->executeAction($expectedSecret, 'order-sync');

        $this->assertInstanceOf(SuccessResult::class, $result);
        $this->assertCount(1, $result->getResult());
        $this->assertEquals('nextToken', $result->getResult()[0]->getMessage());
        $this->assertEquals($expectedNextToken, $result->getResult()[0]->getDetails());
    }
}
