<?php

    include_once ('wpa_cli_functions.php');
    //include_once ('wpa_config_file_functions.php');

    function getConnectedSsid(){

        $ssid = null;
        exec( 'iwconfig ' . RASPI_WIFI_CLIENT_INTERFACE, $iwconfig_return );
        foreach ($iwconfig_return as $line) {
            if (preg_match( '/ESSID:\"([^"]+)\"/i',$line,$iwconfig_ssid )) {
              $ssid = $iwconfig_ssid[1];
              break;
            }
        }    
        return $ssid;
    }

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

   if ( isset($_POST['connect']) ) {
    $result = 0;
    
    $status->addMessage(nl2br(implode("\n",connectToNetwork($_POST['connect']))));
    //echo strval($_POST['connect'] );
    
    showUp($status);

    
    showDown();
    
  }
  else if ( isset($_POST['client_settings']) && CSRFValidate() ) {
    //$status = writeWpaSupplicantConf($networks, $_POST);
      echo "<h1>ma come???</h1>";
  }else{

        $debugOut = "";

        $wpa_networks_list = getListNetworksItemResult();

        $wpa_status = getStatusResult();

        $connected_ssid= $wpa_status['ssid'];
        $connected_id= $wpa_status['id'];
        $connected_bssid= $wpa_status['bssid'];      
      
      
        $networks = getScanResult();

        $ssid_array = prepareNetworkData2Show($networks, $wpa_networks_list, $connected_bssid);

      ?>


        <div class="row">
          <div class="col-lg-12">
            <div class="panel panel-primary">
              <div class="panel-heading"><i class="fa fa-signal fa-fw"></i> <?php echo _("Configure client"); ?></div>
              <!-- /.panel-heading -->
              <div class="panel-body">
                <p><?php $status->showMessages(); ?></p>
                      <br>QUI LE RETI<br>
                  <?php
                  foreach($wpa_networks_list as $network) {
                      echo $network->network_id;
                      echo '_';
                      echo $network->ssid;
                      echo '_';
                      echo $network->bssid;
                      echo '_';
                      echo $network->flags;
                      echo '<br>';
                  }
                  ?>


                      <br>Qui test<br>
                      <?php //print_r(array_values($ssid_array));?>
                      <?php //print_r(array_values($status_info));?>

                      <p>Debug: <?php echo $debugOut ?>
                      <p>
                          <?php //print_r(array_values($networks_list));?>
                      </p>

                  <h4><?php echo _("Client settings"); ?></h4>
                    <div class="btn-group btn-block">
                        <a href=".?<?php echo htmlspecialchars($_SERVER['QUERY_STRING'], ENT_QUOTES); ?>" style="padding:10px;float: right;display: block;position: relative;margin-top: -55px;" class="col-md-2 btn btn-info" id="update"><?php echo _("Rescan"); ?></a>
                      </div>

                  <form method="POST" action="?page=wpa_conf" name="wpa_conf_form">
                    <?php CSRFToken() ?>
                    <input type="hidden" name="client_settings" ?>
                    <script>
                      function showPassword(index) {
                          var x = document.getElementsByName("passphrase"+index)[0];
                          if (x.type === "password") {
                              x.type = "text";
                          } else {
                              x.type = "password";
                          }
                      }
                    </script>


                    <?php showScanResult($ssid_array, $connected_ssid);?>


                </form>
              </div><!-- ./ Panel body -->
              <div class="panel-footer"><?php echo _("<strong>Note:</strong> WEP access points appear as 'Open'. RaspAP does not currently support connecting to WEP"); ?></div>
            </div><!-- /.panel-primary -->
          </div><!-- /.col-lg-12 -->
        </div><!-- /.row -->
<?php
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
                                                      <?php if (!$net->connected) { ?><button type="submit" class="col-xs-4 col-md-4 btn btn-info" name = "connect" value="<?php echo $net->configuration_data->network_id; ?>" ><?php echo _("Connect"); ?></button><?php } ?>  
                                                      <input type="submit" class="col-xs-4 col-md-4 btn btn-warning" value="<?php echo _("Update"); ?>" id="update<?php //echo $index ?>" name="update<?php //echo $index ?>"<?php //echo ($network['protocol'] === 'Open' ? ' disabled' : '')?> />
                                                      <input type="submit" class="col-xs-4 col-md-4 btn btn-danger" value="<?php echo _("Delete"); ?>" name="delete<?php //echo $index ?>"<?php //echo ($network['configured'] ? '' : ' disabled')?> />
                                                  <?php } else { ?>
                                                      <input type="submit" class="col-xs-4 col-md-4 btn btn-info" value="<?php echo _("Add"); ?>" id="update<?php //echo $index ?>" name="update<?php //echo $index ?>" <?php //echo ($network['protocol'] === 'Open' ? '' : ' disabled')?> />
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

        
<?php function showUp($status) {?>
        <div class="row">
          <div class="col-lg-12">
            <div class="panel panel-primary">
              <div class="panel-heading"><i class="fa fa-signal fa-fw"></i> <?php echo _("Configure client"); ?></div>
              <!-- /.panel-heading -->
              <div class="panel-body">
                  <p><?php $status->showMessages(); ?></p>
<?php } ?>

<?php function showDown() {?>

              </div><!-- ./ Panel body -->
              <div class="panel-footer"><?php echo _("<strong>Note:</strong> WEP access points appear as 'Open'. RaspAP does not currently support connecting to WEP"); ?></div>
            </div><!-- /.panel-primary -->
          </div><!-- /.col-lg-12 -->
        </div><!-- /.row -->
        
<?php } ?>
        