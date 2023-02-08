<?php

use Extra\Src\Model;
use Extra\Src\ModelInterface;

class GroupPermissionModel extends Model implements ModelInterface
{
    public int $group_id;
    public string $permission;
}
