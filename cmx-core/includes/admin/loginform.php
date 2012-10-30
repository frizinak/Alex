<form id="login-form" action="index.php" method="post" style="display:none;">
    <div id="head"><span id="llogo"></span></div>
    <?php echo Login::$error !== '' ? '<span id="loginError">' . Login::$error . '</span>' : ''; ?>
    <label for="username">Username:</label>
    <input type="text" name="username" id="username" value="admin"/>
    <label for="password">Password:</label>
    <input type="text" name="password" id="password" value="123"/>
    <input type="hidden" name="login" id="login"/>
    <input type="hidden" name="key" id="key" value="<?php echo $_SESSION['key']; ?>"/>
    <input type="submit" value=" login "/>
</form>
