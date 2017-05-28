<?php

require_once __DIR__ . '/../class.Domoticz.php';

$domo = new Domoticz();
$vars = $domo->GetUserVars();
foreach ($vars as $var => $value) {
    echo "$var = $value \n";
}
exit(0);