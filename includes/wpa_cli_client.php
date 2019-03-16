<?php

    include_once ('wpa_cli/wpa_cli_base_functions.php');
    include_once ('wpa_cli/wpa_cli_show_up_down.php');
    include_once ('wpa_cli/wpa_cli_show_cancel_network.php');
    include_once ('wpa_cli/wpa_cli_show_network_data.php');
    include_once ('wpa_cli/wpa_cli_show_scan_result.php');
    include_once ('wpa_cli/wpa_cli_show_networks_list.php');
    include_once ('wpa_cli/wpa_cli_wps.php');
    //include_once ('wpa_cli/wpa_config_file_functions.php');

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


/**
 * @param $status
 * @throws WpaCliException
 */
function createModifyNetwork(StatusMessages $status)
{
    $p        = isset($_POST['protocol']) ? strval($_POST['protocol'])  : null;
    $ssid     = isset($_POST['ssid'])     ? strval($_POST['ssid'])      : null;
    $psk      = isset($_POST['psk'])      ? strval($_POST['psk'])       : null;
    $pri      = isset($_POST['priority']) ? strval($_POST['priority'])  : null;
    $bssid    = isset($_POST['bssid'])    ? strval($_POST['bssid'])     : null;
    $keyMgmt  = isset($_POST['key_mgmt']) ? strval($_POST['key_mgmt']) : null;
    $identity = isset($_POST['identity']) ? strval($_POST['identity']) : null;
    $eap      = isset($_POST['eap'])      ? strval($_POST['eap'])      : null;

    $dis  = isset($_POST['disabled']) ?
        ((strval($_POST['disabled']) == 'on') ? true : null)
        : null;

    $scs = isset($_POST['scan_ssid']) ?
        ((strval($_POST['scan_ssid']) == 'on') ? true : null)
        : null;

    //if $idNet != null the network will be updated and not created
    $idNet = (isset($_POST['idNet']) && strval($_POST['idNet']) !== '') ? strval($_POST['idNet']) : null;


    $res = createNetwork($p, $idNet, $ssid, $bssid, $psk, $pri, $dis, $keyMgmt, $identity, $eap, $scs);

//    $status->addMessage($_POST['protocol'] . '<br>' . $_POST['ssid'] . '<br>' . $_POST['bssid'] .
//        '<br>' . $_POST['psk'] . '<br>' . $_POST['priority'] . '<br>' . $_POST['disabled'] . '<br>' .
//        $_POST['scan_ssid'] . '<br>' . nl2br(implode("\n", $res)));

    $status->addMessage(nl2br(implode("\n", $res)));
}


function completeConfigurationDataFromWPASupplicantConfFile($net)
{
    if (isset($net->configuration_data)){
        //From wpa_cli_scan_result
        $currentNetId = $net->configuration_data->network_id;
        $ssid = $net->configuration_data->ssid;
    }else{
        //From wpa_cli_show_networks_list
        $currentNetId = $net->network_id;
        $ssid = $net->ssid;
    }
    
    $networkList = getWpaSupplicantConfData();
    
    if (isset($networkList[$currentNetId]) && $ssid == $networkList[$currentNetId]->ssid) {
        $networkList[$currentNetId]->network_id = $currentNetId;
        error_log("completeConfigurationDataFromWPASupplicantConfFile:Network: " . json_encode($networkList[$currentNetId]));
        
        if (isset($net->configuration_data)){
            $net->configuration_data = $networkList[$currentNetId];
            return $net;
        }else{
            return $networkList[$currentNetId]; 
        }
    }else{
        throw new WpaCliException(_("Saved network list not updated."));
    }
}

/**
 * @param $status
 */
function showScanResultData(StatusMessages $status)
{
    $ssid_array = [];
    $connected_ssid = '';

    try {
        if (isset($_POST['connect']) && !is_array($_POST['connect'])) {
            //Connect to the specified network
            $res = connectToNetwork($_POST['connect']);
            $status->addMessage(nl2br(implode("\n", $res)));
        }

        $showScanResults = true;
        $wpa_networks_list = getListNetworksItemResult();

        $wpa_status = getStatusResult();

        $connected_bssid = '';

        if (isset($wpa_status['ssid'])) $connected_ssid = $wpa_status['ssid'];
        if (isset($wpa_status['bssid'])) $connected_bssid = $wpa_status['bssid'];

        $networks = getScanResult();
        $ssid_array = prepareNetworkData2Show($networks, $wpa_networks_list, $connected_bssid);

    } catch (WpaCliException $e) {
        $status->addMessage(nl2br($e->errorMessage()), 'warning');
        $showScanResults = false;
    }


    showUp($status);
    if ($showScanResults) showScanResult($ssid_array, $connected_ssid);
    showDown(true);
}


