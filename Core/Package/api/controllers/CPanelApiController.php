<?php

use Extra\Src\Controller;
use Extra\Src\ModelInterface;
use Extra\Src\Route;
use Extra\Src\Wrapper;

class CPanelApiController extends Controller
{
    public ApiRepository $repo;

    public bool $onHook = true;
    public bool $onCsrfHook = true;
    public bool $onAuthHook = true;

    public bool $onDelete = true;
    public bool $onAuthDelete = true;

    public bool $onRestore = true;
    public bool $onAuthRestore = true;

    public bool $onRemove = true;
    public bool $onAuthRemove = true;

    protected function prepareAuth(): void
    {
        Route::isAuthAdmin();
    }
    protected function prepareHookSaveBefore(array $post): ModelInterface
    {
        $this->postValidation($post);
        return new ApiModel($post);
    }
    protected function prepareHookUpdateBefore(array $post, int $pk): ModelInterface
    {
        $this->postValidation($post);
        return parent::prepareHookUpdateBefore($post, $pk);
    }
    private function postValidation(array &$post): void
    {
        $this->valid($post, 'type');
        if ($post['type'] == 'Bearer') {
            $this->valid($post, 'token');
            $post['username'] = null;
            $post['password'] = null;
        } elseif ($post['type'] == 'Basic') {
            $this->valid($post, 'username');
            $this->valid($post, 'password');
            $post['token'] = null;
        }
    }

    public function index(): void
    {
        $this->method(METHOD::GET);
        Route::isAuthAdmin(1);
        $this->render('api/main');
    }

    public function list(): void
    {
        $this->method(METHOD::GET);
        $this->prepareAuth();
        $this->repo->Limit(10, $_GET['CRD_page'] ?? 1);
        $this->view('api/table', Wrapper::paginatorDecoration($this->repo));
    }

    public function get(?int $pk = null): void
    {
        $this->method(METHOD::GET);
        $this->prepareAuth();

        if($pk) $object = $this->getElement($pk);
        else $object = new ApiModel();

        $this->view('api/form', array(
            'model' => formObject($object),
            'inputCsrf' => $this->csrfTokenInput()
        ));
    }
}
