<?php

use Extra\Src\Controller;
use Extra\Src\Route;

class UserController extends Controller
{
    public bool $onHook = true;
	public bool $onAuthHook = true;

	public bool $onDelete = true;
	public bool $onAuthDelete = true;

    public bool $onRestore = true;
	public bool $onAuthRestore = true;

    public bool $onRemove = true;
	public bool $onAuthRemove = true;

    public function prepareHookSave($data)
    {
        if(!isPermission('user_create')) Route::ErrorPage(423);
    }

    public function prepareHookUpdate($pk, $data)
    {
        if(!isPermission('user_update')) Route::ErrorPage(423);
    }

    public function prepareDelete($pk)
    {
        if(!isPermission('user_delete')) Route::ErrorPage(423);
    }

    public function prepareRestore($pk)
    {
        if(!isPermission('user_restore')) Route::ErrorPage(423);
    }

    public function prepareRemove($pk)
    {
        if(!isPermission('user_remove')) Route::ErrorPage(423);
    }

    public function index()
    {
        Route::isAuth(1);
        if(!isPermission('user_view')) Route::ErrorPage(423);
        $this->render('auth/user/main');
    }

    public function list()
    {
        Route::isAuth();
        if(!isPermission('user_view')) Route::ErrorPage(423);
        importModel('UserInfoModel', 'GroupModel');
        $this->model->as('u');
        $this->model->Data("u.id, u.username, g.name 'group', ui.name, u.is_admin, u.is_delete");
        $this->model->JoinLEFT(new UserInfoModel('ui'), 'u.id=ui.user_id');
        $this->model->JoinLEFT(new GroupModel('g'), 'g.id=ui.group_id');
        $this->model->Limit(10);
        $this->view('auth/user/table', $this->model);
    }

    public function changePassword($pk)
    {
        Route::isAuth();
        $this->getElement($pk);
        if(!$this->permissionChangePassword($this->model->get())) Route::ErrorPage(423);
        $this->view('auth/user/passwordChange', array('model'=> $this->model));
    }

    public function permissionChangePassword($user)
    {
        if (isAdmin()) return true;
        else {
            if($user->id == $_SESSION['id']) return true;
            else return false;
        }
    }

    public function get($pk = null)
	{
        Route::isAuth();
        importModel('GroupModel', 'UserInfoModel');
        if($pk) {
            if (!isPermission('user_update')) Route::ErrorPage(423);
            $this->getElement($pk);
        } else {
            if (!isPermission('user_create')) Route::ErrorPage(423);
        }
        $this->view('auth/user/form', array(
            'model' => $this->model,
            'userInfo' => (new UserInfoModel)->isUser($pk),
            'groupList' => (new GroupModel)->list()
        ));
	}

    /*
    public function getPerm($pk = null)
	{
        Route::isAuth();
        if(!(isPermission('user_create') or isPermission('user_update'))) Route::ErrorPage(423);
        importModel('PermissionModel', 'UserPermissionModel', 'UserInfoModel');
        if($pk) $this->getElement($pk);
        $this->view('user/form', array(
            'model' => $this->model,
            'userInfo' => (new UserInfoModel)->isUser($pk),
            'permissionList' => (new PermissionModel)->list(),
            'permission' => ($pk) ? (new UserPermissionModel)->getAllPermission($pk) : [],
        ));
	}
    */
    
}

?>