<?php

use Extra\Src\Model;
use Extra\Src\ModelInterface;

class FirmwareWebhookModel extends Model implements ModelInterface
{
    public int|null $id = null;
    public int $enterprise_id;
    public string $unique_key;
}