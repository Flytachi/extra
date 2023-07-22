<?php

namespace api;

use Extra\Src\Api;
use Extra\Src\API_DATA;
use METHOD;
use Warframe;

class FirewallApi extends Api
{
    public function license()
    {
        $this->method(METHOD::POST);
        if ($this->getBearerToken() !== Warframe::$cfg['SECURITY']['PRODUCT_FIRMWARE'])
            $this->responseError(401);
        if (!($data = $this->request(API_DATA::JSON)) ||
            !isset($data->series) ||
            !isset($data->date_from) ||
            !isset($data->date_to) ||
            !isset($data->firmware)
        ) $this->responseError(400);
        if ($data->firmware !== Warframe::$cfg['SECURITY']['PRODUCT_FIRMWARE'])
            $this->responseError(400);

        $license = array(
            'licenseFirmware' => $data->firmware,
            'licenseDateFrom' => strtotime($data->date_from),
            'licenseDateTo' => strtotime($data->date_to),
            'motherboardSeries' => $data->series,
        );
        $fp = fopen(LICENSE_PATH_KEY, "w");
        fwrite($fp, bin2hex(zlib_encode(json_encode($license), ZLIB_ENCODING_DEFLATE)));
        fclose($fp);

        $this->response(204);
    }
}
