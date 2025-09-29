<?php

namespace Axytos\KaufAufRechnung\Core\Model\OrderStateMachine\States;

use Axytos\ECommerce\Clients\Invoice\ShopActions;
use Axytos\KaufAufRechnung\Core\Abstractions\Model\AxytosOrderCheckoutAction;
use Axytos\KaufAufRechnung\Core\Abstractions\Model\AxytosOrderEvents;
use Axytos\KaufAufRechnung\Core\Model\OrderStateMachine\OrderStates;

class UncheckedState extends AbstractState
{
    /**
     * @param bool $skipPrecheck
     *
     * @return void
     */
    public function checkout($skipPrecheck = false)
    {
        try {
            $this->context->emit(AxytosOrderEvents::CHECKOUT_BEFORE_CHECK);

            $pluginOrder = $this->context->getPluginOrder();
            $pluginOrder->freezeBasket();

            if (!$skipPrecheck) {
                $shopAction = $this->context->checkoutPrecheck();
                if (ShopActions::CHANGE_PAYMENT_METHOD === $shopAction) {
                    $this->context->changeState(OrderStates::CHECKOUT_REJECTED);
                    $this->context->emit(AxytosOrderEvents::CHECKOUT_AFTER_REJECTED);

                    return;
                }
            }

            $this->context->emit(AxytosOrderEvents::CHECKOUT_AFTER_ACCEPTED);

            $this->context->checkoutConfirm($skipPrecheck);
            $this->context->changeState(OrderStates::CHECKOUT_CONFIRMED);

            $this->context->emit(AxytosOrderEvents::CHECKOUT_AFTER_CONFIRMED);
        } catch (\Throwable $th) {
            $this->context->changeState(OrderStates::CHECKOUT_FAILED);
            $this->context->emit(AxytosOrderEvents::CHECKOUT_AFTER_FAILED);
            throw $th;
        } catch (\Exception $th) { // @phpstan-ignore-line because of php5 compatibility
            $this->context->changeState(OrderStates::CHECKOUT_FAILED);
            $this->context->emit(AxytosOrderEvents::CHECKOUT_AFTER_FAILED);
            throw $th;
        }
    }

    /**
     * @return string|null
     *
     * @phpstan-return \Axytos\KaufAufRechnung\Core\Abstractions\Model\AxytosOrderCheckoutAction::*|null
     */
    public function getCheckoutAction()
    {
        return AxytosOrderCheckoutAction::CHANGE_PAYMENT_METHOD;
    }
}
