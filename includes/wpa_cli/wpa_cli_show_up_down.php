<?php
    define('WPA_CLI_PAGE_REF', 'wpa_cli_conf');
    
    function showNavigationButton(){
        ?>
                        <a href=".?page=<?php echo WPA_CLI_PAGE_REF; ?>" class="btn btn-info" id="update"><?php echo _("Scan")."..."; ?></a>
                        <a href=".?page=<?php echo WPA_CLI_PAGE_REF; ?>&netList=yes" class="btn btn-info" id="showNet"><?php echo _("Net list")."..."; ?></a>
                        <a href=".?page=<?php echo WPA_CLI_PAGE_REF; ?>&wps=yes" class="btn btn-success" id="showNet"><?php echo _("WPS")."..."; ?></a>
<?php
    }
    
    
    function showUp(StatusMessages $status)



    {?>
        <div class="row">
          <div class="col-lg-12">
            <div class="panel panel-primary">
              <div class="panel-heading"><i class="fa fa-signal fa-fw"></i> <?php echo _("Configure client"); ?></div>
                <!-- /.panel-heading -->
                <div class="panel-body">
                    <p><?php $status->showMessages(); ?></p>
                    <?php showNavigationButton(); ?>
                    <br>
                    <form method="POST" action="?page=<?php echo WPA_CLI_PAGE_REF; ?>" name="wpa_cli_conf_form">
                        <?php CSRFToken() ?>
<?php } ?>

<?php function showDown(bool $showNavButt) {?>
                    </form>
                </div><!-- ./ Panel body -->
                <div class="panel-footer"><?php //echo _("<strong>Note:</strong> WEP access points appear as 'Open'. RaspAP does not currently support connecting to WEP"); ?>
                    <?php if($showNavButt) showNavigationButton(); ?>
                </div>
            </div><!-- /.panel-primary -->
          </div><!-- /.col-lg-12 -->
        </div><!-- /.row -->
        
<?php } ?>
