<?php

function dieConnection($_error = null): never
{
    die(include dirname(__DIR__, 3) . "/" . APP_PUBLIC . "/" . VIEW_FOLDER . "/error/system.php"); 
}

function cfgGet(): array
{
    if (!file_exists(CFG_PATH_CLOSE)) dieConnection("Configuration file not found.");
    return json_decode(zlib_decode(hex2bin( str_replace("\n", "", file_get_contents(CFG_PATH_CLOSE)) )), true);
}

function dd($value = null): never
{
    echo '<pre style="background-color: black; color: #00ff00; border-style: solid; border-color: #ff0000; border-width: medium;">';
    print_r($value);
    echo '</pre>';
    exit();
}

function parad($title = null, $value = null) 
{
    echo '<pre style="background-color: black; color: #00ff00; border-style: solid; border-color: #ff0000; border-width: medium;">';
    echo "<strong style=\"color: #ffffff;\">$title</strong><br>";
    print_r($value);
    echo '</pre>';
}

function getDirContent($dir, $filter = '', &$results = array())
{
    $files = scandir($dir);

    foreach($files as $key => $value){
        $path = realpath($dir.DIRECTORY_SEPARATOR.$value); 

        if(!is_dir($path)) {
            if(empty($filter) || preg_match($filter, $path)) $results[] = $path;
        } elseif($value != "." && $value != "..") {
            getDirContent($path, $filter, $results);
        }
    }

    return $results;
}

function importLib(String ...$libs)
{
    foreach ($libs as $lib) {
        include dirname(__DIR__, 2) ."/libs/$lib";
    }
}

function importModel(String ...$models)
{
    foreach ($models as $model) {
        $path = dirname(__DIR__, 2) .'/models/' . $model . '.php';
        if (file_exists($path)) {
            try { 
                if( !class_exists($model) ) include $path;
            }
            catch (\Throwable $th) { 
                if (!cfgGet()['GLOBAL_SETTING']['DEBUG']) dd('Ошибка в модели');
                else dd($th);
                die;
            }
        }
    }
}

function importController(String ...$controllers)
{
    foreach ($controllers as $controller) {
        $path = dirname(__DIR__, 2) .'/controllers/' . $controller . '.php';
        if (file_exists($path)) {
            try { 
                include $path;
            } catch (\Throwable $th) { 
                if (!cfgGet()['GLOBAL_SETTING']['DEBUG']) dd('Ошибка в контроллере');
                else dd($th);
                die;
            }
        } else Route::ErrorPage(404);
    }
}

function importPluginController(String $plugin, String ...$controllers)
{
    foreach ($controllers as $controller) {
        $path = dirname(__DIR__, 3) . "/Plugins/Frame.$plugin/controllers/$controller.php";
        if (file_exists($path)) {
            try { 
                include $path;
            } catch (\Throwable $th) { 
                if (!cfgGet()['GLOBAL_SETTING']['DEBUG']) dd('Ошибка в контроллере');
                else dd($th);
                die;
            }
        } else Route::ErrorPage(404);
    }
}

function importPluginModel(String $plugin, String ...$models){
    foreach ($models as $model) {
        $path = dirname(__DIR__, 3) . "/Plugins/Frame.$plugin/models/$model.php";
        if (file_exists($path)) {
            try { 
                if( !class_exists($model) ) include $path;
            }
            catch (\Throwable $th) { 
                if (!cfgGet()['GLOBAL_SETTING']['DEBUG']) dd('Ошибка в модели');
                else dd($th);
                die;
            }
        }
    }
}

function checkPlugin(String $plugin)
{
    if(empty($plugin)) return false;
    $path = dirname(__DIR__, 3) . "/Plugins/Frame.$plugin";
    return is_dir($path);
}

function bytes($bytes, $force_unit = NULL, $format = NULL, $si = TRUE)
{
    // Format string
    $format = ($format === NULL) ? '%01.2f %s' : (string) $format;

    // IEC prefixes (binary)
    if ($si == FALSE OR strpos($force_unit, 'i') !== FALSE) {
        $units = array('B', 'KiB', 'MiB', 'GiB', 'TiB', 'PiB');
        $mod   = 1024;
    }
    // SI prefixes (decimal)
    else {
        $units = array('B', 'kB', 'MB', 'GB', 'TB', 'PB');
        $mod   = 1000;
    }

    // Determine unit to use
    if (($power = array_search((string) $force_unit, $units)) === FALSE)
    {
        $power = ($bytes > 0) ? floor(log($bytes, $mod)) : 0;
    }

    return sprintf($format, $bytes / pow($mod, $power), $units[$power]);
}

?>
