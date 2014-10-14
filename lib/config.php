<?php

function load_flourish_classes($class_name)
{

    // Customize this to your root Flourish directory
    $flourish_root = $_SERVER['DOCUMENT_ROOT'] . '/../lib/flourish/';

    $file = $flourish_root . $class_name . '.php';

    if (file_exists($file)) {
        require_once($file);
        return;
    }

}

spl_autoload_register('load_flourish_classes');
