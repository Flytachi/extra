<?php

use Extra\Src\Model;
use Extra\Src\ModelInterface;

class FirmwareEnterpriseModel extends Model implements ModelInterface
{
    public int|null $id = null;
    public string $name;
    public string|null $contact = null;
    public int $is_delete = 0;
    public string|null $create_date = null;
}