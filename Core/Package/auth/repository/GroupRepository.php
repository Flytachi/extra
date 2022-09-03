<?php

use Extra\Src\Repository;

class GroupRepository extends Repository
{
    public string $table = 'auth_groups';
    public string $modelName = 'GroupModel';
    
    public function prepare()
    {
        $this->permissions = $this->getData('permission') ?? [];
        if ($this->getData('permission')) $this->deleteDataItem('permission');
    }

    public function permission()
    {
        $groupPerm = new GroupPermissionRepository();

        // Delete
        $obj = $this->db->delete($groupPerm->table, array('group_id'=>$this->getPk()));
        if (!is_numeric($obj)) $this->error($obj);

        // Create
        if (is_array($this->permissions)) {
            foreach ($this->permissions as $permission) {
                if (!$this->db->query("SELECT id FROM " . $groupPerm->table . " WHERE group_id=" . $this->getPk() . " AND permission LIKE '$permission'")->fetchColumn()) {
                    $obj = $this->db->insert($groupPerm->table, array('group_id' => $this->getPk(), 'permission' => $permission));
                    if (!is_numeric($obj)) $this->error($obj);
                }
            }
        }
    }

    public function saveBody(): void
    {
        $this->prepare();
        parent::saveBody();
        $this->permission();
    }

    public function updateBody(): void
    {
        $this->prepare();
        parent::updateBody();
        $this->permission();
        $infos = (new UserInfoRepository)->Where("group_id = ". $this->getPk());
        $userModel = new UserRepository;
        foreach ($infos->getAll() as $info) {
            $userModel->setPk($info->user_id);
            $userModel->permission($this->permissions);
        }
    }

    public function deleteBody(): void
    {
        $userPerm = new UserPermissionRepository;

        $object = $this->db->delete($this->table, $this->getPk());
        if (!is_numeric($object)) $this->error($object);

        $perm = (new GroupPermissionRepository)->getAllPermission($this->getPk());
        if ($perm) {
            $obj = $this->db->delete($userPerm->table, array('name' => $perm));
            if (!is_numeric($obj)) $this->error($obj);
        }
    }
}

?>