function showNetworskList(StatusMessages $status){
    try {
        $showNetworksList = true;
        $wpa_networks_list = getListNetworksItemResult();

    } catch (WpaCliException $e) {
        $status->addMessage(nl2br($e->errorMessage()), 'warning');
        $showNetworksList = false;
    }


    showUp($status);
    if ($showNetworksList) showNetworksListData($wpa_networks_list);
    showDown(true);    
}

/**
 * @param $status
 */
function deleteNetwork(StatusMessages $status)
{
    $net = null;
    if (isset($_POST['deleteNetwork'])) {
        $oldNetData = json_decode(urldecode(strval($_POST['oldNetData'])));
        try {
            if (isset($oldNetData->configuration_data)){
                $res = removeNetwork($oldNetData->configuration_data->network_id);
            }else{
                $res = removeNetwork($oldNetData->network_id);
            }
            $status->addMessage(nl2br(implode("\n", $res)));

            $res = reconfigure();
            $status->addMessage(nl2br(implode("\n", $res)));
            
        } catch (WpaCliException $e) {
            $status->addMessage(nl2br($e->errorMessage()), 'warning');
        }
    } else {
        $net = json_decode(urldecode(strval($_POST['delete'])));
    }

    showUp($status);
    if ($net != null) showCancelNetworkData($net);
    showDown(false);
}

function showWps($status){
    showUp($status);
    showWPSPage();
    showDown(false);
}

function wpsPbc($status){

    try {

        $res = wpsPbcConnect();
        $status->addMessage(nl2br(implode("\n", $res)));

    } catch (WpaCliException $e) {
       $status->addMessage(nl2br($e->errorMessage()), 'warning');
    }


    showUp($status);
    showDown(false);

}

function wpsPin($status){

    $pin = isset($_POST['pin']) ? strval($_POST['pin'])  : '';
    
    try {

        $res = wpsPinConnect($pin);
        $status->addMessage(nl2br(implode("\n", $res)));

    } catch (WpaCliException $e) {
       $status->addMessage(nl2br($e->errorMessage()), 'warning');
    }

    showUp($status);
    showDown(false);
}


/**
 * @param $status
 */
function createUpdateNetwork(StatusMessages $status)
{
    $net = null;
    try {
        if (isset($_POST['addNetwork'])) {
            createModifyNetwork($status);

            // better don't show the used network data, also not updated
            //$net = json_decode(urldecode(strval($_POST['oldNetData'])));
        } elseif (isset($_POST['update'])) {
            //$net = (isset($_POST['add']))? json_decode(urldecode(strval($_POST['add']))) : json_decode(urldecode(strval($_POST['update'])));
            $net = json_decode(urldecode(strval($_POST['update'])));

            $net = completeConfigurationDataFromWPASupplicantConfFile($net);
        } else {
            //$net = (isset($_POST['add']))? json_decode(urldecode(strval($_POST['add']))) : json_decode(urldecode(strval($_POST['update'])));
            $net = json_decode(urldecode(strval($_POST['add'])));
        }
    } catch (WpaCliException $e) {
        $status->addMessage(nl2br($e->errorMessage()), 'warning');
        $net = null;
    }

    showUp($status);
    if ($net != null) showNetworkData($net);
    showDown(true);
}

function DisplayWPACliConfig(){

    $status = new StatusMessages();

    if ( (isset($_POST['add']) || isset($_POST['update'])|| isset($_POST['addNetwork'])  ) && CSRFValidate() ) {
        createUpdateNetwork($status);
    }elseif ( (isset($_POST['delete']) || isset($_POST['deleteNetwork']))   && CSRFValidate() ) {
        deleteNetwork($status);
    }elseif ( isset($_GET['netList'])) {
        showNetworskList($status);
    }elseif ( isset($_GET['wps'])) {
        showWps($status);
    }elseif ( isset($_POST['wpsPbc'])) {
        wpsPbc($status);
    }elseif ( isset($_POST['wpsPin'])) {
        wpsPin($status);
    }else{
        showScanResultData($status);
    }
}

?>
