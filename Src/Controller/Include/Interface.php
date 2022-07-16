<?php

interface ControllerInterface
{
    public function hook($pk = null);
    public function delete($pk);
    public function restore($pk);
    public function remove($pk);

    public function prepareHook($data, $pk = null);
    public function prepareHookUpdate($data, $pk);
	public function prepareHookSave($data);
	public function prepareDelete($pk);
	public function prepareRestore($pk);
	public function prepareRemove($pk);
}

?>