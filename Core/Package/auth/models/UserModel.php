<?php

use Extra\Src\Model;

class UserModel extends Model
{
    public $id;
    public $username;
    public $password;
    public $is_admin;
    public $is_delete;
}
