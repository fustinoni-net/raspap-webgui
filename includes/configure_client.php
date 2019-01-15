<?php

/**
*
*
*/

//network id / ssid / bssid / flags
//0       mobile_guest    any     [CURRENT]
//1       TIM-06501189    any
//2       Toyota Free_Wi-Fi       any
//3       test    any     [DISABLED]
//4       pippo   any     [DISABLED]
//5       mobile_guest    00:1a:1e:67:76:63       [DISABLED]


//bssid / frequency / signal level / flags / ssid
//d8:c7:c8:b0:39:43       2412    -50     [WPA2-PSK-CCMP+TKIP][ESS]       mobile_guest
//00:1a:1e:67:76:62       2462    -41     [WPA2-PSK-CCMP][ESS]    dadaguest
//00:1a:1e:67:76:60       2462    -46     [WPA2-EAP-CCMP][ESS]    dadacorp
//00:1a:1e:67:76:63       2462    -46     [WPA2-PSK-CCMP+TKIP][ESS]       mobile_guest
//d8:c7:c8:b0:39:42       2412    -47     [WPA2-PSK-CCMP][ESS]    dadaguest
//d8:c7:c8:b0:39:40       2412    -54     [WPA2-EAP-CCMP][ESS]    dadacorp
//00:1a:1e:67:76:64       2462    -54     [WPA2-PSK-CCMP][ESS]    dada-personal
//d8:c7:c8:b0:39:44       2412    -60     [WPA2-PSK-CCMP][ESS]    dada-personal
//00:1a:1e:67:76:61       2462    -47     [WPA2-PSK-CCMP][ESS]    dadaguest-old
//d8:c7:c8:b0:39:41       2412    -54     [WPA2-PSK-CCMP][ESS]    dadaguest-old
//d8:c7:c8:b0:39:02       2437    -68     [WPA2-PSK-CCMP][ESS]    dadaguest
//d8:c7:c8:b0:39:03       2437    -72     [WPA2-PSK-CCMP+TKIP][ESS]       mobile_guest
//d8:c7:c8:b0:39:01       2437    -73     [WPA2-PSK-CCMP][ESS]    dadaguest-old
//d8:c7:c8:b0:39:00       2437    -75     [WPA2-EAP-CCMP][ESS]    dadacorp
//d8:c7:c8:b0:39:04       2437    -78     [WPA2-PSK-CCMP][ESS]    dada-personal
//f8:e7:1e:2f:28:68       2412    -92     [WPA2-PSK-CCMP][ESS]    IBMCIC
//00:1a:1e:67:7f:21       2462    -92     [WPA2-PSK-CCMP][ESS]    dadaguest-old
//88:f0:77:bf:aa:72       2412    -90     [ESS]   GUEST


//bssid=d8:c7:c8:b0:39:43
//freq=2412
//ssid=mobile_guest
//id=0
//mode=station
//pairwise_cipher=CCMP
//group_cipher=TKIP
//key_mgmt=WPA2-PSK
//wpa_state=COMPLETED
//ip_address=1.2.3.108
//p2p_device_address=42:f2:73:97:85:1f
//address=b8:27:eb:5e:11:0f
//uuid=4443a4a8-e55a-52f5-88a2-606bfc0b7c86



function getWpaSupplicantConfData(){
    
  $network = null;
  $ssid = null;
  // Find currently configured networks
  exec(' sudo cat ' . RASPI_WPA_SUPPLICANT_CONFIG, $known_return);

  foreach($known_return as $line) {
    if (preg_match('/network\s*=/', $line)) {
      $network = array('visible' => false, 'configured' => true, 'connected' => false);
    } elseif ($network !== null) {
      if (preg_match('/^\s*}\s*$/', $line)) {
        $networks[$ssid] = $network;
        $network = null;
        $ssid = null;
      } elseif ($lineArr = preg_split('/\s*=\s*/', trim($line))) {
        switch(strtolower($lineArr[0])) {
          case 'ssid':
            $ssid = trim($lineArr[1], '"');
            break;
          case 'psk':
            if (array_key_exists('passphrase', $network)) {
              break;
            }
          case '#psk':
            $network['protocol'] = 'WPA';
          case 'wep_key0': // Untested
            $network['passphrase'] = trim($lineArr[1], '"');
            break;
          case 'key_mgmt':
            if (! array_key_exists('passphrase', $network) && $lineArr[1] === 'NONE') {
              $network['protocol'] = 'Open';
            }
            break;
          case 'priority':
            $network['priority'] = trim($lineArr[1], '"');
            break;
        }
      }
    }
  }
    
  return $networks;
}

