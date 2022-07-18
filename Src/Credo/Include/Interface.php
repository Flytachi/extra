<?php

interface CredoInterface
{
    public function get(String ...$items);
    public function by(Array $params, $item = '');
    public function byId(Int $id, $item = '');
    public function list(Bool $counter = false);
    public function getId();
    public function as(String $context);
    public function Data(String $context = "*");
    public function Limit(Int $limit = 0);
    public function Join(Model $model, String $on);
    public function JoinLEFT(Model $model, String $on);
    public function JoinRIGHT(Model $model, String $on);
    public function Where(Mixed $context);
    public function Wr(Mixed $context);
    public function Order(String $context);
    public function Group(String $context);
    public function panel();
    public function showError(Bool $status = false);
    public function getSearch();
    public function getSql();
}

?>