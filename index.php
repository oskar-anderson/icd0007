<?php

require_once ("Person.php");
require_once("vendor/tpl.php");
const USERNAME = "kaande";
const PASSWORD = "fb85";
const URL = "mysql:host=db.mkalmo.xyz;dbname=kaande";


$cmd = "index.php?cmd=show_list_page";
if (isset($_GET["cmd"])) {
    $cmd = $_GET["cmd"];
}

$footerAndTitle = array(
    'title' => 'HW_05',
    "footer" => "ICD0007 Homework_05"
);

function showAddPage() {
    print renderTemplate('add.html', $GLOBALS["footerAndTitle"]);
}

function addData() {
    $firstName = urlencode($_POST["firstName"]);
    $lastName = urlencode($_POST["lastName"]);
    $phone1 = urlencode($_POST["phone1"]);
    $phone2 = urlencode($_POST["phone2"]);
    $phone3 = urlencode($_POST["phone3"]);

    $phones = [];
    array_push($phones, $phone1);
    if ($phone2) {
        array_push($phones, $phone2);
    }
    if ($phone3) {
        array_push($phones, $phone3);
    }

    $connection = new PDO(URL, USERNAME, PASSWORD);
    $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $statement1 = $connection->prepare(
        "insert into persons (person_first_name, person_last_name)
                   values (:person_first_name, :person_last_name)");

    $statement1->bindValue(":person_first_name", $firstName);
    $statement1->bindValue(":person_last_name", $lastName);
    $statement1->execute();
    $personLastId = $connection->lastInsertId();

    for ($i= 0; $i < count($phones); $i++) {
        $statement2 = $connection->prepare(
            "insert into phones (person_id_FK, phone_number) values (:person_id, :phone_number)");
        $statement2->bindValue(":person_id", $personLastId);
        $statement2->bindValue(":phone_number", $phones[$i]);
        $statement2->execute();
    }
    header("Location: ?cmd=show_list_page");
}

function getListPageData() {
    $connection = new PDO(URL, USERNAME, PASSWORD);
    $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $statement = $connection->prepare("select * from persons
                                    LEFT JOIN phones ON 
                                    kaande.phones.person_id_FK = kaande.persons.person_id_PK
                                    ORDER BY person_id_PK, phones.phone_id_PK");
    // how can you add "AND phones.phone_number != "" ?"
    $statement->execute();

    $statement2 = $connection->prepare("select person_id_FK, phone_number from phones");
    $statement2->execute();
    $phones = $statement2->fetchAll(PDO::FETCH_ASSOC);

    $persons = [];
    $personAlreadyInList = [];
    foreach ($statement as $row) {
        $personIdPK = $row["person_id_PK"];

        if (personAlreadyInListCheck($personAlreadyInList, $personIdPK)) {
            continue;
        }
        array_push($personAlreadyInList, $personIdPK);

        $phonesForUser = "";
        $firstPhonePrefix = "";
        foreach ($phones as $phoneRow) {
            $personIdFK = $phoneRow["person_id_FK"];
            if ($personIdPK === $personIdFK) {
                $phoneNumber = urldecode($phoneRow["phone_number"]);
                if (! $phoneNumber or is_null($phoneNumber)) {
                    break;
                }
                $phonesForUser .= $firstPhonePrefix . $phoneNumber;
                $firstPhonePrefix = " | ";
            }
        }

        $firstName = urldecode($row["person_first_name"]);
        $lastName = urldecode($row["person_last_name"]);
        $person = new Person($firstName, $lastName, $phonesForUser, $personIdPK);
        $persons[$personIdPK] = $person;
    }
    // $persons += $GLOBALS["footerAndTitle"];  no idea how to get this from the template

    $data = ['persons' => $persons];
    print renderTemplate("list.html", $data);
}


function personAlreadyInListCheck($personInList, $personIdPK) {
    foreach ($personInList as $personId) {
        if ($personId === $personIdPK) {
            return true;
        }
    }
    return false;
}


if ($cmd === "show_add_page") {
    showAddPage();
} elseif ($cmd === "add") {
    addData();
} else {
    getListPageData();
}