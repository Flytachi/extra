<?php

use Extra\Src\Controller;
use Extra\Src\Route;

class GroupController extends Controller
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
        $this->render('auth/group/main');
    }

    public function list()
    {
        Route::isAuthAdmin();
        $this->model->Limit(10);
        $this->view('auth/group/table', $this->model);
    }

    public function get($pk = null)
	{
        Route::isAuthAdmin();
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