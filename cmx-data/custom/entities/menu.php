<?php
$menucached = Utils::get_cache("rootmenu" . Cmx::$language . ".htm");
if ($menucached !== false) {
    echo $menucached;
} else {
    render();
}
function render()
{
    ob_start();
    echo 'language: ' . Cmx::$language;

    printSubPages(Cmx::$pages['pages']);
    Utils::save_ob("rootmenu" . Cmx::$language . ".htm", Config::$globalCacheTime);
}

function printSubPages($parent, $url = "")
{
    if (isset($parent['subpages'])) {
        if (count($parent['subpages']) > 0 && (!isset($parent['menu']) || $parent['menu'] === true || $parent['menu'] === "true")) {
            echo '<ul>';
            foreach ($parent['subpages'] as $page) {
                if ((!isset($page['menu']) || $page['menu'] === true || $page['menu'] === "true") && in_array($page['page'], Cmx::$pages['pagesList'])) {
                    //echo '<li><a href="/' . Config::$siteDir . '/' . Utils::htmlent($url) . Utils::htmlent($page['page']) . '">' . Utils::htmlent($page['page']) . '</a>';
                    echo '<li><a href="' . Cmx::get_page_url($page['page']) . '">' . $page['page'] . '</a>';
                    printSubPages($page, $url . $page['page'] . '/');
                    echo '</li>';
                }

            }
            echo '</ul>';
        }
    }
}

?>
