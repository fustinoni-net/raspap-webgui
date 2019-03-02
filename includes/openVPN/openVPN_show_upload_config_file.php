<?php
function showUploadConfigFile(){
    ?>
    <div class="panel panel-default" id='uploadConfigFile'>
        <div class="panel-body">
            <form role="form" action="?page=openvpn_conf" method="post" enctype="multipart/form-data">
                <div class="row">
                    <div class="form-group col-md-8">
                        <label for="fileUpload">Import OpenVPN configuration file (.ovpn)</label>
                        <input type="file" name="fileUpload" id="fileUpload" accept=".ovpn,.pass">
                    </div>
                </div>
                <div class="row">
                    <div class="form-group col-md-8">
                        <input type="submit" value="Upload file" name="submit">
                    </div>
                </div>
            </form>
        </div>
    </div>


<?php

}
?>