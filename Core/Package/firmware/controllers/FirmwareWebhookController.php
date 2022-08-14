<?php

use Extra\Src\Controller;
use Extra\Src\Route;

class FirmwareWebhookController extends Controller
{
    public bool $onHook = true;
	public bool $onAuthHook = true;

	public bool $onDelete = false;
	public bool $onAuthDelete = false;

    public bool $onRestore = false;
	public bool $onAuthRestore = false;
	
	public bool $onRemove = false;
	public bool $onAuthRemove = false;

	public function prepareAuth():void
    {
        Route::isAuthAdmin();
    }
}

?>