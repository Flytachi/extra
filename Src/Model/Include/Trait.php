<?php

trait ModelTSave
{
    public function saveBefore()
    {
        $this->db = $this->con->connection();
        $this->db->beginTransaction();
        $this->setData(Connect::cleanForm($this->getData()));
        $this->setData(Connect::toNull($this->getData()));
    }

    public function saveBody()
    {
        $object = $this->con->cInsert($this->table, $this->getData());
        if (!is_numeric($object)) $this->error($object);
        $this->setPk($object);
    }

    public function saveAfter()
    {
        $this->db->commit();
    }
}

trait ModelTUpdate
{
    public function updateBefore()
    {
        $this->db = $this->con->connection();
        $this->db->beginTransaction();
        $this->setData(Connect::cleanForm($this->getData()));
        $this->setData(Connect::toNull($this->getData()));
    }

    public function updateBody()
    {
        $object = $this->con->cUpdate($this->table, $this->getData(), $this->getPk());
        if (!is_numeric($object) and $object <= 0) $this->error($object);
    }

    public function updateAfter()
    {
        $this->db->commit();
    }
}

trait ModelTDelete
{
    public function deleteBefore()
    {
        $this->db = $this->con->connection();
        $this->db->beginTransaction();
    }

    public function deleteBody()
    {
        $object = $this->con->cDelete($this->table, $this->getPk());
        if ($object <= 0) $this->error($object);
    }

    public function deleteAfter()
    {
        $this->db->commit();
    }
}

trait ModelTJsonResponce
{
    public function error($message)
    {
        if($this->db->inTransaction()) $this->db->rollBack();
        Route::ErrorResponseJson(array(
            'status' => 'error', 
            'message' => $message,
        ));
    }
}

?>