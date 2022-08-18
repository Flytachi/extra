<?php

use Extra\Src\Model;

class FirmwareLicenseModel extends Model
{
    public $id;
    public $enterprise_id;
    public $series;
    public $date_from;
    public $date_to;
    public $is_delete;
}

?>