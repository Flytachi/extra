<?php

namespace Extra\Src;

use METHOD;
use Random\Randomizer;
use ReflectionClass;
use ReflectionProperty;
use Warframe;

/**
 *  Warframe collection
 *
 *  Controller - controller for web requests
 *
 *  ! The default repository must be specified in the class
 *  * Example: public 'Repository' $repo;
 *
 *  @version 9.0
 *  @author itachi
 *  @package Extra\Src
 */
abstract class Controller
{
    /** @var string $template the path to the template */
    public string $template = VIEW_TEMPLATE;
    /** @var array $storage betta test */
    protected array $storage = [];

    /** @var bool $onHook default hook status */
    protected bool $onHook = false;
    /** @var bool $onCsrfHook default hook CSRF secure status */
    protected bool $onCsrfHook = false;
    /** @var bool $onAuthHook default hook authentication status */
    protected bool $onAuthHook = false;

    /** @var bool $onHook default delete status */
    protected bool $onDelete = false;
    /** @var bool $onHook default delete authentication status */
    protected bool $onAuthDelete = false;

    /** @var bool $onHook default restore status */
    protected bool $onRestore = false;
    /** @var bool $onHook default restore authentication status */
    protected bool $onAuthRestore = false;

    /** @var bool $onHook default remove status */
    protected bool $onRemove = false;
    /** @var bool $onHook default remove authentication status */
    protected bool $onAuthRemove = false;

    /** @var array $uploadFileFormat upload file format */
    public array $uploadFileFormat;
    /** @var int $uploadFileFormat upload file size (byte) */
    public int $uploadFileSize;

    /**
     * Constructor
     *
     * Initializes the specified Repositories
     *
     * @return void
     */
    final function __construct()
    {
        $reflect = new ReflectionClass($this);
        foreach ($reflect->getProperties(ReflectionProperty::IS_PUBLIC) as $property) {
            if (strrpos($property->getType(), 'Repository'))
                $this->{$property->getName()} = new ((string) $property->getType());
        }
    }

    /**
     * Allow method
     *
     * @param METHOD ...$allowMethods allowed methods
     *
     * @return void
     */
    final protected function method(METHOD ...$allowMethods): void
    {
        foreach ($allowMethods as $method) {
            if($method->name === $_SERVER['REQUEST_METHOD']) return;
        }
        Route::Throwable(405, 'Method ' . $_SERVER['REQUEST_METHOD'] . ' not allowed!');
    }

    /**
     * CSRF Token Change
     *
     * Checks csrf token for validity
     *
     * @return void
     */
    final protected function csrfTokenChange(): void
    {
        if($this->onCsrfHook === true) {
            if ((isset($_SESSION['CSRF_TOKEN']) and isset($_POST['csrf_token']) and
                hash_equals($_SESSION['CSRF_TOKEN'], $_POST['csrf_token']))) {
                unset($_SESSION['CSRF_TOKEN']);
                unset($_POST['csrf_token']);
            } else Route::Throwable(419, 'CSRF Token authentication error occurred');
        }
    }

    /**
     * CSRF Token Generation
     *
     * Generate csrf token (24 chars)
     *
     * @return string
     *
     */
    final protected function csrfTokenGen(): string
    {
        $random = new RandomGenerator();
        $token = bin2hex($random->generate(20));
        $_SESSION['CSRF_TOKEN'] = $token;
        return $token;
    }

    /**
     * CSRF Token Input
     *
     * Outputs the qsq input tag with the generated token
     *
     * @return string
     */
    final protected function csrfTokenInput(): string
    {
        $token = $this->csrfTokenGen();
        return "<input type=\"hidden\" name=\"csrf_token\" value=\"" . $token . "\">";
    }

    /**
     * Get Model Object
     *
     * Searches for an element in the Model,
     * if it does not, it gives a http 500 error
     *
     * @param array $data model data
     *
     * @return ModelInterface
     */
    final protected function modelObject(array $data = []): ModelInterface
    {
        $model = str_replace('Controller', 'Model', $this::class);
        if (class_exists($model)) return new $model($data);
        else Route::Throwable('500', 'Model '. $model . ' not found!');
    }