function getStatusResult(){
    $status_info = array();
    exec('python /home/pi/wifiExtender/utils/wpa_supplicant/wpaGateway.py STATUS' , $known_return);
 
  
    foreach($known_return as $line) {
        $lineArr = preg_split('/[=]+/', trim($line));
        $status_info[trim($lineArr[0], ' ')] = trim($lineArr[1], ' ');
    }
    return $status_info;
}


class NetworkListItem{
    public $network_id = '';
    public $ssid = ''; 
    public $bssid = '';
    public $flags = '';
}


function getListNetworksItemResult(){
    $networks_list = array();
    exec('python /home/pi/wifiExtender/utils/wpa_supplicant/wpaGateway.py list_networks' , $known_return);
 
    array_shift($known_return);
  
    foreach($known_return as $line) {
        if (trim($line) == 'network id / ssid / bssid / flags') continue;

        $lineArr = preg_split('/[\t]+/', trim($line));

        $ssid = trim($lineArr[1], ' ');
        if ($ssid == '') $ssid = '-';

        $network_item = new NetworkListItem();
        $network_item->bssid = trim($lineArr[2]);
        $network_item->flags = trim($lineArr[3], ' ');
        $network_item->network_id = trim($lineArr[0], ' ');
        $network_item->ssid = $ssid;

        array_push($networks_list, $network_item);
    }
    return $networks_list;
}



function getListNetworksResult(){
    $networks_list = array();
    exec('python /home/pi/wifiExtender/utils/wpa_supplicant/wpaGateway.py list_networks' , $known_return);
 
    array_shift($known_return);
  
    foreach($known_return as $line) {
        if (trim($line) == 'network id / ssid / bssid / flags') continue;
        
        $network = array('visible' => false, 'configured' => true, 'connected' => false);
        $lineArr = preg_split('/[\t]+/', trim($line));
        $network['id'] = trim($lineArr[0], ' ');
        $ssid = trim($lineArr[1], ' ');
        if ($ssid == '') $ssid = '-';
        $network['ssid'] = $ssid;
        $network['bssid'] = $lineArr[2];
        $network['flags'] = trim($lineArr[3], ' ');
        $networks_list[$network['id']] = $network;
    }
    return $networks_list;
}

