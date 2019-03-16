 <?php function showCancelNetworkData($net) { ?>       
    <h3><?php echo _("Cancel network"); ?></h3>
        <input type="hidden" name="oldNetData" value="<?php echo urlencode (json_encode($net)); ?>">

            <div class="form-group">
                <label for="ssid">SSID</label>
                <input class="form-control" name="ssid" id="ssid" type="text" value="<?php echo $net->ssid; ?>" readonly >
            </div>        
            <div class="form-group">
                <label for="bssid">BSSID</label>
                <input class="form-control" name="bssid" id="bssid" type="text" value="<?php echo $net->bssid; ?>" readonly >
            </div>

            <div class="form-group">
                <div class="btn-group btn-block ">
                    <button type="submit" class="col-xs-4 col-md-4 btn btn-danger" value="deleteNetwork" name="deleteNetwork"><?php echo _("Delete"); ?></button>
                </div>
            </div>
<?php } ?>