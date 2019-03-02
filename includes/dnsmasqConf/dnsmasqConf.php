<?php

define( 'JAIL_FILE', ERMES_INSTALL_DIR.'jail');
define( 'ADS_BLOCK_FILE', ERMES_INSTALL_DIR.'ads_block');
define( 'FK_CONN_CHECK_FILE', ERMES_INSTALL_DIR.'kk_conn_check');

class DnsMasqConf{
    public $setJail;
    public $setADSBlock;
    public $setFakeConnectionCheck;
}

function isActive($fileName){
    if (file_exists($fileName)) {
        return true;
    }
    return false;
}

function readConfig(){
    $conf = new DnsMasqConf();
    $conf->setADSBlock = isActive(ADS_BLOCK_FILE);
    $conf->setFakeConnectionCheck = isActive(FK_CONN_CHECK_FILE);
    $conf->setJail = isActive(JAIL_FILE);

    return $conf;
}

function setConfig(DnsMasqConf $currentCong, DnsMasqConf $newConf){
    if ($currentCong->setADSBlock != $newConf->setADSBlock){
        ($newConf->setADSBlock) ? touch(ADS_BLOCK_FILE) : unlink(ADS_BLOCK_FILE);
    }
    if ($currentCong->setFakeConnectionCheck != $newConf->setFakeConnectionCheck){
        ($newConf->setFakeConnectionCheck) ? touch(FK_CONN_CHECK_FILE) : unlink(FK_CONN_CHECK_FILE);
    }
    if ($currentCong->setJail != $newConf->setJail){
        ($newConf->setJail) ? touch(JAIL_FILE) : unlink(JAIL_FILE);
    }
}

function DisplayDnsMasqConf($formLandingPage){

    $dnsMasqConf = readConfig();
    
    if (isset($_POST['update'])) {
        $newConf = new DnsMasqConf();

        $newConf->setADSBlock = (isset($_POST['adsBlock']) ? true : false);
        $newConf->setFakeConnectionCheck = (isset($_POST['connCheck']) ? true : false);
        $newConf->setJail = (isset($_POST['jail']) ? true : false);

        setConfig($dnsMasqConf, $newConf);
        exec('sudo '.ERMES_INSTALL_DIR.'setDnsMasqOptions.sh');
        $dnsMasqConf = readConfig();
    }

    ?>
                  <div class="row">
                      <div class="col-md-6">
                        <div class="panel panel-default">
                          <div class="panel-body">
                            <h4><?php echo _("DNSMASQ Configuration"); ?></h4>

                              <form action='?page=" <?php echo $formLandingPage; ?> "' method="POST">

                                  <div class="form-group">
                                      <label for="adsBlock">ADS Block</label>
                                      <input class="form-control" id="adsBlock" name="adsBlock" type="checkbox" <?php  if ($dnsMasqConf->setADSBlock) {echo 'checked';} ?>>
                                  </div>
                                  <div class="form-group">
                                      <label for="connCheck">Local Android connection check </label>
                                      <input class="form-control" id="connCheck" name="connCheck" type="checkbox" <?php  if ($dnsMasqConf->setFakeConnectionCheck){ echo 'checked';} ?>>
                                  </div>
                                  <div class="form-group">
                                      <label for="jail">Jail (resolve all domain locally) </label>
                                      <input class="form-control" id="jail" name="jail" type="checkbox" <?php  if ($dnsMasqConf->setJail){ echo 'checked';} ?>>
                                  </div>
                                <div class="form-group">
                                    <div class="btn-group btn-block ">
                                        <button type="submit" class="col-xs-4 col-md-4 btn btn-danger" value="upate" name="update"><?php echo _("Update"); ?></button>
                                    </div>
                                </div>
                              </form>
                          </div><!-- /.panel-body -->
                        </div><!-- /.panel-default -->
                        </div><!-- /.col-md-6 -->
                    </div><!-- /.row -->
    <?php
}