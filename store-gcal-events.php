#!/usr/bin/env php
<?php

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/class.Logs.php';
require_once __DIR__ . '/class.Tools.php';
require_once __DIR__ . '/class.Db.php';

$Log = new Logs(basename($argv[0]));

$orders = array();
$cancelled = array();

$json_events = file_get_contents(EVENT_LOCAL_STORAGE);
$events = json_decode($json_events, true);
foreach ($events as $event) {
    $new_orders = ParseCalEvent($event);
    foreach ($new_orders as $o) {
        if ($o['status'] == "cancelled") {
            $cancelled[] = $o;
        } else {
            $o['htime'] = strftime("%d/%m/%Y %H:%M",$o['time']);
            $o['json'] = json_encode($event);
            $orders[] = $o;
        }
        $Log->Debug($event['id']."/".$event['summary']."> status : ".$event['status']);
    }
}

$db = new Db();
if (!$db) exit(1);

if (count($orders) > 0) {
    foreach ($orders as $k => $order) {
        $query = sprintf(
            "INSERT OR REPLACE INTO orders ('Id', 'Device', 'Order', 'Time', 'Status', 'Htime', 'Json', 'Options') "
            ."    VALUES ('%s', '%s', '%s', %d,  NULL, '%s', '%s', '%s');",
            $order['id'],
            $order['device'],
            $order['order'],
            $order['time'],
            $order['htime'], 
            $order['json'],
            $order['options']
        );
        $res = $db->ExecQuery($query); 
    }
}

if (count($cancelled) > 0) {
    foreach ($cancelled as $k => $order) {
        $query = sprintf("DELETE FROM orders WHERE Id='%s';", $order['id']);        
        $db->ExecQuery($query); 
    }
}

// Remove old entries
$last = Tools::getLastRunDate();
$query = sprintf("DELETE FROM orders WHERE Time < %d OR Status not NULL;", $last);
$Log->Info("Clear order before ".date('c', $last));
$db->ExecQuery($query);

$db->Close();

exit(0);


function ParseCalEvent($event)
{
    $orders = array();
    
    $regexp = '/^(?P<device>[^\[]+)\[(?P<onstart>\w+)(\>(?P<onend>\w+))?\](?P<options>.*)/i';
    $status = preg_match($regexp, trim($event['summary']), $m);
    if (!empty($m['onstart']) && !empty($m['device'])) {
        $devices = explode(",",$m['device']);
        foreach ($devices as $dev) {
            $orders[] = array(
                "id"      => $event['id'],
                "device"  => $dev,
                "order"   => $m['onstart'],
                "time"    => strtotime($event['startTime']),
                "status"  => $event['status'],
                "options" => strtolower($m['options'])
            );
            if (!empty($m['onend'])) {
                $orders[] = array(
                    "id"      => $event['id'],
                    "device"  => $dev,
                    "order"   => $m['onend'],
                    "time"    => strtotime($event['endTime']),
                    "status"  => $event['status'],
                    "options" => strtolower($m['options'])
                );
            }
        }
    }
    return $orders;
}