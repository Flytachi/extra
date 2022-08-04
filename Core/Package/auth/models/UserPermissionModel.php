<?php

use Extra\Src\Model;

class UserPermissionModel extends Model
{
    public string $table = 'auth_user_permissions';

    public function getAllPermission($pk = null) : array
    {
        $permission = [];
        $this->Wr(array('user_id' => $pk ?? $_SESSION['id']));
        foreach ($this->list() as $value) $permission[] = $value->name;
        return $permission;
    }

    public function getPermission($permission) : bool
    {
        $this->Where("user_id = {$_SESSION['id']} AND name LIKE '$permission'");
        if ($this->get()) return true;
        else return false;
    }

}

?>