<?php

namespace Extra\Src;

abstract class Controller 
{
    /**
     * 
     * Controller
     * 
     * @version 4.2
     */


    public Model $model;
    public $template = VIEW_TEMPLATE;
    protected bool $onHook = false;
    protected bool $onAuthHook = false;

    protected bool $onDelete = false;
    protected bool $onAuthDelete = false;
    
    protected bool $onRestore = false;
    protected bool $onAuthRestore = false;
    
    protected bool $onRemove = false;
    protected bool $onAuthRemove = false;

    public array $uploadFileFormat;
    public int $uploadFileSize;

    function __call($name, $arguments)
    {
        Route::ErrorPage(404);
    }

    final public function setModel(string $modelName): void
    {
        $this->model = new $modelName;
    }

    final protected function csrfTokenChange(): void
    {
        if ((isset($_SESSION['CSRFTOKEN']) and isset($_POST['csrf_token']) and hash_equals($_SESSION['CSRFTOKEN'], $_POST['csrf_token']))) {

            unset($_SESSION['CSRFTOKEN']);
            unset($_POST['csrf_token']);
    
        } else Route::ErrorPage(419);
    }

    final protected function getElement($pk): void
    {
        $object = $this->model->byId($pk);
        if ($object) $this->model->setData($object);
        else Route::ErrorPage(404);
    }

    final protected function uploadFile(array $file): string
    {
        $uploadFolder = str_replace('Controller', '', get_class($this));
        // $uploadDir = PATH_MEDIA . $uploadFolder;
        if( !is_dir(PATH_MEDIA . $uploadFolder) ) mkdir(PATH_MEDIA . $uploadFolder, 0777);

        if ( $file['name'] ) {
            // Upload File
            if ($file['error'] === UPLOAD_ERR_OK) {
                $fileTmpPath = $file['tmp_name'];
                $fileSize = $file['size'];
                $fileNameCmps = explode(".", $file['name']);
                $fileExtension = strtolower(end($fileNameCmps));
                $newFileName = sha1(time() . $file['name']) . '.' . $fileExtension;
                // $fileType = $file['type'];

                // File size
                if (!$this->uploadFileSize or ($this->uploadFileSize and $this->uploadFileSize < $fileSize) ) {
                    Route::ErrorResponseJson(array(
                        'status' => 'error',
                        'message' => 'Error file is too big!'
                    ));
                }
        
                // File format
                if (empty($this->uploadFileFormat) or ($this->uploadFileFormat and (in_array($fileExtension, $this->uploadFileFormat) or $this->uploadFileFormat == $fileExtension)) ) {

                    if(move_uploaded_file($fileTmpPath, PATH_MEDIA . "$uploadFolder/$newFileName")) return "$uploadFolder/$newFileName";
                    else{
                        Route::ErrorResponseJson(array(
                            'status' => 'error',
                            'message' => 'Error writing to database!'
                        ));
                    }
        
                }else {
                    Route::ErrorResponseJson(array(
                        'status' => 'error',
                        'message' => 'Error unsupported file format!'
                    ));
                }

            }else {
                Route::ErrorResponseJson(array(
                    'status' => 'error',
                    'message' => 'Error loading to temporary folder!'
                ));
            }   
        }
    }

    /*  
    ---------------------------------------------
        CRUD
    ---------------------------------------------
    */
    public function hook(string $pk = null): void
    {
        if ($this->onAuthHook === true) $this->prepareAuth();
        if ($this->onHook === false) Route::ErrorPage('404');
        if (empty($_POST)) Route::ErrorPage(400);
        $this->csrfTokenChange();

        $this->prepareHook($_POST, $pk);

        if ( $pk ) {
            $this->prepareHookUpdate($_POST, $pk);
            $result = $this->model->update($pk, $_POST);
        } else {
            $this->prepareHookSave($_POST);
            $result = $this->model->save($_POST);
        }
        $this->renderJsonSuccess($result);
    }

    public function delete(string $pk): void
    {
        if ($this->onAuthDelete === true) $this->prepareAuth();
        if ($this->onDelete === false) Route::ErrorPage('404');

        $this->prepareDelete($pk);

        if ($this->model->byId($pk)) {

            $this->model->update($pk, array('is_delete' => true));
            $this->renderJsonSuccess($pk);
            
        } else Route::ErrorPage(404);
    }

    public function restore(string $pk): void
    {
        if ($this->onAuthRestore === true) $this->prepareAuth();
        if ($this->onRestore === false) Route::ErrorPage('404');

        $this->prepareRestore($pk);

        if ($this->model->byId($pk)) {

            $this->model->update($pk, array('is_delete' => false));
            $this->renderJsonSuccess($pk);
            
        } else Route::ErrorPage(404);
    }

    public function remove(string $pk): void
    {
        if ($this->onAuthRemove === true) $this->prepareAuth();
        if ($this->onRemove === false) Route::ErrorPage('404');

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
    final protected function render(string $content, mixed $data = null): void
    {
        if(is_array($data)) extract($data);
        $content = VIEW_FOLDER . "/$content.php";
        include VIEW_FOLDER . "/" . $this->template;
    }

    final protected function view(string $content, mixed $data = null): void
    {
        if(is_array($data)) extract($data);
        include VIEW_FOLDER . "/$content.php";
    }

    final protected function renderJson(array $data): never
    {
        header('Content-type: application/json');
        echo json_encode( $data );
        die;
    }

    final protected function renderJsonSuccess(string $message = null): never
    {
        header('Content-type: application/json');
        echo json_encode( array('status' => 'success', 'message' => $message) );
        die;
    }

    final protected function renderJsonError(string $message): never
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
    public function prepareAuth(): void
    {
        Route::isAuth();
    }

    protected function prepareHook(array $data, string $pk = null) {}
    protected function prepareHookUpdate(array $data, string $pk) {}
    protected function prepareHookSave(array $data) {}
    protected function prepareDelete(string $pk) {}
    protected function prepareRestore(string $pk) {}
    protected function prepareRemove(string $pk) {}
    /*
    ---------------------------------------------
    */
}

?>