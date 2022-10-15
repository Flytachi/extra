<?php

use Extra\Src\Repository;

class UserPermissionRepository extends Repository
{
    public string $table = 'auth_user_permissions';
    public string $modelName = 'UserPermissionModel';
    
    public function getAllPermission($pk = null) : array
    {
        $permission = [];
        $this->Where(array('user_id' => $pk ?? $_SESSION['id']));
        foreach ($this->getAll() as $value) $permission[] = $value->name;
        return $permission;
    }

    public function getPermission($permission) : bool
    {
        $this->Where("user_id = {$_SESSION['id']} AND name LIKE '$permission'");
        if ($this->get()) return true;
        else return false;
    }
}
