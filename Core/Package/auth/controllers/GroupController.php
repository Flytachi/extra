<?php

use Extra\Src\Controller;
use Extra\Src\Route;

class GroupController extends Controller
{
    public bool $onHook = true;
	public bool $onAuthHook = true;

	public bool $onDelete = false;
	public bool $onAuthDelete = false;

    public bool $onRestore = false;
	public bool $onAuthRestore = false;
	//
	public bool $onRemove = false;
	public bool $onAuthRemove = false;

    public function isAuth($r = false)
    {
        Route::isAuth($r);
        if(!isAdmin()) Route::ErrorPage(423);
    }

    public function hookPrepare($post, $pk = null)
    {
        if(!isAdmin()) Route::ErrorPage(423);
    }

    public function index()
    {
        $this->isAuth(1);
        $this->render('auth/group/main');
    }

    public function list()
    {
        $this->isAuth();
        $this->model->Limit(10);
        $this->view('auth/group/table', $this->model);
    }

    public function get($pk = null)
	{
		$this->isAuth();
        if($pk) $this->getElement($pk);
        importModel('PermissionModel', 'GroupPermissionModel');

        $this->view('auth/group/form', array(
            'model' => $this->model,
            'permissionList' => (new PermissionModel)->list(),
            'permission' => ($pk) ? (new GroupPermissionModel)->getAllPermission($pk) : [],
        ));
	}
    
}

?>