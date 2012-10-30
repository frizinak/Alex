<?php

class Upload
{
    public static $imgMimes = array('image/gif', 'image/jpeg', 'image/png');
    public static $imgExt = array('gif', 'jpg', 'jpeg', 'png');

    public static function up($dir)
    {
        $errors = array();
        $fns = array();
        for ($i = 0; $i < count($_FILES['files']['name']); $i++) {
            if ($_FILES['files']['error'][$i] === UPLOAD_ERR_OK) {
                $scans = 0;
                do {
                    $fn = $scans === 0 ? $_FILES['files']['name'][$i] : self::add_string_to_filename($_FILES['files']['name'][$i], '(' . $scans . ')');
                    $scans++;
                } while (is_file($dir . '/' . $fn));
                $fns[] = $fn;
                $moved = @move_uploaded_file($_FILES['files']['tmp_name'][$i], $dir . '/' . $fn);
                if ($moved === false) {
                    $errors[] = $_FILES['files']['name'][$i] . ' Was not saved, check permissions.';
                } else {
                    @chmod($dir . '/' . $fn, Config::$newFileMask);
                    $ext = explode('.', $fn);
                    $ext = strtolower($ext[count($ext) - 1]);
                    $imgSize = getimagesize($dir . '/' . $fn);
                    if (in_array($imgSize['mime'], self::$imgMimes) && in_array($ext, self::$imgExt)) {
                        self::resize($fn, $dir);
                    }
                }


            } else {
                switch ($_FILES['files']['error'][$i]) {
                    case UPLOAD_ERR_INI_SIZE:
                        $max_upload = (int)(ini_get('upload_max_filesize'));
                        $max_post = (int)(ini_get('post_max_size'));
                        $memory_limit = (int)(ini_get('memory_limit'));
                        $errors[] = $_FILES['files']['name'][$i] . ' file exceeds: ' . min($max_upload, $max_post, $memory_limit) . 'mb';
                        break;
                    case UPLOAD_ERR_NO_FILE:
                        $errors[] = $_FILES['files']['name'][$i] . ' Please specify a file';
                        break;
                    default:
                        $errors[] = 'An error occurred for: ' . $_FILES['files']['name'][$i];
                        break;
                }
            }
        }
        if (count($errors) === 0) {
            return true;
        }
        return $errors;

    }

    private static function add_string_to_filename($filename, $str)
    {
        $filenameArr = explode('.', $filename);
        $filenameArr[count($filenameArr) - 2] .= $str;
        return implode('.', $filenameArr);

    }

    public static function resize($imgname, $dir)
    {
        if (isset(Config::$imageSizes) && Config::$imageSizes != null && count(Config::$imageSizes) > 0) {
            require_once('SimpleImage.php');
            $image = new SimpleImage();
            $size = getimagesize($dir . '/' . $imgname);
            $toSizes = Config::$imageSizes;
            $ratio = $size[0] / $size[1];

            for ($i = 0; $i < count($toSizes); $i++) {
                if ($toSizes[$i][0] < $size[0] || $toSizes[$i][1] < $size[1]) {
                    $image->load($dir . '/' . $imgname);
                    $resRatio = $toSizes[$i][0] / $toSizes[$i][1];

                    if ($ratio < $resRatio) {
                        $newSize = $image->resizeToHeight($toSizes[$i][1]);
                    } else {
                        $newSize = $image->resizeToWidth($toSizes[$i][0]);
                    }
                    $fileArr = explode('.', $imgname);
                    $fileArr[count($fileArr) - 2] .= '-' . $newSize[0] . 'x' . $newSize[1];
                    $file = implode('.', $fileArr);
                    $image->save($dir . '/' . $file, $image->image_type, $toSizes[$i][2], Config::$newFileMask);
                }
            }
        }
    }

}
