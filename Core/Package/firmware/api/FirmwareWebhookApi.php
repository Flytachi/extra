<?php

use Extra\Src\Api;
use Extra\Src\Route;

class FirmwareWebhookApi extends Api
{
    
    public function giveLicense()
    {
        // $this->authorizationBearer();
        $body = $this->requestJson();

        importModel('FirmwareWebhookModel', 'FirmwareLicenseModel');
        
        $enterprise = (new FirmwareWebhookModel)->Where("unique_key = '$body->key'")->get();
        if(!$enterprise) Route::ApiError(401);

        $license = (new FirmwareLicenseModel)->Data("series, date_from, date_to")->Order("id DESC")->Where("is_delete IS NULL AND enterprise_id = '$enterprise->enterprise_id'")->get();
        $license->firmware = EXTRA_KEY;
        Route::ApiSuccess( $license );
    }

}

?>