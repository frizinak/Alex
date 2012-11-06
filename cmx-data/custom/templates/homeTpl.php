[[header.html]]
<h1>{{title}}</h1>

<div id="content">
    <?php
    //aze

    echo nl2br(Cmx::$pageObject['pageInfo']['tplData']['content']);
    /*$paragraphs = explode("\n\n", Cmx::$pageObject['pageInfo']['tplData']['content']);
    //fopen('azeaze','r');
    foreach ($paragraphs as $para) {
        $para = explode("\n", $para);
        echo '<p>';
        foreach ($para as $p) {
            echo $p . '<br/>';
        }
        echo '</p>';
    }*/

    $urls = glob('upload/img???.jpg');
    foreach ($urls as $url) {
        $img = Cmx::get_img($url, 350, 350, true);
        if ($img !== false) {
            echo '<img src="' . $img[0] . '" ' . $img[3] . '/>';
        }
        break;
    }

    /*var_dump(Cmx::$flatPages);
    foreach(Cmx::$flatPages as $page)
    {
        var_dump(Cmx::get_page($page['page']));
        var_dump(Cmx::get_content($page['page']));

    }*/


    ?>
</div>
{{test1}} <br/>
{{test2}} <br/>
[[footer.html]]
