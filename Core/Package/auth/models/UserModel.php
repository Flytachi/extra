<?php

use Extra\Src\Model;
use Extra\Src\ModelInterface;

class UserModel extends Model implements ModelInterface
{
    public int|null $id = null;
    public string $username;
    public string $password;
    public int $is_admin = 0;
    public int $is_delete = 0;
}
