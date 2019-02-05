<?php

    include_once ('wpa_cli_functions.php');
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
  
function DisplayWPAConfig(){
  $status = new StatusMessages();

    if ( (isset($_POST['add']) || isset($_POST['update'])|| isset($_POST['addNetwork'])  ) && CSRFValidate() ) {
        //$status = writeWpaSupplicantConf($networks, $_POST);
        
        $net = null;
        if (isset($_POST['addNetwork'])){
        
         $p=null;
         $ssid=null;
         $bssid=null;
         $psk=null;
         $pri=null;
         $en=null;
         $scs=null;
         
         $idNet = null;
         $keyMgmt = null;
         $identity = null;
         $eap = null;
         
        if (isset($_POST['protocol']))
            $p =strval($_POST['protocol']);
        if (isset($_POST['ssid']))
            $ssid =strval($_POST['ssid']);
        if (isset($_POST['bssid']))
            if (strval($_POST['bssid']) != 'any')
            $bssid =strval($_POST['bssid']);
        if (isset($_POST['psk']))
            $psk =strval($_POST['psk']);
        if (isset($_POST['priority']))
            $pri =strval($_POST['priority']);
        if (isset($_POST['enabled']))
            $en = (strval($_POST['enabled'])=='on')? true : null;
        if (isset($_POST['scan_ssid']))
            $scs =strval($_POST['scan_ssid']);

        $res = createNetwork($p, $idNet, $ssid, $bssid, $psk, $pri, $en, $keyMgmt, $identity, $eap);
            
            $status->addMessage($_POST['protocol'].'<br>'.$_POST['ssid'].'<br>'.$_POST['bssid'].'<br>'.$_POST['psk'].'<br>'.$_POST['priority'].'<br>'.$_POST['enabled'].'<br>'.$_POST['scan_ssid'].'<br>'.nl2br(implode("\n",$res)));

            $net = json_decode(urldecode(strval($_POST['oldNetData'])));
            
        }else{
            $net = (isset($_POST['add']))? json_decode(urldecode(strval($_POST['add']))) : json_decode(urldecode(strval($_POST['update'])));
        }
        
        showUp($status);
        
        if ($net!=null)
            showNetworkData($net);

        showDown();
    }else if ( (isset($_POST['delete']) || isset($_POST['deleteNetwork']))   && CSRFValidate() ) {

        $net = null;
        if (isset($_POST['deleteNetwork'])){
            $net = json_decode(urldecode(strval($_POST['oldNetData'])));
            $res =removeNetwork($net->configuration_data->network_id);
            
            $status->addMessage(strval($net->configuration_data->network_id).'<br>'.nl2br(implode("\n",$res)));
            
        }else{
            $net = json_decode(urldecode(strval($_POST['delete'])));
        }
        showUp($status);
        showCancelNetworkData($net);
        showDown();
    }else{

        try{
            if ( isset($_POST['connect']) && !is_array($_POST['connect']) ) {
                //Connect to the specified network
                $status->addMessage(nl2br(implode("\n",connectToNetwork($_POST['connect']))));
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

<?php
    function echoSignalPercentage($signalDb){
        echo " (";
        if($signalDb >= -50) { echo 100; }
        else if($signalDb <= -100) { echo 0;}
        else {echo  2*($signalDb + 100); }
        echo "%)";
    } 
?>

<?php
    function showUp($status) 

    {?>
        <div class="row">
          <div class="col-lg-12">
            <div class="panel panel-primary">
              <div class="panel-heading"><i class="fa fa-signal fa-fw"></i> <?php echo _("Configure client"); ?></div>
                <!-- /.panel-heading -->
                <div class="panel-body">
                    <p><?php $status->showMessages(); ?></p>
                    <h4><?php echo _("Client settings"); ?></h4>
                    <div class="btn-group btn-block">
                        <a href=".?<?php echo htmlspecialchars($_SERVER['QUERY_STRING'], ENT_QUOTES); ?>" style="padding:10px;float: right;display: block;position: relative;margin-top: -55px;" class="col-md-2 btn btn-info" id="update"><?php echo _("Rescan"); ?></a>
                    </div>

                    <form method="POST" action="?page=wpa_conf" name="wpa_conf_form">
                        <?php CSRFToken() ?>
<?php 
    } 
?>
<?php function showDown() {?>
                    </form>
                </div><!-- ./ Panel body -->
                <div class="panel-footer"><?php //echo _("<strong>Note:</strong> WEP access points appear as 'Open'. RaspAP does not currently support connecting to WEP"); ?>
                </div>
            </div><!-- /.panel-primary -->
          </div><!-- /.col-lg-12 -->
        </div><!-- /.row -->
        
<?php } ?>
                  
<?php function showScanResult ($ssid_array, $connected_ssid){ ?>
            <div>  
                <?php foreach ($ssid_array as $ssid => $network) { ?>
                    <ul class="list-group">
                        <li class="list-group-item list-group-item-info">
                            <h4> <?php echo htmlspecialchars($ssid, ENT_QUOTES); ?><?php if ($ssid == $connected_ssid) { ?> <i class="fa fa-exchange fa-fw"></i><?php } ?></h4>
                                <ul class="list-group">
                                <?php foreach ($ssid_array[$ssid] as $net) { ?>                    
                                    <li class="list-group-item">
                                        BSSID:<span class="badge">  <?php echo htmlspecialchars($net->bssid, ENT_QUOTES); ?></span>
                                        <ul class="list-group">
                                            <li class="list-group-item">Status:
                                                <?php if ($net->configured) { ?><i class="fa fa-check-circle fa-fw"></i><?php } ?>
                                                <?php if ($net->connected) { ?><i class="fa fa-exchange fa-fw"></i><?php } ?>
                                            </li>
                                            <li class="list-group-item">Channel:<span class=""><?php echo htmlspecialchars($net->channel, ENT_QUOTES); ?> (<?php echo htmlspecialchars($net->frequency, ENT_QUOTES); ?> MHz)</span></li>
                                            <li class="list-group-item">Signal:<span class=""><?php echo htmlspecialchars($net->signal, ENT_QUOTES); ?> dB <?php echoSignalPercentage($net->signal) ?></span></li>
                                            <li class="list-group-item">Flags:<span class=""><?php echo htmlspecialchars($net->flags, ENT_QUOTES); ?></span></li>
                                            <li class="list-group-item">Protocol:<span class=""><?php echo htmlspecialchars($net->protocol, ENT_QUOTES); ?></span></li>
                                            <li class="list-group-item">
                                                <div class="btn-group btn-block ">
                                                  <?php if ($net->configured) { ?>
                                                      <?php if (!$net->connected) { ?>
                                                                <button type="submit" class="col-xs-4 col-md-4 btn btn-success" name = "connect" value="<?php echo $net->configuration_data->network_id; ?>" ><?php echo _("Connect"); ?></button>
                                                      <?php } ?>  
                                                            <button type="submit" class="col-xs-4 col-md-4 btn btn-info" name = "update" value="<?php echo urlencode (json_encode($net)); ?>" ><?php echo _("Update"); ?></button>
                                                            <button type="submit" class="col-xs-4 col-md-4 btn btn-danger" value="<?php echo urlencode (json_encode($net)); ?>" name="delete"><?php echo _("Delete"); ?></button>
                                                  <?php } else { ?>
                                                            <button type="submit" class="col-xs-4 col-md-4 btn btn-info" name = "add" value="<?php echo urlencode (json_encode($net)); ?>" ><?php echo _("Add"); ?></button>
                                                  <?php } ?>
                                                      
                                                </div><!-- /.btn-group -->
                                            </li>
                                        </ul>
                                    </li>
                                <?php } ?>
                            </ul>
                        </li>
                    </ul>
                <?php } ?>
            </div>
<?php } ?>


        
        
 <?php function showNetworkData($net) {
     
     // nessuno
     //wep password [WEP]
     //wpa/wpa2/ft psk password e gestione chiavi ft
     //802.1 EAP
//            -- peap, tls, ttls, pwd, sim, aka, aka', fast, leap
//                    
//                    peap
//                        autenticazine fase 2
//                            nessuno
//                            mschapv2
//                            gtc
//                        selezione certificato
//                        identità
//                        identità anonima
//                        password
//                    
//                    tls
//                        certificato
//                        certificato utente
//                        identità
//                    ttls
//                      autenticazione fase 2
//                          none
//                          pap
//                          mschap
//                          mschapv2
//                          gtc
//                      certificato
//                        identità
//                        identità anonima
//                        password
//                    //                    
//                    pwd
//                        identità
//                        pwd
//                      sim
//                        
//                      aka
//                      aka'
//                      fast
//                        provisioning
//                            0
//                            1
//                            2
//                            3
//                      
//                      autenticazione fase 2
//                          none

//                          mschapv2
//                          gtc
//                      leap
//                        identità
//                        pwd
     
     ?>       
 
        <input type="hidden" name="oldNetData" value="<?php echo urlencode (json_encode($net)); ?>">
        <input type="hidden" name="protocol" value="<?php if (strpos($net->flags, "EAP")!= false){ 
                echo "EAP";
            }elseif (strpos($net->flags, "WPA2") != false){
                echo "WPA2";
            }elseif (strpos($net->flags, "WPA") != false){
                echo "WPA";
            }elseif (strpos($net->flags, "WEP") != false) {
                echo "WEP";
            } else {
                echo "OPEN";
            }?>">

            <div class="form-group">
                <label for="focusedInput">SSID</label>
                <input class="form-control" name="ssid" id="ssid" type="text" value="<?php echo $net->ssid; ?>" readonly >
            </div>        
            <div class="form-group">
                <label for="selBssis">BSSID</label>
                <select class="form-control" id="bssid" name="bssid">
                    <option value="any" >ANY</option>
                    <option value="<?php echo $net->bssid; ?>" <?php  if ($net->configuration_data != null && $net->configuration_data->bssid == $net->bssid ) echo "selected" ; ?>> <?php echo $net->bssid; ?></option>
                 </select>
            </div>

            <?php if (strpos($net->flags, "EAP")!= false){ ?>
                <!--WPA2-EAP-->
                <!-- 
                    Required :
                        -identity
                        -password
                        -eap
                        -key_mgmt
                -->
            <?php }elseif (strpos($net->flags, "WPA") != false || strpos($net->flags, "WEP") != false) {?>
                <!--WPA2-PSK or WPA-PSK or WEP-->
                <!--WEP no password, no validation empty-->
                <div class="form-group">
                    <label for="password">PASSFRASE</label>
                    <input class="form-control" name="psk" id="psk" type="password" value="<?php echo $net->ssid; ?>">
                </div>                
            <?php }?>
            <!--open->

            <!--Common part-->
            <div class="form-group">
                <label for="priority">PRIORITY</label>
                <input class="form-control" id="priority" name="priority" type="text" list="priorityValues" pattern="[0-9]{1,3}" value="0">
                <datalist id="priorityValues">
                    <option value="0" >
                    <option value="10">
                    <option value="20">
                    <option value="30">
                    <option value="40">
                </datalist>                
            </div>                        
            <div class="form-group">
                <label for="enable">ENABLE</label>
                <input class="form-control" id="enabled" name="enabled" type="checkbox" checked="true">
            </div>                
            <div class="form-group">
                <label for="scan_ssid">SCAN SSID</label>
                <input class="form-control" id="scan_ssid" name="scan_ssid" type="checkbox">
            </div>                
            <div class="form-group">
                <div class="btn-group btn-block ">
                    <button type="submit" class="col-xs-4 col-md-4 btn btn-info" name = "addNetwork" value="addNetwork" ><?php echo _("Add"); ?></button>        
                </div>
            </div>
<?php } ?>

 <?php function showCancelNetworkData($net) { ?>       
 
        <input type="hidden" name="oldNetData" value="<?php echo urlencode (json_encode($net)); ?>">

            <div class="form-group">
                <label for="focusedInput">SSID</label>
                <input class="form-control" name="ssid" id="ssid" type="text" value="<?php echo $net->ssid; ?>" readonly >
            </div>        
            <div class="form-group">
                <label for="selBssis">BSSID</label>
                <input class="form-control" name="bssid" id="bssid" type="text" value="<?php echo $net->bssid; ?>" readonly >
            </div>

            <div class="form-group">
                <div class="btn-group btn-block ">
                    <button type="submit" class="col-xs-4 col-md-4 btn btn-danger" value="deleteNetwork" name="deleteNetwork"><?php echo _("Delete"); ?></button>
                </div>
            </div>
<?php } ?>
            
