<?php
class Login
{
    public static $error = '';

    public static function log_in($un, $pw)
    {
        $passwords = json_decode(file_get_contents('../' . Config::$dataDir . '/data/users.json'), true);
        if (isset($passwords[$_POST['username']])) {
            session_regenerate_id(true);

            if (sha1(sha1($passwords[$_POST['username']]['salt'] . $_POST['password']) . Config::$pwSalt) === $passwords[$_POST['username']]['pw']) {
                $_SESSION['logged'] = true;
                $_SESSION['age'] = microtime(true);
                $_SESSION['ua'] = $_SERVER['HTTP_USER_AGENT'];
                return true;
            }
        }
        self::$error = 'Failed to login';
        return false;
    }

    public static function log_out()
    {
        $sesVars = array('logged', 'treeOpened', 'age', 'ua');
        foreach ($sesVars as $var) {
            $_SESSION[$var] = false;
            unset($_SESSION[$var]);
        }
    }

    public static function is_logged()
    {
        return (isset($_SESSION['logged']) &&
            $_SESSION['logged'] === true &&
            isset($_SESSION['age']) &&
            microtime(true) - $_SESSION['age'] < Config::$sessionExpiry &&
            isset($_SESSION['ua']) &&
            $_SESSION['ua'] === $_SERVER['HTTP_USER_AGENT']);
    }

    public static function prepare_session()
    {
        //session_regenerate_id(true);
        //$_SESSION['key'] = Utils::random_string(15);
        $_SESSION['logged'] = false;
        $_SESSION['age'] = 0;
        $_SESSION['ua'] = '';
    }


}
