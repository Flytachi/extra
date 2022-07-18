<?php

abstract class Controller {
	
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

    final public function setModel(string $modelName): void
    {
        $this->model = new $modelName;
    }

    final public function csrfTokenChange(): void
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

    /*  
    ---------------------------------------------
        CRUD
    ---------------------------------------------
    */
    public function hook(string $pk = null): void
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

    public function delete(string $pk): void
    {
        if ($this->onAuthDelete == true) Route::isAuth();
        if ($this->onDelete == false) Route::ErrorPage('404');

        $this->prepareDelete($pk);

        if ($this->model->byId($pk)) {

            $this->model->update($pk, array('is_delete' => true));
            $this->renderJsonSuccess($pk);
            
        } else Route::ErrorPage(404);
    }

    public function restore(string $pk): void
    {
        if ($this->onAuthRestore == true) Route::isAuth();
        if ($this->onRestore == false) Route::ErrorPage('404');

        $this->prepareRestore($pk);

        if ($this->model->byId($pk)) {

            $this->model->update($pk, array('is_delete' => false));
            $this->renderJsonSuccess($pk);
            
        } else Route::ErrorPage(404);
    }

    public function remove(string $pk): void
    {
        if ($this->onAuthRemove == true) Route::isAuth();
        if ($this->onRemove == false) Route::ErrorPage('404');

        $this->prepareRemove($pk);

        if ($this->model->byId($pk)) {

            $this->model->delete($pk);
            $this->renderJsonSuccess($pk);
            
        } else Route::ErrorPage(404);
    }
    /*
    ---------------------------------------------
    */

    /*  
    ---------------------------------------------
        RESPONSE
    ---------------------------------------------
    */
    final public function render(string $content, mixed $data = null): void
    {
        if(is_array($data)) extract($data);
        $content = VIEW_FOLDER . "/$content.php";
        include VIEW_FOLDER . "/" . $this->template;
    }

    final public function view(string $content, mixed $data = null): void
    {
        if(is_array($data)) extract($data);
        include VIEW_FOLDER . "/$content.php";
    }

    final public function renderJson(array $data): never
    {
        header('Content-type: application/json');
        echo json_encode( $data );
        die;
    }

    final public function renderJsonSuccess(string $message): never
    {
        header('Content-type: application/json');
        echo json_encode( array('status' => 'success', 'message' => $message) );
        die;
    }

    final public function renderJsonError(string $message): never
    {
        header('Content-type: application/json');
        echo json_encode( array('status' => 'error', 'message' => $message) );
        die;
    }
    /*
    ---------------------------------------------
    */

    /*  
    ---------------------------------------------
        PREPARE
    ---------------------------------------------
    */
    public function prepareHook($data, $pk = null) {}
    public function prepareHookUpdate($data, $pk) {}
    public function prepareHookSave($data) {}
    public function prepareDelete($pk) {}
    public function prepareRestore($pk) {}
    public function prepareRemove($pk) {}
    /*
    ---------------------------------------------
    */
}

?>