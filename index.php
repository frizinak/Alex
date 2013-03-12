<?php
/*
 *  Cmx v0.1  (24-10-2012)
 *  Project home: https://github.com/frizinak/Alex
 *
 *  Distributed under the MIT license
 *  Copyright (C) 2012 Kobe Lipkens
 *  Committers: /
 *
 *  Permission is hereby granted, free of charge,
 *  to any person obtaining a copy of this software
 *  and associated documentation files (the "Software")3,
 *  to deal in the Software without restriction,
 *  including without limitation the rights to use,
 *  copy, modify, merge, publish, distribute, sublicense,
 *  and/or sell copies of the Software, and to permit
 *  persons to whom the Software is furnished to do so,
 *  subject to the following conditions:
 *
 *  The above copyright notice and this permission notice
 *  shall be included in all copies or substantial portions
 *  of the Software.
 *
 *  THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
 *  EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
 *  MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT.
 *  IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY
 *  CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT,
 *  TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE
 *  SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 *
 */

class Index {

  public static $scriptStart = 0;
  private static $initiated = FALSE;
  private static $cache = FALSE;
  public static $timer = 0;
  public static $subtimer = 0;

  public static function init() {
    self::$subtimer = self::$timer = microtime(TRUE);

    if (!self::$initiated) {
      self::$scriptStart = microtime(TRUE);
      require_once('Config.class.php');
      require_once(Config::$coreDir . '/classes/EHandler.php');
      require_once(Config::$coreDir . '/classes/Utils.php');
      require_once(Config::$coreDir . '/classes/Cmx.php');
      require_once(Config::$coreDir . '/classes/TplParser.php');
      if (Config::$startSession) {
        session_start();
      }
      self::$initiated = TRUE;
      self::set_custom_handlers();
      self::init_pages();
      self::show_page();
    }
  }

  private static function init_pages() {
    if (!empty(Config::$languages)) {
      if (Config::$startSession) {
        if (isset($_GET['cmx-lang']) && in_array($_GET['cmx-lang'], Config::$languages)) {
          $_SESSION['cmx-lang'] = $_GET['cmx-lang'];
        }
        else if (!isset($_SESSION['cmx-lang'])) {
          $_SESSION['cmx-lang'] = Config::$languages[0];
        }
        Cmx::$language = $_SESSION['cmx-lang'];
      }
      else {
        if (isset($_GET['cmx-lang']) && in_array($_GET['cmx-lang'], Config::$languages)) {
          Cmx::$language = $_GET['cmx-lang'];
        }
        else if (!isset($_SESSION['cmx-lang'])) {
          Cmx::$language = Config::$languages[0];
        }
      }
    }
    Cmx::$pages = json_decode(file_get_contents(Config::$dataDir . '/data/pages.json'), TRUE);
    Cmx::$requestString = (!empty($_GET['page']) && trim($_GET['page'], '/') !== '') ? trim($_GET['page'], '/') : Config::$homepage;
    Cmx::$requestParts = explode('/', Cmx::$requestString);
    foreach (Cmx::$requestParts as $requestPart) {
      if (!in_array($requestPart, Cmx::$pages['pagesList'])) {
        self::show_404();
      }
    }
    Cmx::$requestPage = Cmx::$requestParts[count(Cmx::$requestParts) - 1];
    Utils::parse_pages(); // multidim Cmx::$pages -> singledim Cmx::$flatPages
    //Cmx::$pageObject = isset(Cmx::$flatPages[Cmx::$requestPage]) && Cmx::$flatPages[Cmx::$requestPage]['url'] === implode('/', Cmx::$requestParts) ? Cmx::$flatPages[Cmx::$requestPage] : false;
    if (isset(Cmx::$flatPages[Cmx::$requestPage])) {
      if (Cmx::$flatPages[Cmx::$requestPage]['url'] === Cmx::$requestString || (Config::$ignoreHomepage && Cmx::$flatPages[Cmx::$requestPage]['url'] === Config::$homepage . '/' . Cmx::$requestString)) {
        Cmx::$pageObject = Cmx::$flatPages[Cmx::$requestPage];
      }
    }
    //Config::$fullSiteCache = (Cmx::$pageObject !== false && Config::$fullSiteCache && (!isset(Cmx::$pageObject['cache']) || Cmx::$pageObject['cache'] !== "false"));
    Index::$cache = (Cmx::$pageObject !== FALSE && Config::$globalCacheTime > 0 && (!isset(Cmx::$pageObject['cache']) || Cmx::$pageObject['cache'] !== "false"));
  }

