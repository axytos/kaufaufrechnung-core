<?php

namespace Axytos\KaufAufRechnung\Core\Tests\Unit\Model\OrderStateMachine\States;

use Axytos\KaufAufRechnung\Core\Model\OrderStateMachine\OrderStateContextInterface;
use Axytos\KaufAufRechnung\Core\Model\OrderStateMachine\OrderStates;
use Axytos\KaufAufRechnung\Core\Model\OrderStateMachine\States\CanceledState;
use Axytos\KaufAufRechnung\Core\Plugin\Abstractions\PluginOrderInterface;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class CanceledStateTest extends TestCase
{
    /**
     * @var OrderStateContextInterface&MockObject
     */
    private $context;

    /**
     * @var PluginOrderInterface&MockObject
     */
    private $pluginOrder;

    /**
     * @var CanceledState
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
        $this->context = $this->createMock(OrderStateContextInterface::class);
        $this->pluginOrder = $this->createMock(PluginOrderInterface::class);

        $this->context
            ->method('getPluginOrder')
            ->willReturn($this->pluginOrder)
        ;

        $this->sut = new CanceledState();
        $this->sut->setContext($this->context);
    }

    /**
     * @return void
     */
    public function test_sync_critical_changes_does_nothing_if_still_canceled()
    {
        $this->pluginOrder
            ->method('hasBeenCanceled')
            ->willReturn(true)
        ;
        $this->context
            ->method('getStateValue')
            ->with(CanceledState::CANCEL_DATE)
            ->willReturn(time())
        ;

        $this->context
            ->expects($this->never())
            ->method('changeState')
        ;

        $this->sut->syncCriticalChanges();
    }

    /**
     * @return void
     */
    public function test_sync_critical_changes_transitions_to_confirmed_when_uncanceled()
    {
        $this->pluginOrder
            ->method('hasBeenCanceled')
            ->willReturn(false)
        ;
        $this->context
            ->method('getStateValue')
            ->with(CanceledState::CANCEL_DATE)
            ->willReturn(time())
        ;

        $this->context
            ->expects($this->once())
            ->method('changeState')
            ->with(OrderStates::CHECKOUT_CONFIRMED)
        ;

        $this->sut->syncCriticalChanges();
    }

    /**
     * @return void
     */
    public function test_sync_critical_changes_transitions_to_completely_canceled_when_canceled_for_at_least30_days()
    {
        $this->pluginOrder
            ->method('hasBeenCanceled')
            ->willReturn(true)
        ;
        $this->context
            ->method('getStateValue')
            ->with(CanceledState::CANCEL_DATE)
            ->willReturn(time() - 3024000) // 35 days
        ;

        $this->context
            ->expects($this->once())
            ->method('changeState')
            ->with(OrderStates::COMPLETELY_CANCELED)
        ;

        $this->sut->syncCriticalChanges();
    }

    /**
     * @return void
     */
    public function test_sync_critical_changes_transitions_to_self_when_no_cancel_date_was_given()
    {
        $this->pluginOrder
            ->method('hasBeenCanceled')
            ->willReturn(true)
        ;
        $this->context
            ->method('getStateValue')
            ->with(CanceledState::CANCEL_DATE)
            ->willReturn(null)
        ;

        $this->context
            ->expects($this->once())
            ->method('changeState')
            ->with(OrderStates::CANCELED)
        ;

        $this->sut->syncCriticalChanges();
    }

    /**
     * @return void
     */
    public function test_on_enter_save_current_time_as_cancel_date()
    {
        $matcher = null;
        /** @phpstan-ignore-next-line */
        if (method_exists($this, 'equalToWithDelta')) {
            $matcher = $this->equalToWithDelta(time(), 1.0);
        } else {
            /** @phpstan-ignore-next-line because fallback for php5 */
            $matcher = $this->equalTo(time(), 1.0);
        }

        $this->context
            ->expects($this->once())
            ->method('setStateValue')
            ->with(
                CanceledState::CANCEL_DATE,
                $matcher
            )
        ;

        $this->sut->onEnter();
    }

    /**
     * @return void
     */
    public function test_on_exit_deletes_previously_saved_cancel_date()
    {
        $this->context
            ->expects($this->once())
            ->method('unsetStateValue')
            ->with(CanceledState::CANCEL_DATE)
        ;

        $this->sut->onExit();
    }
}
