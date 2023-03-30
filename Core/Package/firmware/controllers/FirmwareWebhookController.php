<?php

use Extra\Src\Controller;
use Extra\Src\Route;

class FirmwareWebhookController extends Controller
{
    public FirmwareWebhookRepository $repo;

    public bool $onHook = true;
    public bool $onCsrfHook = true;
    public bool $onAuthHook = true;

    protected function prepareAuth():void
    {
        Route::isAuthAdmin();
    }
}