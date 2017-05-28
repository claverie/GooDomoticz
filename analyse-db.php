#!/usr/bin/env php
<?php

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/class.Logs.php';
require_once __DIR__ . '/class.Db.php';
require_once __DIR__ . '/class.Tools.php';

$last = Tools::getLastRunDate();
echo "-- Last run at ".strftime("%d/%m/%Y %H:%M:%S %Z",$last)."\n"; 


$db = new Db();
if (!$db) exit(1);

$query = sprintf("SELECT * from orders ORDER BY Time;");
$res = $db->ExecQuery($query);

$orders = array();
while ($t = $res->fetchArray(SQLITE3_ASSOC)) {
    array_push($orders, $t);
}

$db->Close();

echo "-- Registered events : \n";
foreach ($orders as $order) {
    echo sprintf("   [%s] %s > %s [%s]\n",$order['Id'], strftime('%d/%m/%Y %H:%M %Z',$order['Time']),$order['Device'],$order['Order'],$order['Status']);
}
exit(0);
