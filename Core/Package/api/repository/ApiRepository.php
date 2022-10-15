<?php

use Extra\Src\Repository;

class ApiRepository extends Repository
{
    public string $table = 'auth_apis';
    public string $modelName = 'ApiModel';
}
