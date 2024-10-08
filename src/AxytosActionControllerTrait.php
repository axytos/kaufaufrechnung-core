<?php

namespace Axytos\KaufAufRechnung\Core;

use Axytos\KaufAufRechnung\Core\Model\Actions\Results\FatalErrorResult;
use Axytos\KaufAufRechnung\Core\Model\Actions\Results\InvalidDataResult;
use Axytos\KaufAufRechnung\Core\Model\Actions\Results\InvalidMethodResult;
use Axytos\KaufAufRechnung\Core\Model\Actions\Results\PluginNotConfiguredResult;

trait AxytosActionControllerTrait
{
    /**
     * @var Abstractions\Model\Actions\ActionExecutorInterface
     */
    protected $actionExecutor;

    /**
     * @var Plugin\Abstractions\Logging\LoggerAdapterInterface
     */
    protected $logger;

    /**
     * @var \Axytos\ECommerce\Clients\Invoice\PluginConfigurationValidator
     */
    private $pluginConfigurationValidator;

    /**
     * @return string
     */
    abstract protected function getRequestBody();

    /**
     * @return string
     */
    abstract protected function getRequestMethod();

    /**
     * @param string $responseBody
     * @param int    $statusCode
     *
     * @return void
     */
    abstract protected function setResponseBody($responseBody, $statusCode);

    /**
     * @return void
     */
    protected function executeActionInternal()
    {
        if ($this->isNotPostRequest()) {
            $this->setResult(new InvalidMethodResult($this->getRequestMethod()));

            return;
        }

        if ($this->pluginConfigurationValidator->isInvalid()) {
            $this->setResult(new PluginNotConfiguredResult());

            return;
        }

        $rawBody = $this->getRequestBody();

        if ('' === $rawBody) {
            $this->logger->error('Process Action Request: HTTP request body empty');
            $this->setResult(new InvalidDataResult('HTTP request body empty'));

            return;
        }

        $decodedBody = json_decode($rawBody, true);
        if (!is_array($decodedBody)) {
            $this->logger->error('Process Action Request: HTTP request body is not a json object');
            $this->setResult(new InvalidDataResult('HTTP request body is not a json object'));

            return;
        }

        $loggableRequestBody = $decodedBody;
        if (array_key_exists('clientSecret', $loggableRequestBody)) {
            $loggableRequestBody['clientSecret'] = '****';
        }
        $encodedLoggableRequestBody = json_encode($loggableRequestBody);
        $this->logger->info("Process Action Request: request body '{$encodedLoggableRequestBody}'");

        $clientSecret = array_key_exists('clientSecret', $decodedBody) ? $decodedBody['clientSecret'] : null;
        if (!is_string($clientSecret)) {
            $this->logger->error("Process Action Request: Required string property 'clientSecret' is missing");
            $this->setResult(new InvalidDataResult('Required string property', 'clientSecret'));

            return;
        }

        $action = array_key_exists('action', $decodedBody) ? $decodedBody['action'] : null;
        if (!is_string($action)) {
            $this->logger->error("Process Action Request: Required string property 'action' is missing");
            $this->setResult(new InvalidDataResult('Required string property', 'action'));

            return;
        }

        $params = array_key_exists('params', $decodedBody) ? $decodedBody['params'] : null;
        if (!is_null($params) && !is_array($params)) {
            $this->logger->error("Process Action Request: Optional object property 'params' ist not an array");
            $this->setResult(new InvalidDataResult('Optional object property', 'params'));

            return;
        }

        $result = $this->actionExecutor->executeAction($clientSecret, $action, $params);
        $this->setResult($result);
    }

    /**
     * @return void
     */
    protected function setErrorResult()
    {
        $this->setResult(new FatalErrorResult());
    }

    /**
     * @return bool
     */
    protected function isNotPostRequest()
    {
        return 'POST' !== $this->getRequestMethod();
    }

    /**
     * @param Abstractions\Model\Actions\ActionResultInterface $actionResult
     *
     * @return void
     */
    protected function setResult($actionResult)
    {
        $this->setResponseBody(strval(json_encode($actionResult)), intval($actionResult->getHttpStatusCode()));
    }
}
