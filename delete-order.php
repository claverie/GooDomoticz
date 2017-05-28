#!/usr/bin/env php
<?php

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/class.Logs.php';
require_once __DIR__ . '/class.Db.php';

function usage($prog) {
    echo "\nusage : $prog (-i ID|-a) \n";
    echo "\n   Delete order that have identifier ID (-i) or all (-a)\n\n";
}

$Id = null;
$All = false;
$options = getopt("i:ah");

$log = new Logs(basename($argv[0]));

if (!isset($options['i'])) {
    if (isset($options['a'])) {
        $All = true;
    } else {
        $log->Error("Option -i needed");
        usage($argv[0]);
        exit(1);
    }
} else {
    $Id = $options['i'];
}

$db = new Db();
if (!$db) exit(1);
$log->Trace("Deleting ".($All ? "all orders":"order $Id"));
$query = "DELETE from orders ".($Id!=null? sprintf(" where Id='%s';", $Id) : "").";";
$db->ExecQuery($query);
$db->Close();

exit(0);
