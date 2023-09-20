<?php

namespace Extra\Src\Service;

use Extra\Src\Model\ModelInterface;
use Extra\Src\Repo\Repository;

class ServiceCrud
{
    public static function save(Repository $repo, ModelInterface $model)
    {
        $repo->db()->beginTransaction();
        $object = $repo->insert($model);
        $repo->db()->commit();
        return $object;
    }

    public static function update(Repository $repo, mixed $pk, ModelInterface $model)
    {
        $repo->db()->beginTransaction();
        $repo->update($model, $pk);
        $repo->db()->commit();
        return $pk;
    }

    public static function delete(Repository $repo, mixed $pk)
    {
        $repo->db()->beginTransaction();
        $repo->delete($pk);
        $repo->db()->commit();
        return $pk;
    }
}