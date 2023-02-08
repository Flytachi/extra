<?php

use Extra\Src\Repository;

class UserInfoRepository extends Repository
{
    public string $table = 'user_info';
    
    public function isUser($pk): mixed
    {
        return $this->getBy(['user_id' => $pk]);
    }
}
