<?php

class Config
{

    //backend related entries

    //logoff user after $sessionExpiry seconds of inactivity
    public static $sessionExpiry = 3600;

    //salt for useragent validation (extra measure to prevent session hijacking)
    public static $salt = 'pfy26udK1qgOBv5cCnwU';

    //password noise
    public static $pwSalt = 'VzAVKtFAixn8B0rZq32k';

    //allow html input in backend to be shown in frontend
    public static $allowHTML = true;

    //allow php input in backend to be parsed
    public static $allowPHP = false;

    //resize images when being uploaded (each new image will be created based on array(maxwidth,maxheight,jpgquality)).
    //only jpgs, gifs and pngs are affected.
    public static $imageSizes = array(
        array(200, 200, 60),
        array(2000, 2000, 100)
    );


    //frontend related entries

    //starts a session (recommended: true when multilingual or need access to $_SESSION in templates)
    public static $startSession = true;

    //amount of time templates are cached, use 0 when developing
    public static $tplCacheTime = 1;

    //amount of time pages are cached, does not affect manual caching
    public static $globalCacheTime = 1;

    //enable caching, does not affect manual caching / tplcaching
    public static $fullSiteCache = true;

    //uses APC instead of files for caching (recommended: true, will fallback to files if APC is not installed)
    public static $useAPC = false;

    //default page to be loaded when visiting site without pagename (http://yoursite.com)
    public static $homepage = 'home';

    //omit homepage in urls (home/subpage -> yoursite.com/subpage)
    public static $ignoreHomepage = true;

    //if site resides in a subdir of public_html/wwwroot use: /subdir, otherwise leave empty
    public static $siteDir = "/Alex";

    //languages for multilingual site, leave empty or prepend with // for non-multilingual site (update .htaccess accordingly)
    public static $languages = array('en', 'nl', 'fr');

    //false: http://site.com/?page=subpage&cmx-lang=en, true: http://site.com/en/home/subpage
    public static $seoUrls = true;

    //true: show default php exceptions/errors, false: custom userfriendly message
    public static $debug = true;

    //true: show processing time of pages and latency in back-end
    public static $showTimers = true;


    //all relative to index.php
    public static $uploadDir = "upload";
    public static $adminDir = "cmx-admin";

    // !=upload dir, its a cache for resized images
    public static $imageDir = "upload/image-cache";

    //these should be outside public_html
    public static $cacheDir = "cmx-cache";
    public static $coreDir = "cmx-core";
    public static $dataDir = "cmx-data";


    //permissions to use for all directories/files created by cmx, with suPHP should be max 0755-0644
    public static $newDirMask = 0777;
    public static $newFileMask = 0666;


    private function __construct()
    {
        //static class
    }
}
