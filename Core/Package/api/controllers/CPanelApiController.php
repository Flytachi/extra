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
        $post = $this->postValidation($post);
        $this->csrfTokenChange();
        if(isset($post['csrf_token'])) unset($post['csrf_token']);
        return new ApiModel($post);
    }
    protected function prepareHookUpdateBefore(array $post, int $pk): ModelInterface
    {
        $post = $this->postValidation($post);
        return parent::prepareHookUpdateBefore($post, $pk);
    }
    private function postValidation(array $post): array
    {
        if (empty($post['type'])) Route::ErrorPage(400);
        if ($post['type'] == 'Bearer') {
            if (empty($post['token'])) Route::ErrorPage(400);
            $post['username'] = null;
            $post['password'] = null;
        } elseif ($post['type'] == 'Basic') {
            if (empty($post['username'])) Route::ErrorPage(400);
            if (empty($post['password'])) Route::ErrorPage(400);
            $post['token'] = null;
        }
        return $post;
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

    public function get(?int $pk): void
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
