<?php


class Person
{
    public $firstName;
    public $lastName;
    public $phone;
    public $id;

    public function __construct($eesNimi, $perekonnaNimi, $telefon, $id) {
        $this->firstName = $eesNimi;
        $this->lastName = $perekonnaNimi;
        $this->phone = $telefon;
        $this->id = $id;
    }

    public function __toString() {
        return "Person{id: $this->id, firstName: $this->firstName,
         lastName: $this->lastName, phone: $this->phone}";
    }
}
