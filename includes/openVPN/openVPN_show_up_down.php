<?php
function showVPNUp($status){ ?>
    <div class="row">
    <div class="col-lg-12">
    <div class="panel panel-primary">
    <div class="panel-heading"><i class="fa fa-lock fa-fw"></i><?php echo _("OpenVPN manager"); ?></div>
    <div class="panel-body">
    <p><?php echo $status; ?></p>
    <div class="row">
    <div class="col-md-12">
    <div class="panel panel-default">
    <div class="panel-body">
    <h4>Client settings</h4>

<?php
}

function showVPNDown(){ ?>

    </div><!-- /.panel-body -->
    </div><!-- /.panel-default -->
    </div><!-- /.col-md-12 -->
    </div><!-- /.row -->
    </div><!-- /.panel-body -->
    </div><!-- /.panel-primary -->
    <div class="panel-footer"> Information provided by openvpn</div>
    </div><!-- /.col-lg-12 -->
    </div><!-- /.row -->


<?php
}
?>
