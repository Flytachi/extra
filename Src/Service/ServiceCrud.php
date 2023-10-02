<?php

namespace Extra\Src\Service;

use Extra\Src\CDO\BKB;
use Extra\Src\Model\ModelInterface;
use Extra\Src\Repo\Repository;

class ServiceCrud
{
    public static function save(Repository $repo, ModelInterface $model): ModelInterface
    {
        $repo->db()->beginTransaction();
        $object = $repo->insert($model);
        $repo->db()->commit();
        return $object;
    }

    public static function update(Repository $repo, int $id, ModelInterface $model): int
    {
        $repo->db()->beginTransaction();
        $repo->update($model, BKB::eq('id', $id));
        $repo->db()->commit();
        return $id;
    }

    public static function delete(Repository $repo, int $id): int
    {
        $repo->db()->beginTransaction();
        $repo->delete(BKB::eq('id', $id));
        $repo->db()->commit();
        return $id;
    }
}