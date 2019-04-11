@extends('layouts.master')

@section('title', trans('general-ledger/menu.setting-journal'))

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="widget">
            <div class="widget-header">
                <h2><i class="fa fa-truck"></i> <strong>{{ $title }}</strong> {{ trans('general-ledger/menu.setting-journal') }}</h2>
                <div class="additional-btn">
                    <a href="#" class="widget-maximize"><i class="icon-resize-full-1"></i></a>
                    <a href="#" class="widget-toggle"><i class="icon-down-open-2"></i></a>
                    <a href="#" class="widget-help"><i class="icon-help-2"></i></a>
                </div>
            </div>
            <div class="widget-content padding">
                <div id="horizontal-form">
                    <form  role="form" id="add-form" class="form-horizontal" method="post" action="{{ url($url . '/save') }}">
                        {{ csrf_field() }}
                        <input type="hidden" name="id" value="{{ $model->setting_journal_id }}">
                        <div class="col-sm-6 portlets">
                            <div class="form-group">
                                <label for="settingName" class="col-sm-4 control-label">{{ trans('general-ledger/fields.setting-name') }}</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="settingName" name="settingName"  value="{{ $model->setting_name }}" disabled>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="segmentName" class="col-sm-4 control-label">{{ trans('general-ledger/fields.segment-name') }}</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="segmentName" name="segmentName"  value="{{ $model->segment_name }}" disabled>
                                </div>
                            </div>
                            <?php $coa = !empty($model->coa) ? $model->coa->coa_code.' - '.$model->coa->description : ''; ?>
                            <div class="form-group {{ $errors->has('coaId') ? 'has-error' : '' }}" id="form-group-coa">
                                <label for="coa" class="col-sm-4 control-label">{{ trans('general-ledger/fields.account') }} <span class="required">*</span></label>
                                <div class="col-sm-8">
                                    <div class="input-group">
                                        <input type="hidden" id="coaId" name="coaId" value="{{ count($errors) > 0 ? old('coaId') : $model->coa_id }}">
                                        <input type="text" class="form-control" id="coa" name="coa" value="{{ count($errors) > 0 ? old('coa') : $coa }}" readonly>
                                        <span class="btn input-group-addon" id="show-lov-coa"><i class="fa fa-search"></i></span>
                                    </div>
                                    @if($errors->has('coaId'))
                                        <span class="help-block">{{ $errors->first('coaId') }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="description" class="col-sm-4 control-label">{{ trans('shared/common.description') }}</label>
                                <div class="col-sm-8">
                                    <textarea class="form-control" id="description" name="description">{{ count($errors) > 0 ? old('description') : $model->description }}</textarea>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-12 data-table-toolbar text-right">
                            <div class="form-group">
                                <a href="{{ URL($url) }}" class="btn btn-sm btn-warning"><i class="fa fa-reply"></i> {{ trans('shared/common.cancel') }}</a>
                                <button type="submit" class="btn btn-sm btn-primary"><i class="fa fa-save"></i> {{ trans('shared/common.save') }}</button>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="clearfix"></div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('modal')
@parent
<div id="modal-lov-coa" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title text-center">{{ trans('general-ledger/fields.coa') }}</h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-sm-12">
                        <div class="form-group search-lov">
                            <label for="searchCoa" class="col-sm-1 col-sm-offset-8 control-label">{{ trans('shared/common.search') }}</label>
                            <div class="col-sm-3">
                                <input type="text" class="form-control input-xs" id="searchCoa" name="searchCekGiro">
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-12">
                        <table id="table-lov-coa" class="table table-hover table-striped table-bordered" cellspacing="0" width="100%">
                            <thead>
                                <tr>
                                    <th>{{ trans('general-ledger/fields.coa-code') }}</th>
                                    <th>{{ trans('general-ledger/fields.segment-name') }}</th>
                                    <th>{{ trans('shared/common.description') }}</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-sm btn-warning" data-dismiss="modal">{{ trans('shared/common.close') }}</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
@parent()
<script type="text/javascript">
$(document).on('ready', function(){
    $('#show-lov-coa').on('click', showLovCoa);
    $('#searchCoa').on('keyup', loadLovCoa);
    $('#table-lov-coa tbody').on('click', 'tr', selectCoa);
});

var showLovCoa = function() {
    $('#searchCoa').val('');
    loadLovCoa(function() {
        $('#modal-lov-coa').modal('show');
    });
};

var xhrCoa;
var loadLovCoa = function(callback) {
    if(xhrCoa && xhrCoa.readyState != 4){
        xhrCoa.abort();
    }
    xhrCoa = $.ajax({
        url: '{{ URL($url.'/get-json-coa') }}',
        data: {search: $('#searchCoa').val(), segmentName: $('#segmentName').val()},
        success: function(data) {
            $('#table-lov-coa tbody').html('');
            data.forEach(function(item) {
                $('#table-lov-coa tbody').append(
                    '<tr data-json=\'' + JSON.stringify(item) + '\'>\
                        <td>' + item.coa_code + '</td>\
                        <td>' + item.segment_name + '</td>\
                        <td>' + item.description + '</td>\
                    </tr>'
                );
            });

            if (typeof(callback) == 'function') {
                callback();
            }
        }
    });
};

var selectCoa = function() {
    var data = $(this).data('json');
    $('#coaId').val(data.coa_id);
    $('#coa').val(data.coa_code + ' - ' + data.description);

    $('#modal-lov-coa').modal('hide');
};
</script>
@endsection
