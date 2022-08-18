<?php

use Extra\Src\Controller;
use Extra\Src\Route;
use Extra\Src\Wrapper;

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
        importRepository('UserInfoRepository', 'GroupRepository');
        $this->repo->as('u');
        $this->repo->Option("u.id, u.username, g.name 'group', ui.name, u.is_admin, u.is_delete");
        $this->repo->JoinLEFT(new UserInfoRepository('ui'), 'u.id=ui.user_id');
        $this->repo->JoinLEFT(new GroupRepository('g'), 'g.id=ui.group_id');
        $this->repo->Limit(10);
        $this->view('auth/user/table', Wrapper::paginator($this->repo));
    }

    public function changePassword($pk)
    {
        Route::isAuth();
        $object = $this->getElement($pk);
        if(!$this->permissionChangePassword($object)) Route::ErrorPage(423);
        $this->view('auth/user/passwordChange', array(
            'model'=> $object,
            'inputCsrf' => $this->csrfTokenGen()
        ));
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
        importRepository('GroupRepository', 'UserInfoRepository');
        if($pk) {
            if (!isPermission('user_update')) Route::ErrorPage(423);
            $object = $this->getElement($pk);
        } else {
            if (!isPermission('user_create')) Route::ErrorPage(423);
        }
        $this->view('auth/user/form', array(
            'model' => $object ?? new $this->repo->modelName,
            'userInfo' => (new UserInfoRepository)->isUser($pk),
            'groupList' => (new GroupRepository)->getAll(),
            'inputCsrf' => $this->csrfTokenGen()
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