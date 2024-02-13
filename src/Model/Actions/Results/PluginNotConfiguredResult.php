<?php

namespace Axytos\KaufAufRechnung\Core\Model\Actions\Results;

class PluginNotConfiguredResult extends AbstractActionResult
{
    /**
     * @return string
     */
    public function getResultCode()
    {
        return 'PLUGIN_NOT_CONFIGURED';
    }

    /**
     * @return int
     */
    public function getHttpStatusCode()
    {
        return 500;
    }
}
