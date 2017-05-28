<?php
require_once __DIR__ . "/config.php";
require_once __DIR__ . "/class.Logs.php";

class Domoticz {

    private $log = null;
    private $refCommand = array();
    public $DeviceList = null;
    
    function __construct()
    {
        $this->log = new Logs("Domoticz");
        
        $this->refCommand = array(
            "blinds"  => array( 
                "up"   => "Off",  "open"  => "Off",
                "down" => "On",   "close" => "On",
                "my"   => "Stop", "mid"   => "Stop"
                 ),
            "push on button"  => array( "push" => "On"),
            "on/off" => array( "on" => "On", "off" => "Off" ),
            "smoke detector" => array("alarm" => "On")
        );

        $this->InitDevices();
    }
    
    function ConvertOrder($device, $order)
    {
        $baseCommand = "type=command&param=switchlight&idx=%d&switchcmd=%s";
        $command=false;
        
        $device = strtolower($device);
        $order = strtolower($order);

        $IDX = false;
        $COM = false;
        $TYP = false;
        
        if (isset($this->DeviceList['byName'][$device])) {
            $IDX = $this->DeviceList['byName'][$device]->idx;
        } else if (isset($this->DeviceList['byId'][$device])) {
            $IDX = $this->DeviceList['byId'][$device]->idx;
        } else {
            $this->log->Error("Unknown device $device");
            return false;
        }
        
        $TYP =  strtolower($this->DeviceList['byId'][$IDX]->SwitchType);
        if (isset($this->refCommand[$TYP])) {
            if (isset($this->refCommand[$TYP][$order])) {
                $COM = $this->refCommand[$TYP][$order];
            } else {
                $this->log->Error("Command $order for device type $TYP");
            }
        } else {
            $this->log->Error("Unknown device type $TYP");
            return false;
        }
        
        $this->log->Debug("Device:$device Type:$TYP Order:$order -> IDX = $IDX TYPE = $TYP COMM = $COM"); 
  
        $command = sprintf($baseCommand, $IDX, $COM);
        $this->log->Debug("Command [$command]");
        return $command;
    }

    function SendCommand($command="type=command&param=getSunRiseSet", $args=array())
    {
        $command = "http://".DOMO_HOST."/json.htm?".vsprintf($command, $args);
        $this->log->Debug("Send command : $command");
        $json = file_get_contents($command);
        $res = json_decode($json);
        return ( [ 'status' => ($res->status=="OK"), 'content' => (property_exists($res,'result') ? $res->result : false ) ] );
    }
    
    function InitDevices()
    {
        //$res = $this->SendCommand("type=command&param=getlightswitches");
        //foreach ($res['content'] as $device) {
        //    $this->DeviceList['byName'][strtolower($device->Name)] = $device;
        //    $this->DeviceList['byId'][$device->idx] = $device;
        //}
        $res = $this->SendCommand("type=devices&filter=light&used=true");
        if ($res) {
            foreach ($res['content'] as $device) {
                $this->DeviceList['byName'][strtolower($device->Name)] = $device;
                $this->DeviceList['byId'][$device->idx] = $device;
            }
        } else {
            $this->log->Error("InitDevice: error in Domoticz command");
        }
    }

    function GetDevices()
    {
        return $this->DeviceList;
    }

    function GetUserVars()
    {
        $vars = array();
        $res = $this->SendCommand("type=command&param=getuservariables");
        $value = function($var) {
            switch ($var->Type) {
            case "0" : return (integer)$var->Value; break;
            case "1" : return (float)$var->Value; break;
            default  : return "$var->Value";
            }
        };
       foreach ($res['content'] as $var) {
           $vars[$var->Name] = $value($var);
       }
       return $vars;
    }
}