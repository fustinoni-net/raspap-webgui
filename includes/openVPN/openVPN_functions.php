<?php

    define('OPENVPN_UNIX_SOCKET', '/tmp/openVpn');
    function saveUploadedFile()
    {
        $outMsg = '';

        $target_dir = RASPI_OPENVPN_CLIENT_CONFIG_DIR;
        $target_file = $target_dir . basename($_FILES["fileUpload"]["name"]);
        $uploadOk = 1;
        $fileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        // Check if file already exists
        if (file_exists($target_file)) {
            $outMsg = $outMsg."Sorry, file already exists.<BR>";
            $uploadOk = 0;
        }
        // Check file size
        if ($_FILES["fileUpload"]["size"] > 500000) {
            $outMsg = $outMsg."Sorry, your file is too large.<BR>";
            $uploadOk = 0;
        }
        // Allow certain file formats
        if ($fileType != "ovpn" && $fileType != "pass") {
            $outMsg = $outMsg."Sorry, only ovpn and pass files are allowed.<BR>";
            $uploadOk = 0;
        }
        // Check if $uploadOk is set to 0 by an error
        if ($uploadOk == 0) {
            $outMsg = $outMsg."Sorry, your file was not uploaded.<BR>";
            // if everything is ok, try to upload file
        } else {
            if (move_uploaded_file($_FILES["fileUpload"]["tmp_name"], $target_file)) {
                $outMsg = $outMsg."The file " . basename($_FILES["fileUpload"]["name"]) . " has been uploaded.";
            } else {
                $outMsg = $outMsg."Sorry, there was an error uploading your file.";
            }
        }

        return $outMsg;
    }

    function scanDirectories($rootDir, $allowext, $allData=array()) {
        $dirContent = scandir($rootDir);
        foreach($dirContent as $key => $content) {
            $path = $rootDir.$content;
            $ext = substr($content, strrpos($content, '.') + 1);

            if(in_array($ext, $allowext)) {
                if(is_file($path) && is_readable($path)) {
                    $allData[] = $path;
                }elseif(is_dir($path) && is_readable($path)) {
                    // recursive callback to open new directory
                    $allData = scanDirectories($path, $allData);
                }
            }
        }
        return $allData;
    }

    function startsWith ($string, $startString) 
    { 
        $len = strlen($startString); 
        return (substr($string, 0, $len) === $startString); 
    } 
    
    function unixSocketCom($command){
        
        try{
            $sock = stream_socket_client('unix://'.OPENVPN_UNIX_SOCKET, $errno, $errstr);

            fwrite($sock, $command."\r\n");

            $out = fread($sock, 4096);
            error_log($out);

            fclose($sock);
        }catch(Exception $e){
            $out = $e->getMessage();
        }

        return $out;
    }

    function getVPNState(){
        return unixSocketCom('state all');
    }
    
    function stopVPN(){
        exec('sudo '.ERMES_INSTALL_DIR.'utils/system/removeVPNRoute.sh', $return);
        //error_log("Stop: ".implode("\n",$return));
        return unixSocketCom('signal SIGTERM');
    }
    
    function startVPN($fileName, $ipAddress){
        exec('sudo '.ERMES_INSTALL_DIR.'utils/system/setVPNRoute.sh', $return);
        //error_log("Start string: ".'sudo '.ERMES_INSTALL_DIR.'utils/system/setVPNRoute.sh');
        //error_log("Start: ".implode("\n",$return));
        
        if($ipAddress!= null){
            //error_log("IP: ".$ipAddress);
            //error_log('sudo openvpn  --config '.$fileName.'  --remote '.$ipAddress.' --management '.OPENVPN_UNIX_SOCKET.' unix  > /dev/null 2>&1 &');
            exec( 'sudo openvpn  --config '.$fileName.'  --remote '.$ipAddress.' --management '.OPENVPN_UNIX_SOCKET.' unix  > /dev/null 2>&1 &');
        }else{
            //error_log('sudo openvpn  --config '.$fileName.' --management '.OPENVPN_UNIX_SOCKET.' unix  > /dev/null 2>&1 &');
            exec( 'sudo openvpn  --config '.$fileName.' --management '.OPENVPN_UNIX_SOCKET.' unix  > /dev/null 2>&1 &');
        }
        
        sleep(1);
    }