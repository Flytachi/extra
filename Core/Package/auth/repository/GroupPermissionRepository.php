<?php

use Extra\Src\Repository;

class GroupPermissionRepository extends Repository
{
    public string $table = 'auth_group_permissions';
    public string $modelName = 'GroupPermissionModel';
    
    public function getAllPermission($pk)
    {
        $permission = [];
        $this->Where(array('group_id' => $pk));
        foreach ($this->getAll() as $value) $permission[] = $value->permission;
        return $permission;
    }
}

?>