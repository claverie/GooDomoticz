<?php

require_once __DIR__ . '/config.php';

class Tools {

    static function getLastRunDate() 
    {
        if (is_readable(LAST_RUN)) {
            $last = file_get_contents(LAST_RUN);
        } else {
            $last = time() - ORDERS_PROCESSING_INTERVAL_SEC;
        }   
        return $last;    
    }

    static function setLastRunDate($date) {
        return file_put_contents(LAST_RUN, $date);
    }

}