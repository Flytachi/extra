<?php

use Extra\Src\Model;
use Extra\Src\ModelInterface;

class UserPermissionModel extends Model implements ModelInterface
{
    public int $user_id;
    public string $name;
}
