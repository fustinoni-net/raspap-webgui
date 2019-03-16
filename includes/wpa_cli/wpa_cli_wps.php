 <?php function showWPSPage() { ?>       
 <h3><?php echo _("WPS"); ?></h3>
        
 The WPS functions help you securely pair your WiFi devices automatically with the router.
            
 <hr>
     
 <h4>WPS-PBC: Push button on devices</h4>
<div class="form-group">
    <label>To pair the devices press Pair on the router and then on this page. This  device will try pairing for 2 minutes after you press Pair.</label>
    <button type="submit" class="btn btn-success" value="wpsPbc" name="wpsPbc"><?php echo _("Pair"); ?></button>
</div>
<br>
<hr>
<br>
<h4>WPS-PIN: use PIN from another device</h4>
<div class="form-group">
    <label >To pair your devices, enable pairing on another device and enter the WPS-PIN that is provided by that device here.</label>
    <label for="pin">WPS-PIN provided by router</label>
    <input class="form-control" name="pin" id="pin" type="text" >
    <button type="submit" class="btn btn-success" value="wpsPin" name="wpsPin"><?php echo _("Pair"); ?></button>
</div>

<?php } ?>