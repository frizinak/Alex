<?php

if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
  session_start();
  require_once('AdminConfig.class.php');
  require_once(AdminConfig::$frontendDir . '/Config.class.php');
  require_once(AdminConfig::$frontendDir . '/' . Config::$coreDir . '/classes/Utils.php');
  require_once(AdminConfig::$frontendDir . '/' . Config::$coreDir . '/classes/Login.php');

  $logged = Login::is_logged();
  if ($logged) {

    function stripslashes_deep($value) {
      $value = is_array($value) ? array_map("stripslashes_deep", $value) : stripslashes($value);

      return $value;
    }

    if (get_magic_quotes_gpc()) {
      $_POST = array_map("stripslashes_deep", $_POST);
      $_GET = array_map("stripslashes_deep", $_GET);
      //$_COOKIE = array_map("stripslashes_deep", $_COOKIE);
      //$_REQUEST = array_map("stripslashes_deep", $_REQUEST);
    }

    $_SESSION['age'] = microtime(TRUE);
    $content = 'error';
    if (isset($_POST['getpage'])) {
      $content = @file_get_contents(AdminConfig::$frontendDir . '/' . Config::$dataDir . '/pages/' . $_POST['getpage'] . '.json');
      $content = $content !== FALSE ? $content : 'error';
    }

    if (isset($_POST['getpagetree'])) {
      $content = @file_get_contents(AdminConfig::$frontendDir . '/' . Config::$dataDir . '/data/pages.json');
      $content = $content !== FALSE ? $content : 'error';
    }

    if (isset($_POST['setpage']) && isset($_POST['name'])) {
      $path = AdminConfig::$frontendDir . '/' . Config::$dataDir . '/pages/' . $_POST['name'] . '.json';
      if (($data = @file_get_contents($path)) && ($data = json_decode($data, TRUE)) && (!isset($data['canSave']) || $data['canSave'] === TRUE || $data['canSave'] === 'true')) {
        if (($newContent = @json_encode($_POST['setpage'])) && @file_put_contents($path, $newContent)) {
          $content = 'saved';
        }
      }
    }

    if (isset($_POST['setpagetree'])) {
      $path = AdminConfig::$frontendDir . '/' . Config::$dataDir . '/data/pages.json';
      if (($newContent = @json_encode($_POST['setpagetree'])) && @file_put_contents($path, $newContent)) {
        $content = 'saved';
      }
    }

    if (isset($_POST['makepage']) && isset($_POST['tpl'])) {
      $content = 'error';
      $fn = Utils::safe_filename($_POST['makepage'], 'json', AdminConfig::$frontendDir . '/' . Config::$dataDir . '/pages');
      $data = @file_get_contents(AdminConfig::$frontendDir . '/' . Config::$dataDir . '/custom/templates/' . $_POST['tpl'] . '.json');
      $object = json_decode($data);
      foreach ($object->tplData as &$tplVar) {
        $tplVar = $tplVar->default;
      }
      $data = json_encode($object);
      $file = @Utils::fopen_recursive(AdminConfig::$frontendDir . '/' . Config::$dataDir . '/pages/' . $fn, 'w', Config::$newDirMask, Config::$newFileMask);
      if ($file !== FALSE && $data !== FALSE) {
        fwrite($file, $data);
        fclose($file);
        $content = explode('.', $fn);
        $content = $content[0];
      }
    }

    if (isset($_POST['delpage'])) {
      $path = AdminConfig::$frontendDir . '/' . Config::$dataDir . '/pages/' . $_POST['delpage'] . '.json';
      if (($data = @file_get_contents($path)) && ($data = json_decode($data, TRUE)) && (!isset($data['canDelete']) || $data['canDelete'] === TRUE || $data['canDelete'] === 'true')) {
        @unlink($path);
      }
      $content = file_exists($path) ? 'error' : 'success';
    }

    if (isset($_POST['gettpl'])) {
      $content = @file_get_contents(AdminConfig::$frontendDir . '/' . Config::$dataDir . '/custom/templates/' . $_POST['gettpl'] . '.json');
      $content = $content !== FALSE ? $content : 'error';
    }

    if (isset($_POST['getalltpl'])) {
      $content = @glob(AdminConfig::$frontendDir . '/' . Config::$dataDir . '/custom/templates/' . "*.json", GLOB_BRACE);
      if ($content !== FALSE) {
        foreach ($content as $k => $v) {
          $content[$k] = basename($v, '.json');
        }
      }
      $content = $content !== FALSE ? json_encode($content) : 'error';
    }

    if (isset($_POST['gettplbyparent'])) {
      if (isset(Config::$templateNesting) && count(Config::$templateNesting) > 0) {
        require_once(AdminConfig::$frontendDir . '/' . Config::$coreDir . '/classes/Cmx.php');
        Cmx::$pages = json_decode(file_get_contents(AdminConfig::$frontendDir . '/' . Config::$dataDir . '/data/pages.json'), TRUE);
        Utils::parse_pages();
        $parentPage = FALSE;
        if ($_POST['gettplbyparent'] === "_root") {
          $parentPage = "_root";
        }
        else {
          ($parent = Cmx::get_page($_POST['gettplbyparent'])) &&
          ($parent = @file_get_contents(AdminConfig::$frontendDir . '/' . Config::$dataDir . '/pages/' . $parent['file'] . '.json')) &&
          ($parent = json_decode($parent, TRUE)) &&
          $parentPage = $parent['template'];
        }
        if ($parentPage !== FALSE) {
          if (isset(Config::$templateNesting[$parentPage]) && count(Config::$templateNesting[$parentPage]) > 0) {
            $content = json_encode(Config::$templateNesting[$parentPage]);
          }
          else {
            $content = json_encode(array());
          }
        }
        else {
          $content = FALSE;
        }

        $content = $content !== FALSE ? $content : 'error';
      }
      else {
        $content = @glob(AdminConfig::$frontendDir . '/' . Config::$dataDir . '/custom/templates/' . "*.json", GLOB_BRACE);
        if ($content !== FALSE) {
          foreach ($content as $k => $v) {
            $content[$k] = basename($v, '.json');
          }
        }
        $content = $content !== FALSE ? json_encode($content) : 'error';
      }
    }

    if (isset($_POST['clearcache'])) {
      if (extension_loaded('apc')) {
        $del = new APCIterator('user', '/^cmx_cache_/', APC_ITER_VALUE);
        apc_delete($del);
      }
      Utils::empty_dir(AdminConfig::$frontendDir . '/' . Config::$cacheDir . '/');
      $content = 'success';
    }

    if (isset($_POST['pagetreeopened'])) {
      $_SESSION['treeOpened'] = $_POST['pagetreeopened'];
    }

    if (isset($_POST['deletefile'])) {
      $file = str_replace('..', '', $_POST['deletefile']);
      $delete = @unlink(AdminConfig::$frontendDir . '/' . Config::$uploadDir . '/' . $file);
      $content = $delete ? 'success' : 'error';
    }

    if (isset($_POST['deletedir'])) {
      $dir = str_replace('..', '', $_POST['deletedir']);
      $delete = @rmdir(AdminConfig::$frontendDir . '/' . Config::$uploadDir . '/' . $dir);
      $content = $delete ? 'success' : 'error';
    }

    die($content);
  }
  else {
    die('log');
  }
}

