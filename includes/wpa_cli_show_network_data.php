       
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
<div class="form-group">
        <script>
         function showPassword() {
             var x = document.getElementsByName("psk")[0];
             if (x.type === "password") {
                 x.type = "text";
             } else {
                 x.type = "password";
             }
         }
        </script>

        <input type="hidden" name="oldNetData" value="<?php echo urlencode (json_encode($net)); ?>">
        <input type="hidden" name="idNet" value="<?php 
            if ($net->configuration_data !== null && $net->configuration_data->network_id !== ''){ echo $net->configuration_data->network_id;} 
            ?>">
        
        <input type="hidden" name="protocol" value="<?php 
            
        if ($net->configuration_data !== null && $net->configuration_data->protocol !== ''){
            echo $net->configuration_data->protocol;
        }
        else{
            if (strpos($net->flags, "EAP")!= false){ 
                echo "EAP";
            }elseif (strpos($net->flags, "WPA2") != false){
                echo "WPA2";
            }elseif (strpos($net->flags, "WPA") != false){
                echo "WPA";
            }elseif (strpos($net->flags, "WEP") != false) {
                echo "WEP";
            } else {
                echo "OPEN";
            }
        }
            ?>">

            <div class="form-group">
                <label for="ssid">SSID</label>
                <input class="form-control" name="ssid" id="ssid" type="text" value="<?php echo $net->ssid; ?>" readonly >
            </div>        
            <div class="form-group">
                <label for="bssid">BSSID</label>
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
                <div class="form-group">
                    <label for="identity">Identity</label>
                    <?php //error_log("Network: ".json_encode($net)); ?>
                    <input class="form-control" name="identity" id="ssid" type="text" <?php  if ($net->configuration_data != null && '' != $net->configuration_data->identity) echo 'value="'.$net->configuration_data->identity.'"' ; ?>>
                </div> 
                <div class="form-group">
                    <label for="psk">PASSFRASE</label>
                    <input class="form-control" name="psk" id="psk" type="password" value="<?php  if ($net->configuration_data != null) echo $net->configuration_data->psk ; ?>" onKeyUp="CheckPSK(this, 'update')">
                    <span class="input-group-btn">
                      <button class="btn btn-default" onclick="showPassword()" type="button">Show</button>
                    </span>
                </div>                
                <div class="form-group">
                    <label for="eap">EAP</label>
                    <select class="form-control" id="eap" name="eap" <?php  if ($net->configuration_data != null) echo 'value="'.$net->configuration_data->priority.'"' ; ?>>
                        <option value="PEAP" >peap</option>
                        <option value="TLS" >tls</option>
                        <option value="TTLS" >ttls</option>
                        <option value="PWD" >pwd</option>
                        <option value="SIM" >sim</option>
                        <option value="AKA" >aka</option>
                        <option value="AKA'" >aka'</option>
                        <option value="FAST" >fast</option>
                        <option value="LEAP" >leap</option>
                     </select>
                </div>                                
                <div class="form-group">
                    <label for="key_mgmt">key_mgmt</label>
                    <select class="form-control" id="key_mgmt" name="key_mgmt" <?php  if ($net->configuration_data != null) echo 'value="'.$net->configuration_data->key_mgmt.'"' ; ?>>
                        <option value="NONE"                >NONE</option>
                        <option value="WPA-PSK"             >WPA-PSK</option>
                        <option value="WPA-EAP"             >WPA-EAP</option>
                        <option value="WPA-NONE"            >WPA-NONE</option>
                        <option value="FT-PSK"              >FT-PSK</option>             
                        <option value="FT-EAP"              >FT-EAP</option>             
                        <option value="FT-EAP-SHA384"       >FT-EAP-SHA384</option>      
                        <option value="WPA-PSK-SHA256"      >WPA-PSK-SHA256</option>     
                        <option value="WPA-EAP-SHA256"      >WPA-EAP-SHA256</option>     
                        <option value="SAE"                 >SAE</option>                
                        <option value="FT-SAE"              >FT-SAE</option>             
                        <option value="WPA-EAP-SUITE-B"     >WPA-EAP-SUITE-B</option>    
                        <option value="WPA-EAP-SUITE-B-192" >WPA-EAP-SUITE-B-192</option>
                        <option value="OSEN"                >OSEN</option>               
                        <option value="FILS-SHA256"         >FILS-SHA256</option>        
                        <option value="FILS-SHA384"         >FILS-SHA384</option>        
                        <option value="FT-FILS-SHA256"      >FT-FILS-SHA256</option>     
                        <option value="FT-FILS-SHA384"      >FT-FILS-SHA384</option>     
                        <option value="OWE"                 >OWE</option>                
                        <option value="DPP"                 >DPP</option>                                
                     </select>
                </div>                                

            <?php }elseif (strpos($net->flags, "WPA") != false || strpos($net->flags, "WEP") != false) {?>
                <!--WPA2-PSK or WPA-PSK or WEP-->
                <!--WEP no password, no validation empty-->
                <div class="form-group">
                    <label for="psk">PASSFRASE</label>
                    <input class="form-control" name="psk" id="psk" type="password" value="<?php  if ($net->configuration_data != null) echo $net->configuration_data->psk ; ?>" onKeyUp="CheckPSK(this, 'update')">
                    <span class="input-group-btn">
                      <button class="btn btn-default" onclick="showPassword()" type="button">Show</button>
                    </span>
                </div>                
            <?php }?>
            <!--open->

            <!--Common part-->
            <div class="form-group">
                <label for="priority">PRIORITY</label>
                <input class="form-control" id="priority" name="priority" type="text" list="priorityValues" pattern="-{0,1}[0-9]{1,3}" value="<?php  if ($net->configuration_data != null) echo $net->configuration_data->priority ; else echo '0';?>">
                <datalist id="priorityValues">
                    <option value="0" >
                    <option value="10">
                    <option value="20">
                    <option value="30">
                    <option value="40">
                </datalist>                
            </div>                        
            <div class="form-group">
                <label for="disabled">DISABLE</label>
                <input class="form-control" id="disabled" name="disabled" type="checkbox" <?php  if ($net->configuration_data != null && $net->configuration_data->disabled) echo 'checked'; ?>>
            </div>
            <div class="form-group">
                <label for="scan_ssid">SCAN SSID</label>
                <input class="form-control" id="scan_ssid" name="scan_ssid" type="checkbox" <?php  if ($net->configuration_data != null && $net->configuration_data->scanSSID) echo 'checked'; ?>>
            </div>
            <div class="form-group">
                <div class="btn-group btn-block ">
                    <button type="submit" class="col-xs-4 col-md-4 btn btn-warning" name = "addNetwork" value="addNetwork" >
                        <?php if ($net->configuration_data !== null && $net->configuration_data->network_id !== ''){echo _("Update");} else {echo _("Add");} ?>
                    </button>
                </div>
            </div>
</div>
<?php } ?>
