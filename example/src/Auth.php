<?php

namespace App\Controller;

/**
 * Class Auth
 * @package App\Controller
 * @doc-api-path /api/auth
 */
class Auth
{
    /**
     * Logs you in
     *
     * @doc-var    (string) identity!       - User email.
     * @doc-var    (string) credential!     - User password.
     * @doc-var    (int=300) lifetime       - Session lifetime, in minutes. Maximum — 1440.
     *
     * @return bool
     * @throws \Exception
     */
    static function login()
    {
        return true;
    }

    /**
     * Registers a user in the system
     *
     * @doc-var    (string) email!                      - User email.
     * @doc-var    (string) password!                   - User password.
     * @doc-var    (string) name                        - Full name.
     * @doc-var    (date) birthday                      - Birthday date.
     * @doc-var    (enum:en_US|ru_RU|fi_FI) locale      - Preferred user locale.
     * @doc-var    (bool) skipEmail                     - Whether to skip email sending after sign-up ot not. Default is 'false'.
     *
     * @return bool
     * @throws \Exception
     *
     * @doc-output
     * {
     *      "id":       (int) user ID,
     *      "name":     (string) user name,
     *      "email":    (string) user email,
     * }
     */
    static function register()
    {
        return true;
    }

    /**
     * Logs you out
     *
     * @return bool
     */
    static function logout()
    {
        return true;
    }

    /**
     * Returns current status
     *
     * @return bool
     */
    static function status()
    {
        return true;
    }

    /**
     * [no-doc] This method will not get into the documentation
     *
     * @return bool
     */
    static function secret()
    {
        return true;
    }
}
