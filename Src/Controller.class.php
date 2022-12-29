<?php

namespace Extra\Src;

use METHOD;

abstract class Controller 
{
    /**
     * 
     * Controller
     * 
     * @version 7.5
     */


    public Repository $repo;
    public string $template = VIEW_TEMPLATE;
    protected array $storage = [];

    protected bool $onHook = false;
    protected bool $onCsrfHook = false;
    protected bool $onAuthHook = false;

    protected bool $onDelete = false;
    protected bool $onAuthDelete = false;
    
    protected bool $onRestore = false;
    protected bool $onAuthRestore = false;
    
    protected bool $onRemove = false;
    protected bool $onAuthRemove = false;

    public array $uploadFileFormat;
    public int $uploadFileSize;

    final function __construct()
    {
        $repoName = str_replace('Controller', 'Repository', get_class($this));
        if (class_exists($repoName)) $this->repo = new $repoName;
    }

    final function __call($name, $arguments)
    {
        Route::ErrorPage(404);
    }

    final protected function method(METHOD ...$allowMethods): void
    {
        foreach ($allowMethods as $method) {
            if($method->name === $_SERVER['REQUEST_METHOD']) return;
        }
        Route::ErrorPage(405);
    }

    final protected function csrfTokenChange(): void
    {
        if($this->onCsrfHook === true) {
            if ((isset($_SESSION['CSRF_TOKEN']) and isset($_POST['csrf_token']) and
                    hash_equals($_SESSION['CSRF_TOKEN'], $_POST['csrf_token']))) {
                unset($_SESSION['CSRF_TOKEN']);
                unset($_POST['csrf_token']);
            } else Route::ErrorPage(419);
        }
    }

    final protected function csrfTokenGen(): string
    {
        $token = bin2hex(random_bytes(24));
        $_SESSION['CSRF_TOKEN'] = $token;
        return $token;
    }

    final protected function csrfTokenInput(): string
    {
        $token = $this->csrfTokenGen();
        return "<input type=\"hidden\" name=\"csrf_token\" value=\"" . $token . "\">";
    }

    final protected function getElement($pk): ModelInterface
    {
        $object = $this->repo->getById($pk);
        if ($object) return $object;
        else Route::ErrorPage(404);
    }

