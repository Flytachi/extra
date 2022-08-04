<?php

use Extra\Src\Model;

class UserModel extends Model
{
    public string $table = 'auth_users';

    public function prepare()
    {
        if ($this->getData('password')) {
            $this->setDataItem('password', sha1($this->getData('password')));
        }
        if ($this->getData('info')) {
            $this->info = $this->getData('info');
            $this->deleteDataItem('info');
        }
    }

    public function info()
    {
        if (isset($this->info) and $this->info) {
            importModel('UserInfoModel', 'GroupPermissionModel');
            $modelInfo = new UserInfoModel;
            $userInfo = $modelInfo->isUser($this->getPk());
            $this->info['user_id'] = $this->getPk();

            if ( $userInfo ) $this->db->update($modelInfo->getTable(), $this->info, array('user_id' => $this->getPk()));
            else $this->db->insert($modelInfo->getTable(), $this->info);
            $this->permission((new GroupPermissionModel)->getAllPermission($this->info['group_id']));
        }
    }

    public function permission($permissions)
    {
        importModel('UserPermissionModel');
        $userPerm = new UserPermissionModel;

        // Delete
        $obj = $this->db->delete($userPerm->getTable(), array('user_id'=>$this->getPk()));
        if (!is_numeric($obj)) $this->error($obj);

        // Create
        foreach ($permissions as $permission) {
            if (!$this->db->query("SELECT id FROM " . $userPerm->getTable() . " WHERE user_id=" . $this->getPk() . " AND name LIKE '$permission'")->fetchColumn()) {
                $obj = $this->db->insert($userPerm->getTable(), array('user_id' => $this->getPk(), 'name' => $permission));
                if (!is_numeric($obj)) $this->error($obj);
            }
        }
    }

    public function saveBody(): void
    {
        $this->prepare();
        parent::saveBody();
        $this->info();
    }

    public function updateBody(): void
    {
        $this->prepare();
        parent::updateBody();
        $this->info();
    }
    
}

?>