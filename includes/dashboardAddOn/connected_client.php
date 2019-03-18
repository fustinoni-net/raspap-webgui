<?php

function showConnectedClient(){

    ?>
                    <div class="col-md-6">
                       <div class="panel panel-default">
                          <div class="panel-body wireless">
                            <h4><?php echo _("Connected Devices"); ?></h4>
                            <div class="table-responsive">
                              <table class="table table-hover">
                                <thead>
                                  <tr>
                                    <th><?php echo _("Host name"); ?></th>
                                    <th><?php echo _("IP Address"); ?></th>
                                    <th><?php echo _("MAC Address"); ?></th>
                                  </tr>
                                </thead>
                                <tbody>
<?php
$clients = '';
//exec('cat '.RASPI_DNSMASQ_LEASES.'| grep -E $(arp -i '.'ap0'.' | grep -oE "(([0-9]|[a-f]|[A-F]){2}:){5}([0-9]|[a-f]|[A-F]){2}" | tr "\n" "\|" | sed "s/.$//")', $clients);
exec('cat '.RASPI_DNSMASQ_LEASES.'| grep -E $(iw dev  '.'ap0'.' station dump | grep -oE "(([0-9]|[a-f]|[A-F]){2}:){5}([0-9]|[a-f]|[A-F]){2}" | tr "\n" "\|" | sed "s/.$//")', $clients);
//error_log('cat '.RASPI_DNSMASQ_LEASES.'| grep -E $(arp -i '.RASPI_WIFI_CLIENT_INTERFACE.' | grep -oE "(([0-9]|[a-f]|[A-F]){2}:){5}([0-9]|[a-f]|[A-F]){2}" | tr "\n" "\|" | sed "s/.$//")');

//error_log("Clients: ".implode("\n",$clients));
foreach( $clients as $client ) {
    $client_items = explode(' ', $client);
    echo '<tr>'.PHP_EOL;
    echo '<td>'.htmlspecialchars($client_items[3], ENT_QUOTES).'</td>'.PHP_EOL;
    echo '<td>'.htmlspecialchars($client_items[2], ENT_QUOTES).'</td>'.PHP_EOL;
    echo '<td>'.htmlspecialchars($client_items[1], ENT_QUOTES).'</td>'.PHP_EOL;
    echo '</tr>'.PHP_EOL;
};
?>
                                </tbody>
                              </table>
                            </div><!-- /.table-responsive -->
                          </div><!-- /.panel-body -->
                        </div><!-- /.panel-default -->
                     </div>
<?php
}