<?php
session_start();
require_once('../Config.class.php');
require_once('../' . Config::$coreDir . '/classes/Utils.php');
require_once('../' . Config::$coreDir . '/classes/Login.php');

if (!Login::is_logged()) {
    die();
}
$allowedExts = array('bmp', 'jpg', 'jpeg', 'png', 'bmp');
function get_images_in_dir($dir)
{
    global $allowedExts;
    $ret = '';
    $list = glob($dir . '/*');
    foreach ($list as $file) {
        if (is_dir($file)) {
            $ret .= get_images_in_dir($file);
        } else {
            $fileExt = explode('.', $file);
            if (in_array(strtolower($fileExt[count($fileExt) - 1]), $allowedExts)) {
                $ret .= '["' . basename($file) . '", "' . $file . '"],';
            }
        }
    }
    return $ret;
}

header('Content-type: text/javascript');
header('pragma: no-cache');
header('expires: 0');
$o = 'var tinyMCEImageList = new Array(';
$o .= get_images_in_dir('../' . Config::$uploadDir);
$o = rtrim($o, ',');
$o .= ')';
die($o);


