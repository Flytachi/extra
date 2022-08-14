<?php

use Extra\Src\Controller;
use Extra\Src\Route;

class PermissionController extends Controller
{
    public bool $onHook = true;
	public bool $onAuthHook = true;

	public bool $onRemove = true;
	public bool $onAuthRemove = true;

    public function prepareAuth():void
    {
        Route::isAuthAdmin();
    }

    public function index()
    {
        Route::isAuthAdmin(1);
        $this->render('auth/permission/main');
    }

    public function list()
    {
        Route::isAuthAdmin();
        $this->model->Limit(10);
        $this->view('auth/permission/table', $this->model);
    }

    public function get($pk = null)
	{
        Route::isAuthAdmin();
        if($pk) {
            $object = $this->model->by(array('name'=> $pk));   
            if ($object) $this->model->setData($object);
            else Route::ErrorPage(404);
        }

        $this->view('auth/permission/form', array(
            'model' => $this->model,
        ));
	}

    public function remove(string $pk): void
    {
        if ($this->onAuthRemove === true) $this->prepareAuth();
		if ($this->onRemove === false) Route::ErrorPage('404');
        if (!$pk) Route::ErrorPage(400);
        $this->prepareRemove($pk);
		if ($this->model->by(array('name'=> $pk))) {

			$this->model->delete($pk);
			$this->renderJsonSuccess($pk);
			
		} else Route::ErrorPage(404);
    }
    
}

?>