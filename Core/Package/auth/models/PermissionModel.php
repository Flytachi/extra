<?php

use Extra\Src\Model;
use Extra\Src\ModelInterface;

class PermissionModel extends Model implements ModelInterface
{
    public string $name;
    public string $description;
}

