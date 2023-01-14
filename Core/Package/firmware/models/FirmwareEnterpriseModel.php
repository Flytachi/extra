<?php

use Extra\Src\Model;
use Extra\Src\ModelInterface;

class FirmwareEnterpriseModel extends Model implements ModelInterface
{
    private int|null $id = null;
    private string $name;
    private string|null $contact = null;
    private int $is_delete = 0;
    private string|null $create_date = null;

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
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return string|null
     */
    public function getContact(): ?string
    {
        return $this->contact;
    }

    /**
     * @param string|null $contact
     */
    public function setContact(?string $contact): void
    {
        $this->contact = $contact;
    }

    /**
     * @return int
     */
    public function getIsDelete(): int
    {
        return $this->is_delete;
    }

    /**
     * @param int $is_delete
     */
    public function setIsDelete(int $is_delete): void
    {
        $this->is_delete = $is_delete;
    }

    /**
     * @return string|null
     */
    public function getCreateDate(): ?string
    {
        return $this->create_date;
    }

    /**
     * @param string|null $create_date
     */
    public function setCreateDate(?string $create_date): void
    {
        $this->create_date = $create_date;
    }

}