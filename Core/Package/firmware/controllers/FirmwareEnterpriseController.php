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

    public function get(?int $pk = null): void
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

    public function getWebhook(?int $pk = null): void
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

    public function sync(int $pk): void
    {
        $this->method(METHOD::GET);
        Route::isAuthAdmin();
        $object = $this->getElement($pk);
        if (!$object->url) Route::ErrorPage(400);

        $licenseRepo = (new FirmwareLicenseRepository);
        $licenseRepo->Option("series, date_from, date_to");
        $licenseRepo->Order("id DESC");
        $license = $licenseRepo->getBy(['is_delete' => 0, 'enterprise_id' => $object->id]);
        if ($license) $license->firmware = EXTRA_KEY;

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $object->url . '/api/firewall/license',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $license,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . EXTRA_KEY,
                'Content-Type: application/json'
            ],
        ));

        $response = json_decode(curl_exec($curl));
        curl_close($curl);
        $this->renderJsonSuccess($response);
    }
}