<?php

use Extra\Src\Model;
use Extra\Src\ModelInterface;

class UserInfoModel extends Model implements ModelInterface
{
    public int $user_id;
    public string $name;
    public int|null $group_id = null;
}
