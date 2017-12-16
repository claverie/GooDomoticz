<?php

// Google access

require_once __DIR__ . '/vendor/autoload.php';

define('APPLICATION_NAME', 'Domoticz Google Calendar Management');
define('SYNC_TOKEN_PATH', __DIR__ . '/.sync.token');
define('CREDENTIALS_PATH', '~/.credentials/google.json');
define('DOMOTICZ_CONFIGURATION', __DIR__ . '/.domoticz.json');
define('CLIENT_SECRET_PATH', __DIR__ . '/secret.json');
define('SCOPES', implode(' ', array(
  Google_Service_Calendar::CALENDAR_READONLY)
));

// Interval (hours) to get event, must be greather then script execution interval
define('GCAL_RETRIEVING_INTERVAL_SECS', 7*24*3600);

// Interval to process ordersxs (secondes)
define('ORDERS_PROCESSING_INTERVAL_SEC',300);

// Local storage for event
define('EVENT_LOCAL_STORAGE', __DIR__ . '/events.json');

// DB file for orders
define('ORDERS_DB_FILE', __DIR__ . '/domoticz-orders.sqlite3');

// Last orders processing
define("LAST_RUN", __DIR__ . "/.last-run");

// SMS
define("URL_SMS", "https://smsapi.free-mobile.fr/sendmsg?user=%s&pass=%s&msg=%s");
define('SMS_CREDENTIALS_FILE', '/home/pi/.credentials/sms.json');