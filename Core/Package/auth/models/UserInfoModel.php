<?php

use Extra\Src\Model;

class UserInfoModel extends Model
{
    public string $table = 'user_info';

    public function isUser($pk)
    {
        return $this->by(array('user_id'=>$pk));
    }
        
}

?>