    final protected function uploadFile(array $file): string
    {
        $uploadFolder = str_replace('Controller', '', get_class($this));
        // $uploadDir = PATH_MEDIA . $uploadFolder;
        if( !is_dir(PATH_MEDIA . '/' . $uploadFolder) ) mkdir(PATH_MEDIA . '/' . $uploadFolder);

        if ( $file['name'] ) {
            // Upload File
            if ($file['error'] === UPLOAD_ERR_OK) {
                $fileTmpPath = $file['tmp_name'];
                $fileSize = $file['size'];
                $fileNameCms = explode(".", $file['name']);
                $fileExtension = strtolower(end($fileNameCms));
                $newFileName = sha1(time() . $file['name']) . '.' . $fileExtension;
                // $fileType = $file['type'];

                // File size
                if ($this->uploadFileSize > 0 and $this->uploadFileSize < $fileSize) {
                    Route::ErrorResponseJson(array(
                        'status' => 'error',
                        'message' => 'Error file is too big!'
                    ));
                }
        
                // File format
                if (empty($this->uploadFileFormat) or ($this->uploadFileFormat > 0 and (in_array($fileExtension, $this->uploadFileFormat) or $this->uploadFileFormat == $fileExtension)) ) {

                    if(move_uploaded_file($fileTmpPath, PATH_MEDIA . "/$uploadFolder/$newFileName")) return "$uploadFolder/$newFileName";
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
        if ($this->onHook === false) Route::ErrorPage(404);
        if ($this->onAuthHook === true) $this->prepareAuth();

        $this->method(METHOD::POST);
        if (empty($_POST)) Route::ErrorPage(400);

        if ( $pk ) {
            $object = $this->prepareHookUpdateBefore($_POST, $pk);
            $result = $this->repo->update($pk, $object);
            $this->prepareHookUpdateAfter($object, $result);
        } else {
            $object = $this->prepareHookSaveBefore($_POST);
            $result = $this->repo->save($object);
            $this->prepareHookSaveAfter($object, $result);
        }
    }

    public function delete(string $pk): void
    {
        if ($this->onDelete === false) Route::ErrorPage(404);
        if ($this->onAuthDelete === true) $this->prepareAuth();
        $this->method(METHOD::GET);

        $object = $this->prepareDeleteBefore($pk);
        $result = $this->repo->update($pk, $object);
        $this->prepareDeleteAfter($object, $result);
    }

    public function restore(string $pk): void
    {
        if ($this->onRestore === false) Route::ErrorPage(404);
        if ($this->onAuthRestore === true) $this->prepareAuth();
        $this->method(METHOD::GET);

        $object = $this->prepareRestoreBefore($pk);
        $result = $this->repo->update($pk, $object);
        $this->prepareRestoreAfter($object, $result);
    }

    public function remove(string $pk): void
    {
        if ($this->onRemove === false) Route::ErrorPage(404);
        if ($this->onAuthRemove === true) $this->prepareAuth();
        $this->method(METHOD::GET);

        $this->prepareRemoveBefore($pk);
        $this->repo->delete($pk);
        $this->prepareRemoveAfter($pk);
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
        include VIEW_FOLDER . '/' . $this->template;
    }

    final protected function view(string $content, mixed $data = null): void
    {
        if(is_array($data)) extract($data);
        include VIEW_FOLDER . "/$content.php";
    }

    final protected function renderJson(array $data): never
    {
        $code = 200;
        $status = Route::$httpStatus[$code];
		header("HTTP/1.1 $code " . $status);
		header("Status: $code " . $status);
        header('Content-type: application/json');
        echo json_encode( $data );
        die;
    }

    final protected function renderJsonSuccess(mixed $message = null): never
    {
        $code = 200;
        $status = Route::$httpStatus[$code];
		header("HTTP/1.1 $code " . $status);
		header("Status: $code " . $status);
        header('Content-type: application/json');
        echo json_encode( array('status' => 'success', 'message' => $message) );
        die;
    }

    final protected function renderJsonError(mixed $message): never
    {
        $code = 200;
        $status = Route::$httpStatus[$code];
		header("HTTP/1.1 $code " . $status);
		header("Status: $code " . $status);
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
    protected function prepareAuth(): void
    {
        Route::isAuth();
    }

    protected function prepareHookSaveBefore(array $post): ModelInterface
    {
        $this->csrfTokenChange();
        if(isset($post['csrf_token'])) unset($post['csrf_token']);
        return new $this->repo->modelName($post);
    }
    protected function prepareHookSaveAfter(ModelInterface $model, string $result): void
    {
        $this->renderJsonSuccess($result);
    }

    protected function prepareHookUpdateBefore(array $post, string $pk): ModelInterface
    {
        $this->csrfTokenChange();
        if(isset($post['csrf_token'])) unset($post['csrf_token']);
        $object = $this->getElement($pk);
        $object->reConstruct($post);
        return $object;
    }
    protected function prepareHookUpdateAfter(ModelInterface $model, string $result): void
    {
        $this->renderJsonSuccess($result);
    }

    protected function prepareDeleteBefore(string $pk): ModelInterface
    {
        $object = $this->getElement($pk);
        $object->reConstruct(array('is_delete' => 1));
        return $object;
    }
    protected function prepareDeleteAfter(ModelInterface $model, string $result): void
    {
        $this->renderJsonSuccess($result);
    }

    protected function prepareRestoreBefore(string $pk): ModelInterface
    {
        $object = $this->getElement($pk);
        $object->reConstruct(array('is_delete' => 0));
        return $object;
    }
    protected function prepareRestoreAfter(ModelInterface $model, string $result): void
    {
        $this->renderJsonSuccess($result);
    }

    protected function prepareRemoveBefore(string $pk): void
    {
        $this->getElement($pk);
    }
    protected function prepareRemoveAfter(string $pk): void
    {
        $this->renderJsonSuccess($pk);
    }
    /*
    ---------------------------------------------
    */
}
