<?php

use Extra\Src\Controller;
use Extra\Src\ModelInterface;
use Extra\Src\Route;
use Extra\Src\Wrapper;

class UserController extends Controller
{
    public UserRepository $repo;

    public bool $onHook = true;
    public bool $onCsrfHook = true;
    public bool $onAuthHook = true;

    public bool $onDelete = true;
    public bool $onAuthDelete = true;

    public bool $onRestore = true;
    public bool $onAuthRestore = true;

    public bool $onRemove = true;
    public bool $onAuthRemove = true;

    protected function prepareHookSaveBefore(array $post): ModelInterface
    {
        if(!isPermission('user_create')) Route::ErrorPage(423);
        if(isset($post['info'])) unset($post['info']);
        if(isset($post['password'])) {
            $post['password'] = password_hash($post['password'], PASSWORD_BCRYPT);
        }
        return parent::prepareHookSaveBefore($post);
    }
    protected function prepareHookUpdateBefore(array $post, int $pk): ModelInterface
    {
        if(!isPermission('user_update')) Route::ErrorPage(423);
        if(isset($post['info'])) unset($post['info']);
        if(isset($post['password'])) {
            $post['password'] = password_hash($post['password'], PASSWORD_BCRYPT);
        }
        return parent::prepareHookUpdateBefore($post, $pk);
    }
    protected function prepareDeleteBefore(int $pk): ModelInterface
    {
        if(!isPermission('user_delete')) Route::ErrorPage(423);
        return parent::prepareDeleteBefore($pk);
    }
    protected function prepareRestoreBefore(int $pk): ModelInterface
    {
        if(!isPermission('user_restore')) Route::ErrorPage(423);
        return parent::prepareRestoreBefore($pk);
    }
    protected function prepareRemoveBefore(int $pk): void
    {
        if(!isPermission('user_remove')) Route::ErrorPage(423);
        parent::prepareRemoveBefore($pk);
    }

    public function index(): void
    {
        $this->method(METHOD::GET);
        Route::isAuth(1);
        if(!isPermission('user_view')) Route::ErrorPage(423);
        $this->render('auth/user/main');
    }

    public function list(): void
    {
        $this->method(METHOD::GET);
        $this->prepareAuth();
        if(!isPermission('user_view')) Route::ErrorPage(423);
        $this->repo->as('u');
        $this->repo->Option("u.id, u.username, g.name 'group', ui.name, u.is_admin, u.is_delete");
        $this->repo->JoinLEFT(new UserInfoRepository('ui'), 'u.id=ui.user_id');
        $this->repo->JoinLEFT(new GroupRepository('g'), 'g.id=ui.group_id');
        $this->repo->Limit(10, $_GET['CRD_page'] ?? 1);
        $this->view('auth/user/table', Wrapper::paginatorDecoration($this->repo));
    }

    public function get(?int $pk = null): void
    {
        $this->method(METHOD::GET);
        $this->prepareAuth();
        if($pk) {
            if (!isPermission('user_update')) Route::ErrorPage(423);
            $object = $this->getElement($pk);
            $info = (new UserInfoRepository)->isUser($pk);
            if(!$info) $info = new UserInfoModel;
        } else {
            if (!isPermission('user_create')) Route::ErrorPage(423);
            $object = $this->modelObject();
            $info = new UserInfoModel;
        }
        $this->view('auth/user/form', [
            'model' => formObject($object),
            'userInfo' => formObject($info),
            'groupList' => (new GroupRepository)->getAll(),
            'inputCsrf' => $this->csrfTokeninput()
        ]);
    }

    public function changePassword(int $pk): void
    {
        $this->method(METHOD::GET);
        $this->prepareAuth();
        $object = $this->getElement($pk);
        if(!$this->permissionChangePassword($object)) Route::ErrorPage(423);
        $this->view('auth/user/passwordChange', array(
            'model'=> $object,
            'inputCsrf' => $this->csrfTokenInput()
        ));
    }

    private function permissionChangePassword($user): bool
    {
        if (isAdmin()) return true;
        else return $user->id == $_SESSION['id'];
    }

}
