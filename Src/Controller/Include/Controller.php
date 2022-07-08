<?php

abstract class Controller implements ControllerInterface {
	
	public Model $model;
	public $template = VIEW_TEMPLATE;
	protected $onHook = false;
	protected $onAuthHook = false;

	protected $onDelete = false;
	protected $onAuthDelete = false;
	
	protected $onRestore = false;
	protected $onAuthRestore = false;
	
	protected $onRemove = false;
	protected $onAuthRemove = false;

	use ControllerCrudMethod, ControllerResponseMethod;

	final public function setModel($modelName)
	{
		$this->model = new $modelName;
	}

	final public function csrfTokenChange()
	{
		if ((isset($_SESSION['CSRFTOKEN']) and isset($_POST['csrf_token']) and hash_equals($_SESSION['CSRFTOKEN'], $_POST['csrf_token']))) {

            unset($_SESSION['CSRFTOKEN']);
            unset($_POST['csrf_token']);
    
        } else Route::ErrorPage(419);
	}

	final public function getElement($pk)
	{
		$object = $this->model->byId($pk);
        if ($object) $this->model->setData($object);
        else Route::ErrorPage(404);
	}

}

?>