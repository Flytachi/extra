<?php

interface ModelInterface
{
    public function setPk($pk);
    public function setData($data);
    public function setDataItem(String $item, $value = null);
    public function deleteDataItem(String $item);
    public function getPk();
    public function getTable();
    public function getData(String $item = null);
    public function csrfToken();
    
    public function save($data);
    public function saveBefore();
    public function saveBody();
    public function saveAfter();
    
    public function update($pk, $data);
    public function updateBefore();
    public function updateBody();
    public function updateAfter();
    
    public function delete($pk);
    public function deleteBefore();
    public function deleteBody();
    public function deleteAfter();
}

?>