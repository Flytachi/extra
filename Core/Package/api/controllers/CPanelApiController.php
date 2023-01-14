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
        return parent::prepareHookSaveBefore($post);
    }
    protected function prepareHookUpdateBefore(array $post, string $pk): ModelInterface
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
        return parent::prepareHookUpdateBefore($post, $pk);
    }

    public function index()
    {
        $this->method(METHOD::GET);
        Route::isAuthAdmin(1);
        $this->render('api/main');
    }

    public function list()
    {
        $this->method(METHOD::GET);
        $this->prepareAuth();
        $this->repo->Limit(10);
        $this->view('api/table', Wrapper::paginator($this->repo));
    }

    public function get(?int $pk)
    {
        $this->method(METHOD::GET);
        $this->prepareAuth();

        if($pk) $object = $this->getElement($pk);
        else $object = new $this->repo->modelName;

        $this->view('api/form', array(
            'model' => formObject($object),
            'inputCsrf' => $this->csrfTokenInput()
        ));
    }
}
