<?php

function arrayToRequest(array $param = null): ?string
{
    if ($param == null) return null;
    else {
        $str = "?";
        foreach ($param as $key => $value) $str .= "$key=$value&";
        return substr($str,0,-1);
    }
}

function import(string $path): void
{
    include PATH_RESOURCE . "/$path.php";
}
