<?php
class Utils
{
    private function __construct()
    {
    }

    // fopen and chmod each dir and the file
    public static function fopen_recursive($path, $mode, $chmod = 0755, $fchmod = 0644)
    {
        $directory = explode('/', $path);
        array_pop($directory);

        $cur = '';
        foreach ($directory as $d) {
            $cur .= $d . '/';
            if (!is_dir($cur)) {
                if (!mkdir($cur, $chmod, true)) {
                    return false;
                }
            }
            @chmod($cur, $chmod);
        }
        $ret = @fopen($path, $mode);
        if ($ret !== false) {
            if (intval(substr(decoct(fileperms($path)), -4), 8) !== $fchmod) {
                @chmod($path, $fchmod);
            }
        }
        return $ret;
    }

    //save an opened ob
    public static function save_ob($filename, $ttl)
    {
        Utils::string_to_cache($filename, ob_get_flush(), $ttl);
    }

    public static function string_to_cache($filename, $string, $ttl)
    {
        if ($ttl < 1) {
            return;
        }
        // apc
        if (Config::$useAPC && extension_loaded('apc')) {
            $stored = apc_store('cmx_cache_' . $filename, $string, $ttl);
            if ($stored) {
                return;
            }
        }
        // no apc
        $t = microtime(true);
        $string = ($t + $ttl) . '--ENDTIMESTAMP--' . $string;
        $f = Utils::fopen_recursive(Config::$cacheDir . '/' . $filename, 'w', Config::$newDirMask, Config::$newFileMask);
        if ($f !== false) {
            fwrite($f, $string);
            fclose($f);
        }
    }

    public static function get_cache($filename)
    {
        // apc
        if (Config::$useAPC && extension_loaded('apc')) {
            $file = apc_fetch('cmx_cache_' . $filename);
            if ($file === false) {
                return false;
            }
            return $file;
        }
        // no apc
        if (file_exists(Config::$cacheDir . '/' . $filename)) {
            $file = file_get_contents(Config::$cacheDir . '/' . $filename);
            $arr = explode('--ENDTIMESTAMP--', $file, 2);
            if (floatval($arr[0]) > microtime(true)) {
                return $arr[1];
            }
        }
        return false;
    }

    public static function parse_pages($cache = true)
    {
        $pageCache = $cache ? Utils::get_cache("core/flatpagecache.json") : false;

        if ($pageCache !== false) {
            Cmx::$flatPages = json_decode($pageCache, true);
        } else {
            Cmx::$flatPages = array();
            self::parse_page(Cmx::$pages['pages']);
            self::string_to_cache("core/flatpagecache.json", json_encode(Cmx::$flatPages), Config::$globalCacheTime);
        }
    }

    private static function parse_page($parent, $url = "")
    {
        if (isset($parent['subpages']) && count($parent['subpages']) > 0) {
            $order = 0;
            foreach ($parent['subpages'] as $page) {
                $temp = $page;

                if (isset($temp['subpages'])) {
                    for ($i = 0; $i < count($temp['subpages']); $i++) {
                        $temp['subpages'][$i] = $temp['subpages'][$i]['page'];
                    }
                }
                $pageName = $page['page'];

                Cmx::$flatPages[$pageName] = $temp;
                Cmx::$flatPages[$pageName]['url'] = $url . $pageName;
                Cmx::$flatPages[$pageName]['order'] = $order;
                Cmx::$flatPages[$pageName]['parent'] = $parent['page'];
                Cmx::$flatPages[$pageName]['published'] = in_array($pageName, Cmx::$pages['pagesList']);
                self::parse_page($page, $url . $pageName . '/');
                $order++;
            }
        }
    }

    public static function htmlent($string)
    {
        return htmlentities($string, ENT_QUOTES, "UTF-8");

    }

    public static function safe_filename($filename, $ext, $dir)
    {
        clearstatcache();
        return self::numbered_filename(preg_replace("/[^a-zA-Z0-9]/", "", $filename), $ext, $dir);
    }

    public static function numbered_filename($prefix, $ext, $dir)
    {
        clearstatcache();
        $tries = 0;
        $nr = 0;
        $fn = '';
        do {
            $tries++;
            $fn = $prefix . ($nr === 0 ? '' : $nr);
            $nr++;
        } while (is_file($dir . '/' . $fn . '.' . $ext));
        return $fn . '.' . $ext;
    }

    public static function random_filename($minlength, $prefix, $ext, $dir)
    {
        clearstatcache();
        $tries = 0;
        $length = $minlength;
        do {
            $tries++;
            if ($tries > 3) {
                $length++;
                $tries = 0;
            }
            $fn = $prefix . self::random_string($length);

        } while (is_file($dir . '/' . $fn . '.' . $ext));
        return $fn . '.' . $ext;
    }

    public static function random_string($length)
    {
        $string = "";
        $chars = array('a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z', '0', '1', '2', '3', '4', '5', '6', '7', '8', '9');
        $cl = count($chars);
        for ($i = 0; $i < $length; $i++) {
            $string .= $chars[mt_rand(0, $cl - 1)];
        }
        return $string;

    }

    public static function empty_dir($dir)
    {
        //by Pascal MARTIN on stackoverflow
        $files = glob($dir . '*', GLOB_MARK);
        if ($files !== false) {
            foreach ($files as $file) {
                if (substr($file, -1) === '/' || substr($file, -1) === '\\') {
                    self::empty_dir($file);
                    rmdir($file);
                } else {
                    unlink($file);
                }
            }
        }

    }

    public static function backup($outputFile, $dirs, $mask)
    {
        if (extension_loaded('zip')) {
            $type = "zip";
            $outputFile .= '.zip';
            $zip = new ZipArchive();
            if ($zip->open($outputFile, ZIPARCHIVE::CREATE) === true) {
                foreach ($dirs as $dir) {
                    self::backup_dir($dir, $zip);
                }
                $zip->close();
                @chmod($outputFile, $mask);
            }
        } else {
            $type = "txt";
            $bu = '';
            $outputFile .= '.txt';
            $div = array('---[[[', ']]]---');
            foreach ($dirs as $dir) {
                $bu .= self::backup_dir($dir, false, $div);
            }
            @file_put_contents($outputFile, $bu); //gzcompress($bu));
            @chmod($outputFile, $mask);
        }
        return array($outputFile, $type);

    }

    private static function backup_dir($dir, $zip, $div = "")
    {
        $contents = glob($dir . '/*');
        if ($zip !== false) {
            foreach ($contents as $file) {
                if (is_dir($file))
                    self::backup_dir($file, $zip);
                else
                    $zip->addFile($file);
            }
        } else {
            $bu = '';
            foreach ($contents as $file) {
                if (is_file($file)) {
                    $c = @file_get_contents($file);
                    if ($c !== false) {
                        $bu .= $div[0] . $file . $div[1] . "\n" . $c . "\n";
                    } else {
                        $bu .= $div[0] . $file . $div[1] . ' error' . "\n";
                    }
                } else if (is_dir($file)) {
                    $bu .= self::backup_dir($file, false, $div);
                }
            }
            return $bu;
        }
    }
}
