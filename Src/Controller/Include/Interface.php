<?php

interface ControllerInterface
{
    public function hook($pk = null);
    public function delete($pk = null);
    public function restore($pk = null);
    public function remove($pk = null);

    public function prepareHook($pk = null, $data);
    public function prepareHookUpdate($pk, $data);
	public function prepareHookSave($data);
	public function prepareDelete($pk);
	public function prepareRestore($pk);
	public function prepareRemove($pk);
}

?>