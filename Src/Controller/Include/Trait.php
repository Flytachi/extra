<?php

trait ControllerCrudMethod
{

    public function hook($pk = null)
    {
		if ($this->onAuthHook == true) Route::isAuth();
		if ($this->onHook == false) Route::ErrorPage('404');
		if (empty($_POST)) Route::ErrorPage(400);
		$this->csrfTokenChange();

		$this->prepareHook($pk, $_POST);

		if ( $pk ) {
			$this->prepareHookUpdate($pk, $_POST);
			$result = $this->model->update($pk, $_POST);
		} else {
			$this->prepareHookSave($_POST);
			$result = $this->model->save($_POST);
		}
		$this->renderJsonSuccess($result);
    }

	public function delete($pk = null)
    {
		if ($this->onAuthDelete == true) Route::isAuth();
		if ($this->onDelete == false) Route::ErrorPage('404');
        if (!$pk) Route::ErrorPage(400);

		$this->prepareDelete($pk);

		if ($this->model->byId($pk)) {

			$this->model->update($pk, array('is_delete' => true));
			$this->renderJsonSuccess($pk);
			
		} else Route::ErrorPage(404);
    }

	public function restore($pk = null)
    {
		if ($this->onAuthRestore == true) Route::isAuth();
		if ($this->onRestore == false) Route::ErrorPage('404');
        if (!$pk) Route::ErrorPage(400);

		$this->prepareRestore($pk);

		if ($this->model->byId($pk)) {

			$this->model->update($pk, array('is_delete' => false));
			$this->renderJsonSuccess($pk);
			
		} else Route::ErrorPage(404);
    }

	public function remove($pk = null)
    {
		if ($this->onAuthRemove == true) Route::isAuth();
		if ($this->onRemove == false) Route::ErrorPage('404');
        if (!$pk) Route::ErrorPage(400);

		$this->prepareRemove($pk);

		if ($this->model->byId($pk)) {

			$this->model->delete($pk);
			$this->renderJsonSuccess($pk);
			
		} else Route::ErrorPage(404);
    }

	public function prepareHook($pk = null, $data) {}
	public function prepareHookUpdate($pk, $data) {}
	public function prepareHookSave($data) {}
	public function prepareDelete($pk) {}
	public function prepareRestore($pk) {}
	public function prepareRemove($pk) {}

}

trait ControllerResponseMethod
{

    final public function render($content, $data = null) 
	{
		if(is_array($data)) extract($data);
		$content = VIEW_FOLDER . "/$content.php";
		include VIEW_FOLDER . "/" . $this->template;
	}

	final public function view($content, $data = null) 
	{
		if(is_array($data)) extract($data);
		include VIEW_FOLDER . "/$content.php";
	}

	final public function renderJson($data)
	{
		header('Content-type: application/json');
		echo json_encode( $data );
		die;
	}

	final public function renderJsonSuccess($message)
	{
		header('Content-type: application/json');
		echo json_encode( array('status' => 'success', 'message' => $message) );
		die;
	}

	final public function renderJsonError($message)
	{
		header('Content-type: application/json');
		echo json_encode( array('status' => 'error', 'message' => $message) );
		die;
	}

}

?>