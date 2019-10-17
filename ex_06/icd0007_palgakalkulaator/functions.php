<?php

// siia tuleb funktsioon(id) palga ja maksude arvutamiseks

function calculateNetSalary($brutoPalk) {
    $tulumaksuvabaMiinimum = 500;

    $tootuskindlustusmakse = $brutoPalk * 0.016;
    $kogumispension = $brutoPalk * 0.02;

    $maksustatavTulu = $brutoPalk - $tulumaksuvabaMiinimum - $tootuskindlustusmakse - $kogumispension;

    $tulumaks = 0;

    if ($maksustatavTulu > 0) {
        $tulumaks = $maksustatavTulu * 0.2;
    }

    $netoPalk = $brutoPalk - $tootuskindlustusmakse - $kogumispension - $tulumaks;

    print "Tootuskindlustusmakse: $tootuskindlustusmakse\n";
    print "Kogumispension: $kogumispension\n";
    print "Tulumaks: $tulumaks\n";
    print "Netopalk: $netoPalk\n";

    return ["töötuskindlustusMakse" => $tootuskindlustusmakse,
        "kogumispension" => $kogumispension,
        "tulumaks" => $tulumaks,
        "netopalk" => $netoPalk
    ];
}

function calculateEmployerExpenses($brutoPalk) {
    $sotsMaks = $brutoPalk * 0.33;
    $tooandjaTootusKindlustus = $brutoPalk * 0.008;
    $kogukulu = $brutoPalk + $tooandjaTootusKindlustus + $sotsMaks;

    return ["sotsMaks" => $sotsMaks,
        "tööandjaTöötusKindlustus" => $tooandjaTootusKindlustus,
        "koguKulu" => $kogukulu];
}

$result = calculateEmployerExpenses(1000);
print_r($result);