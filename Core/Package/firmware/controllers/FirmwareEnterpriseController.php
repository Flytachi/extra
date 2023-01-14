<?php

use Extra\Src\Controller;
use Extra\Src\Route;
use Extra\Src\Wrapper;

class FirmwareEnterpriseController extends Controller
{
    public FirmwareEnterpriseRepository $repo;

    public bool $onHook = true;
    public bool $onCsrfHook = true;
	public bool $onAuthHook = true;

	public bool $onDelete = true;
	public bool $onAuthDelete = true;

    public bool $onRestore = true;
	public bool $onAuthRestore = true;
	
	public bool $onRemove = true;
	public bool $onAuthRemove = true;

    protected function prepareAuth():void
    {
        Route::isAuthAdmin();
    }

    public function index()
    {
        $this->method(METHOD::GET);
        Route::isAuthAdmin(1);
        $this->render('firmware/enterprise/main');
    }

    public function list()
    {
        $this->method(METHOD::GET);
        Route::isAuthAdmin();
        $this->repo->Limit(10);
        $this->view('firmware/enterprise/table', Wrapper::paginator($this->repo));
    }

    public function get(?int $pk)
	{
        $this->method(METHOD::GET);
        Route::isAuthAdmin();
        if($pk) $object = $this->getElement($pk);
        else $object = new $this->repo->modelName;
        $this->view('firmware/enterprise/form', array(
            'model' => formObject($object),
            'inputCsrf' => $this->csrfTokenInput()
        ));
	}

    public function getWebhook(?int $pk)
    {
        $this->method(METHOD::GET);
        Route::isAuthAdmin();
        if($pk) $object = $this->getElement($pk);
        $webHook = (new FirmwareWebhookRepository)->getBy(['enterprise_id' => $pk]);
        $this->view('firmware/enterprise/formWebhook', array(
            'model' => formObject($object ?? new $this->repo->modelName),
            'webHook' => $webHook ? formObject($webHook) : null,
            'inputCsrf' => $this->csrfTokenInput()
        ));
    }
}