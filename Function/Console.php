<?php

function multiCopy($source, $dest, $over=false)
{
    if(!is_dir($dest)) mkdir($dest);
    if($handle = opendir($source))
    {
        while(false !== ($file = readdir($handle)))
        {
            if($file != '.' && $file != '..')
            {
                $path = $source . '/' . $file;
                if(is_file($path)) {
                    if(!is_file($dest . '/' . $file || $over)) if(!@copy($path, $dest . '/' . $file)) echo "('.$path.') Ошибка!!! "; 
                } elseif(is_dir($path)) {
                    if(!is_dir($dest . '/' . $file)) mkdir($dest . '/' . $file);
                    multiCopy($path, $dest . '/' . $file, $over);
                }
            }
        }
        closedir($handle);
    }
}

function softLicenseCorrect(): bool
{
    $cfg = cfgGet();
    if ($cfg['SECURITY']['PRODUCT_GUARD']) {
        $license = licenseKey();
        $toDay = strtotime(date('Y-m-d'));
        if (
            ($cfg['SECURITY']['PRODUCT_FIRMWARE'] . '-' . motherboardSeries() === $license->licenseFirmware . '-' . $license->motherboardSeries)
            and ($license->licenseDateFrom <= $toDay and $toDay <= $license->licenseDateTo)
        ) return true;
        else return false;
    } else return true;
}

function motherboardSeries(): string
{
    ob_start();
    switch (PHP_OS) {
        case 'Linux':
            $result = system("cat /sys/devices/virtual/dmi/id/board_name"); 
            break;
        
        case 'WINNT':
            $result = system("wmic baseboard get product"); 
            break;
    }
    ob_clean(); 
    return $result;
}

function licenseKey(): object|null
{
    if (file_exists(LICENSE_PATH_KEY)) {
        $data = file_get_contents(LICENSE_PATH_KEY);
        return json_decode(zlib_decode(hex2bin($data)));
    } else return null;
}

?>