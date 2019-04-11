@extends('layouts.master')

@section('title', trans('operational/menu.resi-stock-correction'))

<?php 
    use App\Modules\Operational\Model\Transaction\ResiStockCorrection; 
?>
@section('header')
@parent
<style type="text/css">
    #table-resi tbody tr{
        cursor: pointer;
    }
</style>
@endsection
@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="widget">
            <div class="widget-header">
                <h2><i class="fa fa-truck"></i> <strong>{{ $title }}</strong> {{ trans('operational/menu.resi-stock-correction') }}</h2>
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
                        <input type="hidden" name="id" value="{{ $model->resi_stock_correction_id }}">
                        <div class="col-sm-6 portlets">
                            <div class="form-group {{ $errors->has('correctionNumber') ? 'has-error' : '' }}">
                                <label for="correctionNumber" class="col-sm-4 control-label">{{ trans('operational/fields.correction-number') }} <span class="required">*</span></label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="correctionNumber" name="correctionNumber" value="{{ count($errors) > 0 ? old('correctionNumber') : $model->delivery_order_number }}" readonly>
                                    @if($errors->has('correctionNumber'))
                                    <span class="help-block">{{ $errors->first('correctionNumber') }}</span>
                                    @endif
                                </div>
                            </div>
                            <?php
                            if (count($errors) > 0) {
                                $date = !empty(old('date')) ? new \DateTime(old('date')) : new \DateTime();
                            } else {
                                $date = !empty($model->created_date) ? new \DateTime($model->created_date) : new \DateTime();
                            }
                            ?>
                            <div class="form-group {{ $errors->has('date') ? 'has-error' : '' }}">
                                <label for="date" class="col-sm-4 control-label">{{ trans('shared/common.date') }}</label>
                                <div class="col-sm-8">
                                    <div class="input-group">
                                        <input type="text" id="date" name="date" class="form-control datepicker-input" data-mask="99-99-9999" placeholder="dd-mm-yyyy" value="{{ $date !== null ? $date->format('d-m-Y') : '' }}">
                                        <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                    </div>
                                    @if($errors->has('date'))
                                    <span class="help-block">{{ $errors->first('date') }}</span>
                                    @endif
                                </div>
                            </div>
                            <?php
                                $modelOfficial           = $model->officialReport;
                                $officialReportNumber = !empty($modelOfficial) ? $modelOfficial->official_report_number : '' ; 
                            ?>
                            <div class="form-group {{ $errors->has('officialReportId') ? 'has-error' : '' }}">
                                <label for="officialReportNumber" class="col-sm-4 control-label">{{ trans('operational/fields.official-report-number') }} <span class="required">*</span></label>
                                <div class="col-sm-8">
                                    <div class="input-group">
                                    <input type="hidden" class="form-control" id="officialReportId" name="officialReportId" value="{{ count($errors) > 0 ? old('officialReportId') : $model->official_report_id }}">
                                    <input type="text" class="form-control" id="officialReportNumber" name="officialReportNumber" value="{{ count($errors) > 0 ? old('officialReportNumber') : $officialReportNumber }}" readonly>
                                    <span class="btn input-group-addon" id="modalService" data-toggle="modal" data-target="#modal-official"><i class="fa fa-search"></i></span>
                                    </div>
                                    @if($errors->has('officialReportId'))
                                    <span class="help-block">{{ $errors->first('officialReportId') }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="form-group {{ $errors->has('type') ? 'has-error' : '' }}">
                                <label for="type" class="col-sm-4 control-label">{{ trans('shared/common.type') }} <span class="required">*</span></label>
                                <div class="col-sm-8">
                                    <select class="form-control" id="type" name="type">
                                        <option value="">{{ trans('shared/common.please-select') }} {{ trans('shared/common.type') }}</option>
                                        <?php $stringType = count($errors) > 0 ? old('type') : $model->type ?>
                                        @foreach($optionType as $type)
                                        <option value="{{ $type }}" {{ $type == $stringType ? 'selected' : '' }}>{{ $type }}</option>
                                        @endforeach
                                    </select>
                                    @if($errors->has('type'))
                                    <span class="help-block">{{ $errors->first('type') }}</span>
                                    @endif
                                </div>
                            </div>
                            <?php
                                if($model->type == ResiStockCorrection::DELIVERY_ORDER){
                                    $doLine       = $model->doLine;
                                    $doCtId       = !empty($doLine) ? $doLine->delivery_order_line_id : '';
                                    $doHeader     = !empty($doLine) ? $doLine->header : '';
                                    $doCtNumber   = !empty($doHeader) ? $doHeader->delivery_order_number : '';
                                    $resi         = !empty($doLine) ? $doLine->resi : null;
                                    $resiHeaderId = !empty($resi) ? $resi->resi_header_id : '' ;
                                    $resiNumber   = !empty($resi) ? $resi->resi_number : '' ;
                                }elseif($model->type == ResiStockCorrection::CUSTOMER_TAKING){
                                    $ctTransact   = $model->customerTakingTransact;
                                    $doCtId       = !empty($ctTransact) ? $ctTransact->customer_taking_transact_id : '';
                                    $doCtNumber   = !empty($ctTransact) ? $ctTransact->customer_taking_transact_number : '';
                                    $ct           = !empty($ctTransact) ? $ctTransact->customerTaking : null;
                                    $resi         = !empty($ct) ? $ct->resi : null;
                                    $resiHeaderId = !empty($resi) ? $resi->resi_header_id : '' ;
                                    $resiNumber   = !empty($resi) ? $resi->resi_number : '' ; 
                                }elseif($model->type == ResiStockCorrection::CORRECTION_PLUS || $model->type == ResiStockCorrection::CORRECTION_MINUS){
                                    $doCtId       = '';
                                    $doCtNumber   = '';
                                    $resi         = $model->resi;
                                    $resiHeaderId = !empty($resi) ? $resi->resi_header_id : '' ;
                                    $resiNumber   = !empty($resi) ? $resi->resi_number : '' ; 
                                }else{
                                    $doCtId       = '';
                                    $doCtNumber   = '';
                                    $resiHeaderId = '';
                                    $resiNumber   = ''; 
                                }
                            ?>
                            <div class="form-group {{ $errors->has('resiNumber') ? 'has-error' : '' }}">
                                <label for="resiNumber" class="col-sm-4 control-label">{{ trans('operational/fields.resi-number') }} <span class="required">*</span></label>
                                <div class="col-sm-8">
                                    <div class="input-group">
                                        <input type="hidden" class="form-control" id="resiHeaderId" name="resiHeaderId" value="{{ count($errors) > 0 ? old('resiHeaderId') : $resiHeaderId }}">
                                        <input type="text" class="form-control" id="resiNumber" name="resiNumber" value="{{ count($errors) > 0 ? old('resiNumber') : $resiNumber }}" readonly>
                                        <span class="btn input-group-addon" id="modalResi" data-toggle="modal" data-target="#modal-resi"><i class="fa fa-search"></i></span>
                                        <span class="btn input-group-addon" id="modalResiDo" data-toggle="modal" data-target="#modal-resi-do"><i class="fa fa-search"></i></span>
                                        <span class="btn input-group-addon" id="modalResiCt" data-toggle="modal" data-target="#modal-resi-ct"><i class="fa fa-search"></i></span>
                                    </div>
                                    @if($errors->has('resiNumber'))
                                    <span class="help-block">{{ $errors->first('resiNumber') }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="form-group {{ $errors->has('doCtNumber') ? 'has-error' : '' }}">
                                <label for="doCtNumber" class="col-sm-4 control-label">{{ trans('operational/fields.do-ct-number') }} </label>
                                <div class="col-sm-8">
                                    <input type="hidden" class="form-control" id="doCtId" name="doCtId" value="{{ count($errors) > 0 ? old('doCtId') : $doCtId }}" readonly>
                                    <input type="text" class="form-control" id="doCtNumber" name="doCtNumber" value="{{ count($errors) > 0 ? old('doCtNumber') : $doCtNumber }}" readonly>
                                    @if($errors->has('doCtNumber'))
                                    <span class="help-block">{{ $errors->first('doCtNumber') }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="form-group {{ $errors->has('totalColy') ? 'has-error' : '' }}">
                                <label for="totalColy" class="col-sm-4 control-label">{{ trans('operational/fields.total-coly') }} </label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control currency text-right" id="totalColy" name="totalColy" value="{{ count($errors) > 0 ? old('totalColy') : $model->total_coly }}" >
                                    @if($errors->has('totalColy'))
                                    <span class="help-block">{{ $errors->first('totalColy') }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="form-group {{ $errors->has('note') ? 'has-error' : '' }}">
                                <label for="note" class="col-sm-4 control-label">{{ trans('shared/common.note') }} <span class="required">*</span></label>
                                <div class="col-sm-8">
                                    <textarea class="form-control" rows="3" id="note" name="note" >{{ count($errors) > 0 ? old('note') : $model->note }}</textarea>
                                    @if($errors->has('note'))
                                    <span class="help-block">{{ $errors->first('note') }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-12 data-table-toolbar text-right">
                            <div class="form-group">
                                <a href="{{ URL($url) }}" class="btn btn-sm btn-warning"><i class="fa fa-reply"></i> {{ trans('shared/common.cancel') }}</a>
                                <button type="submit" name="btn-save" class="btn btn-sm btn-primary"><i class="fa fa-save"></i> {{ trans('shared/common.save') }}</button>
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
<div id="modal-official" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title text-center">Official Report List</h4>
            </div>
            <div class="modal-body">
                <table id="datatables-official" class="table table-hover table-striped table-bordered" cellspacing="0" width="100%">
                   <thead>
                        <tr>
                            <th>{{ trans('operational/fields.official-report-number') }}</th>
                            <th>{{ trans('operational/fields.person-name') }}</th>
                            <th>{{ trans('operational/fields.resi-number') }}</th>
                            <th>{{ trans('operational/fields.item-name') }}</th>
                            <th>{{ trans('shared/common.description') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                       @foreach ($optionOfficial as $official)
                        <tr style="cursor: pointer;" data-official="{{ json_encode($official) }}">
                            <td>{{ $official->official_report_number }}</td>
                            <td>{{ $official->person_name }}</td>
                            <td>{{ $official->resi_number }}</td>
                            <td>{{ $official->item_name }}</td>
                            <td>{{ $official->description }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-sm btn-warning" data-dismiss="modal">{{ trans('shared/common.close') }}</button>
            </div>
        </div>
    </div>
</div>

<div id="modal-resi" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title text-center">Resi List</h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-sm-12">
                        <div class="form-group search-lov">
                            <label for="searchResi" class="col-sm-1 col-sm-offset-8 control-label">{{ trans('shared/common.search') }}</label>
                            <div class="col-sm-3">
                                <input type="text" class="form-control input-xs" id="searchResi" name="searchResi">
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-12">
                        <table id="table-resi" class="table table-hover table-striped table-bordered" cellspacing="0" width="100%">
                            <thead>
                                <tr>
                                    <th>{{ trans('operational/fields.resi-number') }}</th>
                                    <th>{{ trans('operational/fields.item-name') }}</th>
                                    <th>{{ trans('operational/fields.sender-name') }}</th>
                                    <th>{{ trans('operational/fields.receiver-name') }}</th>
                                    <th>{{ trans('shared/common.description') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
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

<div id="modal-resi-do" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title text-center">List of Resi</h4>
            </div>
            <div class="modal-body">
                <table id="datatables-resi-do" class="table table-hover table-striped table-bordered" cellspacing="0" width="100%">
                    <thead>
                        <tr>
                            <th>{{ trans('operational/fields.resi-number') }}</th>
                            <th>{{ trans('operational/fields.do-number') }}</th>
                            <th>{{ trans('operational/fields.receiver-name') }}</th>
                            <th>{{ trans('operational/fields.address') }}</th>
                            <th>{{ trans('operational/fields.phone') }}</th>
                            <th>{{ trans('inventory/fields.item') }}</th>
                            <th>{{ trans('operational/fields.total-coly') }}</th>
                            <th>{{ trans('operational/fields.delivery-area') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($optionResiDo as $resi)
                        <tr style="cursor: pointer;" data-resi="{{ json_encode($resi) }}">
                            <td>{{ $resi->resi_number }}</td>
                            <td>{{ $resi->delivery_order_number }}</td>
                            <td>{{ $resi->receiver_name }}</td>
                            <td>{{ $resi->receiver_address }}</td>
                            <td>{{ $resi->receiver_phone }}</td>
                            <td>{{ $resi->item_name }}</td>
                            <td>{{ $resi->total_coly }}</td>
                            <td>{{ $resi->delivery_area_name }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-sm btn-warning" data-dismiss="modal">{{ trans('shared/common.close') }}</button>
            </div>
        </div>
    </div>
</div>

<div id="modal-resi-ct" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title text-center">List of Resi</h4>
            </div>
            <div class="modal-body">
                <table id="datatables-resi-ct" class="table table-hover table-striped table-bordered" cellspacing="0" width="100%">
                    <thead>
                        <tr>
                            <th>{{ trans('operational/fields.resi-number') }}</th>
                            <th>{{ trans('operational/fields.customer-taking-transact-number') }}</th>
                            <th>{{ trans('operational/fields.taker-name') }}</th>
                            <th>{{ trans('operational/fields.address') }}</th>
                            <th>{{ trans('operational/fields.phone') }}</th>
                            <th>{{ trans('inventory/fields.item') }}</th>
                            <th>{{ trans('operational/fields.total-coly') }}</th>
                            <th>{{ trans('operational/fields.delivery-area') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($optionResiCt as $resi)
                        <tr style="cursor: pointer;" data-resi="{{ json_encode($resi) }}">
                            <td>{{ $resi->resi_number }}</td>
                            <td>{{ $resi->customer_taking_transact_number }}</td>
                            <td>{{ $resi->taker_name }}</td>
                            <td>{{ $resi->taker_address }}</td>
                            <td>{{ $resi->taker_phone }}</td>
                            <td>{{ $resi->item_name }}</td>
                            <td>{{ $resi->coly_taken }}</td>
                            <td>{{ $resi->delivery_area_name }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
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
        enableModal();
        $("#type").on('change', function(){
            clearForm();
            enableModal();
        });

        $('#searchResi').on('keyup', loadLovResi);
        $('#modalResi').on('click', showLovResi);
        $('#table-resi tbody').on('click', 'tr', selectResi);


        $("#datatables-official").dataTable({
            "pagelength" : 10,
            "lengthChange": false
        });

        $('#datatables-official tbody').on('click', 'tr', function () {
            var official = $(this).data('official');

            $('#officialReportId').val(official.official_report_id);
            $('#officialReportNumber').val(official.official_report_number);
            $('#note').val(official.description);

            $('#modal-official').modal('hide');
        });

        $("#datatables-resi").dataTable({
            "pageLength" : 10,
            "lengthChange": false
        });

        $('#datatables-resi tbody').on('click', 'tr', function () {
            var item = $(this).data('item');
            $('#resiHeaderId').val(item.item_id);
            $('#resiNumber').val(item.item_code);
            $('#totalColy').autoNumeric('update', {mDec: 0 , vMax:resi.total_coly});

            $('#modal-resi').modal('hide');
        });

        $("#datatables-resi-do").dataTable({
            "pageLength" : 10,
            "lengthChange": false
        });

        $('#datatables-resi-do tbody').on('click', 'tr', function () {
            var resi = $(this).data('resi');

            $('#doCtId').val(resi.delivery_order_line_id);
            $('#doCtNumber').val(resi.delivery_order_number);
            $('#resiNumber').val(resi.resi_number);
            $('#totalColy').val(resi.total_coly);

            $('#totalColy').autoNumeric('update', {mDec: 0 , vMax:resi.total_coly});

            $('#modal-resi-do').modal('hide');
        });

        $("#datatables-resi-ct").dataTable({
            "pageLength" : 10,
            "lengthChange": false
        });

        $('#datatables-resi-ct tbody').on('click', 'tr', function () {
            var resi = $(this).data('resi');

            $('#doCtId').val(resi.customer_taking_transact_id);
            $('#doCtNumber').val(resi.customer_taking_transact_number);
            $('#resiNumber').val(resi.resi_number);
            $('#totalColy').val(resi.coly_taken);

            $('#totalColy').autoNumeric('update', {mDec: 0, vMax:resi.coly_taken});

            $('#modal-resi-ct').modal('hide');
        });


    });

    var showLovResi = function() {
        $('#searchResi').val('');
        loadLovResi(function() {
            $('#modal-resi').modal('show');
        });
    };

    var xhrResi;
    var loadLovResi = function(callback) {
        if(xhrResi && xhrResi.readyState != 4){
            xhrResi.abort();
        }
        xhrResi = $.ajax({
            url: '{{ URL($url.'/get-json-resi') }}',
            data: {search: $('#searchResi').val()},
            success: function(data) {
                $('#table-resi tbody').html('');
                data.forEach(function(resi) {
                    $('#table-resi tbody').append(
                        '<tr data-json=\'' + JSON.stringify(resi) + '\'>\
                            <td>' + resi.resi_number + '</td>\
                            <td>' + resi.item_name + '</td>\
                            <td>' + resi.sender_name + '</td>\
                            <td>' + resi.receiver_name + '</td>\
                            <td>' + resi.description + '</td>\
                        </tr>'
                    );
                });

                if (typeof(callback) == 'function') {
                    callback();
                }
            }
        });
    };

    var selectResi = function() {
        var data = $(this).data('json');

        $('#resiHeaderId').val(data.resi_header_id);
        $('#resiNumber').val(data.resi_number);
        $('#totalColy').autoNumeric('update', {mDec: 0 , vMax:data.total_coly});
        $('#modal-resi').modal('hide');
    };

    var clearForm = function(){
        $('#resiNumber').val('');
        $('#doCtNumber').val('');
        $('#doCtId').val('');
        $('#totalColy').val('');
    };

    var enableModal = function(){
        $('#modalResi').addClass('disabled');
        $('#modalResiDo').addClass('disabled');
        $('#modalResiCt').addClass('disabled');
        $('#modalResiDo').removeClass('hidden');
        $('#modalResi').addClass('hidden');
        $('#modalResiCt').addClass('hidden');

        if ($('#type').val() == '{{ ResiStockCorrection::DELIVERY_ORDER }}') { 
            $('#modalResiDo').removeClass('disabled');
        }else if($('#type').val() == '{{ ResiStockCorrection::CUSTOMER_TAKING }}') { 
            $('#modalResiDo').addClass('hidden');
            $('#modalResiResi').addClass('hidden');
            $('#modalResiCt').removeClass('disabled');
            $('#modalResiCt').removeClass('hidden');
        }else if($('#type').val() == '{{ ResiStockCorrection::CORRECTION_PLUS }}' || $('#type').val() == '{{ ResiStockCorrection::CORRECTION_MINUS }}') { 
            $('#modalResiDo').addClass('hidden');
            $('#modalResiCt').addClass('hidden');
            $('#modalResi').removeClass('disabled');
            $('#modalResi').removeClass('hidden');
        }
    };
</script>
@endsection
