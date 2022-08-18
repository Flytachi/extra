<?php

use Extra\Src\Repository;

class PermissionRepository extends Repository
{
    public string $table = 'auth_permissions';
    public string $modelName = 'PermissionModel';
 
    public function updateBody(): void
    {
        $object = $this->db->update($this->table, $this->getData(), array('name' => $this->getPk()));
        if (!is_numeric($object)) $this->error($object);
    }

    public function deleteBody(): void
    {
        $object = $this->db->delete($this->table, array('name' => $this->getPk()));
        if (!is_numeric($object)) $this->error($object);   
    }
}

?>