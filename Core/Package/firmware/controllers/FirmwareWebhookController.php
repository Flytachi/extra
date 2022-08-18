<?php

use Extra\Src\Controller;
use Extra\Src\Route;

class FirmwareWebhookController extends Controller
{
    public bool $onHook = true;
	public bool $onAuthHook = true;

	public function prepareAuth():void
    {
        Route::isAuthAdmin();
    }
}

?>