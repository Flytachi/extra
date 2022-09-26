<?php

use Extra\Src\Controller;
use Extra\Src\Route;
use Extra\Src\Wrapper;

class GroupController extends Controller
{
    public bool $onHook = true;
	public bool $onAuthHook = true;
	
	public bool $onRemove = true;
	public bool $onAuthRemove = true;

    protected function prepareAuth():void
    {
        Route::isAuthAdmin();
    }

    public function index()
    {
        Route::isAuthAdmin(1);
        $this->render('auth/group/main');
    }

    public function list()
    {
        Route::isAuthAdmin();
        $this->repo->Limit(10);
        $this->view('auth/group/table', Wrapper::paginator($this->repo));
    }

    public function get(?int $pk)
	{
        Route::isAuthAdmin();
        if($pk) $object = $this->getElement($pk);

        $this->view('auth/group/form', array(
            'model' => $object ?? new $this->repo->modelName,
            'permissionList' => (new PermissionRepository)->getAll(),
            'permission' => ($pk) ? (new GroupPermissionRepository)->getAllPermission($pk) : [],
            'inputCsrf' => $this->csrfTokenGen()
        ));
	}
}

?>