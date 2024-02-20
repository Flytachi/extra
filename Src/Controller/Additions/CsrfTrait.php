<?php

namespace Extra\Src\Controller\Additions;

use Extra\Src\Error\CsrfError;
use Extra\Src\HttpCode;
use Extra\Src\Sheath\Algorithm;

/**
 * Trait CsrfTrait
 *
 * `CsrfTrait` provides functionality for managing CSRF tokens for form submissions.
 * It includes methods for token generation, validation, and form input creation.
 *
 * The methods provided by `CsrfTrait` include:
 *
 * - `csrfTokenChange(): void`: Validates and resets the CSRF token after form submission.
 * - `csrfTokenGen(): string`: Generates a new CSRF token and stores it in the session.
 * - `csrfTokenInput(): string`: Generates and returns a hidden form input field with a CSRF token value.
 *
 * @version 1.0
 * @author Flytachi
 */
trait CsrfTrait {
    /**
     * CSRF Token Change
     *
     * Checks csrf token for validity
     *
     * @return void
     */
    final protected function csrfTokenChange(): void
    {
        if ((isset($_SESSION['CSRF_TOKEN']) and isset($_POST['csrf_token']) and
            hash_equals($_SESSION['CSRF_TOKEN'], $_POST['csrf_token']))) {
            unset($_SESSION['CSRF_TOKEN']);
            unset($_POST['csrf_token']);
        } else CsrfError::throw(HttpCode::AUTHENTICATION_TIMEOUT_NOT_IN_RFC_2616, 'CSRF Token authentication error occurred');
    }

    /**
     * CSRF Token Generation
     *
     * Generate csrf token (24 chars)
     *
     * @return string
     *
     */
    final protected function csrfTokenGen(): string
    {
        $token = bin2hex(Algorithm::random(20));
        $_SESSION['CSRF_TOKEN'] = $token;
        return $token;
    }

    /**
     * CSRF Token Input
     *
     * Outputs the qsq input tag with the generated token
     *
     * @return string
     */
    final protected function csrfTokenInput(): string
    {
        $token = $this->csrfTokenGen();
        return "<input type=\"hidden\" name=\"csrf_token\" value=\"" . $token . "\">";
    }
}