function writeWpaSupplicantConf($tmp_networks, $tmp_post){
    
    $status = null;
    
    if ($wpa_file = fopen('/tmp/wifidata', 'w')) {
      fwrite($wpa_file, 'ctrl_interface=DIR=' . RASPI_WPA_CTRL_INTERFACE . ' GROUP=netdev' . PHP_EOL);
      fwrite($wpa_file, 'update_config=1' . PHP_EOL);
      fwrite($wpa_file, 'country=' . RASPI_WIFI_COUNTRY_CODE . PHP_EOL);

      foreach(array_keys($tmp_post) as $post) {
        if (preg_match('/delete(\d+)/', $post, $post_match)) {
          unset($tmp_networks[$tmp_post['ssid' . $post_match[1]]]);
        } elseif (preg_match('/update(\d+)/', $post, $post_match)) {
          // NB, at the moment, the value of protocol from the form may
          // contain HTML line breaks
          $tmp_networks[$tmp_post['ssid' . $post_match[1]]] = array(
            'protocol' => ( $tmp_post['protocol' . $post_match[1]] === 'Open' ? 'Open' : 'WPA' ),
            'passphrase' => $tmp_post['passphrase' . $post_match[1]],
            'configured' => true
          );
          if (array_key_exists('priority' . $post_match[1], $tmp_post)) {
            $tmp_networks[$tmp_post['ssid' . $post_match[1]]]['priority'] = $tmp_post['priority' . $post_match[1]];
          }
        }
      }

      $ok = true;
      foreach($tmp_networks as $ssid => $network) {
        if ($network['protocol'] === 'Open') {
          fwrite($wpa_file, "network={".PHP_EOL);
          fwrite($wpa_file, "\tssid=\"".$ssid."\"".PHP_EOL);
          fwrite($wpa_file, "\tkey_mgmt=NONE".PHP_EOL);
          if (array_key_exists('priority', $network)) {
            fwrite($wpa_file, "\tpriority=".$network['priority'].PHP_EOL);
          }
          fwrite($wpa_file, "}".PHP_EOL);
        } else {
          if (strlen($network['passphrase']) >=8 && strlen($network['passphrase']) <= 63) {
            unset($wpa_passphrase);
            unset($line);
            exec( 'wpa_passphrase '.escapeshellarg($ssid). ' ' . escapeshellarg($network['passphrase']),$wpa_passphrase );
            foreach($wpa_passphrase as $line) {
              if (preg_match('/^\s*}\s*$/', $line)) {
                if (array_key_exists('priority', $network)) {
                  fwrite($wpa_file, "\tpriority=".$network['priority'].PHP_EOL);
                }
                fwrite($wpa_file, $line.PHP_EOL);
              } else {
                fwrite($wpa_file, $line.PHP_EOL);
              }
            }
          } else {
            $status->addMessage('WPA passphrase must be between 8 and 63 characters', 'danger');
            $ok = false;
          }
        }
      }

      if ($ok) {
        system( 'sudo cp /tmp/wifidata ' . RASPI_WPA_SUPPLICANT_CONFIG, $returnval );
        if( $returnval == 0 ) {
          exec('sudo wpa_cli -i ' . RASPI_WIFI_CLIENT_INTERFACE . ' reconfigure', $reconfigure_out, $reconfigure_return );
          if ($reconfigure_return == 0) {
            $status->addMessage('Wifi settings updated successfully', 'success');
            $networks = $tmp_networks;
          } else {
            $status->addMessage('Wifi settings updated but cannot restart (cannot execute "wpa_cli reconfigure")', 'danger');
          }
        } else {
          $status->addMessage('Wifi settings failed to be updated', 'danger');
        }
      }
    } else {
      $status->addMessage('Failed to update wifi settings', 'danger');
    }
    
    return $status;
}

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



function getScanResultOld($networks){
//  exec( 'sudo wpa_cli -i ' . RASPI_WIFI_CLIENT_INTERFACE . ' scan' );
//  sleep(3);
//  exec( 'sudo wpa_cli -i ' . RASPI_WIFI_CLIENT_INTERFACE . ' scan_results',$scan_return );

  exec( 'python /home/pi/wifiExtender/utils/wpa_supplicant/wpaScanResults.py',$scan_return );
  
  for( $shift = 0; $shift < 1; $shift++ ) {
    array_shift($scan_return);
  }

  // display output
  foreach( $scan_return as $network ) {
    $arrNetwork = preg_split("/[\t]+/",$network);  // split result into array

    // Save RSSI
    if (array_key_exists(4, $arrNetwork)) {
      $networks[$arrNetwork[4]]['RSSI'] = $arrNetwork[2];
    }

    // If network is saved
    if (array_key_exists(4, $arrNetwork) && array_key_exists($arrNetwork[4], $networks)) {
      $networks[$arrNetwork[4]]['visible'] = true;
      $networks[$arrNetwork[4]]['channel'] = ConvertToChannel($arrNetwork[1]);
      // TODO What if the security has changed?
    } else {
      $networks[$arrNetwork[4]] = array(
        'configured' => false,
        'protocol' => ConvertToSecurity($arrNetwork[3]),
        'channel' => ConvertToChannel($arrNetwork[1]),
        'passphrase' => '',
        'visible' => true,
        'connected' => false
      );
    }
  }

  $cssid = getConnectedSsid();
  if ($cssid != null)
      $networks[$cssid]['connected'] = true;

   return $networks; 
}

