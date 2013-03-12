<?php
class Login {

  public static $error = '';

  public static function log_in($un, $pw) {
    $passwords = json_decode(file_get_contents('../' . Config::$dataDir . '/data/users.json'), TRUE);
    if (isset($passwords[$_POST['username']])) {
      session_regenerate_id(TRUE);

      if (sha1(sha1($passwords[$_POST['username']]['salt'] . $_POST['password']) . Config::$pwSalt) === $passwords[$_POST['username']]['pw']) {
        $_SESSION['logged'] = TRUE;
        $_SESSION['age'] = microtime(TRUE);
        $_SESSION['ua'] = $_SERVER['HTTP_USER_AGENT'];

        return TRUE;
      }
    }
    self::$error = 'Failed to login';

    return FALSE;
  }

  public static function log_out() {
    $sesVars = array('logged', 'treeOpened', 'age', 'ua');
    foreach ($sesVars as $var) {
      $_SESSION[$var] = FALSE;
      unset($_SESSION[$var]);
    }
  }

  public static function is_logged() {
    return (isset($_SESSION['logged']) &&
            $_SESSION['logged'] === TRUE &&
            isset($_SESSION['age']) &&
            microtime(TRUE) - $_SESSION['age'] < Config::$sessionExpiry &&
            isset($_SESSION['ua']) &&
            $_SESSION['ua'] === $_SERVER['HTTP_USER_AGENT']);
  }

  public static function prepare_session() {
    //session_regenerate_id(true);
    //$_SESSION['key'] = Utils::random_string(15);
    $_SESSION['logged'] = FALSE;
    $_SESSION['age'] = 0;
    $_SESSION['ua'] = '';
  }
}
