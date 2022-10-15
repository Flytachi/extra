<?php

function isAdmin(): bool
{
    if (isset($_SESSION['is_admin']) and $_SESSION['is_admin'] === 1) return true;
    else return false;
}

function arrayToRequest(array $param = null): ?string
{
    if ($param == null) return null;
    else {
        $str = "?";
        foreach ($param as $key => $value) $str .= "$key=$value&";
        return substr($str,0,-1);
    }
}

function isActiveLink(array|string $link, string $class = 'active'): void
{
    if (is_array($link)) {
        if (in_array($_SERVER['REQUEST_URI'], $link)) echo $class; 
    } else {
        if($_SERVER['REQUEST_URI'] == $link) echo $class;
    }
}

function import(string $path): void
{
    include PATH_PUBLIC . "/" . VIEW_FOLDER . "/$path.php";
}
