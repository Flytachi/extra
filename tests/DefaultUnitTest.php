<?php

use Flytachi\Kernel\Extra;
use PHPUnit\Framework\TestCase;

class DefaultUnitTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        Extra::init();
    }

    public function testDirectories()
    {
        $this->assertTrue(is_dir(Extra::$pathRoot));
        $this->assertTrue(is_dir(Extra::$pathMain));
        $this->assertTrue(is_dir(Extra::$pathPublic));
        $this->assertTrue(is_dir(Extra::$pathStorage));
        $this->assertTrue(is_dir(Extra::$pathStorageCache));
        $this->assertTrue(is_dir(Extra::$pathStorageLog));
    }

    public function testMapping()
    {
        \Flytachi\Kernel\Src\Http\Router::generateMappingRoutes();
        $this->assertTrue(is_file(Extra::$pathFileMapping));
    }
}
