<?php

use Extra\Src\Repository;

class GroupPermissionRepository extends Repository
{
    public string $table = 'auth_group_permissions';
    public string $modelName = 'GroupPermissionModel';
    
    public function getAllPermission($pk): array
    {
        $permission = [];
        $this->Where(['group_id' => $pk]);
        foreach ($this->getAll() as $value) $permission[] = $value->getPermission();
        return $permission;
    }
}
