<?php


    //https://unix.stackexchange.com/questions/310752/how-do-i-configure-wpa-supplicant-conf-for-wps-push-button
    //https://w1.fi/cgit/hostap/plain/wpa_supplicant/README-WPS
    //Questo sembra quello giusto:
    //    https://roidelapluie.be/blog/2015/12/27/wi-fi-protected-setup/
    //
    //wps premuto:
    //    d4:60:e3:07:de:d3	2412	-57	[WPA2-PSK-CCMP][WPS-PBC][ESS]	TIM-06501189
    //normale:
    //d4:60:e3:07:de:d3	2412	-59	[WPA2-PSK-CCMP][WPS][ESS]	TIM-06501189    
    //
    //per collegare: wps_pbc ssid non mettere ssid ma solo: wps_pbc 
    // con pin:
    // https://askubuntu.com/questions/120367/how-to-connect-to-wi-fi-ap-through-wps

    //ESS extended service set
    //https://www.techopedia.com/definition/24968/extended-service-set-ess

    define('EMPTY_SSID_REPlACE_STRING', '#');

    class NetworkListItem{
        public $network_id = '';
        public $ssid = ''; 
        public $bssid = '';
        public $flags = '';

        public $priority = 0;
        public $disabled = false;
        public $psk = ''; // password for all mode
        public $scanSSID = false;

        public $protocol = '';
        public $key_mgmt = '';
        //public $wep_key0 = ''; psk
        public $wep_tx_keyidx = '';
        public $identity = '';
        public $eap = '';
    }



    
    class WpaCliException extends Exception {
      public function errorMessage() {
        //error message
        $errorMsg = $this->getMessage();
        return $errorMsg;
      }
    }


    function getStatusResult(){
        $status_info = [];
        $known_return = [];
        $return_err = 0;
        //exec('python '.WPA_PY_SCRIPT_PATH.'/wpaGateway.py STATUS' , $known_return);
        exec('wpa_cli_py '.RASPI_WIFI_CLIENT_INTERFACE.' STATUS 2>&1' , $known_return, $return_err);
        if ($return_err != 0){
            throw new WpaCliException(implode("\n",$known_return), $return_err);
            //$known_return = array();
        }

        foreach($known_return as $line) {
            $lineArr = preg_split('/[=]+/', trim($line));
            $status_info[trim($lineArr[0], ' ')] = trim($lineArr[1], ' ');
        }
        return $status_info;
    }

    function getListNetworksItemResult(){

        $networks_list = [];
        $known_return = [];
        $return_err = 0;
        exec('wpa_cli_py '.RASPI_WIFI_CLIENT_INTERFACE.' list_networks 2>&1' , $known_return, $return_err);
        if ($return_err != 0){
            throw new WpaCliException(implode("\n",$known_return), $return_err);
        }

        array_shift($known_return);

        foreach($known_return as $line) {
            if (trim($line) == 'network id / ssid / bssid / flags') continue;
            if (trim($line) == '') continue;
            $lineArr = preg_split('/[\t]/', trim($line));

            $ssid = trim($lineArr[1], ' ');
            if ($ssid == '') $ssid = EMPTY_SSID_REPlACE_STRING;

            $network_item = new NetworkListItem();
            $network_item->bssid = trim($lineArr[2]);
            if (count($lineArr)==4)
                $network_item->flags = trim($lineArr[3], ' ');
            $network_item->network_id = trim($lineArr[0], ' ');
            $network_item->ssid = $ssid;

            array_push($networks_list, $network_item);
        }
        return $networks_list;
    }

    function getScanResult(){


        class networkItem{
            public $configuration_data = null;
            public $ssid = ''; 
            public $bssid = '' ;
            public $flags = '';
            public $frequency = '';
            public $signal = '';
            public $channel = '';
            public $protocol = '';
            public $configured = false;
            public $connected = false;

        }


        $networks_list = array();
        //exec( 'python '.WPA_PY_SCRIPT_PATH.'/wpaScanResults.py',$scan_return );
        exec( 'wpa_cli_py_scan_results '.RASPI_WIFI_CLIENT_INTERFACE.' 2>&1',$scan_return, $return_err );

        if ($return_err != 0){
            throw new WpaCliException(implode("\n",$scan_return), $return_err);
            //$known_return = array();
        }
        
        array_shift($scan_return);

        foreach($scan_return as $line) {
            if (trim($line) == '\n') continue;
            if (trim($line) == 'bssid / frequency / signal level / flags / ssid') continue;

            $network = new networkItem();

            $lineArr = preg_split('/[\t]+/', trim($line));
            $network->bssid = trim($lineArr[0], ' ');
            $network->frequency = trim($lineArr[1], ' ');
            $network->signal = trim($lineArr[2], ' ');
            $network->flags = trim($lineArr[3], ' ');

            $ssid = trim($lineArr[4], ' ');
            if ($ssid == '') $ssid = EMPTY_SSID_REPlACE_STRING;
            $network->ssid = $ssid;

            $network->configured = false;
            $network->channel = ConvertToChannel(trim($lineArr[1], ' '));
            $network->protocol = ConvertToSecurity($network->flags);

            array_push($networks_list, $network);
        }

        return $networks_list;
    }

    function connectToNetwork($network_id){
        $return_value = null;
        //exec ( 'python '.WPA_PY_SCRIPT_PATH.'/wpaConnect.py '.strval($network_id),$return_value, $return_err );
        exec( 'wpa_cli_py_connect_to_network '.RASPI_WIFI_CLIENT_INTERFACE.' '.strval($network_id).' 2>&1', $return_value, $return_err );
        if ($return_err != 0){
            throw new WpaCliException(implode("\n",$return_value), $return_err);
        }

        return $return_value;
    }    

    function wpsPbcConnect(){
        $return_value = null;
        exec( 'wpa_cli_py_wps_connect_to_network '.RASPI_WIFI_CLIENT_INTERFACE.' '.' 2>&1', $return_value, $return_err );
        if ($return_err != 0){
            throw new WpaCliException(implode("\n",$return_value), $return_err);
        }

        return $return_value;
    }    
    
    function wpsPinConnect($pin){
        $return_value = null;
        exec( 'wpa_cli_py_wps_connect_to_network '.RASPI_WIFI_CLIENT_INTERFACE.' -p '.$pin.' 2>&1', $return_value, $return_err );
        if ($return_err != 0){
            throw new WpaCliException(implode("\n",$return_value), $return_err);
        }

        return $return_value;
    }    
    
    
    
    function reconfigure(){
        $return_value = null;
        
        exec( 'wpa_cli_py '.RASPI_WIFI_CLIENT_INTERFACE.' reconfigure'.' 2>&1',$return_value, $return_err );
        if ($return_err != 0){
            throw new WpaCliException(implode("\n",$return_value), $return_err);
        }

        return $return_value;
    }    
    
    function removeNetwork($network_id){
        $return_value_out = array();
        array_push($return_value_out, 'REMOVE_NETWORK '.strval($network_id));
        $return_value = array();
        //exec ( 'python '.WPA_PY_SCRIPT_PATH.'/wpaGateway.py remove_network '.strval($network_id),$return_value);
        exec( 'wpa_cli_py '.RASPI_WIFI_CLIENT_INTERFACE.' remove_network '.strval($network_id).' 2>&1',$return_value, $return_err );
        error_log($network_id.' '.implode("\n",$return_value).' '.strval($return_err));
        $return_value_out = array_merge($return_value_out,$return_value);
        if ($return_err != 0){
            throw new WpaCliException(implode("\n",$return_value_out), $return_err);
        }

        $return_value1 = array();
        array_push($return_value_out, 'SAVE_CONFIG');
        exec( 'wpa_cli_py '.RASPI_WIFI_CLIENT_INTERFACE.' save_config'.' 2>&1',$return_value1, $return_err1 );
        error_log($network_id.' '.implode("\n",$return_value1).' '.strval($return_err1));

        $return_value_out = array_merge($return_value_out,$return_value1);
        
        if ($return_err1 != 0){
            throw new WpaCliException(implode("\n",$return_value_out), $return_err1);
        }
        error_log('Out: '.implode("\n",$return_value_out));
        return $return_value_out;
    }

    function getNetworkPriority($network_id){
        $status_info = array();
        //exec('python '.WPA_PY_SCRIPT_PATH.'/wpaGateway.py GET_NETWORK '.strval($network_id).' priority' , $known_return);
        exec( 'wpa_cli_py '.RASPI_WIFI_CLIENT_INTERFACE.' GET_NETWORK '.strval($network_id).' priority 2>&1',$known_return, $return_err );
        if ($return_err != 0){
            throw new WpaCliException(implode("\n",$known_return), $return_err);
        }

        foreach($known_return as $line) {
            $lineArr = preg_split('/[=]+/', trim($line));
            $status_info[trim($lineArr[0], ' ')] = trim($lineArr[1], ' ');
        }
        return $status_info;
    }

    
    //"hp:i:s:b:p:P:r:ek:I:E:",["protocol=","idNet=","ssid=","bssid=","password=","priority=","keyMgmt=","identity=","eap="]
    //wpaCreateNet.py -p (--protocol) -i (--idNet) -s (--ssid) -b (--bssid) -P (--password) -r (--priority) -e -k (--keyMgmt) -I (--identity) -E (--eap)
    function createNetwork($protocol, $idNet, $ssid, $bssid, $password, $priority, $disable, $keyMgmt, $identity, $eap, $scanSsid){
        
        $parameters ='';
        
//        if ($protocol!= null)
//            $parameters .= ' -p '.$protocol;
            
        if ($idNet!= null)
            $parameters .= ' -i '.$idNet;

        if ($ssid!= null && $ssid != EMPTY_SSID_REPlACE_STRING)
            $parameters .= ' -s "'.$ssid.'"';
        
        if ($bssid!= null)
            $parameters .= ' -b '.$bssid;

        if ($password!= null)
            $parameters .= ' -P "'.$password.'"';

        if ($priority!= null)
            $parameters .= ' -r '.$priority;
        
        if ($disable == true)
            $parameters .= ' -d';

        if ($keyMgmt != null)
            $parameters .= ' -k '.$keyMgmt;
        
        if ($identity!= null)
            $parameters .= ' -I "'.$identity.'"';
        
        if ($eap!= null)
            $parameters .= ' -E '.$eap;

        if ($scanSsid == true)
            $parameters .= ' --scanSsid';


        $return_value = null;
        exec ( 'wpa_cli_py_create_network '.$parameters.' '.RASPI_WIFI_CLIENT_INTERFACE.' '.$protocol.' 2>&1' ,$return_value, $return_err );
        error_log($parameters.' '.implode("\n",$return_value).' '.strval($return_err));
        if ($return_err != 0){
            throw new WpaCliException(implode("\n",$return_value), $return_err);
        }
        array_push($return_value, $parameters);
        return $return_value;
    }

    
    function getWpaSupplicantConfData(){

        $networks = array();
        $network = null;

        $return_value = null;        
        // Find currently configured networks
        exec(' sudo cat ' . RASPI_WPA_SUPPLICANT_CONFIG, $return_value);

        foreach ($return_value as $line) {
            if (preg_match('/network\s*=/', $line)) {
                $network = new NetworkListItem();
                $network->protocol = 'OPEN';
            } elseif ($network !== null) {
                if (preg_match('/^\s*}\s*$/', $line)) {
                    //error_log ("Network: ".json_encode($network));
                    array_push($networks, $network);
                } elseif ($lineArr = preg_split('/\s*=\s*/', trim($line))) {
                    //error_log("Line: ".$line);
                    if(!isset($lineArr[1])) continue;
                    $keyName = strtolower($lineArr[0]);
                    $keyValue = trim($lineArr[1], '"');

                    switch ($keyName) {
                        case 'ssid':
                            $network->ssid = $keyValue;
                            break;
                        case 'scan_ssid':
                            $network->scanSSID = $keyValue == 1 ? true : false;
                            break;
                        case 'bssid':
                            $network->bssid = $keyValue;
                            break;
                        case 'priority':
                            $network->priority = $keyValue;
                            break;
                        case 'key_mgmt':
                            $network->key_mgmt = $keyValue;
                            break;
                        case 'wep_key0':
                            $network->psk = $keyValue;
                            $network->protocol = 'WEP';
                            break;
                        case 'wep_tx_keyidx':
                            $network->wep_tx_keyidx = $keyValue;
                            $network->protocol = 'WEP';
                            break;
                        case 'password':
                            $network->psk = $keyValue;
                            break;                        
                        case 'psk':
                            $network->psk = $keyValue;
                            $network->protocol = 'WPA';
                            break;
                        case 'eap':
                            $network->eap = $keyValue;
                            $network->protocol = 'EAP';
                            break;
                        case 'identity':
                            $network->identity = $keyValue;
                            break;
                        case 'disabled':
                            $network->disabled = $keyValue == 1 ? true : false;
                            break;
                    }
                }
            }
        }
        return $networks;
   }
?>