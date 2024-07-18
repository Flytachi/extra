<?php

namespace Extra\Src\Service;

use Extra\Src\Entity\Model\ModelInterface;
use Extra\Src\Repo\BKB;
use Extra\Src\Repo\Repository;

/**
 * Class ServiceCrud
 *
 * `ServiceCrud` provides a set of static methods for performing Create, Read, Update, and Delete (CRUD) operations on your model
 * instances using an associated repository. It ensures transactions are used with these operations to maintain the integrity
 * of your data.
 *
 * The methods provided by `ServiceCrud` include:
 *
 * - `save(Repository $repo, ModelInterface $model): ModelInterface`: Saves the provided model to the database using the provided repository and returns the saved model.
 * - `update(Repository $repo, int $id, ModelInterface $model): int`: Updates the model with the provided ID using the provided repository and returns the ID.
 * - `delete(Repository $repo, int $id): int`: Deletes the model with the provided ID using the provided repository and returns the ID.
 *
 * @version 1.0
 * @author Flytachi
 */
class ServiceCrud
{
    public static function save(Repository $repo, ModelInterface $model): ModelInterface
    {
        $model->id = $repo->insert($model);
        return $model;
    }

    public static function update(Repository $repo, int $id, ModelInterface $model): int
    {
        $repo->update($model, BKB::eq('id', $id));
        return $id;
    }

    public static function delete(Repository $repo, int $id): int
    {
        $repo->delete(BKB::eq('id', $id));
        return $id;
    }
}