    /**
     * Get Element (from Repository)
     *
     * Searches for an element in the Repository,
     * if it does not, it gives a http 404 error
     *
     * @param int $pk id
     *
     * @return ModelInterface
     */
    final protected function getElement(int $pk): ModelInterface
    {
        $object = $this->repo->getById($pk);
        if ($object) return $object;
        else Route::Throwable(404, 'Object not found');
    }

    /**
     * Upload File
     *
     * Saves the file in the folder PATH_MEDIA/'the name of the Controller'.
     *
     * @param array $file variable from from array $_FILES[?]
     *
     * @return string the path to the saved file
     */
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
                if ($this->uploadFileSize > 0 and $this->uploadFileSize < $fileSize)
                    Route::Throwable(507, 'UploadFile: Error file is too big.');

                // File format
                if (empty($this->uploadFileFormat) or ($this->uploadFileFormat > 0 and (in_array($fileExtension, $this->uploadFileFormat) or $this->uploadFileFormat == $fileExtension)) ) {

                    if(move_uploaded_file($fileTmpPath, PATH_MEDIA . "/$uploadFolder/$newFileName")) return "$uploadFolder/$newFileName";
                    else Route::Throwable(507, 'UploadFile: Error writing to storage.');

                } else Route::Throwable(507, 'UploadFile: Error unsupported file format.');

            } else Route::Throwable(507, 'UploadFile: Error loading to temporary folder.');
        } else Route::Throwable(507, 'UploadFile: Error file not name.');
    }

    /**
     * Default Method Hook
     *
     * The method is used to update or create a record in the database
     *
     * The method checks the POST data for CSRF validity
     * and creates a Model from the POST data
     *
     * @param ?int $pk id
     *
     * * if there is a pk, it updates, otherwise it creates a record
     *
     * @return void
     */
    public function hook(?int $pk = null): void
    {
        if ($this->onHook === false) Route::Throwable(404, 'Hook locked');
        $this->method(METHOD::POST);
        if ($this->onAuthHook === true) $this->prepareAuth();
        if (empty($_POST)) Route::Throwable(400, 'Empty post request');

        $this->csrfTokenChange();
        if(array_key_exists('csrf_token', $_POST)) unset($_POST['csrf_token']);
        $this->cleaner($_POST);

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

    /**
     * Default Method Delete
     *
     * activates the "is_deleted" status
     *
     * @param int $pk id
     *
     * @return void
     */
    public function delete(int $pk): void
    {
        if ($this->onDelete === false) Route::Throwable(404, 'Delete locked');
        $this->method(METHOD::GET);
        if ($this->onAuthDelete === true) $this->prepareAuth();

        $object = $this->prepareDeleteBefore($pk);
        $result = $this->repo->update($pk, $object);
        $this->prepareDeleteAfter($object, $result);
    }

    /**
     * Default Method Restore
     *
     * deactivates the "is_deleted" status
     *
     * @param int $pk id
     *
     * @return void
     */
    public function restore(int $pk): void
    {
        if ($this->onRestore === false) Route::Throwable(404, 'Restore locked');
        $this->method(METHOD::GET);
        if ($this->onAuthRestore === true) $this->prepareAuth();

        $object = $this->prepareRestoreBefore($pk);
        $result = $this->repo->update($pk, $object);
        $this->prepareRestoreAfter($object, $result);
    }

    /**
     * Default Method Remove
     *
     * Deletes an entry
     *
     * @param int $pk id
     *
     * @return void
     */
    public function remove(int $pk): void
    {
        if ($this->onRemove === false) Route::Throwable(404, 'Remove locked');
        $this->method(METHOD::GET);
        if ($this->onAuthRemove === true) $this->prepareAuth();

        $this->prepareRemoveBefore($pk);
        $this->repo->delete($pk);
        $this->prepareRemoveAfter($pk);
    }

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
        if (Warframe::$cfg['GLOBAL_SETTING']['DEBUG']) $this->debugBar($content, $data);
    }

    private function debugBar(string $content, mixed $data = null): void
    {
        ?>
        <link rel="stylesheet" type="text/css" href="/static/warframe/css/debug.css"/>
        <script type="text/javascript" src="/static/warframe/js/debug.js"></script>
        <button id="warframe_debug-btn" onclick="WarframeDebugBar()"><em>Debug</em></button>

        <div id="warframe_debug-bar">
            <div id="warframe_debug-bar_body-indicator">
                <?php $delta = round(microtime(true)-$_SERVER['REQUEST_TIME'], 3) ?>
                <b>Memory:</b> <?= bytes(memory_get_usage(), 'MiB')  ?> /
                <b>Time:</b> <?= ($delta < 0.001) ? 0.001 : $delta; ?> sec
            </div>

            <div id="warframe_debug-bar_body-accordion-container">

                <input type="checkbox" id="debug-item_general">
                <label for="debug-item_general">GENERAL</label>
                <div class="warframe_debug-accordion-body">
                    <pre><?php print_r([
                            'sapi' => PHP_SAPI,
                            'controller' => get_class($this),
                            'mainTemplate' => VIEW_FOLDER . '/' . $this->template,
                            'template' => $content,
                            'data' => $data
                        ]) ?></pre>
                </div>

                <input type="checkbox" id="debug-item_server">
                <label for="debug-item_server">SERVER</label>
                <div class="warframe_debug-accordion-body">
                    <pre><?php print_r($_SERVER) ?></pre>
                </div>

                <?php if($_SESSION): ?>
                    <input type="checkbox" id="debug-item_session">
                    <label for="debug-item_session">SESSION</label>
                    <div class="warframe_debug-accordion-body">
                        <pre><?php print_r($_SESSION) ?></pre>
                    </div>
                <?php endif; ?>

                <?php if($data): ?>
                    <input type="checkbox" id="debug-item_data">
                    <label for="debug-item_data">DATA</label>
                    <div class="warframe_debug-accordion-body">
                        <pre><?php print_r($data) ?></pre>
                    </div>
                <?php endif; ?>

                <?php if($_REQUEST): ?>
                    <input type="checkbox" id="debug-item_request">
                    <label for="debug-item_request">REQUEST</label>
                    <div class="warframe_debug-accordion-body">
                        <pre><?php print_r($_REQUEST) ?></pre>
                    </div>
                <?php endif; ?>

                <?php if($_FILES): ?>
                    <input type="checkbox" id="debug-item_files">
                    <label for="debug-item_files">FILES</label>
                    <div class="warframe_debug-accordion-body">
                        <pre><?php print_r($_FILES) ?></pre>
                    </div>
                <?php endif; ?>

                <?php if($_COOKIE): ?>
                    <input type="checkbox" id="debug-item_cookie">
                    <label for="debug-item_cookie">COOKIE</label>
                    <div class="warframe_debug-accordion-body">
                        <pre><?php print_r($_COOKIE) ?></pre>
                    </div>
                <?php endif; ?>

                <?php if($_ENV): ?>
                    <input type="checkbox" id="debug-item_env">
                    <label for="debug-item_env">ENV</label>
                    <div class="warframe_debug-accordion-body">
                        <pre><?php print_r($_ENV) ?></pre>
                    </div>
                <?php endif; ?>

                <?php if($GLOBALS): ?>
                    <input type="checkbox" id="debug-item_globals">
                    <label for="debug-item_globals">GLOBALS</label>
                    <div class="warframe_debug-accordion-body">
                        <pre><?php print_r($GLOBALS) ?></pre>
                    </div>
                <?php endif; ?>

            </div>

        </div>
        <?php
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

    /**
     * Prepare authentication
     *
     * Authorization method
     *
     * @return void
     */
    protected function prepareAuth(): void
    {
        Route::isAuth();
    }

    /**
     * Prepare Hook Save Before
     *
     * The method is applied before creating a record in the database
     *
     * @param array $post $_POST request
     *
     * @return ModelInterface
     */
    protected function prepareHookSaveBefore(array $post): ModelInterface
    {
        return $this->modelObject($post);
    }

    /**
     * Prepare Hook Save After
     *
     * The method is applied after creating a record in the database
     *
     * The standard method returns the id of the created record in JSON format
     *
     * @param ModelInterface $model data model
     * @param string $result result message
     *
     * @return void
     */
    protected function prepareHookSaveAfter(ModelInterface $model, string $result): void
    {
        $this->renderJsonSuccess($result);
    }

    /**
     * Prepare Hook Update Before
     *
     * The method is applied before updating a record in the database
     *
     * The standard method also checks the element for existence,
     * then overwrites the Model data on the POST data
     *
     * @param array $post $_POST request
     * @param int $pk id
     *
     * @return ModelInterface
     */
    protected function prepareHookUpdateBefore(array $post, int $pk): ModelInterface
    {
        $object = $this->getElement($pk);
        $object->reConstruct($post);
        return $object;
    }

    /**
     * Prepare Hook Update After
     *
     * The method is applied after updating a record in the database
     *
     * The standard method returns the id of the created record in JSON format
     *
     * @param ModelInterface $model data model
     * @param string $result result message
     *
     * @return void
     */
    protected function prepareHookUpdateAfter(ModelInterface $model, string $result): void
    {
        $this->renderJsonSuccess($result);
    }

    /**
     * Prepare Delete Before
     *
     * This method is applied before updating the 'is_delete' status in the database
     *
     * The standard method checks the element for presence,
     * then overwrites the 'is_delete' status to the POST data
     *
     * @param int $pk id
     *
     * @return ModelInterface
     */
    protected function prepareDeleteBefore(int $pk): ModelInterface
    {
        $object = $this->getElement($pk);
        $object->reConstruct(['is_delete' => 1]);
        return $object;
    }

    /**
     * Prepare Delete After
     *
     * This method is applied after updating the 'is_delete' status in the database
     *
     * The standard method returns the id of the created record in JSON format
     *
     * @param ModelInterface $model data model
     * @param string $result result message
     *
     * @return void
     */
    protected function prepareDeleteAfter(ModelInterface $model, string $result): void
    {
        $this->renderJsonSuccess($result);
    }

    /**
     * Prepare Restore Before
     *
     * This method is applied before updating the 'is_delete' status in the database
     *
     * The standard method checks the element for presence,
     * then overwrites the 'is_delete' status to the POST data
     *
     * @param int $pk id
     *
     * @return ModelInterface
     */
    protected function prepareRestoreBefore(int $pk): ModelInterface
    {
        $object = $this->getElement($pk);
        $object->reConstruct(array('is_delete' => 0));
        return $object;
    }

    /**
     * Prepare Restore After
     *
     * This method is applied after updating the 'is_delete' status in the database
     *
     * The standard method returns the id of the created record in JSON format
     *
     * @param ModelInterface $model data model
     * @param string $result result message
     *
     * @return void
     */
    protected function prepareRestoreAfter(ModelInterface $model, string $result): void
    {
        $this->renderJsonSuccess($result);
    }

    /**
     * Prepare Remove Before
     *
     * This method is applied before deleting a record in the database
     *
     * The standard method checks the element for the presence of
     *
     * @param int $pk id
     *
     * @return void
     */
    protected function prepareRemoveBefore(int $pk): void
    {
        $this->getElement($pk);
    }

    /**
     * Prepare Remove After
     *
     * This method is applied after deleting a record in the database
     *
     * The standard method returns the id of the created record in JSON format
     *
     * @param int $pk id
     *
     * @return void
     */
    protected function prepareRemoveAfter(int $pk): void
    {
        $this->renderJsonSuccess($pk);
    }


    /**
     * Cleaner Method By Data
     *
     * The method cleans all data in the array from html tags and special characters
     *
     * @param array &$data array data
     *
     * @return void
     */
    protected final function cleaner(array &$data): void
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) $this->cleaner($value);
            else $value = CDO::clean($value);
            $data[$key] = $value;
        }
    }

    /**
     * Validate Method
     *
     * Checking the existence of a value in the data.
     *
     * If you set the argument "validateFunc" will check the
     * data on the function with the condition that the
     * function returns a bool value, and takes 1 argument
     *
     * @param array $data data -> array data
     * @param string $field field name -> array key
     * @param callable|null $validateFunc validation func returned bool!
     * @param string|null $message message with incorrect validation in func
     *
     * @return void
     */
    protected final function valid(array $data, string $field, callable $validateFunc = null, string $message = null): void
    {
        if (!array_key_exists($field, $data))
            Route::Throwable(400, "Field \"{$field}\" not found!");
        if ($validateFunc !== null) {
            if (!$validateFunc($data[$field]))
                Route::Throwable(400, $message ?? "The \"{$field}\" field has the wrong data type!");
        }
    }

}
