<?php

use Extra\Src\Controller;
use Extra\Src\Route;
use Extra\Src\Wrapper;

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
        $this->repo->Limit(10);
        $this->view('auth/permission/table', Wrapper::paginator($this->repo));
    }

    public function get($pk = null)
	{
        Route::isAuthAdmin();
        if($pk) {
            $object = $this->repo->getBy(array('name'=> $pk));   
            if ($object) $this->repo->setData($object);
            else Route::ErrorPage(404);
        }

        $this->view('auth/permission/form', array(
            'model' => $object ?? new $this->repo->modelName,
            'inputCsrf' => $this->csrfTokenGen()
        ));
	}

    public function remove(string $pk): void
    {
        if ($this->onAuthRemove === true) $this->prepareAuth();
        if ($this->onRemove === false) Route::ErrorPage(404);

        $this->prepareRemove($pk);

        if ($this->repo->getBy(array('name'=> $pk))) {

            $this->repo->delete($pk);
            $this->renderJsonSuccess($pk);
            
        } else Route::ErrorPage(404);
    }
}

?>