<?php

abstract class Model extends Credo implements ModelInterface
{
    /**
     * 
     * Model
     * 
     * @version 9.3
     */

    private $pk;
    private $data = [];
    protected $table = '';

    use
        ModelTSave,
        ModelTUpdate,
        ModelTDelete,
        ModelTJsonResponce;


    final public function setPk($pk)
    {
        $this->pk = $pk;
    }
    
    final public function setData($data)
    {
        $this->data = $data;
    }

    final public function getPk()
    {
        return $this->pk;
    }

    final public function getTable()
    {
        return $this->table;
    }

    final public function getData(String $item = null)
    {
        return ($item == null) ? $this->data : ((array) $this->data)[$item] ?? null;
    }

    final public function setDataItem(String $item, $value = null)
    {
        $this->data[$item] = $value;
    }

    final public function deleteDataItem(String $item)
    {
        unset($this->data[$item]);
    }

    final public function save($data)
    {
        $this->setData($data);
        $this->saveBefore();
        $this->saveBody();
        $this->saveAfter();
        return $this->getPk();
    }

    final public function update($pk, $data)
    {
        $this->setPk($pk);
        $this->setData($data);
        $this->updateBefore();
        $this->updateBody();
        $this->updateAfter();
        return $this->getPk();
    }

    final public function delete($pk)
    {
        $this->setPk($pk);
        $this->deleteBefore();
        $this->deleteBody();
        $this->deleteAfter();
        return $this->getPk();
    }

    final public function csrfToken()
    {
        $token = bin2hex(random_bytes(24));
        $_SESSION['CSRFTOKEN'] =  $token;
        echo "<input type=\"hidden\" name=\"csrf_token\" value=\"$token\">";
    }

    final public function stop(): never
    {
        if($this->db->inTransaction()) $this->db->rollBack();
        exit;
    }

    final public function dd()
    {
        parad("Data", $this->getData());
        $this->stop();
    }

}

?>