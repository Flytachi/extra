<?php

use Extra\Src\Controller;
use Extra\Src\Route;
use Extra\Src\Wrapper;

class FirmwareEnterpriseController extends Controller
{
    public bool $onHook = true;
	public bool $onAuthHook = true;

	public bool $onDelete = true;
	public bool $onAuthDelete = true;

    public bool $onRestore = true;
	public bool $onAuthRestore = true;
	
	public bool $onRemove = true;
	public bool $onAuthRemove = true;

    public function prepareAuth():void
    {
        Route::isAuthAdmin();
    }

    public function index()
    {
        Route::isAuthAdmin(1);
        $this->render('firmware/enterprise/main');
    }

    public function list()
    {
        Route::isAuthAdmin();
        $this->repo->Limit(10);
        $this->view('firmware/enterprise/table', Wrapper::paginator($this->repo));
    }

    public function get($pk = null)
	{
        Route::isAuthAdmin();
        if($pk) $object = $this->getElement($pk);
        $this->view('firmware/enterprise/form', array(
            'model' => $object ?? new $this->repo->modelName,
            'inputCsrf' => $this->csrfTokenGen()
        ));
	}

    public function getWebhook($pk)
    {
        Route::isAuthAdmin();
        if($pk) $object = $this->getElement($pk);
        $this->view('firmware/enterprise/formWebhook', array(
            'model' => $object ?? new $this->repo->modelName,
            'webHook' => (new FirmwareWebhookRepository)->getBy(array('enterprise_id' => $pk)),
            'inputCsrf' => $this->csrfTokenGen()
        ));
    }
}

?>