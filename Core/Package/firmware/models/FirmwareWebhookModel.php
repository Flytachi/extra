<?php

use Extra\Src\Model;
use Extra\Src\ModelIterator;

class FirmwareWebhookModel extends Model
{
    use ModelIterator;
    private int|null $id = null;
    private int $enterprise_id;
    private string $unique_key;

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param int|null $id
     */
    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    /**
     * @return int
     */
    public function getEnterpriseId(): int
    {
        return $this->enterprise_id;
    }

    /**
     * @param int $enterprise_id
     */
    public function setEnterpriseId(int $enterprise_id): void
    {
        $this->enterprise_id = $enterprise_id;
    }

    /**
     * @return string
     */
    public function getUniqueKey(): string
    {
        return $this->unique_key;
    }

    /**
     * @param string $unique_key
     */
    public function setUniqueKey(string $unique_key): void
    {
        $this->unique_key = $unique_key;
    }

}