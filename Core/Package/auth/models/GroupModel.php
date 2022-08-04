<?php

use Extra\Src\Model;

class GroupModel extends Model
{
    public string $table = 'auth_groups';

    public function prepare()
    {
        $this->permissions = $this->getData('permission') ?? [];
        if ($this->getData('permission')) $this->deleteDataItem('permission');
    }

    public function permission()
    {
        importModel('GroupPermissionModel');
        $groupPerm = new GroupPermissionModel();

        // Delete
        $obj = $this->db->delete($groupPerm->getTable(), array('group_id'=>$this->getPk()));
        if (!is_numeric($obj)) $this->error($obj);

        // Create
        foreach ($this->permissions as $permission) {
            if (!$this->db->query("SELECT id FROM " . $groupPerm->getTable() . " WHERE group_id=" . $this->getPk() . " AND permission LIKE '$permission'")->fetchColumn()) {
                $obj = $this->db->insert($groupPerm->getTable(), array('group_id' => $this->getPk(), 'permission' => $permission));
                if (!is_numeric($obj)) $this->error($obj);
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
        importModel('UserInfoModel', 'UserModel', 'GroupPermissionModel');
        $infos = (new UserInfoModel)->Where("group_id = ". $this->getPk());
        $userModel = new UserModel;
        foreach ($infos->list() as $info) {
            $userModel->setPk($info->user_id);
            $userModel->permission($this->permissions);
        }
    }
    
}

?>