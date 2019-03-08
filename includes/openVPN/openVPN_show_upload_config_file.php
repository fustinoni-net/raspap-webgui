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
                <div class="row">
                    <div class="form-group col-md-8">
                        To use the VPN upload an ovpn file with the OpenVPN configuration.<br>
                        If the certificate key is password protected:
                        <ul>
                            <li>Create a .pass file containing the password.</li>
                            <li>Include into the ovpn file the line:<br>
                                <code> askpass /etc/openvpn/client/key.pass</code><br> (Instead of 'key' use the name of your .pass file) </li>
                            <li>Upload the .ovpn file and the .pass file. </li>
                        </ul>
                    </div>
                </div>
        </div>
    </div>


<?php

}
?>