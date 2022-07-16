<?php

function isMaster(): bool
{
    if (isset($_SESSION) and isset($_SESSION['is_master']) and $_SESSION['is_master']) return true;
    else return false;
}

function isAdmin(): bool
{
    if (isset($_SESSION) and isset($_SESSION['is_admin']) and $_SESSION['is_admin']) return true;
    else return false;
}

function isPermission(String $name)
{
    if (isAdmin()) return true;
    else {
        importModel('UserPermissionModel');
        return (new UserPermissionModel)->getPermission($name); 
    }
    
}

function arrayToRequest(Array $param = null) 
{
    if ($param == null) return null;
    else {
        $str = "?";
        foreach ($param as $key => $value) $str .= "$key=$value&";
        return substr($str,0,-1);
    }
}

function isActiveLink($link, $class = 'active')
{
    if (is_array($link)) {
        if (in_array($_SERVER['REQUEST_URI'], $link)) echo $class; 
    } else {
        if($_SERVER['REQUEST_URI'] == $link) echo $class;
    }
}

function import(String $path)
{
    include dirname(__DIR__, 3) . "/" . APP_PUBLIC . "/" . VIEW_FOLDER . "/$path.php";
}

?>