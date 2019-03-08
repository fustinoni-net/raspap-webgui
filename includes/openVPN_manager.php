<?php

include_once ('openVPN/openVPN_show_start_stop.php');
include_once ('openVPN/openVPN_show_status.php');
include_once ('openVPN/openVPN_show_up_down.php');
include_once ('openVPN/openVPN_show_upload_config_file.php');
include_once ('openVPN/openVPN_functions.php');


function DisplayOpenVPNManager() {

    $isVPNRunning = false;
    $status = '';
    if(isset($_POST["submit"])) {

        $r = saveUploadedFile();

        if (startsWith($r, 'Sorry')){
            $status = '<div class="alert alert-danger alert-dismissable">'.$r.'<button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button></div>';
        }else{

            $status = '<div class="alert alert-success alert-dismissable">'.$r.'<button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button></div>';
        }

    }


    if (isset($_POST["StartOpenVPN"])){
        //Abilitare il routing
        
        $idAddr = isset($_POST['ipServer']) ? strval($_POST['ipServer'])  : null;
        
        startVPN(strval($_POST["confFileName"]), $idAddr);
    }
    
    if (isset($_POST["StopOpenVPN"])){
        //rimuovere il routing
        stopVPN();
        sleep(1);
    }
    
    $allowext = array("ovpn");
    $files_array = scanDirectories(RASPI_OPENVPN_CLIENT_CONFIG_DIR,$allowext);

    $openvpnstatus = null;    
    exec( 'pidof openvpn | wc -l', $openvpnstatus);

    if( $openvpnstatus[0] == 0 ) {
        $status = $status.'<div class="alert alert-warning alert-dismissable">OpenVPN is not running
                            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button></div>';
    } else {
        $isVPNRunning = true;
        $status = '<div class="alert alert-success alert-dismissable">OpenVPN is running
                            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button></div>';
    }


    showVPNUp ($status);
    if ($isVPNRunning) showVPNStatus();    
    showStartStopVPN($files_array, (bool) $isVPNRunning);
    showUploadConfigFile();
    showVPNDown();

}

?>