<?php
class Cmx {

  //  raw html/php from template
  public static $pageContent = NULL;
  //  data/pages.json
  public static $pages = NULL;
  //  current page object
  public static $pageObject = FALSE;
  // 1dimensional version of $pages
  public static $flatPages = array();
  // last part of the request: page title
  public static $requestPage = '';
  // all parts of the request, e.g.: /home/subpage/blog = array(home,subpage,blog)
  public static $requestParts = array();
  // all parts of the request, e.g.: /home/subpage/blog = 'home/subpage/blog'
  public static $requestString = '';

  // 2-letter code
  public static $language = '';

  private function __construct() {
    // static object, no need to instantiate.
  }

  // returns page object
  public static function get_page($name = NULL) {
    if ($name === NULL) {
      $name = self::$requestPage;
    }
    if (isset(self::$flatPages[$name])) {
      return self::$flatPages[$name];
    }

    return FALSE;
  }

  // returns template variables of specified page
  public static function get_content($name = NULL) {
    $p = self::get_page($name);
    if ($p !== FALSE) {
      $file = @file_get_contents(Config::$dataDir . '/pages/' . $p['file'] . '.json');
      $fileobj = FALSE;
      if ($file !== FALSE) {
        $fileobj = json_decode($file, TRUE);
      }
      if ($fileobj !== FALSE) {
        return $fileobj;
      }
    }

    return FALSE;
  }

  //returns a page url which can be used anywhere.
  public static function get_page_url($name = NULL) {
    $p = self::get_page($name);
    if ($p !== FALSE) {
      $language = empty(self::$language) ? '' : self::$language . '/';
      $p['url'] = '/' . trim($p['url'], '/') . '/';
      if (Config::$ignoreHomepage) {
        $p['url'] = str_replace('/' . Config::$homepage . '/', '', $p['url']);
      }
      $p['url'] = ltrim($p['url'], '/');

      if (Config::$seoUrls) {
        return Config::$siteDir . '/' . $language . $p['url'];
      }
      else {
        return Config::$siteDir . '/?cmx-lang=' . self::$language . '&page=' . $p['url'];
      }
    }

    return FALSE;
  }

  public static function search($term, $pages = 'all', $maxResults = 1000) {
    $results = array();
    $term = preg_quote($term);

    $haystack = self::$flatPages;
    $publishedOnly = FALSE;
    if ($pages === 'published') {
      $publishedOnly = TRUE;
    }
    else if (is_array($pages)) {
      $haystack = $pages;
    }
    else if ($pages !== 'all') {
      return FALSE;
    }
    foreach ($haystack as $page) {
      $page = self::get_page((is_array($page) && isset($page['page']) ? $page['page'] : $page));
      if ($page === FALSE || ($publishedOnly && $page['published'] !== TRUE)) {
        continue;
      }
      $pageContent = self::get_content($page['page']);
      foreach ($pageContent['tplData'] as $k => $entry) {
        if (is_string($entry)) {
          $matches = array();
          $entry = strip_tags($entry);
          preg_match_all('/' . $term . '/i', $entry, $matches);
          if (count($matches[0]) > 0) {

            $index = strpos(strtolower($entry), strtolower($term)) - 20;
            $index = $index < 0 ? 0 : $index;
            $entry = ($index > 0 ? '...' : '') . substr($entry, $index, 40 + strlen($term)) . ($index + 40 + strlen($term) >= strlen($entry) ? '' : '...');
            if (isset($results[$page['page']])) {

              $results[$page['page']][] = array('tplVar' => $k, 'text' => $entry);
            }
            else {
              $results[$page['page']] = array(array('tplVar' => $k, 'text' => $entry));
            }
          }
        }
      }

      if (count($results) >= $maxResults) {
        return $results;
      }
    }

    return $results;
  }

  // param url = relative to index.php (and as shown when clicking an image in back-end (e.g upload/myimg.jpg))
  // param $w & $h = resize image to these dimensions, if maintainAspectRation=false: resize $w x $h, else: resize assumes $w = maximum width and $h = maximum height

  // return depends on $w, $h and $maintainAspect
  // $w=$h=0; returns image from uploadDir
  // $w!=0 || $h!=0 returns image from uploadDir if it has been preresized (during upload) or from cacheDir if it has been resized after uploading
  // otherwise resizes it and stores in in cacheDir
  // return = array(htmlUrl,realWidth,realHeight,htmlString), htmlUrl = ready to be used in templates, htmlString = 'width="int" height="int"'
  public static function get_img($url, $w = 0, $h = 0, $maintainAspectRatio = TRUE) {
    //check if url is valid
    if (is_file($url)) {
      $realDims = @getimagesize($url);
      if ($realDims === FALSE) {
        return FALSE;
      }
      $aspect = $realDims[0] / $realDims[1];
      $cacheImages = Config::$imageDir;
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
            }
            else {
              $hs = round($w / $aspect);
              $ws = $w;
              $h = 0;
            }
          }
          else {
            $hs = $h;
            $ws = $w;
          }
        }
        else if ($w > 0) {
          $ws = $w;
          $hs = round($w / $aspect);
        }
        else if ($h > 0) {
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
        }
        else if (is_file($cacheImages . '/' . $newUrl)) {
          $dims = getimagesize($cacheImages . '/' . $newUrl);

          return array(Config::$siteDir . '/' . $cacheImages . '/' . $newUrl, $dims[0], $dims[1], $dims[3]);
        }
        else {
          //not cached => resize, save and return url + dims
          require_once(Config::$coreDir . '/classes/SimpleImage.php');
          $img = new SimpleImage();
          $img->load($url);
          if ($w > 0 && $h > 0) {
            $img->resize($w, $h);
          }
          else if ($w > 0) {
            $img->resizeToWidth($w);
          }
          else if ($h > 0) {
            $img->resizeToHeight($h);
          }
          if (!is_dir($cacheImages)) {
            mkdir($cacheImages, Config::$newDirMask, TRUE);
            @chmod($cacheImages, Config::$newDirMask);
          }
          $img->save($cacheImages . '/' . $newUrl, $img->image_type, 90, Config::$newFileMask);
          $dims = getimagesize($cacheImages . '/' . $newUrl);

          return array(Config::$siteDir . '/' . $cacheImages . '/' . $newUrl, $dims[0], $dims[1], $dims[3]);
        }
      }
      else {
        $dims = $realDims;

        return array(Config::$siteDir . '/' . $url, $dims[0], $dims[1], $dims[3]);
      }
    }

    return FALSE;
  }
}
