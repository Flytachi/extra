<?php

use Extra\Src\Model;
use Extra\Src\ModelInterface;

class FirmwareLicenseModel extends Model implements ModelInterface
{
    public int|null $id = null;
    public int $enterprise_id;
    public string $series;
    public string $date_from;
    public string $date_to;
    public int $is_delete = 0;
}