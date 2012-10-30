<?php
class Cmx
{
    public static $pageContent = null; //  raw html/php from template
    public static $pages = null; //  data/pages.json
    public static $pageObject = null; //  object in pages

    public static $flatPages = array(); // 1dimensional version of $pages

    public static $requestPage = '';
    public static $requestParts = array();

    public static $language = '';

    private function __construct()
    {

    }


    public static function get_page($name = null)
    {
        if ($name === null) {
            $name = self::$requestPage;
        }
        if (isset(self::$flatPages[$name])) {
            return self::$flatPages[$name];
        }
        return false;
    }

    public static function get_content($name = null)
    {
        $p = self::get_page($name);
        if ($p !== false) {
            $file = @file_get_contents(Config::$dataDir . '/pages/' . $p['file'] . '.json');
            $fileobj = false;
            if ($file !== false) {
                $fileobj = json_decode($file, true);
            }
            if ($fileobj !== false) {
                return $fileobj;
            }
        }
        return false;
    }

    public static function get_page_url($name = null)
    {
        $p = self::get_page($name);
        if ($p !== false) {
            $language = empty(self::$language) ? '' : self::$language . '/';
            if (Config::$seoUrls) {
                return Config::$siteDir . '/' . $language . $p['url'];
            } else {
                return Config::$siteDir . '/?cmx-lang=' . self::$language . '&page=' . $p['page'];
            }

        }
        return false;
    }

    public static function get_img($url, $w = 0, $h = 0, $maintainAspectRatio = true)
    {
        //check if url is valid
        if (is_file($url)) {
            $realDims = @getimagesize($url);
            if ($realDims === false) {
                return false;
            }
            $aspect = $realDims[0] / $realDims[1];
            $cacheImages = Config::$cacheDir . '/core/images';
            //need to resize, if not just return $url relative to root + img dimensions
            if (($w > 0 && $w < $realDims[0]) || ($h > 0 && $h < $realDims[1])) {
                //unique name based on path+filename =>no collisions when working with same filenames in different directories
                $preResized = explode('.', $url);
                $uniqueID = urlencode(str_replace(array('.', '/', '\\'), '-', $url));
                $fnParts = explode('.', basename($url));
                //append dimensions  to unique filename and prepare $w/$h for resizing if needed
                if ($w > 0 && $h > 0) {
                    if ($maintainAspectRatio) {
                        $newAspect = $w / $h;
                        if ($newAspect > $aspect) {
                            $ws = round($h * $aspect);
                            $hs = $h;
                            $w = 0;
                        } else {
                            $hs = round($w / $aspect);
                            $ws = $w;
                            $h = 0;
                        }
                    } else {
                        $hs = $h;
                        $ws = $w;
                    }
                } else if ($w > 0) {
                    $ws = $w;
                    $hs = round($w / $aspect);
                } else if ($h > 0) {
                    $hs = $h;
                    $ws = round($h * $aspect);
                }
                $preResized[count($fnParts) - 2] .= '-' . $ws . 'x' . $hs;
                $preUrl = implode('.', $preResized);
                $fnParts[count($fnParts) - 2] = $uniqueID . '-' . $ws . 'x' . $hs;
                $newUrl = implode('.', $fnParts);
                //check if image is already cached or preresized => return url + dims
                if (is_file($preUrl)) {
                    $dims = getimagesize($preUrl);
                    return array(Config::$siteDir . '/' . $preUrl, $dims[0], $dims[1], $dims[3]);
                } else if (is_file($cacheImages . '/' . $newUrl)) {
                    $dims = getimagesize($cacheImages . '/' . $newUrl);
                    return array(Config::$siteDir . '/' . $cacheImages . '/' . $newUrl, $dims[0], $dims[1], $dims[3]);
                } else {
                    //not cached => resize, save and return url + dims
                    require_once('SimpleImage.php');
                    $img = new SimpleImage();
                    $img->load($url);
                    if ($w > 0 && $h > 0) {
                        $img->resize($w, $h);
                    } else if ($w > 0) {
                        $img->resizeToWidth($w);
                    } else if ($h > 0) {
                        $img->resizeToHeight($h);
                    }
                    if (!is_dir($cacheImages)) {
                        mkdir($cacheImages, Config::$newDirMask, true);
                        @chmod($cacheImages, Config::$newDirMask);
                    }
                    $img->save($cacheImages . '/' . $newUrl, $img->image_type, 90, Config::$newFileMask);
                    $dims = getimagesize($cacheImages . '/' . $newUrl);
                    return array(Config::$siteDir . '/' . $cacheImages . '/' . $newUrl, $dims[0], $dims[1], $dims[3]);
                }
            } else {
                $dims = $realDims;
                return array(Config::$siteDir . '/' . $url, $dims[0], $dims[1], $dims[3]);
            }
        }
        return false;
    }
}
