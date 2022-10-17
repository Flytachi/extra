<?php

use Extra\Src\Model;
use Extra\Src\ModelIterator;

class FirmwareLicenseModel extends Model
{
    use ModelIterator;
    private int|null $id = null;
    private int $enterprise_id;
    private string $series;
    private string $date_from;
    private string $date_to;
    private int $is_delete = 0;

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
    public function getSeries(): string
    {
        return $this->series;
    }

    /**
     * @param string $series
     */
    public function setSeries(string $series): void
    {
        $this->series = $series;
    }

    /**
     * @return string
     */
    public function getDateFrom(): string
    {
        return $this->date_from;
    }

    /**
     * @param string $date_from
     */
    public function setDateFrom(string $date_from): void
    {
        $this->date_from = $date_from;
    }

    /**
     * @return string
     */
    public function getDateTo(): string
    {
        return $this->date_to;
    }

    /**
     * @param string $date_to
     */
    public function setDateTo(string $date_to): void
    {
        $this->date_to = $date_to;
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

}