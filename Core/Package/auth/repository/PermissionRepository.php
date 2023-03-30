<?php

use Extra\Src\Repository;

class PermissionRepository extends Repository
{
    public string $table = 'auth_permissions';
 
    public function updateBody(): void
    {
        Warframe::$db->update($this->table, $this->getModel(), ['name' => $this->getPk()]);
    }

    public function deleteBody(): void
    {
        Warframe::$db->delete($this->table, ['name' => $this->getPk()]);
    }
}
