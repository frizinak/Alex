[[header.html]]
{{title}}
{{descr}}
{{content}}
[[footer.html]]
<?php
$page = Cmx::get_page();
if (isset($page['subpages'])) {
    foreach ($page['subpages'] as $sub) {
        $url = Cmx::get_page_url($sub);
        $sub = Cmx::get_content($sub);
        echo '<h3><a href="' . $url . '">' . $sub['tplData']['title'] . '</a></h3>';
        echo '<p>' . $sub['tplData']['descr'] . '</p>';
    }
}


?>
<!--<?php print_r(get_defined_vars()); ?>-->
