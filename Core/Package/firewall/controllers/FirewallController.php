<?php

use Extra\Src\Controller;
use Extra\Src\Route;

class FirewallController extends Controller
{
    public function prepareAuth(): void
    {
        Route::isAuthAdmin(1);
    }

    public function index()
    {
        $this->prepareAuth();
        $this->render('firewall/main', array(
            'confList' => array_keys(Warframe::$cfg)
        ));
    }

    public function get(string $confName)
    {
        $this->prepareAuth();
        $this->render('firewall/panel/form', array(
            'settName' => $confName,
            'confList' => Warframe::$cfg[$confName]
        ));
    }

    public function license()
    {
        $this->prepareAuth();
        $license = licenseKey();
        $this->render('firewall/panel/license', array(
            'device' => array(
                'guard' => Warframe::$cfg['SECURITY']['PRODUCT_GUARD'],
                'host' => Warframe::$cfg['SECURITY']['PRODUCT_HOST'],
                'api' => Warframe::$cfg['SECURITY']['PRODUCT_KEY'],
                'firmware' => Warframe::$cfg['SECURITY']['PRODUCT_FIRMWARE'],
                'series' => motherboardSeries()
            ),
            'license' => ($license) ? array(
                'licenseDateFrom' => $license->licenseDateFrom,
                'licenseDateTo' => $license->licenseDateTo,
                'motherboardSeries' => $license->motherboardSeries
            ) : null

        ));
    }

    public function licenseSpell()
    {
        $this->prepareAuth();
        if ($_FILES['license'] and $_FILES['license']['error'] === UPLOAD_ERR_OK) {
            $fileTmpPath = $_FILES['license']['tmp_name'];
            $fileName = $_FILES['license']['name'];
            $fileSize = $_FILES['license']['size'];

            $array = explode(".", $fileName);
            $fileExtension = strtolower(end($array));
            if ($fileExtension === 'crt' and $fileSize <= 5000) {
                if (move_uploaded_file($fileTmpPath, LICENSE_PATH_KEY)) {
                    Route::redirect();
                }
            }
        }
    }

    public function spell()
    {
        $this->prepareAuth();
        $cfgNew = Warframe::$cfg;
        foreach ($_POST as $key => $value) {
            if (isset($cfgNew[$key])) $cfgNew[$key] = $value;
        }
        $fp = fopen(CFG_PATH_CLOSE, "w+");
        if ($fp) {
            fwrite($fp, chunk_split( bin2hex(zlib_encode(json_encode($cfgNew), ZLIB_ENCODING_DEFLATE)) , 50, "\n") );
            fclose($fp);
            Route::redirect();
        } else {
            dd("Ошибка записи!");
        }
    }

    public function upgrade()
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => Warframe::$cfg['SECURITY']['PRODUCT_HOST'] . '/api/firmwareWebhook/giveLicense',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_POSTFIELDS =>'{
            "key": "' . Warframe::$cfg['SECURITY']['PRODUCT_KEY'] . '"
        }',
            CURLOPT_HTTPHEADER => array(
                'Authorization: Bearer 5449jeo',
                'Content-Type: application/json'
            ),
        ));

        $response = json_decode(curl_exec($curl));
        curl_close($curl);
        if ($response->statusCode == 200) {
            if ($response->result) {
                $license = array(
                    'licenseFirmware' => $response->result->firmware,
                    'licenseDateFrom' => strtotime($response->result->date_from),
                    'licenseDateTo' => strtotime($response->result->date_to),
                    'motherboardSeries' => $response->result->series,
                );
                $fp = fopen(LICENSE_PATH_KEY, "w");
                fwrite($fp, bin2hex(zlib_encode(json_encode($license), ZLIB_ENCODING_DEFLATE)));
                fclose($fp);
                $this->renderJsonSuccess('Лицензия успешно обновленна!');
            } else $this->renderJsonError('Нет доступных лицензий!');
        } else $this->renderJsonError($response);
    }
}
