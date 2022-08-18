<?php

use Extra\Src\Repository;

class UserInfoRepository extends Repository
{
    public string $table = 'user_info';
    public string $modelName = 'UserInfoModel';
    
    public function isUser($pk)
    {
        return $this->getBy(array('user_id'=>$pk));
    }
}

?>