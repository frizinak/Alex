[[header.html]]
<h1>{{title}}</h1>
<?php if (Cmx::$pageObject['page'] === Config::$homepage): ?>
<form action="<?php echo Cmx::get_page_url(); ?>" method="post">
    <input type="text" name="search"/>
    <input type="submit" value="search"/>
</form>
<?php endif; ?>

<div id="content">
    <?php
    if (isset($_POST['search']) && strlen(trim($_POST['search'])) > 0) {
        $results = Cmx::search($_POST['search'], 'all');
        if (count($results) > 0) {

            echo 'results:<br/>';
            foreach ($results as $page => $result) {
                echo '<p><a href="' . Cmx::get_page_url($page) . '">' . $page . '</a><br/>';
                echo $result[0]['text'] . '</p>';
            }
        } else
        {
            echo 'no results.';
        }
        echo '<br/><br/><br/>';
    }
    $paragraphs = explode("\n\n", Cmx::$pageObject['pageInfo']['tplData']['content']);
    foreach ($paragraphs as $para) {
        $para = explode("\n", $para);
        echo '<p>';
        foreach ($para as $p) {
            echo $p . '<br/>';
        }
        echo '</p>';
    }

    $urls = Cmx::$pageObject['pageInfo']['tplData']['singleimg'];

    foreach ($urls as $url) {
        $img = Cmx::get_img($url[0], 0, 0, true);
        if ($img !== false) {
            echo '<img src="' . $img[0] . '" ' . $img[3] . '/>';
        }
    }

    $urls = Cmx::$pageObject['pageInfo']['tplData']['multiimg'];

    foreach ($urls as $url) {
        $img = Cmx::get_img($url[0], 100, 100, true);
        if ($img !== false) {
            echo '<img src="' . $img[0] . '" ' . $img[3] . '/>';
        }
    }


    ?>
</div>
{{test1}} <br/>
{{test2}} <br/>
[[footer.html]]
