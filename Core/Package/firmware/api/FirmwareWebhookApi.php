<?php

use Extra\Src\Api;
use Extra\Src\Route;

class FirmwareWebhookApi extends Api
{
    public bool $isSecure = false;
    public function giveLicense()
    {
//        $this->authorizationBearer();
        $body = $this->requestJson();
        if (empty($body->key)) $this->responseError(401);

        $enterprise = (new FirmwareWebhookRepository)->getBy(['unique_key' => $body->key]);
        if(!$enterprise) $this->responseError(401);

        $licenseRepo = (new FirmwareLicenseRepository);
        $licenseRepo->Option("series, date_from, date_to");
        $licenseRepo->Order("id DESC");
        $license = $licenseRepo->getBy(['is_delete' => 0, 'enterprise_id' => $enterprise->enterprise_id]);
        if ($license) $license->firmware = EXTRA_KEY;
        $this->responseOk($license);
    }
}