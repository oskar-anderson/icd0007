<?php


class Person
{
    public $eesNimi;
    public $perekonnaNimi;
    public $telefon;
    public $id;

    public function __construct($eesNimi, $perekonnaNimi, $telefon, $id) {
        $this->eesNimi = $eesNimi;
        $this->perekonnaNimi = $perekonnaNimi;
        $this->telefon = $telefon;
        $this->id = $id;
    }

    public function __toString() {
        return "Person{id: $this->id, firstName: $this->eesNimi,
         lastName: $this->perekonnaNimi, phone: $this->telefon}";
    }
}
