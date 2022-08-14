<?php

use Extra\Src\Controller;
use Extra\Src\Route;

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
        $this->model->Where('is_delete IS NULL')->Limit(10);
        $this->view('firmware/enterprise/table', $this->model);
    }

    public function get($pk = null)
	{
        Route::isAuthAdmin();
        if($pk) $this->getElement($pk);
        $this->view('firmware/enterprise/form', array(
            'model' => $this->model
        ));
	}

    public function getWebhook($pk)
    {
        Route::isAuthAdmin();
        if($pk) $this->getElement($pk);
        importModel('FirmwareWebhookModel');
        $this->view('firmware/enterprise/formWebhook', array(
            'model' => $this->model,
            'webHook' => (new FirmwareWebhookModel)->by(array('enterprise_id' => $pk))
        ));
    }

}

?>