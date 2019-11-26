<?php

require_once ("Person.php");
require_once("vendor/tpl.php");
const USERNAME = "kaande";
const PASSWORD = "fb85";
const ADDRESS = "mysql:host=db.mkalmo.xyz;dbname=kaande";


function showAddPage() {
    print renderTemplate('add.html', ['btnValue' => 'Lisa']);
}

function verify() {
    $id = isset($_POST['id']) ? $_POST['id'] : '';
    $firstNameBeingEdited = isset($_POST['firstNameBeingEdited']) ?
        $_POST['firstNameBeingEdited'] : '';
    $lastNameBeingEdited = isset($_POST['lastNameBeingEdited']) ?
        $_POST['lastNameBeingEdited'] : '';
    $firstName = $_POST["firstName"];
    $lastName = $_POST["lastName"];
    $phone1 = $_POST["phone1"];
    $phone2 = $_POST["phone2"];
    $phone3 = $_POST["phone3"];
    $personToBeAdded = array(
        "firstName" => $firstName,
        "lastName" => $lastName,
        "phone1" => $phone1,
        "phone2" => $phone2,
        "phone3" => $phone3
    );
    if(!empty($id) && $id > 0){
        $personToBeAdded['id'] = $id;
        $personToBeAdded['btnValue'] = 'Muuda';
        $personToBeAdded['firstNameBeingEdited'] = $firstNameBeingEdited;
        $personToBeAdded['lastNameBeingEdited'] = $lastNameBeingEdited;
    } else {
        $personToBeAdded['btnValue'] = 'Lisa';
    };

    $valid = true;

    if (strlen($firstName) <= 2) {
        $personToBeAdded['error1'] = "Eesnimi peab olema vähemalt 2 tähemärki!";
        $personToBeAdded['error'] = true;
        $valid = false;
    }
    if (strlen($lastName) <= 2) {
        $personToBeAdded['error2'] = "Perekonnanimi peab olema vähemalt 2 tähemärki!";
        $personToBeAdded['error'] = true;
        $valid = false;
    }
    $personToBeAdded['valid'] = $valid;

    return $personToBeAdded;
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

    $connection = new PDO(ADDRESS, USERNAME, PASSWORD);
    $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $statement1 = $connection->prepare( // shouldn't DB be in separate file and included globally?
        "insert into persons (person_first_name, person_last_name)
                   values (:person_first_name, :person_last_name)");

    $statement1->bindValue(":person_first_name", $firstName);
    $statement1->bindValue(":person_last_name", $lastName);
    $statement1->execute();
    $personLastId = $connection->lastInsertId();

    for ($i= 0; $i < count($phones); $i++) {
        $statement2 = $connection->prepare(
            "insert into phones (person_id_FK, phone_number) 
                        values (:person_id, :phone_number)");
        $statement2->bindValue(":person_id", $personLastId);
        $statement2->bindValue(":phone_number", $phones[$i]);
        $statement2->execute();
    }
    header("Location: ?cmd=show_list_page");
}

function showListPage() {
    $connection = new PDO(ADDRESS, USERNAME, PASSWORD);

    $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $statement1 = $connection->prepare("select * from persons");
    $statement1->execute();
    $personsArr = $statement1->fetchAll(PDO::FETCH_ASSOC);

    $statement2 = $connection->prepare("select person_id_FK, phone_number from phones");
    $statement2->execute();
    $phones = $statement2->fetchAll(PDO::FETCH_ASSOC);

    $persons = [];
    foreach ($personsArr as $person) {
        $firstName = urldecode($person["person_first_name"]);
        $lastName = urldecode($person["person_last_name"]);

        $personIdPK = $person["person_id_PK"];
        $phonesForUser = "";
        $firstPhonePrefix = "";
        foreach ($phones as $phone) {
            $phonePersonIdFK = $phone["person_id_FK"];
            if ($personIdPK === $phonePersonIdFK) {
                $phoneNumber = urldecode($phone["phone_number"]);
                if (! $phoneNumber or is_null($phoneNumber)) {
                    break;
                }
                $phonesForUser .= $firstPhonePrefix . $phoneNumber;
                $firstPhonePrefix = " | ";
            }
        }

        $persons[$personIdPK] = new Person($firstName, $lastName, $phonesForUser, $personIdPK);
    }

    $data = ['persons' => $persons];
    print renderTemplate("list.html", $data);
}

function showEditPage($id) {
    $connection = new PDO(ADDRESS, USERNAME, PASSWORD);
    $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $statement = $connection->prepare("select * from persons
                                    WHERE person_id_PK = $id");
    $statement->execute();
    $person = $statement->fetchAll(PDO::FETCH_ASSOC);
    $statement = $connection->prepare("select * from phones
                                    WHERE person_id_FK = $id");
    $statement->execute();
    $phones = $statement->fetchAll(PDO::FETCH_ASSOC);

    $firstName = urldecode($person[0]["person_first_name"]);
    $lastName = urldecode($person[0]["person_last_name"]);

    $phone1 = "";
    $phone2 = "";
    $phone3 = "";

    if (count($phones) >= 1) {
        $phone1 = urldecode($phones[0]["phone_number"]);
    };
    if (count($phones) >= 2) {
        $phone2 = urldecode($phones[1]["phone_number"]);
    };
    if (count($phones) === 3) {
        $phone3 = urldecode($phones[2]["phone_number"]);
    };

    $personToDisplay = array(
        "firstNameBeingEdited" => $firstName,
        "lastNameBeingEdited" => $lastName,
        "firstName" => $firstName,
        "lastName" => $lastName,
        "phone1" => $phone1,
        "phone2" => $phone2,
        "phone3" => $phone3,
        "id" => $id,
        "btnValue" => 'Muuda',
    );
    print renderTemplate('add.html', $personToDisplay);
}


function editExistingPerson($data) {
    $id = isset($data["id"]) ? $data["id"] : 0;
    $firstName = $data["firstName"];
    $lastName = $data["lastName"];
    $phones = [];
    array_push($phones, $data["phone1"]);
    if ($data["phone2"]) {
        array_push($phones, $data["phone2"]);
    }
    if ($data["phone3"]) {
        array_push($phones, $data["phone3"]);
    }

    $connection = new PDO(ADDRESS, USERNAME, PASSWORD);
    $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if($id > 0){
        // Update record
        $statement = $connection->prepare("update persons SET 
            person_first_name = '$firstName', 
            person_last_name = '$lastName' 
            WHERE person_id_PK = $id");
        $statement->execute();

        // Delete record            Perhaps creating a expired column instead would be better
        $statement = $connection->prepare("delete from phones
                                        WHERE person_id_FK = $id");
        $statement->execute();


        for ($i= 0; $i < count($phones); $i++) {
            $statement2 = $connection->prepare(
                "insert into phones (person_id_FK, phone_number)
                            values (:person_id_FK, :phone_number)");
            $statement2->bindValue(":person_id_FK", $id);
            $statement2->bindValue(":phone_number", $phones[$i]);
            $statement2->execute();
        }
    }
    header("Location: ?cmd=show_list_page");

}


$cmd = "index.php?cmd=show_list_page";
if (isset($_GET["cmd"])) {
    $cmd = $_GET["cmd"];
}


if ($cmd === "show_add_page") {
    showAddPage();
} elseif ($cmd === "show_edit_page") {
    $id = $_GET["id"];
    showEditPage($id);
} elseif ($cmd === "modify") {
    // set
    if (isset($_POST["id"]) && $_POST["id"] > 0) {
        $personToBeAdded = verify();
        if ($personToBeAdded['valid']) {
            editExistingPerson($personToBeAdded);
        }
        else {
            print renderTemplate("add.html", $personToBeAdded);
        }
    }
    // add
    else {
        $personToBeAdded = verify();
        if ($personToBeAdded['valid']) {
            addData();
        }
        else {
            print renderTemplate("add.html", $personToBeAdded);
        }
    }
} else {
    showListPage();
}