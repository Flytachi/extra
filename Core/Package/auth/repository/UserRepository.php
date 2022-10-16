<?php

use Extra\Src\Repository;

class UserRepository extends Repository
{
    public string $table = 'auth_users';
    public string $modelName = 'UserModel';

    public function info()
    {
        if (isset($_POST['info']) and $_POST['info']) {
            $repoInfo = new UserInfoRepository;
            $userInfo = $repoInfo->isUser($this->getPk());

            $data = array_merge($_POST['info'], ['user_id' => $this->getPk()]);
            if ( $userInfo ) {
                $userInfo->setNewObject($data);
                $this->db->update($repoInfo->table, $userInfo, array('user_id' => $this->getPk()));
            } else {
                $userInfo = new $repoInfo->modelName($data);
                $this->db->insert($repoInfo->table, $userInfo);
            }
            if (isset($data['group_id'])) {
                $this->permission((new GroupPermissionRepository)->getAllPermission($data['group_id']));
            }
        }
    }

    public function permission($permissions)
    {
        $userPerm = new UserPermissionRepository;

        // Delete
        $obj = $this->db->delete($userPerm->table, ['user_id'=>$this->getPk()]);
        if (!is_numeric($obj)) $this->error($obj);

        // Create
        if (is_array($permissions)) {
            foreach ($permissions as $permission) {
                if (!$this->db->query("SELECT name FROM " . $userPerm->table . " WHERE user_id=" . $this->getPk() . " AND name LIKE '$permission'")->fetchColumn()) {
                    $model = new $userPerm->modelName(['user_id' => $this->getPk(), 'name' => $permission]);
                    $obj = $this->db->insert($userPerm->table, $model);
                    if (!is_numeric($obj)) $this->error($obj);
                }
            }
        }
    }

    public function saveBody(): void
    {
        parent::saveBody();
        $this->info();
    }

    public function updateBody(): void
    {
        parent::updateBody();
        $this->info();
    }
}
