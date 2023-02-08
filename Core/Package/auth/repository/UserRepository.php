<?php

use Extra\Src\Repository;

class UserRepository extends Repository
{
    public string $table = 'auth_users';

    public function info(): void
    {
        if (isset($_POST['info']) and $_POST['info']) {
            $repoInfo = new UserInfoRepository;
            $userInfo = $repoInfo->isUser($this->getPk());

            $data = array_merge($_POST['info'], ['user_id' => $this->getPk()]);
            if ( $userInfo ) {
                $userInfo->reConstruct($data);
                Warframe::$db->update($repoInfo->table, $userInfo, ['user_id' => $this->getPk()]);
            } else Warframe::$db->insert($repoInfo->table, new UserInfoModel($data));

            if (isset($data['group_id']))
                $this->permission((new GroupPermissionRepository)->getAllPermission($data['group_id']));
        }
    }

    public function permission($permissions): void
    {
        $userPerm = new UserPermissionRepository;

        // Delete
        Warframe::$db->delete($userPerm->table, ['user_id'=>$this->getPk()]);

        // Create
        if (is_array($permissions)) {
            foreach ($permissions as $permission) {
                if (!Warframe::$db->query("SELECT name FROM " . $userPerm->table . " WHERE user_id=" . $this->getPk() . " AND name LIKE '$permission'")->fetchColumn())
                    Warframe::$db->insert($userPerm->table, new UserPermissionModel([
                        'user_id' => $this->getPk(),
                        'name' => $permission
                    ]));
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
