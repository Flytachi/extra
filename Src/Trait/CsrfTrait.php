<?php

namespace Extra\Src\Trait;

use Extra\Src\RandomGenerator;
use Extra\Src\Route;

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
        } else Route::Throwable(419, 'CSRF Token authentication error occurred');
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
        $random = new RandomGenerator();
        $token = bin2hex($random->generate(20));
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