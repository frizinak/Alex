<?php
class TplParser
{
    private function __construct()
    {
    }

    public static function make_tpl($tpl, $data)
    {
        return TplParser::replace($tpl, $data);
    }

    private static function replace($tpl, $data)
    {

        $tplPath = '' . $tpl;
        if (file_exists($tplPath)) {
            $t = file_get_contents($tplPath);
        } else {
            return false;
        }
        $matches = array();
        preg_match_all('|\[\[.+?\]\]|', $t, $matches);
        foreach ($matches[0] as $match) {
            $m = substr($match, 2, count($match) - 3);
            $part = TplParser::replace(Config::$dataDir . '/custom/entities/' . $m, $data);
            $t = str_replace($match, $part, $t);
        }

        $matches = array();
        preg_match_all('|\{\{.+?\}\}|', $t, $matches);
        foreach ($matches[0] as $match) {
            $m = substr($match, 2, count($match) - 3);
            $var = '';
            if (isset($data[$m])) {
                $var = $data[$m];
                if (!is_string($var)) {
                    $var = '';
                }

                if (!Config::$allowPHP) {
                    /*SHOULD catch all occurrences of (ordered): [<? code ?> || <?php code ?>, ?>, <? || <?php ] */
                    $var = preg_replace(array('/<\?(php)?[\n\s\r]*.*[\n\s\r]*\?>/', '/\?>/', '/<\?(php)?/'), '', $var);
                }

                if (!Config::$allowHTML) {
                    //already utf8, no need to run Utils::htmlent (htmlentities(...,ENT_QUOTES,"UTF-8"))
                    $var = htmlentities($var, ENT_NOQUOTES);
                }

                if (Config::$allowPHP) {
                    //in case html is off but we still wanna allow the user to write some logic
                    $var = str_replace(array('&lt;?php', '?&gt'), array('<?php', '?>'), $var);
                }
            }
            $t = str_replace($match, $var, $t);
        }
        return $t;
    }
}
