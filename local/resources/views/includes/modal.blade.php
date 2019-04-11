<div id="modal-alert" class="modal" tabindex="-1" role="dialog" aria-labelledby="" aria-hidden="true">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title text-center text-danger">
                    <span class="icon-attention-1" aria-hidden="true"></span>
                    <span id="title-modal-line-unit">{{ strtoupper(trans('shared/common.alert')) }} !</span>
                </h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-sm-12">
                        <p class="alert-message">Alert Message</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-sm btn-danger" data-dismiss="modal">{{ trans('shared/common.close') }}</button>
            </div>
        </div>
    </div>
</div>

<div id="modal-welcome" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title text-center">Welcome</h3>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-sm-3 text-center">
                        <?php 
                        $foto = !empty(Auth::user()->foto) ? Config::get('app.paths.foto-user').'/'.Auth::user()->foto : 'images/users/user.png'; 
                        $fotoUser = file_exists($foto) ? $foto : 'images/users/user.png';
                        ?>
                        <a ><img style="height: 140px; width: auto;" src="{{ asset($fotoUser) }}"></a>
                    </div>
                    <div class="col-sm-9">
                        <h3 style="margin-top: 0px;">{{ \Auth::user()->full_name }}</h3>
                        <h1 style="margin-top: 0px;"><strong>{{ \Session::get('currentRole')->name }}</strong></h1>
                        <h2 style="margin-top: 0px;"><strong>{{ \Session::get('currentBranch')->branch_name }}</strong></h2>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-sm btn-warning" data-dismiss="modal">{{ trans('shared/common.close') }}</button>
            </div>
        </div>
    </div>
</div>