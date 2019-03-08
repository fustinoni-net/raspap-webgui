<?php
function showStartStopVPN(Array $files_array, bool $isVPNRunning){
    ?>
    <div class="panel panel-default" id='startStopVPN'>
        <div class="panel-body">

            <form role="form" action="?page=openvpn_conf" method="POST">

                <div class="row">
                    <div class="form-group col-md-8">
                        <label for="sel1">Select OpenVPN configuration file:</label>
                        <select class="form-control" id="sel1" name="confFileName">
                            <?php
                            foreach ($files_array as $line) {
                                echo '<option>' . $line . '</option>';
                            }
                            ?>
                        </select>
                    </div>
                </div>
                <div class="row">
                    <div class="form-group col-md-8">
                        <label for="ipServer">Optional the IP address of the OpenVPN server</label>
                        <input class="form-control" name="ipServer" id="ipServer" type="text" pattern="[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}">
                    </div>
                </div>
                <div class="row">
                    <div class="form-group col-md-8">

                        <?php
                        //Cambia con ilk valore giusto
                        if ($isVPNRunning) {
                            echo '<input type="submit" class="btn btn-warning" name="StopOpenVPN" value="Stop OpenVPN" />', PHP_EOL;
                        } else {
                            echo '<input type="submit" class="btn btn-success" name="StartOpenVPN" value="Start OpenVPN" />', PHP_EOL;
                        }
                        ?>
                    </div>
                </div>

            </form>
        </div>
    </div>
<?php
}
?>