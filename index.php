<?php

require_once("vendor/tpl.php");



$cmd = "index.php?cmd=show_list_page";
if (isset($_GET["cmd"])) {
    $cmd = $_GET["cmd"];
}

$data = array(
    'title' => 'HW_04',
    "footer" => "ICD0007 Homework_04",
);

function showAddPage($data) {
    print renderTemplate('add.html', $data);
}

function addData($data) {
    $firstName = urlencode($_POST["firstName"]);
    $lastName = urlencode($_POST["lastName"]);
    $phone = urlencode($_POST["phone"]);

    $file = fopen("data.txt", "a");
    fwrite($file, $firstName);
    fwrite($file, "\n");
    fwrite($file, $lastName);
    fwrite($file, "\n");
    fwrite($file, $phone);
    fwrite($file, "\n");
    fclose($file);
    showListPage($data);
}

function showListPage($data) {
    $file = fopen("data.txt", "r");
    if ($file) {
        $counter = 1;
        while (!feof($file)) {

            $eesnimi = fgets($file);
            $perekonnanimi = fgets($file);
            $telefon = fgets($file);

            $key = "eesNimi" . strval($counter);
            $data += array("$key" => urldecode($eesnimi));
            $key = "perekonnaNimi" . strval($counter);
            $data += array("$key" => urldecode($perekonnanimi));
            $key = "telefon" . strval($counter);
            $data += array("$key" => urldecode($telefon));
            $counter++;

        }
    }
    fclose($file);

    print renderTemplate("list.html", $data);
}

if ($cmd === "show_add_page") {
    showAddPage($data);
} elseif ($cmd === "add") {
    addData($data);
} else {
    showListPage($data);
}

