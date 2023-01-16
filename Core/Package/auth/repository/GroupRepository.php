<?php

use Extra\Src\Repository;

class GroupRepository extends Repository
{
    public string $table = 'auth_groups';
    public string $modelName = 'GroupModel';

    public function permission()
    {
        $groupPerm = new GroupPermissionRepository();

        // Delete
        $obj = Warframe::$db->delete($groupPerm->table, array('group_id'=>$this->getPk()));
        if (!is_numeric($obj)) $this->error($obj);

        // Create
        if (isset($_POST['permission']) && is_array($_POST['permission'])) {
            foreach ($_POST['permission'] as $permission) {
                if (!Warframe::$db->query("SELECT permission FROM " . $groupPerm->table . " WHERE group_id=" . $this->getPk() . " AND permission LIKE '$permission'")->fetchColumn()) {
                    $model = new $groupPerm->modelName(['group_id' => $this->getPk(), 'permission' => $permission]);
                    $obj = Warframe::$db->insert($groupPerm->table, $model);
                    if (!is_numeric($obj)) $this->error($obj);
                }
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
        $infos = (new UserInfoRepository)->Where("group_id = ". $this->getPk());
        $userModel = new UserRepository;
        foreach ($infos->getAll() as $info) {
            $userModel->setPk($info->getUserId());
            $userModel->permission($_POST['permission']);
        }
    }

    public function deleteBody(): void
    {
        $userPerm = new UserPermissionRepository;

        $object = Warframe::$db->delete($this->table, $this->getPk());
        if (!is_numeric($object)) $this->error($object);

        $perm = (new GroupPermissionRepository)->getAllPermission($this->getPk());
        if ($perm) {
            $obj = Warframe::$db->delete($userPerm->table, ['name' => $perm]);
            if (!is_numeric($obj)) $this->error($obj);
        }
    }
}
