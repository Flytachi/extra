<?php

declare(strict_types=1);

namespace Flytachi\Extra\Src\Factory\Http\Response;

use Flytachi\Extra\Extra;
use Flytachi\Extra\Src\Factory\Http\HttpCode;

abstract class ViewBase implements ViewInterface
{
    protected string $resourceName;
    protected mixed $data;
    protected HttpCode $httpCode;

    public function __construct(string $resourceName, mixed $data, mixed $httpCode = HttpCode::OK)
    {
        $this->resourceName = $resourceName;
        if (!file_exists($this->getResource())) {
            throw new \Exception($this->getResource() . ' not found');
        }
        $this->data = $data;
        $this->httpCode = $httpCode;
    }

    final public function getHttpCode(): HttpCode
    {
        return $this->httpCode;
    }

    public function getHeader(): array
    {
        return ['Content-Type' => 'text/html; charset=utf-8'];
    }

    public function getResource(): string
    {
        return Extra::$pathResource . '/' . $this->resourceName . '.php';
    }

    public function getData(): mixed
    {
        return $this->data;
    }

    public function getHandle(): ?string
    {
        return $this->debugger();
    }

    final protected function debugger(): ?string
    {
        if (env('DEBUG', false)) {
            ob_start();
            $delta = round(microtime(true) - EXTRA_STARTUP_TIME, 3);
            ?>
            <link rel="stylesheet" type="text/css" href="/static/extra/css/debug.css"/>
            <script type="text/javascript" src="/static/extra/js/debug.js"></script>
            <button id="extra_debug-btn" onclick="ExtraDebugBar()"><em>Debug</em></button>

            <div id="extra_debug-bar">
                <div id="extra_debug-bar_body-indicator">
                    <b>Memory:</b> <?= bytes(memory_get_usage(), 'MiB')  ?> /
                    <b>Time:</b> <?= ($delta < 0.001) ? 0.001 : $delta; ?> sec
                </div>

                <div id="extra_debug-bar_body-accordion-container">

                    <input type="checkbox" id="debug-item_general">
                    <label for="debug-item_general">GENERAL</label>
                    <div class="extra_debug-accordion-body">
                        <pre><?php print_r([
                            'sapi' => PHP_SAPI,
                            'timezone' => env('TIME_ZONE', 'UTC'),
                            'date' => date(DATE_ATOM),
                            'template' => str_replace(Extra::$pathRoot, '', $this->getResource()),
                            'data' => $this->getData()
                        ]) ?></pre>
                    </div>

                    <input type="checkbox" id="debug-item_mapping">
                    <label for="debug-item_mapping">MAPPING</label>
                    <div class="extra_debug-accordion-body">
                        <?php
                        try {
                            $declaration = \Flytachi\Extra\Src\Factory\Mapping\Mapping::scanningDeclaration();
                            foreach ($declaration->getChildren() as $item) {
                                if ($item->getMethod() == 'GET' || $item->getMethod() == '') {
                                    $classMethod = $item->getClassName() . '->' . $item->getClassMethod();
                                    echo sprintf(
                                        "<div>"
                                            . "<a href=\"/%s\" style='font-size: 1rem; color: cyan; "
                                            . "text-decoration-color: cyan' target=\"_blank\">/%s</a> - "
                                            .  " <em>(%s)<em>"
                                            .  "</div>",
                                        $item->getUrl(),
                                        $item->getUrl(),
                                        $classMethod
                                    ) , "</br>";
                                }
                            }
                        } catch (\Throwable $e) {
                            echo $e->getMessage();
                        }
                        ?>
                    </div>

                    <hr>

                    <?php foreach ($GLOBALS as $name => $INFO) : ?>
                        <?php if (!empty($INFO)) : ?>
                            <?php $name = ltrim($name, '_'); ?>
                            <input type="checkbox" id="debug-item_<?= $name ?>">
                            <label for="debug-item_<?= $name ?>"><?= $name ?></label>
                            <div class="extra_debug-accordion-body">
                                <pre><?php print_r($INFO) ?></pre>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>

                </div>

            </div>
            <?php
            return ob_get_clean();
        } else {
            return null;
        }
    }
}
