# What ?

Set events into a Google Calendar to pilot blinds and more devices

# Install

1. clone this repository
2. install PHP google/apiclient (composer)

# Logic

1. get-gcal-events.php : retrieve events from google calender
1. store-gcal-events.php : convert google calendar events into local database orders
1. process-orders.php : process orders from the local DB and send Domoticz commands

Each command honours -h option

All configurations elements are set in config.php.

All credentials (Google and SMS) are stored in $HOME/.GooDomotiz.

# Tools

* ./analyse-db.php : display content of the orders DB

# Todo

* temporisation (random drift for hours)
* request notification
* get / set vars

* send orders by mail