  private static function show_page() {
    $lang = !empty(Cmx::$language) ? Cmx::$language . '/' : 'default/';
    if (Index::$cache) {
      $cached = Utils::get_cache('pages/' . $lang . Cmx::$pageObject['file'] . '.htm');
      if ($cached !== FALSE) {
        echo $cached;
        echo Config::$showTimers ? ' ' . (floor((microtime(TRUE) - Index::$scriptStart) * 100000) / 100) . 'ms cache' : '';
        die();
      }
    }

    Cmx::$pageContent = FALSE;
    if (Cmx::$pageObject !== FALSE) {
      Cmx::$pageObject['pageInfo'] = Cmx::get_content(Cmx::$pageObject['page']);
      if (Cmx::$pageObject['pageInfo'] !== FALSE) {
        if (Config::$tplCacheTime > 0) {
          Cmx::$pageContent = Utils::get_cache('core/tpl/' . Cmx::$pageObject['file'] . '.php');
        }
        if (Cmx::$pageContent === FALSE) {
          Cmx::$pageContent = TplParser::make_tpl(Config::$dataDir . '/custom/templates/' . Cmx::$pageObject['pageInfo']['template'] . '.php', Cmx::$pageObject['pageInfo']['tplData']);
          Utils::string_to_cache('core/tpl/' . Cmx::$pageObject['file'] . '.php', Cmx::$pageContent, Config::$tplCacheTime);
        }
      }
    }

    if (Cmx::$pageContent !== FALSE) {
      ob_start();
      $check = self::run_eval(Cmx::$pageContent);
      $output = ob_get_clean();
      if ($check !== FALSE || Config::$debug) {
        echo $output;
        Index::$cache ? Utils::string_to_cache('pages/' . $lang . Cmx::$pageObject['file'] . '.htm', $output, Config::$globalCacheTime) : '';
        echo Config::$showTimers ? ' ' . (floor((microtime(TRUE) - Index::$scriptStart) * 100000) / 100) . 'ms' : '';
      }
      else {
        $pattern = '/Parse error(.*?)in (.*?)\((.*?)\)/';
        preg_match_all($pattern, $output, $errors);
        throw new CustomException(0, (isset($errors[1]) ? $errors[1][0] : ''), 'index/eval', 0, NULL);
      }
      die();
    }
    else {
      self::show_404();
    }
  }

  private static function run_eval($tpl) {
    //unset $tpl to keep scope clean
    return eval(' unset($tpl); ?>' . $tpl . '<?php ');
  }

  private static function set_custom_handlers() {
    //encourage clean code.
    error_reporting(E_ALL);
    set_error_handler('EHandler::error_handler');
    set_exception_handler('EHandler::exception_handler');
  }

  private static function show_404() {
    header("HTTP/1.0 404 Not Found");
    echo file_get_contents(Config::$dataDir . '/custom/404.html');
    die();
  }
}

//cache dumping function to check cache when apc is enabled
function showCache() {
  $cachedKeys = new APCIterator('user', '/^cmx_cache_/', APC_ITER_VALUE);

  echo "<pre>\nkeys in cache\n-------------\n";
  foreach ($cachedKeys AS $key => $value) {
    echo $key . "\n";
  }
  echo "-------------\n</pre>";
}

Index::init();
