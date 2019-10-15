<?php

require_once("vendor/tpl.php");

$cmd = "show_list_page";
if (isset($_GET["cmd"])) {
    $cmd = $_GET["cmd"];
}

$data = [
    'title' => 'HW_03',
    "footer" => "ICD0007 Homework_03"
];

if ($cmd === "show_add_page") {
    print renderTemplate('add.html', $data);

} else {
    print renderTemplate("list.html", $data);
}

