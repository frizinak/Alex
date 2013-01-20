<?php
session_start();
require_once('AdminConfig.class.php');
require_once(AdminConfig::$frontendDir . '/Config.class.php');
require_once(AdminConfig::$frontendDir . '/' . Config::$coreDir . '/classes/Utils.php');
require_once(AdminConfig::$frontendDir . '/' . Config::$coreDir . '/classes/Login.php');

if (!Login::is_logged()) {
    die();
}
$notTinyMce = isset($_GET['non-mce']);
$allowedExts = array('bmp', 'jpg', 'jpeg', 'png', 'bmp', 'gif');
function get_images_in_dir($dir) {
    global $allowedExts, $notTinyMce;

    if ($notTinyMce && $dir === AdminConfig::$frontendDir . '/' . Config::$imageDir) {
        return '';
    }
    $ret = '';
    $list = glob($dir . '/*');
    foreach ($list as $file) {
        if (is_dir($file)) {
            $ret .= get_images_in_dir($file);
        } else {
            $fileExt = explode('.', $file);
            if (in_array(strtolower($fileExt[count($fileExt) - 1]), $allowedExts)) {
                $ret .= '["' . utf8_encode(str_replace(AdminConfig::$frontendDir . '/', '', $file)) . '","' . utf8_encode($file) . '"],';
            }
        }
    }
    return $ret;
}

header('Content-type: ' . ($notTinyMce ? 'application/json' : 'text/javascript'));
header('pragma: no-cache');
header('expires: 0');
$o = $notTinyMce ? '[' : 'var tinyMCEImageList = new Array(';
$o .= get_images_in_dir(AdminConfig::$frontendDir . '/' . Config::$uploadDir);
$o = rtrim($o, ',');
$o .= $notTinyMce ? ']' : ')';
die($o);


