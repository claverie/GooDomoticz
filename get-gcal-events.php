#!/usr/bin/env php
<?php

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/class.Logs.php';

if (php_sapi_name() != 'cli') {
  throw new Exception('This application must be run on the command line.');
}

$log = new Logs(basename($argv[0]));

/**
 * Returns an authorized API client.
 * @return Google_Client the authorized client object
 */
function getClient() {
  $client = new Google_Client();
  $client->setApplicationName(APPLICATION_NAME);
  $client->setScopes(SCOPES);
  $client->setAuthConfig(CLIENT_SECRET_PATH);
  $client->setAccessType('offline');

  // Load previously authorized credentials from a file.
  $credentialsPath = expandHomeDirectory(CREDENTIALS_PATH);
  if (file_exists($credentialsPath)) {
    $accessToken = json_decode(file_get_contents($credentialsPath), true);
  } else {
    // Request authorization from the user.
    $authUrl = $client->createAuthUrl();
    printf("Open the following link in your browser:\n%s\n", $authUrl);
    print 'Enter verification code: ';
    $authCode = trim(fgets(STDIN));

    // Exchange authorization code for an access token.
    $accessToken = $client->fetchAccessTokenWithAuthCode($authCode);

    // Store the credentials to disk.
    if(!file_exists(dirname($credentialsPath))) {
      mkdir(dirname($credentialsPath), 0700, true);
    }
    file_put_contents($credentialsPath, json_encode($accessToken));
    printf("Credentials saved to %s\n", $credentialsPath);
  }
  $client->setAccessToken($accessToken);

  // Refresh the token if it's expired.
  if ($client->isAccessTokenExpired()) {
    $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
    file_put_contents($credentialsPath, json_encode($client->getAccessToken()));
  }
  return $client;
}

/**
 * Expands the home directory alias '~' to the full path.
 * @param string $path the path to expand.
 * @return string the expanded path.
 */
function expandHomeDirectory($path) {
  $homeDirectory = getenv('HOME');
  if (empty($homeDirectory)) {
    $homeDirectory = getenv('HOMEDRIVE') . getenv('HOMEPATH');
  }
  return str_replace('~', realpath($homeDirectory), $path);
}

$log->Info("Authenticated, start retrieving events");

// Get the API client and construct the service object.
$client = getClient();
$service = new Google_Service_Calendar($client);

// Print the next 10 events on the user's calendar.
$calendarId = 'primary';

$now = time();

$optParams = array(
    'singleEvents' => true,
    'showDeleted' => true,
    'timeMin' => date('c', $now),
    'timeMax' => date('c', $now+GCAL_RETRIEVING_INTERVAL_SECS)
);

$logprefix = sprintf(
    "[ %s | %s ] ", 
      strftime("%d/%m/%y %H:%M", $now),
      strftime("%d/%m/%y %H:%M", $now+GCAL_RETRIEVING_INTERVAL_SECS)
);

$results = $service->events->listEvents($calendarId, $optParams);
    
if (count($results->getItems()) > 0) {
    $gcalevents = array();
    foreach ($results->getItems() as $event) {
        $event->startTime = $event->start->dateTime;
        if (empty($event->startTime)) {
            $event->startTime = $event->start->date;
        }
        $event->endTime = $event->end->dateTime;
        if (empty($event->endTime)) {
            $event->endTime = $event->end->date;
        }
        $log->Debug(sprintf($logprefix." [%s] %s > %s (%s) %s", $event->id, $event->startTime, $event->endTime, $event->getSummary(), $event->status));
        $gcalevents[] = $event;
    }
    $f = fopen(EVENT_LOCAL_STORAGE, "w");
    fwrite($f, json_encode($gcalevents, JSON_FORCE_OBJECT|JSON_PRETTY_PRINT));
    fclose($f);
}
  $log->Info($logprefix.count($gcalevents)." upcoming events retrieved.");
exit(0);