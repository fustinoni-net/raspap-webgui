<?php
    function showUp(StatusMessages $status)

    {?>
        <div class="row">
          <div class="col-lg-12">
            <div class="panel panel-primary">
              <div class="panel-heading"><i class="fa fa-signal fa-fw"></i> <?php echo _("Configure client"); ?></div>
                <!-- /.panel-heading -->
                <div class="panel-body">
                    <p><?php $status->showMessages(); ?></p>
                    <h4><?php echo _("Client settings"); ?></h4>
                    <div class="btn-group btn-block">
                        <a href=".?<?php echo htmlspecialchars($_SERVER['QUERY_STRING'], ENT_QUOTES); ?>" style="padding:10px;float: right;display: block;position: relative;margin-top: -55px;" class="col-md-2 btn btn-info" id="update"><?php echo _("Rescan"); ?></a>
                    </div>

                    <form method="POST" action="?page=wpa_cli_conf" name="wpa_cli_conf_form">
                        <?php CSRFToken() ?>
<?php } ?>

<?php function showDown() {?>
                    </form>
                </div><!-- ./ Panel body -->
                <div class="panel-footer"><?php //echo _("<strong>Note:</strong> WEP access points appear as 'Open'. RaspAP does not currently support connecting to WEP"); ?>
                </div>
            </div><!-- /.panel-primary -->
          </div><!-- /.col-lg-12 -->
        </div><!-- /.row -->
        
<?php } ?>
