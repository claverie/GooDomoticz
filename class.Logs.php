<?php

require_once __DIR__ . '/config.php';

class Logs {

    private $ident = "";
    
    function __construct($ident="")
    {
        $this->ident = $ident;
    }
    
    function Debug($msg)
    {
        if (getenv("DEBUG")!="") echo "%".$this->ident."% ".date("Y-m-d g:i.s")." $msg"."\n";
    }
    
    function Info($msg)
    {
        $this->Trace($msg);
    }
    
    function Trace($msg)
    {
        $this->Write($msg);
    }

    function Error($msg)
    {
        $this->Write($msg, LOG_ERR);
    }
    
    function Write($msg, $lvl=LOG_NOTICE)
    {
        syslog($lvl, "(".$this->ident.") ".$msg);
        if (getenv("DEBUG")!="") echo ($lvl==LOG_NOTICE?"-".$this->ident."-":"*".$this->ident."*")." ".date("Ymd HM T")." $msg"."\n";
    }

    function Notify($msg, $type="SMS")
    {
        if (file_exists(SMS_CREDENTIALS_FILE)) {
            $access = json_decode(file_get_contents(SMS_CREDENTIALS_FILE), true);
        } else {
            $this->Error("No credential for sending SMS");
            return false;
        }
        $request = sprintf(URL_SMS, $access['user'], $access['password'], urlencode($msg));
        file_get_contents($request);
        return true;
    }
}