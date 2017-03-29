<?php

namespace App\Controller;

/**
 * Class ExcludeMe
 *
 * This file will not be scanned as it's listed in "exclude" section of the documentor config file.
 *
 * @package App\Controller
 * @doc-api-path /api/excluded
 */
class ExcludeMe
{
    /**
     * Dummy method
     *
     * @doc-var    (string) foo     - Foo.
     * @doc-var    (string) bar     - Bar.
     *
     * @return bool
     * @throws \Exception
     */
    static function dummy()
    {
        return true;
    }
}
