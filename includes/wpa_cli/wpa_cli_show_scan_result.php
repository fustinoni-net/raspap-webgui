
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
<h3><?php echo _("Scan result"); ?></h3>
            <div>  
                <?php foreach ($ssid_array as $ssid => $network) { ?>
                    <ul class="list-group">
                        <li class="list-group-item list-group-item-light">
                            <h4> <span class="label label-primary"><?php echo htmlspecialchars($ssid, ENT_QUOTES); ?></span><?php if ($ssid == $connected_ssid) { ?> <i class="fa fa-exchange fa-fw"></i><?php } ?></h4>
                                <ul class="list-group">
                                <?php foreach ($ssid_array[$ssid] as $net) { ?>                    
                                    <li class="list-group-item">
                                        <strong>BSSID: </strong> <span class="label label-default">  <?php echo htmlspecialchars($net->bssid, ENT_QUOTES); ?></span>
                                        <ul class="list-group">
                                            <li class="list-group-item"><strong>Status: </strong>
                                                <?php if ('Open' === $net->protocol) { ?><i class="fa fa-unlock fa-fw"></i><?php } else { ?><i class="fa fa-lock fa-fw"></i><?php } ?>
                                                <?php if ($net->configured) { ?><i class="fa fa-check-circle fa-fw"></i><?php } ?>
                                                <?php if ($net->connected) { ?><i class="fa fa-exchange fa-fw"></i><?php } ?>
                                            </li>
                                            <li class="list-group-item"><strong>Channel: </strong><span class=""><?php echo htmlspecialchars($net->channel, ENT_QUOTES); ?> (<?php echo htmlspecialchars($net->frequency, ENT_QUOTES); ?> MHz)</span></li>
                                            <li class="list-group-item"><strong>Signal: </strong><span class=""><?php echo htmlspecialchars($net->signal, ENT_QUOTES); ?> dB <?php echoSignalPercentage($net->signal) ?></span></li>
                                            <li class="list-group-item"><strong>Flags: </strong><span class=""><?php echo htmlspecialchars($net->flags, ENT_QUOTES); ?></span></li>
                                            <li class="list-group-item"><strong>Protocol: </strong><span class=""><?php echo htmlspecialchars($net->protocol, ENT_QUOTES); ?></span></li>
                                            <li class="list-group-item">
                                                  <?php if ($net->configured) { ?>
                                                      <?php if (!$net->connected) { ?>
                                                                <button type="submit" class="btn btn-success" name = "connect" value="<?php echo $net->configuration_data->network_id; ?>" ><?php echo _("Connect"); ?></button>
                                                      <?php } ?>  
                                                            <button type="submit" class="btn btn-warning" name = "update" value="<?php echo urlencode (json_encode($net)); ?>" ><?php echo _("Update"); ?>...</button>
                                                            <button type="submit" class="btn btn-danger" value="<?php echo urlencode (json_encode($net)); ?>" name="delete"><?php echo _("Delete"); ?>...</button>
                                                  <?php } else { ?>
                                                            <button type="submit" class="btn btn-info" name = "add" value="<?php echo urlencode (json_encode($net)); ?>" ><?php echo _("Add"); ?>...</button>
                                                  <?php } ?>
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
