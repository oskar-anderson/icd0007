<?php

require_once("vendor/tpl.php");

$cmd = "show_list_page";
if (isset($_GET["cmd"])) {
    $cmd = $_GET["cmd"];
}

if ($cmd === "show_add_page") {
    print renderTemplate('add.html');

} else {
    print renderTemplate("list.html");
}

