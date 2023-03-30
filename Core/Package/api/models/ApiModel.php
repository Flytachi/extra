<?php

use Extra\Src\Model;
use Extra\Src\ModelInterface;

class ApiModel extends Model implements ModelInterface
{
    public int|null $id = null;
    public string $type;
    public string|null $token = null;
    public string|null $username = null;
    public string|null $password = null;
    public int $is_delete = 0;
}