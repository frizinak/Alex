<?php

if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
    session_start();
    require_once('../Config.class.php');
    require_once('../' . Config::$coreDir . '/classes/Utils.php');
    require_once('../' . Config::$coreDir . '/classes/Login.php');

    $logged = Login::is_logged(); //isset($_SESSION['logged']) && $_SESSION['logged'] === true && isset($_SESSION['age']) && microtime(true) - $_SESSION['age'] < Config::$sessionExpiry && isset($_SESSION['ua']) && $_SESSION['ua'] === sha1($_SERVER['HTTP_USER_AGENT'] . Config::$salt);

    if ($logged) {

        function stripslashes_deep($value)
        {
            $value = is_array($value) ? array_map("stripslashes_deep", $value) : stripslashes($value);
            return $value;
        }

        if (get_magic_quotes_gpc()) {
            $_POST = array_map("stripslashes_deep", $_POST);
            $_GET = array_map("stripslashes_deep", $_GET);
            //$_COOKIE = array_map("stripslashes_deep", $_COOKIE);
            //$_REQUEST = array_map("stripslashes_deep", $_REQUEST);
        }

        $_SESSION['age'] = microtime(true);
        $content = 'error';
        if (isset($_POST['getpage'])) {
            $content = @file_get_contents('../' . Config::$dataDir . '/pages/' . $_POST['getpage'] . '.json');
            $content = $content !== false ? $content : 'error';
        }

        if (isset($_POST['getpagetree'])) {
            $content = @file_get_contents('../' . Config::$dataDir . '/data/pages.json');
            $content = $content !== false ? $content : 'error';
        }

        if (isset($_POST['setpage']) && isset($_POST['name'])) {
            $newContent = @json_encode($_POST['setpage']);
            $written = false;
            if ($newContent !== false) {
                $written = @file_put_contents('../' . Config::$dataDir . '/pages/' . $_POST['name'] . '.json', $newContent);
            }
            if ($written !== false) {
                $content = 'saved';
            }
        }

        if (isset($_POST['setpagetree'])) {

            $newContent = @json_encode($_POST['setpagetree']);
            $written = false;
            if ($newContent !== false) {
                $written = @file_put_contents('../' . Config::$dataDir . '/data/pages.json', $newContent);
            }
            if ($written !== false) {
                $content = 'saved';
            }
        }

        if (isset($_POST['makepage']) && isset($_POST['tpl'])) {
            $content = 'error';

            $fn = Utils::safe_filename($_POST['makepage'], 'json', '../' . Config::$dataDir . '/pages');
            $data = @file_get_contents('../' . Config::$dataDir . '/custom/templates/' . $_POST['tpl'] . '.json');
            $object = json_decode($data);
            foreach ($object->tplData as &$tplVar) {
                $tplVar = $tplVar->default;
            }
            $data = json_encode($object);
            $file = @Utils::fopen_recursive('../' . Config::$dataDir . '/pages/' . $fn, 'w', Config::$newDirMask, Config::$newFileMask);
            if ($file !== false && $data !== false) {
                fwrite($file, $data);
                fclose($file);
                $content = explode('.', $fn);
                $content = $content[0];
            }

        }
        if (isset($_POST['delpage'])) {
            $content = 'error';
            $deleted = @unlink('../' . Config::$dataDir . '/pages/' . $_POST['delpage'] . '.json');
            if ($deleted !== false) {
                $content = 'success';
            }

        }

        if (isset($_POST['gettpl'])) {
            $content = @file_get_contents('../' . Config::$dataDir . '/custom/templates/' . $_POST['gettpl'] . '.json');
            $content = $content !== false ? $content : 'error';
        }


        if (isset($_POST['getalltpl'])) {
            $content = @glob('../' . Config::$dataDir . '/custom/templates/' . "*.json", GLOB_BRACE);
            if ($content !== false) {
                foreach ($content as $k => $v) {
                    $content[$k] = basename($v, '.json');
                }
            }
            $content = $content !== false ? json_encode($content) : 'error';
        }

        if (isset($_POST['gettplbyparent'])) {
            if (isset(Config::$templateRelations) && count(Config::$templateRelations) > 0) {


                $parentTpl = false;
                if ($_POST['gettplbyparent'] === "_root") {
                    $parentTpl = "_root";
                } else {
                    $parent = @file_get_contents('../' . Config::$dataDir . '/pages/' . $_POST['gettplbyparent'] . '.json');
                    if ($parent !== false) {
                        $parent = json_decode($parent, true);
                        $parentTpl = $parent['template'];
                    }

                }
                if ($parentTpl !== false) {
                    if (isset(Config::$templateRelations[$parentTpl]) && count(Config::$templateRelations[$parentTpl]) > 0) {
                        $content = json_encode(Config::$templateRelations[$parentTpl]);
                    } else {
                        $content = json_encode(array());
                    }
                } else {
                    $content = false;
                }

                $content = $content !== false ? $content : 'error';
            } else {
                $content = @glob('../' . Config::$dataDir . '/custom/templates/' . "*.json", GLOB_BRACE);
                if ($content !== false) {
                    foreach ($content as $k => $v) {
                        $content[$k] = basename($v, '.json');
                    }
                }
                $content = $content !== false ? json_encode($content) : 'error';
            }
        }

        if (isset($_POST['clearcache'])) {
            if (extension_loaded('apc')) {
                $del = new APCIterator('user', '/^cmx_cache_/', APC_ITER_VALUE);
                apc_delete($del);
            }
            Utils::empty_dir('../' . Config::$cacheDir . '/');
            $content = 'success';
        }

        if (isset($_POST['pagetreeopened'])) {
            $_SESSION['treeOpened'] = $_POST['pagetreeopened'];
        }

        if (isset($_POST['deletefile'])) {
            $file = str_replace('..', '', $_POST['deletefile']);
            $delete = @unlink('../' . Config::$uploadDir . '/' . $file);
            $content = $delete ? 'success' : 'error';
        }

        if (isset($_POST['deletedir'])) {
            $dir = str_replace('..', '', $_POST['deletedir']);
            $delete = @rmdir('../' . Config::$uploadDir . '/' . $dir);
            $content = $delete ? 'success' : 'error';
        }

        die($content);
    } else {
        die('log');
    }
}

