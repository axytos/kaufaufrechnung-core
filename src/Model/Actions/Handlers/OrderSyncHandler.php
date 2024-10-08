<?php

namespace Axytos\KaufAufRechnung\Core\Model\Actions\Handlers;

use Axytos\KaufAufRechnung\Core\Model\Actions\ActionHandlerInterface;
use Axytos\KaufAufRechnung\Core\Model\Actions\Results\InvalidDataResult;
use Axytos\KaufAufRechnung\Core\Model\Actions\Results\SuccessResult;
use Axytos\KaufAufRechnung\Core\OrderSyncWorker;

class OrderSyncHandler implements ActionHandlerInterface
{
    /**
     * @var OrderSyncWorker
     */
    private $orderSyncWorker;

    public function __construct(OrderSyncWorker $orderSyncWorker)
    {
        $this->orderSyncWorker = $orderSyncWorker;
    }

    /**
     * @return string
     */
    public function action()
    {
        return 'order-sync';
    }

    /**
     * @param array<string,mixed>|null $parameters
     *
     * @return \Axytos\KaufAufRechnung\Core\Abstractions\Model\Actions\ActionResultInterface
     */
    public function handle($parameters = null)
    {
        $batchSize = $this->getParam($parameters, 'batchSize');
        if (!is_null($batchSize) && !is_int($batchSize)) {
            return new InvalidDataResult('must be int or null', 'params.batchSize');
        }

        $startToken = $this->getParam($parameters, 'startToken');
        if (!is_null($startToken) && !is_string($startToken)) {
            return new InvalidDataResult('must be string or null', 'params.startToken');
        }

        $nextToken = $this->orderSyncWorker->sync($batchSize, $startToken);

        return new SuccessResult($nextToken);
    }

    /**
     * @param array<string,mixed>|null $parameters
     * @param string                   $key
     *
     * @return mixed
     */
    private function getParam($parameters, $key)
    {
        if (is_null($parameters)) {
            return null;
        }

        if (!array_key_exists($key, $parameters)) {
            return null;
        }

        return $parameters[$key];
    }
}
