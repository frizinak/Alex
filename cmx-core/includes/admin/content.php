<?php if (!Admin::$logged) {
    die();
} ?>
<div id="container" style="display:none;">
    <?php if (Config::$showTimers): ?>
    <div id="latency" style="
        font-size: 10px;
        width: 300px;
        padding: 10px;
        height: 10px;
        background-color: #030c16;
        color: #4ab4d2;
        position: fixed;
        bottom: 20px;
        left: 0;
    "></div>
    <?php endif; ?>
    <div id="layer" style="display:none"></div>
    <div id="message" style="display:none;"></div>

    <div id="menu">
        <a id="logo" href="../" target="_blank"><span>CMX</span></a>

        <div id="left">
            <a href="#" id="clearcache"></a>

            <form id="backup" action="index.php?page=<?php echo Admin::$page; ?>" method="post">
                <input type="submit" value="backup" name="backup">
            </form>
        </div>
        <div id="right">
            <span id="currLang" style="background-image:url('js/languages/<?php echo Admin::$lang;?>.png')">
                <span id="langs">
                    <?php
                    echo '<a style="background-image:url(\'js/languages/' . Admin::$lang . '.png\')" href="?page=' . Admin::$page . '&lang=' . Admin::$lang . '" class="lang" ></a>';
                    foreach (Admin::$langs as $l) {
                        $l = substr(basename($l), 0, 2);
                        if ($l !== Admin::$lang) {
                            echo '<a style="background-image:url(\'js/languages/' . $l . '.png\')" href="?page=' . Admin::$page . '&lang=' . $l . '" class="lang" ></a>';
                        }
                    }
                    ?>
                </span>
            </span>
            <a href="?logout" id="logout"></a>
        </div>
    </div>
    <div id="labeltip">&nbsp;</div>
    <div id="tabs">
        <a id="tabcontent" href="index.php?page=edit" class="<?php echo Admin::$page === 'edit' ? 'active' : ''; ?>"></a>
        <a id="tabnew" href="index.php?page=new" class="<?php echo Admin::$page === 'new' ? 'active' : ''; ?>"></a>
        <a id="tabupload" href="index.php?page=upload" class="<?php echo Admin::$page === 'upload' ? 'active' : ''; ?>"></a>
    </div>
    <div class="clear"></div>
    <div id="main">
        <div class="clear"></div>
        <?php if (Admin::$page === 'edit'): ?>
        <div id="treeOpened" style="display: none;"><?php echo isset($_SESSION['treeOpened']) ? json_encode($_SESSION['treeOpened']) : '{}'; ?></div>
        <div id="pageDescr"></div>
        <ul id="pages"></ul>
        <div id="editor">
            <form id="editor-form" action="/" method="post">
            </form>
        </div>
        <div class="clear"></div>
        <?php elseif (Admin::$page === 'new'): ?>
        <div id="pageDescr"></div>
        <div id="newpage">
            <form id="page-form" action="/" method="post">

            </form>
        </div>
        <div class="clear"></div>
        <?php elseif (Admin::$page === 'upload'):
        $dir = isset($_GET['dir']) ? utf8_encode($_GET['dir']) : '';
        $dir = str_replace('\\', '/', $dir);
        $dir = str_replace('..', '', $dir);
        $dir = str_replace('//', '/', $dir);
        $dir = trim($dir, '/');
        $dirs = explode('/', $dir);
        $dirs[count($dirs) - 1] = '';
        $up = implode('/', $dirs);

        ?>
        <div id="uploadwrapper">
            <?php
            if (isset($_POST['uploadfiles']) && isset($_FILES['files'])) {
                require_once(AdminConfig::$frontendDir . '/' . Config::$coreDir . '/classes/Upload.php');
                $upped = Upload::up(AdminConfig::$frontendDir . '/'. Config::$uploadDir . '/' . $dir);
                if ($upped !== true) {
                    foreach ($upped as $error)
                        echo '<span class="error">' . $error . '</span><br/>';
                }
            } elseif (isset($_GET['action']) && $_GET['action'] === 'upload') {
                echo '<span class="error">Failed to upload. Try selecting less or smaller files at once.</span><br/>';
            }
            ?>
            <form id="uploadform" action="index.php?page=upload&action=upload&dir=<?php echo $dir; ?>" method="post" enctype="multipart/form-data">
                <label>Upload files:</label><br/><input type="file" multiple="multiple" name="files[]"/><br/>
                <input type="submit" name="uploadfiles" value="upload">
            </form>
            <div class="clear"></div>
            <?php
            if (isset($_POST['newfolder']) && isset($_POST['name'])) {
                $newdir = str_replace(array('.', '/', '\\'), '', $_POST['name']);
                $created = @mkdir(AdminConfig::$frontendDir . '/' . Config::$uploadDir . '/' . $dir . '/' . $newdir, Config::$newDirMask);
                if ($created === false) {
                    echo '<span class="error">failed to create directory</span>';
                } else {
                    @chmod(AdminConfig::$frontendDir . '/' . Config::$uploadDir . '/' . $dir . '/' . $newdir, Config::$newDirMask);
                }
            }
            ?>
            <form id="newfolderform" action="index.php?page=upload&dir=<?php echo $dir; ?>" method="post" enctype="multipart/form-data">
                <label>New dir:</label><input type="text" name="name"/>
                <input type="submit" name="newfolder" value="create"/>
            </form>
            <div class="clear"></div>
        </div>
        <?php
        $files = glob(AdminConfig::$frontendDir . '/' . Config::$uploadDir . '/' . $dir . '/' . '*');
        $crumbs = trim(str_replace('../', '', AdminConfig::$frontendDir . '/' . Config::$uploadDir . '/' . $dir), '/');
        ?>
        <div id="filesList">
            <?php
            echo '<span id="filecrumbs"><span>Current directory:</span> ' . $crumbs . '</span>';

            if ($dir !== '') {
                echo '<a id="fileup" href="index.php?page=upload&dir=' . $up . '">' . '..' . '</a>';
            }
            $dirString = $fileString = '';
            if ($files !== false) {
                foreach ($files as $file) {
                    $filename = basename($file);
                    if (is_file($file)) {
                        $imgSize = getimagesize($file);
                        $url = str_replace('//', '/', AdminConfig::$frontendDir . '/' . Config::$uploadDir . '/' . $dir . '/' . $filename);
                        $fileString .= '<span class="filewrap"><a data-url="' . $url . '"';
                        $fileString .= ($imgSize !== false ? 'data-width="' . $imgSize[0] . '" data-height="' . $imgSize[1] . '"' : '');
                        $fileString .= ' href="#">&nbsp;&nbsp;' . $filename . '</a><a class="deletefile" data-del="' . trim($dir . '/' . $filename, '/') . '" href="#"></a></span>';
                    } else {
                        $dirString .= '<span class="filewrap">';
                        $dirString .= '<a href="index.php?page=upload&dir=' . $dir . '/' . $filename . '">&gt; ' . $filename . '</a>';
                        $dirString .= '<a class="deletedir" data-del="' . trim($dir . '/' . $filename, '/') . '" href="#"></a></span>';
                        //$dirString .= '<a " href="index.php?page=upload&dir=' . $dir . '/' . $filename . '">&gt; ' . $filename . '</a>';

                    }
                }
            }
            echo $dirString . $fileString;
            ?>
        </div>
        <div id="imageHover"></div>

        <div class="clear"><br/></div>
        <?php endif; ?>
    </div>
</div>
