<?php

use Extra\Src\Controller;
use Extra\Src\Model;
use Extra\Src\Route;
use Extra\Src\Wrapper;

class PermissionController extends Controller
{
    public bool $onHook = true;
    public bool $onAuthHook = true;

    public bool $onRemove = true;
    public bool $onAuthRemove = true;

    protected function prepareAuth(): void
    {
        Route::isAuthAdmin();
    }
    protected function prepareHookUpdateBefore(array $post, string $pk): Model
    {
        $this->csrfTokenChange();
        if(isset($post['csrf_token'])) unset($post['csrf_token']);
        $object = $this->repo->getBy(['name' => $pk]);
        if (!$object) Route::ErrorPage(404);
        $object->setNewObject($post);
        return $object;
    }
    protected function prepareRemoveBefore(string $pk): void
    {
        $object = $this->repo->getBy(['name' => $pk]);
        if (!$object) Route::ErrorPage(404);
    }

    public function index()
    {
        Route::isAuthAdmin(1);
        $this->render('auth/permission/main');
    }

    public function list()
    {
        $this->prepareAuth();
        $this->repo->Limit(10);
        $this->view('auth/permission/table', Wrapper::paginator($this->repo));
    }

    public function get(?string $pk)
    {
        $this->prepareAuth();
        if($pk) {
            $object = $this->repo->getBy(array('name'=> $pk));
            if (!$object) Route::ErrorPage(404);
        }else $object = new $this->repo->modelName;

        $this->view('auth/permission/form', array(
            'model' => formObject($object),
            'inputCsrf' => $this->csrfTokenInput()
        ));
    }

}