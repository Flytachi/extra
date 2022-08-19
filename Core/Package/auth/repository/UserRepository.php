<?php

use Extra\Src\Repository;

class UserRepository extends Repository
{
    public string $table = 'auth_users';
    public string $modelName = 'UserModel';
    
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
            importRepository('UserInfoRepository', 'GroupPermissionRepository');
            $repoInfo = new UserInfoRepository;
            $userInfo = $repoInfo->isUser($this->getPk());
            $this->info['user_id'] = $this->getPk();

            if ( $userInfo ) $this->db->update($repoInfo->table, $this->info, array('user_id' => $this->getPk()));
            else $this->db->insert($repoInfo->table, $this->info);
            if (isset($this->info['group_id'])) {
                $this->permission((new GroupPermissionRepository)->getAllPermission($this->info['group_id']));
            }
        }
    }

    public function permission($permissions)
    {
        importRepository('UserPermissionRepository');
        $userPerm = new UserPermissionRepository;

        // Delete
        $obj = $this->db->delete($userPerm->table, array('user_id'=>$this->getPk()));
        if (!is_numeric($obj)) $this->error($obj);

        // Create
        if (is_array($permissions)) {
            foreach ($permissions as $permission) {
                if (!$this->db->query("SELECT id FROM " . $userPerm->table . " WHERE user_id=" . $this->getPk() . " AND name LIKE '$permission'")->fetchColumn()) {
                    $obj = $this->db->insert($userPerm->table, array('user_id' => $this->getPk(), 'name' => $permission));
                    if (!is_numeric($obj)) $this->error($obj);
                }
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