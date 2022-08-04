<?php

use Extra\Src\Model;

class GroupPermissionModel extends Model
{
    public string $table = 'auth_group_permissions';

    public function getAllPermission($pk)
    {
        $permission = [];
        $this->Wr(array('group_id' => $pk));
        foreach ($this->list() as $value) $permission[] = $value->permission;
        return $permission;
    }    
}

?>