<?php

namespace Axytos\KaufAufRechnung\Core\Model\Actions;

use Axytos\KaufAufRechnung\Core\Abstractions\Model\Actions\ActionExecutorInterface;
use Axytos\KaufAufRechnung\Core\Model\Actions\Handlers\OrderSyncHandler;
use Axytos\KaufAufRechnung\Core\Model\Actions\Results\InvalidClientSecretResult;
use Axytos\KaufAufRechnung\Core\Model\Actions\Results\UnknownActionResult;
use Axytos\KaufAufRechnung\Core\OrderSyncWorker;
use Axytos\KaufAufRechnung\Core\Plugin\Abstractions\Configuration\ClientSecretProviderInterface;

class ActionExecutor implements ActionExecutorInterface
{
    /**
     * @var ClientSecretProviderInterface
     */
    private $clientSecretProvider;

    /**
     * @var \Axytos\KaufAufRechnung\Core\Model\Actions\ActionHandlerInterface[]
     */
    private $handlers;

    public function __construct(
        ClientSecretProviderInterface $clientSecretProvider,
        OrderSyncWorker $orderSyncWorker
    ) {
        $this->clientSecretProvider = $clientSecretProvider;
        $this->handlers = [
            new OrderSyncHandler($orderSyncWorker),
        ];
    }

    /**
     * @param string                   $clientSecret
     * @param string                   $action
     * @param array<string,mixed>|null $parameters
     *
     * @return \Axytos\KaufAufRechnung\Core\Abstractions\Model\Actions\ActionResultInterface
     */
    public function executeAction($clientSecret, $action, $parameters = null)
    {
        if (!$this->validateClientSecret($clientSecret)) {
            return new InvalidClientSecretResult();
        }

        $handler = $this->findHandler($action);
        if (is_null($handler)) {
            return new UnknownActionResult($action);
        }

        return $handler->handle($parameters);
    }

    /**
     * @param string $clientSecret
     *
     * @return bool
     */
    private function validateClientSecret($clientSecret)
    {
        $expectedClientSecret = $this->clientSecretProvider->getClientSecret();
        if (is_null($expectedClientSecret) || '' === $expectedClientSecret) {
            return false;
        }

        return $clientSecret === $expectedClientSecret;
    }

    /**
     * @param string $action
     *
     * @return ActionHandlerInterface|null
     */
    private function findHandler($action)
    {
        foreach ($this->handlers as $handler) {
            if (0 === strcasecmp($handler->action(), $action)) {
                return $handler;
            }
        }

        return null;
    }
}
