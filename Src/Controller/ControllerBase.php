<?php

namespace Extra\Src\Controller;

use Extra\Src\Enum\HttpCode;
use Extra\Src\Enum\Method;
use Extra\Src\Error\ExtraException;
use Extra\Src\Log\Log;
use Extra\Src\Route\Route;
use ReflectionClass;

/**
 *  Warframe collection
 *
 *  ControllerBase - controller for web requests
 *
 *  ! The default repository must be specified in the class
 *  * Example: public 'Repository' $repo;
 *
 *  @version 12.0
 *  @author itachi
 *  @package Extra\Src
 */
abstract class ControllerBase
{
    /** @var string $template the path to the template */
    public string $template = 'template.php';

    /**
     * Constructor
     *
     * Initializes the specified Services
     *
     * @return void
     */
    final function __construct()
    {
        // session start
        session_start();

        $reflect = new ReflectionClass($this);
        foreach ($reflect->getProperties() as $property) {
            if (strrpos($property->getType(), 'Service'))
                $this->{$property->getName()} = new ($property->getType()->getName());
        }
    }

    /**
     * Allow method
     *
     * @param Method ...$allowMethods allowed methods
     *
     * @return void
     */
    final protected function method(Method ...$allowMethods): void
    {
        Log::trace('Controller method: change method');
        foreach ($allowMethods as $method) {
            if($method->name === $_SERVER['REQUEST_METHOD']) return;
        }
        ControllerError::throw(HttpCode::METHOD_NOT_ALLOWED, 'Method ' . $_SERVER['REQUEST_METHOD'] . ' not allowed!');
    }

    /*
    ---------------------------------------------
        RESPONSE
    ---------------------------------------------
    */
    final protected function render(string $content, mixed $data = null): void
    {
        Log::trace('Controller render: ' . $content);
        if(is_array($data)) extract($data);
        $content = PATH_RESOURCE . "/$content.php";
        include PATH_RESOURCE . '/' . $this->template;
        if (env('DEBUG', false)) $this->debugBar($content, $data);
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
                            'mainTemplate' => PATH_RESOURCE . '/' . $this->template,
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
        Log::trace('Controller view: ' . $content);
        if(is_array($data)) extract($data);
        include PATH_RESOURCE . "/$content.php";
    }

    /*
    ---------------------------------------------
    */

    /**
     * Prepare authentication
     *
     * Authorization method
     *
     * @param bool $redirect
     *
     * @return void
     */
    protected function prepareAuth(bool $redirect = false): void
    {
        if ($redirect) {
            if (empty($_SESSION['id'])) Route::redirect('auth/login');
        } else {
            if (empty($_SESSION['id'])) ControllerError::throw(HttpCode::LOCKED, 'You are not authorized');
        }
    }

}
