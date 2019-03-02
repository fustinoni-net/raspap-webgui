<?php
function showStartStopVPN(Array $files_array, bool $isVPNRunning){
    ?>
    <div class="panel panel-default" id='startStopVPN'>
        <div class="panel-body">

            <form role="form" action="?page=openvpn_conf" method="POST">
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
                        <label for="psk">PASSFRASE</label>
                        <input class="form-control" name="psk" id="psk" type="password">
                            <span class="input-group-btn">
                                <button class="btn btn-default" onclick="showPassword()" type="button">Show</button>
                            </span>
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