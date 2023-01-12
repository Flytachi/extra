<?php

use Extra\Src\CDO;
use Extra\Src\Controller;
use Extra\Src\ModelInterface;
use Extra\Src\Route;
use Extra\Src\Wrapper;

class PermissionController extends Controller
{
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
        if (empty($post['name']) or empty($post['description'])) Route::ErrorPage(400);
        $post['name'] = CDO::clean($post['name']);
        $post['description'] = CDO::clean($post['description']);
        return parent::prepareHookSaveBefore($post);
    }
    protected function prepareHookUpdateBefore(array $post, string $pk): ModelInterface
    {
        if (empty($post['name']) or empty($post['description'])) Route::ErrorPage(400);
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
        $this->method(METHOD::GET);
        Route::isAuthAdmin(1);
        $this->render('auth/permission/main');
    }

    public function list()
    {
        $this->method(METHOD::GET);
        $this->prepareAuth();
        $this->repo->Limit(10);
        $this->view('auth/permission/table', Wrapper::paginator($this->repo));
    }

    public function get(?string $pk)
    {
        $this->method(METHOD::GET);
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