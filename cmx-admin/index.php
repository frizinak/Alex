<?php session_start();
//classes
require_once('AdminConfig.class.php');
require_once(AdminConfig::$frontendDir . '/Config.class.php');
require_once(AdminConfig::$frontendDir . '/' . Config::$coreDir . '/classes/Utils.php');
require_once(AdminConfig::$frontendDir . '/' . Config::$coreDir . '/classes/Login.php');

class Admin
{
    public static $page = '';
    public static $lang = '';
    public static $langs = array();
    public static $logged = false;
    public static $backupFile = array();
}

if (isset($_POST['login'])) {
    if (Login::log_in($_POST['username'], $_POST['login'])) {
        header('Location: index.php');
    }
}

Admin::$logged = Login::is_logged();

if (isset($_GET['logout']) && Admin::$logged) {
    Login::log_out();
    Admin::$logged = false;
    header('Location: index.php');
}

Admin::$page = isset($_GET['page']) ? $_GET['page'] : 'edit';
if (isset($_GET['lang']) && preg_match('/^[a-zA-Z]{2}$/', $_GET['lang']) !== 0 && is_file('js/languages/' . $_GET['lang'] . '.js')) {
    $_SESSION['lang'] = $_GET['lang'];
}
Admin::$lang = isset($_SESSION['lang']) ? $_SESSION['lang'] : 'en';
Admin::$langs = glob('js/languages/??.js');

if (Admin::$logged && isset($_POST['backup'])) {
    $curDir=getcwd();
    chdir(AdminConfig::$frontendDir.'/');
    Admin::$backupFile = Utils::backup('' . Config::$cacheDir . '/backup-' . date('Y-m-d_Gi'), array('' . Config::$dataDir), Config::$newFileMask);
    if (is_file(Admin::$backupFile[0])) {
        header("Content-Type: " . (Admin::$backupFile[1] === 'zip' ? "application/zip" : "text/plain"));
        header("Content-Disposition: attachment; filename=" . basename(Admin::$backupFile[0]));
        header("Content-Length: " . filesize(Admin::$backupFile[0]));
        readfile(Admin::$backupFile[0]);
        unlink(Admin::$backupFile[0]);
        exit;
    } else {
        chdir($curDir);
    }
}
//output
require_once(AdminConfig::$frontendDir.'/' . Config::$coreDir . '/includes/admin/header.php');
if (!Admin::$logged) {
    Login::prepare_session();
    require_once(AdminConfig::$frontendDir.'/' . Config::$coreDir . '/includes/admin/loginform.php');
} else {
    require_once(AdminConfig::$frontendDir.'/' . Config::$coreDir . '/includes/admin/content.php');
}
require_once(AdminConfig::$frontendDir.'/' . Config::$coreDir . '/includes/admin/footer.php');
