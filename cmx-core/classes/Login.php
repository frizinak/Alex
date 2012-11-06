<?php
class Login
{
    public static $error = '';

    public static function log_in($un, $pw)
    {
        $passwords = json_decode(file_get_contents('../' . Config::$dataDir . '/data/users.json'), true);
        if (isset($passwords[$_POST['username']])) {
            session_regenerate_id(true);
            //server provides client with random string ($_SESSION['key']) on each page load
            //salt = 'VzAVKtFAixn8B0rZq32k'
            //client sha1(sha1(password+salt)+randomstring)
            //server reads users.json and sha1($_post['password'])+randomstring)
            //users.json has sha1(password+salt)
            // note: the salt is sent to every user in login.js so is no security measure, it's there to prevent saving raw / sha-reversable strings in a file (user-privacy)
            if ($_POST['login'] === sha1($passwords[$_POST['username']] . $_SESSION['key'])) {
                $_SESSION['logged'] = true;
                $_SESSION['age'] = microtime(true);
                $_SESSION['ua'] = sha1($_SERVER['HTTP_USER_AGENT'] . Config::$salt);
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
            $_SESSION['ua'] === sha1($_SERVER['HTTP_USER_AGENT'] . Config::$salt));
    }

    public static function prepare_session()
    {
        session_regenerate_id(true);
        $_SESSION['key'] = Utils::random_string(15);
        $_SESSION['logged'] = false;
        $_SESSION['age'] = 0;
        $_SESSION['ua'] = '';
    }


}
