<?php

use Extra\Src\Model;
use Extra\Src\ModelInterface;

class GroupModel extends Model implements ModelInterface
{
    public int|null $id = null;
    public string $name;
}
