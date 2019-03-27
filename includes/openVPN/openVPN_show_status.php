<?php
function showVPNStatus(){
    ?>
    <div class="panel panel-default" id='showStatus'>
        <div class="panel-body">
            <div class="row">
                <div class="form-group col-md-8">
                    <label for="state">Client Log</label>
                    <div class="alert alert-success alert-dismissable" id="state">'.<?php echo nl2br(htmlspecialchars(getVPNState(), ENT_QUOTES));  ?>.'<button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button></div>
                </div>
                <div class="form-group col-md-8">
                    <div class="btn-group btn-block">
                        <a href=".?<?php echo htmlspecialchars($_SERVER['QUERY_STRING'], ENT_QUOTES); ?>" class="col-md-2 btn btn-info" id="update"><?php echo _("Refresh"); ?></a>
                    </div>
                </div>
            </div>

        </div>
    </div>
<?php
}

