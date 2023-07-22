<?php

use Extra\Src\CDO\CDN;
use Extra\Src\Repository;

class GroupRepository extends Repository
{
    public string $table = 'auth_groups';

    public function permission(): void
    {
        $groupPerm = new GroupPermissionRepository();

        // Delete
        Warframe::$db->delete($groupPerm->table, ['group_id' => $this->getPk()]);

        // Create
        if (isset($_POST['permission']) && is_array($_POST['permission'])) {
            foreach ($_POST['permission'] as $permission) {
                if (!Warframe::$db->query("SELECT permission FROM " . $groupPerm->table . " WHERE group_id=" . $this->getPk() . " AND permission LIKE '$permission'")->fetchColumn())
                    Warframe::$db->insert($groupPerm->table, new GroupPermissionModel([
                        'group_id' => $this->getPk(),
                        'permission' => $permission
                    ]));
            }
        }
    }

    public function saveBody(): void
    {
        parent::saveBody();
        $this->permission();
    }

    public function updateBody(): void
    {
        parent::updateBody();
        $this->permission();
        $infos = (new UserInfoRepository)->Where(CDN::eq('group_id', $this->getPk()));
        $userModel = new UserRepository;
        foreach ($infos->getAll() as $info) {
            $userModel->setPk($info->user_id);
            $userModel->permission($_POST['permission'] ?? []);
        }
    }

    public function deleteBody(): void
    {
        $userPerm = new UserPermissionRepository;
        Warframe::$db->delete($this->table, $this->getPk());
        $perm = (new GroupPermissionRepository)->getAllPermission($this->getPk());
        if ($perm) Warframe::$db->delete($userPerm->table, ['name' => $perm]);
    }
}
