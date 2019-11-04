<?php

require_once("vendor/tpl.php");
const USERNAME = "kaande";
const PASSWORD = "fb85";
const URL = "mysql:host=db.mkalmo.xyz;dbname=kaande";


$cmd = "index.php?cmd=show_list_page";
if (isset($_GET["cmd"])) {
    $cmd = $_GET["cmd"];
}

$data = array(
    'title' => 'HW_05',
    "footer" => "ICD0007 Homework_05",
);

function showAddPage($data) {
    print renderTemplate('add.html', $data);
}

# adds data to db
function addData($data, $loadListPageFromLocalData) {
    $firstName = urlencode($_POST["firstName"]);
    $lastName = urlencode($_POST["lastName"]);
    $phone = urlencode($_POST["phone"]);

    $connection = new PDO(URL, USERNAME, PASSWORD);
    $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $statement1 = $connection->prepare(
        "insert into persons (person_first_name, person_last_name)
                   values (:person_first_name, :person_last_name)");

    $statement1->bindValue(":person_first_name", $firstName);
    $statement1->bindValue(":person_last_name", $lastName);
    $statement1->execute();
    $personLastId = $connection->lastInsertId();

    $statement2 = $connection->prepare(
        "insert into phones (person_id_FK, phone_number) values (:person_id, :phone_number)");
    $statement2->bindValue(":person_id", $personLastId);
    $statement2->bindValue(":phone_number", $phone);
    $statement2->execute();
    if (!$loadListPageFromLocalData){
        getListPageData($data);
    }
}

# gets data from db
function getListPageData($data) {
    $connection = new PDO(URL, USERNAME, PASSWORD);
    $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $statement = $connection->prepare("select * from persons, phones where
                                    kaande.phones.person_id_FK = kaande.persons.person_id_PK
                                    ORDER BY person_id_PK");
    $statement->execute();

    $arr = $statement->fetchALL(PDO::FETCH_ASSOC);
    $counter = 0;
    foreach ($arr as $line) {
        $counter++;
        $eesnimi = $line["person_first_name"];
        $perekonnanimi = $line["person_last_name"];
        $telefon = $line["phone_number"];
        #var_dump($line);
        #echo "<br>";
        #echo $eesnimi;
        #echo "<br>";
        #echo $perekonnanimi;
        #echo "<br>";
        #echo $telefon;
        #echo "<br>";

        $data = listPageTableLoader($data, $counter, $eesnimi, $perekonnanimi, $telefon);
    }
    print renderTemplate("list.html", $data);
}

# old unused code
class localData {

    # add data locally
    public function addDataLocally($data, $loadListPageFromLocalData) {
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

        if ($loadListPageFromLocalData) {
            $local = new LocalData;
            $local->showListPage($data);
        }
    }

    # stores data locally
    public function showListPage($data) {
        $file = fopen("data.txt", "r");
        if ($file) {
            $counter = 1;
            while (!feof($file)) {

                $eesnimi = fgets($file);
                $perekonnanimi = fgets($file);
                $telefon = fgets($file);

                $data = listPageTableLoader($data, $counter, $eesnimi, $perekonnanimi, $telefon);
                $counter++;
            }
        }
        fclose($file);

        print renderTemplate("list.html", $data);
    }

}

function listPageTableLoader($data, $counter, $eesnimi, $perekonnanimi, $telefon) {
    $key = "eesNimi" . strval($counter);
    $data += array("$key" => urldecode($eesnimi));
    $key = "perekonnaNimi" . strval($counter);
    $data += array("$key" => urldecode($perekonnanimi));
    $key = "telefon" . strval($counter);
    $data += array("$key" => urldecode($telefon));
    return $data;
}

$loadListPageFromLocalData = false; // false - load from db, true - load from local txt file.
//Save to both either way.
if ($cmd === "show_add_page") {
    showAddPage($data);
} elseif ($cmd === "add") {
    $local = new LocalData;
    $local->addDataLocally($data, $loadListPageFromLocalData);
    addData($data, $loadListPageFromLocalData);
} elseif ($cmd === "debug") {   //This exist for debugging. Replace with a function call.
    //Change List.html id="debug" to visible.
    throw new Exception("This exist for debugging. Replace with a function call.");
} else {
    if (! $loadListPageFromLocalData) {
        getListPageData($data);
    }
    else {
        $local = new LocalData;
        $local->showListPage($data);
    }
}