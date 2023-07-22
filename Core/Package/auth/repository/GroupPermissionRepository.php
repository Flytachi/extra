<?php

use Extra\Src\CDO\CDN;
use Extra\Src\Repository;

class GroupPermissionRepository extends Repository
{
    public string $table = 'auth_group_permissions';
    
    public function getAllPermission($pk): array
    {
        $permission = [];
        $this->Where(CDN::eq('group_id', $pk) );
        foreach ($this->getAll() as $value) $permission[] = $value->permission;
        return $permission;
    }
}
