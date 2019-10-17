<?php
require_once("vendor/tpl.php");
require_once("functions.php");

// siia tuleb front-controller

$cmd = "view";
if (isset($_GET["cmd"])) {
    $cmd = $_GET["cmd"];
}

if ($cmd === "calculate") {
    print_r("arvutamise tulemused");

    $brutoPalk = $_POST["bruto"];
    print $brutoPalk;
} else {
    print_r("kuva");
    // kuvame sisestamise vormi
    $data = ["subTemplate" => "form.html"];
    print renderTemplate("templates/main.html", $data);
}
