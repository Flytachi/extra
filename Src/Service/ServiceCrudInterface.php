<?php

namespace Extra\Src\Service;

use Extra\Src\Model\ModelInterface;

interface ServiceCrudInterface
{
    public function save(ModelInterface $model): mixed;
    public function update(mixed $pk, ModelInterface $model): mixed;
    public function delete(mixed $pk): mixed;
}