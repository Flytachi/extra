<?php

use Extra\Src\Controller;
use Extra\Src\Route;
use Extra\Src\Wrapper;

class FirmwareEnterpriseController extends Controller
{
    public FirmwareEnterpriseRepository $repo;

    public bool $onHook = true;
    public bool $onCsrfHook = true;
    public bool $onAuthHook = true;

    public bool $onDelete = true;
    public bool $onAuthDelete = true;

    public bool $onRestore = true;
    public bool $onAuthRestore = true;

    public bool $onRemove = true;
    public bool $onAuthRemove = true;

    protected function prepareAuth():void
    {
        Route::isAuthAdmin();
    }

    public function index(): void
    {
        $this->method(METHOD::GET);
        Route::isAuthAdmin(1);
        $this->render('firmware/enterprise/main');
    }

    public function list(): void
    {
        $this->method(METHOD::GET);
        Route::isAuthAdmin();
        $this->repo->Limit(10, $_GET['CRD_page'] ?? 1);
        $this->view('firmware/enterprise/table', Wrapper::paginatorDecoration($this->repo));
    }

    public function get(?int $pk): void
    {
        $this->method(METHOD::GET);
        Route::isAuthAdmin();
        if($pk) $object = $this->getElement($pk);
        else $object = $this->modelObject();

        $this->view('firmware/enterprise/form', [
            'model' => formObject($object),
            'inputCsrf' => $this->csrfTokenInput()
        ]);
    }

    public function getWebhook(?int $pk): void
    {
        $this->method(METHOD::GET);
        Route::isAuthAdmin();
        if($pk) $object = $this->getElement($pk);
        else $object = $this->modelObject();

        $webHook = (new FirmwareWebhookRepository)->getBy(['enterprise_id' => $pk]);
        $this->view('firmware/enterprise/formWebhook', [
            'model' => formObject($object),
            'webHook' => $webHook ? formObject($webHook) : null,
            'inputCsrf' => $this->csrfTokenInput()
        ]);
    }
}