function getScanResult(){

    $networks_list = null;
    exec( 'python /home/pi/wifiExtender/utils/wpa_supplicant/wpaScanResults.py',$scan_return );
  
    array_shift($scan_return);

    foreach($scan_return as $line) {
        if (trim($line) == 'bssid / frequency / signal level / flags / ssid') continue;

        //$network = array('visible' => false, 'configured' => true, 'connected' => false);
        $network = array();
        $lineArr = preg_split('/[\t]+/', trim($line));
        $network['bssid'] = trim($lineArr[0], ' ');
        $network['frequency'] = trim($lineArr[1], ' ');
        $network['signal'] = trim($lineArr[2], ' ');
        $network['flags'] = trim($lineArr[3], ' ');

        $ssid = trim($lineArr[4], ' ');
        if ($ssid == '') $ssid = '-';
        $network['ssid'] = $ssid;

        $network['configured'] = false;
        $network['channel'] = ConvertToChannel(trim($lineArr[1], ' '));
        $network['protocol'] = ConvertToSecurity($network['flags']);
        
        $networks_list[$network['bssid']] = $network;
    }

//  $cssid = getConnectedSsid();
//  if ($cssid != null)
//      $networks[$cssid]['connected'] = true;

    return $networks_list;
}



function DisplayWPAConfig(){
  $status = new StatusMessages();

  //$networks = getWpaSupplicantConfData();

  $debugOut = "";

  $networks_list = getListNetworksItemResult();
  
  $status_info = getStatusResult();
  
  $connected_ssid= $status_info['ssid'];
  $connected_id= $status_info['id'];
  $connected_bssid= $status_info['bssid'];

   if ( isset($_POST['connect']) ) {
    $result = 0;
    //exec ( 'sudo wpa_cli -i ' . RASPI_WPA_CTRL_INTERFACE . ' select_network '.strval($_POST['connect'] ));
    exec ( 'python /home/pi/wifiExtender/utils/wpa_supplicant/wpaTcpGateway.py select_network '.strval($_POST['connect'] ));
    
  }
  else if ( isset($_POST['client_settings']) && CSRFValidate() ) {
    $status = writeWpaSupplicantConf($networks, $_POST);
  }

  $networks = getScanResult();
  
  $ssid_array = array();
  foreach ($networks as $bssid => $network) {
        $ssid = $network['ssid'];
        $bssid = $network['bssid'];
        $net=array();
        //$net['bssid'] = $network['bssid'];
        $net['frequency'] = $network['frequency'];
        $net['signal'] = $network['signal'];
        $net['flags'] = $network['flags'];
        
        if ($bssid == $connected_bssid){
            $net['connected'] = true;
        }else{
            $net['connected'] = false;
        }
        
        $net['configured'] = false;
        foreach ($networks_list as $i){
             if ( $ssid == $i->ssid && ($i->bssid == $bssid || $i->bssid == 'any') ){
                $net['configured'] = true;
            }
        }

        $net['channel'] = $network['channel'];
        $net['protocol'] = $network['protocol'];
        

//        if(in_array($ssid, $ssid_array[0])){
//            array_push($ssid_array[$bssid], $net);
//            //array_push($ssid_array, $ssid);
//        }else{
            
          $ssid_array[$ssid][$bssid] = $net;
          //$ssid_array[$ssid] = $ssid;
          //array_push($ssid_array, $ssid);
//        }
  }
  

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
            foreach($networks_list as $network) {
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
              <br><br><br>
              <?php //showScanResultDiv($ssid_array);?>              
              
              

          </form>
        </div><!-- ./ Panel body -->
	<div class="panel-footer"><?php echo _("<strong>Note:</strong> WEP access points appear as 'Open'. RaspAP does not currently support connecting to WEP"); ?></div>
      </div><!-- /.panel-primary -->
    </div><!-- /.col-lg-12 -->
  </div><!-- /.row -->
<?php
}

?>

<?php function showScanResult ($ssid_array, $connected_ssid){ ?>
            <div>  
                <?php foreach ($ssid_array as $ssid => $network) { ?>
                    <ul class="list-group">
                        <li class="list-group-item list-group-item-info">
                            <h4> <?php echo $ssid; ?><?php if ($ssid == $connected_ssid) { ?> <i class="fa fa-exchange fa-fw"></i><?php } ?></h4>
                                <ul class="list-group">
                                <?php foreach ($ssid_array[$ssid] as $bssid => $net) { ?>                    
                                    <li class="list-group-item">
                                        BSSID:<span class="badge">  <?php echo $bssid; ?></span>
                                        <ul class="list-group">
                                            <li class="list-group-item">Status:
                                                <?php if ($net['configured']) { ?><i class="fa fa-check-circle fa-fw"></i><?php } ?>
                                                <?php if ($net['connected']) { ?><i class="fa fa-exchange fa-fw"></i><?php } ?>
                                            </li>
                                            <li class="list-group-item">Channel:<span class=""> <?php echo $net['channel']; ?> ( <?php echo $net['frequency']; ?> MHz)</span></li>
                                            <li class="list-group-item">Signal:<span class="">  <?php echo $net['signal']; ?> dB</span></li>
                                            <li class="list-group-item">Flags:<span class="">  <?php echo $net['flags']; ?></span></li>
                                            <li class="list-group-item">Protocol:<span class="">  <?php echo $net['protocol']; ?></span></li>
                                        </ul>
                                    </li>
                                <?php } ?>
                            </ul>
                        </li>
                    </ul>
                <?php } ?>
            </div>
<?php } ?>

<?php function showScanResultDiv ($ssid_array){ ?>
            <div>  
                <?php foreach ($ssid_array as $ssid => $network) { ?>
                    <ul>
                        <li>
                            <?php echo $ssid; ?>
                                <ul>
                                <?php foreach ($ssid_array[$ssid] as $bssid => $net) { ?>                    
                                    <li>
                                        <div class="row">
                                            <div class="col-sm-3"><?php echo $bssid; ?></div>
                                            <div class="col-sm-1"><?php echo $net['channel']; ?></div>    
                                            <div class="col-sm-1"><?php echo $net['signal']; ?></div>    
                                            <div class="col-sm-4"><?php echo $net['flags']; ?></div>    
                                            <div class="col-sm-2"><?php echo $net['visible']; ?></div>    
                                            <div class="col-sm-1"><?php echo $net['protocol']; ?></div>
                                        </div>
                                    </li>
                                <?php } ?>
                            </ul>
                        </li>
                    </ul>
                <?php } ?>
            </div>  
  <?php } ?>

  <?php function showScanResultDivOnly ($ssid_array){ ?>
            <?php foreach ($ssid_array as $ssid => $network) { ?>
                <div class="row">
                    <!-- blocco reti -->
                    <div class="col-sm-12">
                        <?php echo $ssid; ?>
                    </div>
                </div>
                <?php foreach ($ssid_array[$ssid] as $bssid => $net) { ?>                    
                    <div class="row">
                        <div class="col-sm-1"> </div>
                        <div class="col-sm-1">&bullet;</div>
                        <div class="col-sm-2">
                          <?php echo $bssid; ?>
                        </div>
                        <div class="col-sm-1">                        
                        <?php echo $net['channel']; ?>
                        </div>    
                        <div class="col-sm-1">
                          <?php echo $net['signal']; ?>
                        </div>    
                        <div class="col-sm-3">
                          <?php echo $net['flags']; ?>
                        </div>    
                        <div class="col-sm-2">
                          <?php echo $net['visible']; ?>
                        </div>    
                        <div class="col-sm-1">
                          <?php echo $net['protocol']; ?>
                        </div>
                      </div>
                <?php } ?>
            <?php } ?>
                
  <?php } ?>

    <?php function showScanResultRaw ($networks){ ?>
                <?php $index = 0; ?>
              <?php foreach ($networks as $bssid => $network) { ?>
              <div class="row">
                <!-- blocco reti -->
                <div class="col-sm-2">
                  <?php echo $network['bssid']; ?>
                </div>    
                <div class="col-sm-1">
                  <?php echo $network['frequency']; ?>
                </div>    
                <div class="col-sm-1">
                  <?php echo $network['channel']; ?>
                </div>    
                <div class="col-sm-1">
                  <?php echo $network['signal']; ?>
                </div>    
                <div class="col-sm-3">
                  <?php echo $network['flags']; ?>
                </div>    
                <div class="col-sm-2">
                  <?php echo $network['ssid']; ?>
                </div>    
                <div class="col-sm-1">
                  <?php echo $network['visible']; ?>
                </div>    
                <div class="col-sm-1">
                  <?php echo $network['protocol']; ?>
                </div>
              </div>
              <br>
            <?php $index += 1; ?>
            <?php } ?>
 <?php } ?>