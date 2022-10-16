<?php

use Extra\Src\Model;
use Extra\Src\ModelIterator;

class UserInfoModel extends Model
{
    use ModelIterator;
    private int $user_id;
    private string $name;
    private int|null $group_id = null;

    /**
     * @return int
     */
    public function getUserId(): int
    {
        return $this->user_id;
    }

    /**
     * @param int $user_id
     */
    public function setUserId(int $user_id): void
    {
        $this->user_id = $user_id;
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
     * @return int|null
     */
    public function getGroupId(): ?int
    {
        return $this->group_id;
    }

    /**
     * @param int|null $group_id
     */
    public function setGroupId(?int $group_id): void
    {
        $this->group_id = $group_id;
    }

}
