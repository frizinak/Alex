<?php
/*
 *  Cmx v0.1  (24-10-2012)
 *  Project home: http://code.google.com/p/cmx/
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


class Index
{

    public static $scriptStart = 0;
    private static $initiated = false;

    public static function init()
    {
        if (!self::$initiated) {
            self::$scriptStart = microtime(true);
            require_once('Config.class.php');
            require_once(Config::$coreDir . '/classes/EHandler.php');
            require_once(Config::$coreDir . '/classes/Utils.php');
            require_once(Config::$coreDir . '/classes/Cmx.php');
            require_once(Config::$coreDir . '/classes/TplParser.php');
            if (Config::$startSession) {
                session_start();
            }

            self::$initiated = true;

            self::set_custom_handlers();
            self::init_pages();
            self::show_page();
        }
    }

    private static function init_pages()
    {
        if (!empty(Config::$languages)) {
            if (Config::$startSession) {
                if (isset($_GET['cmx-lang']) && in_array($_GET['cmx-lang'], Config::$languages)) {
                    $_SESSION['cmx-lang'] = $_GET['cmx-lang'];
                } else if (!isset($_SESSION['cmx-lang'])) {
                    $_SESSION['cmx-lang'] = Config::$languages[0];
                }
                Cmx::$language = $_SESSION['cmx-lang'];
            } else {
                if (isset($_GET['cmx-lang']) && in_array($_GET['cmx-lang'], Config::$languages)) {
                    Cmx::$language = $_GET['cmx-lang'];
                } else if (!isset($_SESSION['cmx-lang'])) {
                    Cmx::$language = Config::$languages[0];
                }
            }
        }
        Cmx::$pages = json_decode(file_get_contents(Config::$dataDir . '/data/pages.json'), true);
        Cmx::$requestPage = (!empty($_GET['page']) && trim($_GET['page'], '/') !== '') ? trim($_GET['page'], '/') : Config::$homepage;
        Cmx::$requestParts = explode('/', Cmx::$requestPage);
        foreach (Cmx::$requestParts as $requestPart) {
            if (!in_array($requestPart, Cmx::$pages['pagesList'])) {
                self::show_404();
            }
        }
        Cmx::$requestPage = Cmx::$requestParts[count(Cmx::$requestParts) - 1];
        Utils::parse_pages(); // multidim cmx::$pages -> singledim cmx::flatPages
        Cmx::$pageObject = isset(Cmx::$flatPages[Cmx::$requestPage]) ? Cmx::$flatPages[Cmx::$requestPage] : false;
        Config::$fullSiteCache = (Cmx::$pageObject !== false && Config::$fullSiteCache && (!isset(Cmx::$pageObject['cache']) || Cmx::$pageObject['cache'] !== "false"));

    }

    private static function show_page()
    {
        $lang = !empty(Cmx::$language) ? Cmx::$language . '/' : 'default/';

        if (Config::$fullSiteCache) {
            $cached = Utils::get_cache('pages/' . $lang . Cmx::$pageObject['file'] . '.htm');
            if ($cached !== false) {
                echo $cached;
                echo Config::$showTimers ? ' ' . (floor((microtime(true) - Index::$scriptStart) * 100000) / 100) . 'ms cache' : '';
                die();
            }
        }

        Cmx::$pageContent = false;
        if (Cmx::$pageObject !== false) {
            Cmx::$pageObject['pageInfo'] = Cmx::get_content(Cmx::$pageObject['page']);
            if (Cmx::$pageObject['pageInfo'] !== false) {
                if (Config::$tplCacheTime > 0) {
                    Cmx::$pageContent = Utils::get_cache('core/tpl/' . Cmx::$pageObject['file'] . '.php');
                }
                if (Cmx::$pageContent === false) {
                    Cmx::$pageContent = TplParser::make_tpl(Config::$dataDir . '/custom/templates/' . Cmx::$pageObject['pageInfo']['template'] . '.php', Cmx::$pageObject['pageInfo']['tplData']);
                    Utils::string_to_cache('core/tpl/' . Cmx::$pageObject['file'] . '.php', Cmx::$pageContent, Config::$tplCacheTime);
                }

            }
        }

        if (Cmx::$pageContent !== false) {
            //Config::$fullSiteCache ? ob_start() : '';
            ob_start();
            $check = self::runEval(Cmx::$pageContent);
            $output = ob_get_clean();
            if ($check !== false || Config::$debug) {
                echo $output;
                // Config::$fullSiteCache ? Utils::save_ob('pages/' . $lang . Cmx::$pageObject['file'] . '.htm', Config::$globalCacheTime) : '';
                Config::$fullSiteCache ? Utils::string_to_cache('pages/' . $lang . Cmx::$pageObject['file'] . '.htm', $output, Config::$globalCacheTime) : '';
                echo Config::$showTimers ? ' ' . (floor((microtime(true) - Index::$scriptStart) * 100000) / 100) . 'ms' : '';
            } else {
                $pattern = '/Parse error(.*?)in (.*?)\((.*?)\)/';
                preg_match_all($pattern, $output, $errors);
                throw new CustomException(0, (isset($errors[1]) ? $errors[1][0] : ''), 'index/eval', 0, null);
            }

            die();
        } else {
            self::show_404();
        }
    }

    private static function runEval($tpl)
    {
        return eval(' unset($tpl); ?>' . $tpl . '<?php ');
    }

    private static function set_custom_handlers()
    {
        error_reporting(E_ALL);
        set_error_handler('EHandler::error_handler');
        set_exception_handler('EHandler::exception_handler');
    }

    private static function show_404()
    {
        header("HTTP/1.0 404 Not Found");
        echo file_get_contents(Config::$dataDir . '/custom/404.html');
        die();
    }


}

/*

function showCache() {
  $cachedKeys = new APCIterator('user', '/^cmx_cache_/', APC_ITER_VALUE);

  echo "<pre>\nkeys in cache\n-------------\n";
  foreach ($cachedKeys AS $key => $value) {
      echo $key . "\n";
  }
  echo "-------------\n</pre>";
}
  */
Index::init();
