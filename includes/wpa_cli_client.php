<?php

    include_once ('wpa_cli_base_functions.php');
    include_once ('wpa_cli_show_up_down.php');
    include_once ('wpa_cli_show_cancel_network.php');
    include_once ('wpa_cli_show_network_data.php');
    include_once ('wpa_cli_show_scan_result.php');
    //include_once ('wpa_config_file_functions.php');


    //RASPI_WIFI_CLIENT_INTERFACE

  function prepareNetworkData2Show($networks, $networks_list, $connected_bssid){
    $ssid_array = array();
    foreach ($networks as $network) {
          $ssid = $network->ssid;
          $bssid = $network->bssid;

          if ($bssid == $connected_bssid){
              $network->connected = true;
          }

          foreach ($networks_list as $i){
               if ( $ssid == $i->ssid && ($i->bssid == $bssid || $i->bssid == 'any') ){
                  $network->configured = true;
                  $network->configuration_data = $i;
              }
          }

          $ssid_array[$ssid][$bssid] = $network;
    }
    return $ssid_array;
  }

function DisplayWPACliConfig(){
  $status = new StatusMessages();

    if ( (isset($_POST['add']) || isset($_POST['update'])|| isset($_POST['addNetwork'])  ) && CSRFValidate() ) {
        try{
            $net = null;
            if (isset($_POST['addNetwork'])){

                $p     = isset($_POST['protocol'])  ? strval($_POST['protocol'])  : null;
                $ssid  = isset($_POST['ssid'])      ? strval($_POST['ssid'])      : null;
                $psk   = isset($_POST['psk'])       ? strval($_POST['psk'])       : null;
                $pri   = isset($_POST['priority'])  ? strval($_POST['priority'])  : null;
                $scs   = isset($_POST['scan_ssid']) ? strval($_POST['scan_ssid']) : null;
                $bssid = isset($_POST['bssid'])     ? strval($_POST['bssid'])     : null;
                
                $en    = isset($_POST['enabled'])   ? 
                        ((strval($_POST['enabled'])=='on')? true : null) 
                                                                                  : null;

//                $bssid = (isset($_POST['bssid']) && strval($_POST['bssid']) != 'any')
//                                                    ? strval($_POST['bssid'])                          
//                                                                                  : null;
                


                //Per update network
                $idNet    = (isset($_POST['idNet']) && strval($_POST['idNet']) !=='')  ? strval($_POST['idNet'])  : null;

                $keyMgmt  = isset($_POST['key_mgmt'])  ? strval($_POST['key_mgmt'])  : null;
                $identity = isset($_POST['identity'])  ? strval($_POST['identity'])  : null;
                $eap      = isset($_POST['eap'])  ? strval($_POST['eap'])  : null;

                error_log("BSSID: ".$bssid);
                $res = createNetwork($p, $idNet, $ssid, $bssid, $psk, $pri, $en, $keyMgmt, $identity, $eap);

                $status->addMessage($_POST['protocol'].'<br>'.$_POST['ssid'].'<br>'.$_POST['bssid'].
                       '<br>'.$_POST['psk'].'<br>'.$_POST['priority'].'<br>'.$_POST['enabled'].'<br>'.
                       $_POST['scan_ssid'].'<br>'.nl2br(implode("\n",$res)));

                //$net = json_decode(urldecode(strval($_POST['oldNetData'])));
                $net = null;

            }else{
                if ( isset($_POST['update'])){
                    $net = (isset($_POST['add']))? json_decode(urldecode(strval($_POST['add']))) : json_decode(urldecode(strval($_POST['update'])));
                    $currentNetId = $net->configuration_data->network_id;
                    
                    $networlList = getWpaSupplicantConfData();
                    if(isset($networlList[$currentNetId]) && $net->configuration_data->ssid == $networlList[$currentNetId]->ssid){
                        $networlList[$currentNetId]->network_id = $net->configuration_data->network_id;
                        error_log ("Network: ".json_encode($networlList[$currentNetId]));
                        $net->configuration_data = $networlList[$currentNetId];
                    }
                    
                }else{
                    $net = (isset($_POST['add']))? json_decode(urldecode(strval($_POST['add']))) : json_decode(urldecode(strval($_POST['update'])));
                }
            }

        }catch(WpaCliException $e){
            $status->addMessage(nl2br($e->errorMessage()), warning);
        }
            
        showUp($status);
        if ($net!=null) showNetworkData($net);
        showDown();
        
    }else if ( (isset($_POST['delete']) || isset($_POST['deleteNetwork']))   && CSRFValidate() ) {

        $net = null;
        if (isset($_POST['deleteNetwork'])){
            $net = json_decode(urldecode(strval($_POST['oldNetData'])));
            try{
                $res =removeNetwork($net->configuration_data->network_id);

                $status->addMessage(nl2br(implode("\n",$res)));
            }catch(WpaCliException $e){
                $status->addMessage(nl2br($e->errorMessage()), warning);
            }
            $net = null;
        }else{
            $net = json_decode(urldecode(strval($_POST['delete'])));
        }
        showUp($status);
        if ($net!=null) showCancelNetworkData($net);
        showDown();
    }else{

        try{
            if ( isset($_POST['connect']) && !is_array($_POST['connect']) ) {
                //Connect to the specified network
                $res = connectToNetwork($_POST['connect']);
                $status->addMessage(nl2br(implode("\n",$res)));
            }

            $ssid_array = array();
            $connected_ssid = '';

            $showScanResults = true;
            $wpa_networks_list = getListNetworksItemResult();

            $wpa_status = getStatusResult();

            $connected_ssid= '';
            $connected_id= '';
            $connected_bssid= '';

            if (isset($wpa_status['ssid'])) $connected_ssid= $wpa_status['ssid'];
            if (isset($wpa_status['id'])) $connected_id= $wpa_status['id'];
            if (isset($wpa_status['bssid'])) $connected_bssid= $wpa_status['bssid'];      

            $networks = getScanResult();
            $ssid_array = prepareNetworkData2Show($networks, $wpa_networks_list, $connected_bssid);

        }catch(WpaCliException $e){
            $status->addMessage(nl2br($e->errorMessage()), warning);
            $showScanResults = false;
        }


        showUp($status);
        if ($showScanResults) showScanResult($ssid_array, $connected_ssid);
        showDown();
    }
}
?>
