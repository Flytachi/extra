<?php

use Extra\Src\Controller;
use Extra\Src\ModelInterface;
use Extra\Src\Route;
use Extra\Src\Wrapper;

class GroupController extends Controller
{
    public GroupRepository $repo;

    public bool $onHook = true;
    public bool $onCsrfHook = true;
    public bool $onAuthHook = true;

    public bool $onRemove = true;
    public bool $onAuthRemove = true;

    protected function prepareAuth(): void
    {
        Route::isAuthAdmin();
    }
    protected function prepareHookSaveBefore(array $post): ModelInterface
    {
        $this->valid($post, 'name');
        if(isset($post['permission'])) unset($post['permission']);
        return parent::prepareHookSaveBefore($post);
    }
    protected function prepareHookUpdateBefore(array $post, int $pk): ModelInterface
    {
        $this->valid($post, 'name');
        if(isset($post['permission'])) unset($post['permission']);
        return parent::prepareHookUpdateBefore($post, $pk);
    }

    public function index(): void
    {
        $this->method(METHOD::GET);
        Route::isAuthAdmin(1);
        $this->render('auth/group/main');
    }

    public function list(): void
    {
        $this->method(METHOD::GET);
        $this->prepareAuth();
        $this->repo->Limit(10, $_GET['CRD_page'] ?? 1);
        $this->view('auth/group/table', Wrapper::paginatorDecoration($this->repo));
    }

    public function get(?int $pk = null): void
    {
        $this->method(METHOD::GET);
        $this->prepareAuth();
        if($pk) $object = $this->getElement($pk);
        else $object = $this->modelObject();

        $this->view('auth/group/form', array(
            'model' => formObject($object),
            'permissionList' => (new PermissionRepository)->getAll(),
            'permission' => ($pk) ? (new GroupPermissionRepository)->getAllPermission($pk) : [],
            'inputCsrf' => $this->csrfTokenInput()
        ));
    }
}