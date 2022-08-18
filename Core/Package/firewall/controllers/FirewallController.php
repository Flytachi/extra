<?php

use Extra\Src\Controller;
use Extra\Src\Route;

class FirewallController extends Controller
{
    public function index()
    {
        Route::isAuthAdmin(1);
        $this->render('firewall/main', array('confList' => array_keys(cfgGet())));
    }

    public function apache()
    {
        Route::isAuthAdmin(1);
        $this->render('firewall/panel/form', array(
            'settName' => 'APACHE',
            'confList' => cfgGet()['APACHE']
        ));
    }

    public function security()
    {
        Route::isAuthAdmin(1);
        $this->render('firewall/panel/form', array(
            'settName' => 'SECURITY',
            'confList' => cfgGet()['SECURITY']
        ));
    }

    public function global_setting()
    {
        Route::isAuthAdmin(1);
        $this->render('firewall/panel/form', array(
            'settName' => 'GLOBAL_SETTING',
            'confList' => cfgGet()['GLOBAL_SETTING']
        ));
    }

    public function database()
    {
        Route::isAuthAdmin(1);
        $this->render('firewall/panel/form', array(
            'settName' => 'DATABASE',
            'confList' => cfgGet()['DATABASE']
        ));
    }

    public function license()
    {
        Route::isAuthAdmin(1);
        $cfg = cfgGet();
        $license = licenseKey();
        $this->render('firewall/panel/license', array(
            'device' => array(
                'guard' => $cfg['SECURITY']['PRODUCT_GUARD'],
                'host' => $cfg['SECURITY']['PRODUCT_HOST'],
                'api' => $cfg['SECURITY']['PRODUCT_KEY'],
                'firmware' => $cfg['SECURITY']['PRODUCT_FIRMWARE'],
                'series' => motherboardSeries()
            ),
            'license' => ($license) ? array(
                'licenseDateFrom' => $license->licenseDateFrom,
                'licenseDateTo' => $license->licenseDateTo,
                'motherboardSeries' => $license->motherboardSeries
            ) : null
            
        ));
    }

    public function liceseSpell()
    {
        Route::isAuthAdmin(1);
        if ($_FILES['license'] and $_FILES['license']['error'] === UPLOAD_ERR_OK) {
            $fileTmpPath = $_FILES['license']['tmp_name'];
            $fileName = $_FILES['license']['name'];
            $fileSize = $_FILES['license']['size'];

            $fileExtension = strtolower(end(explode(".", $fileName)));
            if ($fileExtension === 'crt' and $fileSize <= 5000) {
                if (move_uploaded_file($fileTmpPath, LICENSE_PATH_KEY)) {
                    Route::redirect();
                }
            }
        }
    }

    public function spell()
    {
        Route::isAuthAdmin(1);
        $cfgNew = cfgGet();
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
        $cfg = cfgGet();
        $curl = curl_init();

        curl_setopt_array($curl, array(
        CURLOPT_URL => $cfg['SECURITY']['PRODUCT_HOST'] . '/api/firmwareWebhook/giveLicense',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'GET',
        CURLOPT_POSTFIELDS =>'{
            "key": "' . $cfg['SECURITY']['PRODUCT_KEY'] . '"
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

?>