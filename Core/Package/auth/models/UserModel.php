<?php

use Extra\Src\Model;
use Extra\Src\ModelIterator;

class UserModel extends Model
{
    use ModelIterator;
    private int|null $id = null;
    private string $username;
    private string $password;
    private int $is_admin = 0;
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
     * @return string
     */
    public function getUsername(): string
    {
        return $this->username;
    }

    /**
     * @param string $username
     */
    public function setUsername(string $username): void
    {
        $this->username = $username;
    }

    /**
     * @return string
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    /**
     * @param string $password
     */
    public function setPassword(string $password): void
    {
        $this->password = $password;
    }

    /**
     * @return int
     */
    public function getIsAdmin(): int
    {
        return $this->is_admin;
    }

    /**
     * @param int $is_admin
     */
    public function setIsAdmin(int $is_admin): void
    {
        $this->is_admin = $is_admin;
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
