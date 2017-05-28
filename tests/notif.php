<?php

require_once __DIR__ . '/../class.Logs.php';
$log = new Logs();
$log->Notify("Ceci est un message\nsur 2 lignes");
exit(0);