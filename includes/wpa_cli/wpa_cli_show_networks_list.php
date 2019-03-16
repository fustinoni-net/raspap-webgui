


<?php function showNetworksListData($wpa_networks_list){ ?>
    <h3><?php echo _("Network list"); ?></h3>
    <div>  
        <?php foreach ($wpa_networks_list as $network) { ?>                    
            <ul class="list-group">
                <li class="list-group-item"><h4><span class="label label-primary"><?php echo htmlspecialchars($network->ssid, ENT_QUOTES); ?></span></h4>
                    <ul class="list-group">
                        <li class="list-group-item"><strong>id: </strong><span class=""><?php echo htmlspecialchars($network->network_id, ENT_QUOTES); ?></span></li>
                        <li class="list-group-item"><strong>bssid: </strong><span class=""><?php echo htmlspecialchars($network->bssid, ENT_QUOTES); ?></span></li>
                        <li class="list-group-item"><strong>flags: </strong><span class=""><?php echo htmlspecialchars($network->flags, ENT_QUOTES); ?></span></li>
                        <li class="list-group-item">
                            <button type="submit" class="btn btn-warning" value="<?php echo urlencode (json_encode($network)); ?>" name="update"><?php echo _("Update"); ?>...</button>
                            <button type="submit" class="btn btn-danger" value="<?php echo urlencode (json_encode($network)); ?>" name="delete"><?php echo _("Delete"); ?>...</button>
                        </li>
                    </ul>
                </li>
            </ul>
        <?php } ?>
    </div>
<?php } ?>
