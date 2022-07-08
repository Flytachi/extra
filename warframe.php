<?php

require dirname(__DIR__) . '/defines.php';

// ! Import function
foreach (glob(dirname(__FILE__)."/Function/*") as $function) require $function;
// ! END Import

// ! Import src
foreach (glob(dirname(__FILE__)."/Src/*") as $plugin) require $plugin . '/__load__.php';
// ! END Import

?>