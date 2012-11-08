<?php
//header('Location: '.Cmx::$pageObject['pageInfo']['tplData']['location']);
header('Location: ' . Cmx::get_page_url(Cmx::$pageObject['pageInfo']['tplData']['location']));

?>
