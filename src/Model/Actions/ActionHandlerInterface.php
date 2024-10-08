<?php

namespace Axytos\KaufAufRechnung\Core\Model\Actions;

interface ActionHandlerInterface
{
    /**
     * @return string
     */
    public function action();

    /**
     * @param array<string,mixed>|null $parameters
     *
     * @return \Axytos\KaufAufRechnung\Core\Abstractions\Model\Actions\ActionResultInterface
     */
    public function handle($parameters = null);
}
