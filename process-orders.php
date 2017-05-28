#!/usr/bin/php
<?php

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/class.Logs.php';
require_once __DIR__ . '/class.Db.php';
require_once __DIR__ . '/class.Domoticz.php';
require_once __DIR__ . '/class.Tools.php';


function usage($proc) {
    echo "\nusage : ${proc} [-s YYYY-MM-DD HH:MM]\n";
    echo "\n            -s simulate execution at the given date\n\n";
}

$now  = time();
$interval = ORDERS_PROCESSING_INTERVAL_SEC;

$log = new Logs(basename($argv[0]));

$options = getopt("s:h");

if (isset($options['h'])) {
    usage($argv[0]);
    exit(1);
}

$simulation = false;
$run_date = ""; 
if (isset($options['s'])) {
    $simulation = true;
    $run_date = $options['s'];
    $now = strtotime($run_date);
    $last = $now - $interval;
    $log->Info("Simulation run time, start at $run_date ($now)");
} else {
    $now = time();
    $last = Tools::getLastRunDate();
    Tools::setLastRunDate($last);
}    

$db = new Db();
if (!$db) exit(1);

$orders = array();

$query = sprintf("SELECT * from orders where Time > %d AND Time <= %d ".($simulation ? "" : "AND Status is NULL ORDER BY Time").";",$last,$now);
$res = $db->ExecQuery($query);

$orders = array();
while ($t = $res->fetchArray(SQLITE3_ASSOC)) {
    array_push($orders, $t);
}
$log->Info("From:".strftime('%Y-%m-%d %T',$last)." To: ".strftime('%Y-%m-%d %T',$now)." ($interval sec) : ".count($orders)." order(s) to process");

$domoticz = new Domoticz();

foreach ($orders as $order) {
    $log->Trace(sprintf("%s[%s] run at programmed time : %s", $order['Device'],$order['Order'],date("Y-m-d g:i.s",$order['Time'])));
    $command = $domoticz->ConvertOrder($order['Device'], $order['Order']);

    if ($command) {
        $log->Debug(sprintf("%s[%s] %s", $order['Device'],$order['Order'],$command));
        $status = $domoticz->SendCommand($command);
        $options = explode('+', $order['Options']);
        if (in_array("sms", $options)) {
            $log->Notify(sprintf("Domo: %s[%s]\nDomo: Prog=%s\nDomo: Status=%s",$order['Device'],$order['Order'],date("Y-m-d g:i.s",$order['Time']),($status["status"]?"OK":"KO")));
        }
        if ($status && !$simulation) {
            $query = sprintf("DELETE FROM orders WHERE Id='%s';", $order['Id']);
            $r = $db->ExecQuery($query);
            $log->Trace(sprintf("%s[%s] order has been removed",$order['Device'],$order['Order']));
        }
    }
}
$db->Close();

exit(0);
