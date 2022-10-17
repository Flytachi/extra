<?php

use Extra\Src\Api;
use Extra\Src\Route;

class FirmwareWebhookApi extends Api
{
    public function giveLicense()
    {
        // $this->authorizationBearer();
        $body = $this->requestJson();

        $enterprise = (new FirmwareWebhookRepository)->getBy(array('unique_key' => $body->key));
        if(!$enterprise) Route::ApiError(401);

        $licenseRepo = (new FirmwareLicenseRepository);
        $licenseRepo->modelName = "stdClass";
        $licenseRepo->Option("series, date_from, date_to");
        $licenseRepo->Order("id DESC");
        $license = $licenseRepo->getBy(array('is_delete' => 0, 'enterprise_id' => $enterprise->getEnterpriseId()));
        if ($license) {
            $license->firmware = EXTRA_KEY;
            Route::ApiSuccess( $license );
        }
    }
}