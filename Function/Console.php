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

?>