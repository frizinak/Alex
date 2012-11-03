<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">

    <title>admin</title>
    <link type="text/css" rel="stylesheet" href="css/style.css">
    <link rel="shortcut icon" type="image/x-icon" href="favicon.ico" />
    <script type="text/javascript" src="js/utils/jqy.js"></script>
    <?php if (Admin::$logged): ?>
    <script type="text/javascript" src="js/languages/<?php echo Admin::$lang; ?>.js"></script>

    <script type="text/javascript" src="js/utils/jsn.js"></script>
    <script type="text/javascript" src="js/cmx.js"></script>
    <script type="text/javascript" src="js/app.js"></script>
    <?php if (Admin::$page === 'edit'): ?>
        <script type="text/javascript" src="js/utils/jqyui.js"></script>
        <?php
        $plugins = glob('../' . Config::$dataDir . '/custom/admin-plugins/*.js');
        foreach ($plugins as $plugin) {
            echo '<script type="text/javascript" src="'.$plugin.'"></script>';
        }
        ?>
        <script type="text/javascript" src="js/utils/tiny_mce/tiny_mce.js"></script>
        <script type="text/javascript" src="js/editor.js"></script>
        <?php elseif (Admin::$page === 'new'): ?>
        <script type="text/javascript" src="js/new.js"></script>
        <?php elseif (Admin::$page === 'upload'): ?>
        <script type="text/javascript" src="js/upload.js"></script>
        <?php endif; ?>
    <?php else: ?>
    <script type="text/javascript" src="js/utils/sha.js"></script>
    <script type="text/javascript" src="js/login.js"></script>
    <?php endif; ?>
</head>
<body>
<div id="nojs">
    <p>
        Javascript is required to use the admin panel.<br/>
        Please upgrade your browser and/or <a href="http://www.activatejavascript.org/" target="_blank" title="learn how to enable js">enable javascript</a>.
    </p>
</div>
