<?php

trait ModelTSave
{
    public function saveBefore()
    {
        $this->db->beginTransaction();
        $this->setData(CDO::cleanForm($this->getData()));
        $this->setData(CDO::toNull($this->getData()));
    }

    public function saveBody()
    {
        $object = $this->db->insert($this->table, $this->getData());
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
        $this->db->beginTransaction();
        $this->setData(CDO::cleanForm($this->getData()));
        $this->setData(CDO::toNull($this->getData()));
    }

    public function updateBody()
    {
        $object = $this->db->update($this->table, $this->getData(), $this->getPk());
        if (!is_numeric($object)) $this->error($object);
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
        $this->db->beginTransaction();
    }

    public function deleteBody()
    {
        $object = $this->db->delete($this->table, $this->getPk());
        if (!is_numeric($object)) $this->error($object);
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