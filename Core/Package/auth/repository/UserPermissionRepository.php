<?php

use Extra\Src\CDO\CDN;
use Extra\Src\Repository;

class UserPermissionRepository extends Repository
{
    public string $table = 'auth_user_permissions';
    
    public function getAllPermission($pk = null) : array
    {
        $permission = [];
        $this->Where(CDN::eq('user_id',$pk ?? $_SESSION['id']));
        foreach ($this->getAll() as $value) $permission[] = $value->name;
        return $permission;
    }

    public function getPermission($permission) : bool
    {
        $this->Where(CDN::and(
            CDN::eq('user_id', $_SESSION['id']),
            CDN::like('name', $permission)
        ));
        if ($this->get()) return true;
        else return false;
    }
}
