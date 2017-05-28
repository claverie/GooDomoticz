<?php

require_once __DIR__ . '/../class.Domoticz.php';

$domo = new Domoticz();
$devices = $domo->GetDevices();
//var_dump($devices);
foreach ($devices['byId'] as $idx => $dev) {
    echo sprintf("[%4d] %30s %30s/%s\n", $idx, $dev->Name, $dev->Type, $dev->SwitchType);
}
exit(0);