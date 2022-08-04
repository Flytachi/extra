<?php

use Extra\Src\Controller;
use Extra\Src\Route;

class PermissionController extends Controller
{
    public bool $onHook = true;
	public bool $onAuthHook = true;

	public bool $onDelete = false;
	public bool $onAuthDelete = false;

    public bool $onRestore = false;
	public bool $onAuthRestore = false;
	
	public bool $onRemove = true;
	public bool $onAuthRemove = true;

    public function isAuth($r = false)
    {
        Route::isAuth($r);
        if(!isAdmin()) Route::ErrorPage(423);
    }

    public function hookPrepare($post, $pk = null)
    {
        if(!isAdmin()) Route::ErrorPage(423);
    }

    public function prepareRemove($pk)
    {
        if(!isAdmin()) Route::ErrorPage(423);
    }

    public function index()
    {
        $this->isAuth(1);
        $this->render('auth/permission/main');
    }

    public function list()
    {
        $this->isAuth();
        $this->model->Limit(10);
        $this->view('auth/permission/table', $this->model);
    }

    public function get($pk = null)
	{
        $this->isAuth();
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
        if ($this->onAuthRemove == true) Route::isAuth();
		if ($this->onRemove == false) Route::ErrorPage('404');
        if(!isAdmin()) Route::ErrorPage(423);
        if (!$pk) Route::ErrorPage(400);
        $this->prepareRemove($pk);
		if ($this->model->by(array('name'=> $pk))) {

			$this->model->delete($pk);
			$this->renderJsonSuccess($pk);
			
		} else Route::ErrorPage(404);
    }
    
}

?>