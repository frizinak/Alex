<?php
//generates passwords+salt
//do not upload to live server
//

if (isset($_POST['raw'])) {
    require_once('Config.class.php');
    require_once(Config::$coreDir . '/classes/Utils.php');
    $uniqueSalt = sha1(Utils::random_string(30) . microtime(true) . memory_get_usage(true));

    echo '<textarea cols="50" rows="5">';
    echo '"' . $_POST['username'] . '":{' . "\n";
    echo '"pw":"' . sha1(sha1($uniqueSalt . $_POST['raw']) . Config::$pwSalt) . "\",\n";
    echo '"salt":"' . $uniqueSalt . "\"\n}";
    echo '</textarea>';
}



?>
<form id="gegn-form" action="generate.php" method="post">
    <label for="username">Username:</label>
    <input type="text" name="username" id="username" value=""/><br/>
    <label for="password">Password:</label>
    <input type="text" name="raw" id="password" value=""/>
    <input type="submit" value="gen"/>
</form>
