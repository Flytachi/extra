<?php

use Extra\Src\CDO;
use Extra\Src\Controller;
use Extra\Src\ModelInterface;
use Extra\Src\Route;
use Extra\Src\Wrapper;

class PermissionController extends Controller
{
    public PermissionRepository $repo;

    protected function prepareAuth(): void
    {
        Route::isAuthAdmin();
    }

    public function index(): void
    {
        $this->method(METHOD::GET);
        Route::isAuthAdmin(1);
        $this->render('auth/permission/main');
    }

    public function list(): void
    {
        $this->method(METHOD::GET);
        $this->prepareAuth();
        $this->repo->Limit(10, $_GET['CRD_page'] ?? 1);
        $this->view('auth/permission/table', Wrapper::paginatorDecoration($this->repo));
    }

    public function get(?string $pk): void
    {
        $this->method(METHOD::GET);
        $this->prepareAuth();
        if($pk) {
            $object = $this->repo->getBy(array('name'=> $pk));
            if (!$object) Route::ErrorPage(404);
        }else $object = $this->modelObject();

        $this->view('auth/permission/form', array(
            'model' => formObject($object),
            'inputCsrf' => $this->csrfTokenInput()
        ));
    }

    public function createOrUpdate(?string $pk = null): void
    {
        $this->method(METHOD::POST);
        $this->prepareAuth();
        if (empty($_POST)) Route::ErrorPage(400);
        if (empty($_POST['name'])) Route::ErrorPage(400);
        if (empty($_POST['description'])) Route::ErrorPage(400);
        $post = $_POST;
        $post['name'] = CDO::clean($post['name']);
        $post['description'] = CDO::clean($post['description']);

        $this->csrfTokenChange();
        if(isset($post['csrf_token'])) unset($post['csrf_token']);
        if ( $pk ) {
            $object = $this->repo->getBy(['name' => $pk]);
            if (!$object) Route::ErrorPage(404);
            $object->reConstruct($post);
            $result = $this->repo->update($pk, $object);
            $this->renderJsonSuccess($result);
        } else {
            $object = $this->modelObject($post);
            $result = $this->repo->save($object);
            $this->renderJsonSuccess($result);
        }
    }

    public function del(string $pk): void
    {
        $this->method(METHOD::GET);
        $this->prepareAuth();

        $object = $this->repo->getBy(['name' => $pk]);
        if (!$object) Route::ErrorPage(404);
        $result = $this->repo->delete($pk);
        $this->renderJsonSuccess($result);
    }

}