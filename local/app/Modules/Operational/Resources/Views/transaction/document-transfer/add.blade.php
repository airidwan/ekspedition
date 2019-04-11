@extends('layouts.master')

@section('title', trans('operational/menu.document-transfer'))

<?php
    use App\Modules\Operational\Model\Transaction\DocumentTransferHeader;
    use App\Modules\Operational\Model\Transaction\TransactionResiHeader;
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
                <h2><i class="fa fa-briefcase"></i> <strong>{{ $title }}</strong> {{ trans('operational/menu.document-transfer') }}</h2>
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
                        <input type="hidden" name="id" value="{{ $model->document_transfer_header_id }}">
                        <ul id="demo1" class="nav nav-tabs">
                            <li class="active">
                                <a href="#tabHeaders" data-toggle="tab">{{ trans('shared/common.headers') }} <span class="label label-success"></span></a>
                            </li>
                            <li class="">
                                <a href="#tabLines" data-toggle="tab">{{ trans('shared/common.lines') }} <span class="badge badge-primary"></span></a>
                            </li>
                        </ul>
                        <div class="tab-content">
                            <div class="tab-pane fade active in" id="tabHeaders">
                                <div class="col-sm-6 portlets">
                                    <div class="form-group {{ $errors->has('documentTransferNumber') ? 'has-error' : '' }}">
                                        <label for="documentTransferNumber" class="col-sm-4 control-label">{{ trans('operational/fields.document-transfer-number') }} <span class="required">*</span></label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="documentTransferNumber" name="documentTransferNumber" value="{{ count($errors) > 0 ? old('documentTransferNumber') : $model->document_transfer_number }}" readonly>
                                            @if($errors->has('documentTransferNumber'))
                                            <span class="help-block">{{ $errors->first('documentTransferNumber') }}</span>
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
                                                <input type="text" id="date" name="date" class="form-control datepicker-input" data-mask="99-99-9999" placeholder="dd-mm-yyyy" value="{{ $date !== null ? $date->format('d-m-Y') : '' }}" disabled>
                                                <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                            </div>
                                            @if($errors->has('date'))
                                            <span class="help-block">{{ $errors->first('date') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="branch" class="col-sm-4 control-label">{{ trans('shared/common.status') }}</label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="status" name="status" value="{{ count($errors) > 0 ? old('status') : $model->status }}" readonly>
                                            </select>
                                        </div>
                                    </div>
                                    <?php
                                        $modelDriver = $model->driver;
                                        $driverId    = !empty($modelDriver) ? $modelDriver->driver_id : '' ;
                                        $driverName  = !empty($modelDriver) ? $modelDriver->driver_name : '' ;
                                    ?>
                                    <div class="form-group {{ $errors->has('driverName') ? 'has-error' : '' }}">
                                        <label for="driverName" class="col-sm-4 control-label">{{ trans('operational/fields.driver') }} <span class="required">*</span></label>
                                        <div class="col-sm-8">
                                            <div class="input-group">
                                            <input type="hidden" class="form-control" id="driverId" name="driverId" value="{{ count($errors) > 0 ? old('driverId') : $model->driver_id }}">
                                            <input type="text" class="form-control" id="driverName" name="driverName" value="{{ count($errors) > 0 ? old('driverName') : $driverName }}" readonly>
                                            <span class="btn input-group-addon" id="modalDriver" data-toggle="{{ $model->status == DocumentTransferHeader::INCOMPLETE ? 'modal' : '' }}" data-target="#modal-driver"><i class="fa fa-search"></i></span>
                                            </div>
                                            @if($errors->has('driverName'))
                                            <span class="help-block">{{ $errors->first('driverName') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    <?php
                                        $modelTruck = $model->truck;
                                        $truckId    = !empty($modelTruck) ? $modelTruck->truck_id : '' ;
                                        $truckCode  = !empty($modelTruck) ? $modelTruck->truck_code : '' ;
                                    ?>
                                    <div class="form-group {{ $errors->has('truckCode') ? 'has-error' : '' }}">
                                        <label for="truckCode" class="col-sm-4 control-label">{{ trans('operational/fields.truck') }}</label>
                                        <div class="col-sm-8">
                                            <div class="input-group">
                                            <input type="hidden" class="form-control" id="truckId" name="truckId" value="{{ count($errors) > 0 ? old('truckId') : $model->truck_id }}">
                                            <input type="text" class="form-control" id="truckCode" name="truckCode" value="{{ count($errors) > 0 ? old('truckCode') : $truckCode }}" readonly>
                                            <span class="btn input-group-addon {{ $model->status == DocumentTransferHeader::INCOMPLETE ? 'remove-truck' : '' }}"><i class="fa fa-remove"></i></span>
                                            <span class="btn input-group-addon" id="modalTruck" data-toggle="{{ $model->status == DocumentTransferHeader::INCOMPLETE ? 'modal' : '' }}" data-target="#modal-truck"><i class="fa fa-search"></i></span>
                                            </div>
                                            @if($errors->has('truckCode'))
                                            <span class="help-block">{{ $errors->first('truckCode') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="toCity" class="col-sm-4 control-label">{{ trans('operational/fields.to-city') }} <span class="required">*</span></label>
                                        <div class="col-sm-8">
                                            <select class="form-control" id="toCity" name="toCity"  {{ $model->status != DocumentTransferHeader::INCOMPLETE ? 'disabled' : '' }}>
                                                <option value="" >ALL</option>
                                                <?php $toCityId = count($errors) > 0 ? old('toCity') : $model->to_city_id; ?>
                                                @foreach($optionRoute as $toCity)
                                                <option value="{{ $toCity->city_end_id }}" {{ $toCityId == $toCity->city_end_id ? 'selected' : '' }}>{{ $toCity->route_code }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group {{ $errors->has('description') ? 'has-error' : '' }}">
                                        <label for="description" class="col-sm-4 control-label">{{ trans('shared/common.description') }} <span class="required">*</span></label>
                                        <div class="col-sm-8">
                                            <textarea class="form-control" rows="4" id="description" name="description" {{ $model->status != DocumentTransferHeader::INCOMPLETE ? 'disabled' : '' }}>{{ count($errors) > 0 ? old('description') : $model->description }}</textarea>
                                            @if($errors->has('description'))
                                            <span class="help-block">{{ $errors->first('description') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="tabLines">
                                <div class="col-sm-12 portlets">
                                    @if(Gate::check('access', [$resource, 'insert']) && $model->status == DocumentTransferHeader::INCOMPLETE )
                                    <div class="data-table-toolbar">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="toolbar-btn-action">
                                                    <a class="btn btn-sm btn-primary add-line">
                                                        <i class="fa fa-plus-circle"></i> {{ trans('shared/common.add-new') }}
                                                    </a>
                                                    <a id="clear-lines" href="#" class="btn btn-sm btn-danger">
                                                        <i class="fa fa-remove"></i> {{ trans('shared/common.clear') }}
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @endif
                                    <div class="table-responsive">
                                        <table class="table table-striped table-bordered" id="table-line" cellspacing="0" width="100%">
                                            <thead>
                                                <tr>
                                                    <th>{{ trans('operational/fields.resi-number') }}</th>
                                                    <th>{{ trans('operational/fields.item-name') }}</th>
                                                    <th>{{ trans('operational/fields.sender-name') }}</th>
                                                    <th>{{ trans('operational/fields.receiver-name') }}</th>
                                                    <th>{{ trans('operational/fields.to-branch') }}</th>
                                                    <th>{{ trans('shared/common.description') }}</th>
                                                    @if($model->status == DocumentTransferHeader::INCOMPLETE)
                                                        <th style="min-width:60px;">{{ trans('shared/common.action') }}</th>
                                                    @endif
                                                </tr>
                                            </thead>
                                            <tbody>

                                                <?php $dataIndex = 0; ?>
                                                @if(count($errors) > 0)
                                                @for($i = 0; $i < count(old('resiHeaderId', [])); $i++)
                                                <tr data-index="{{ $dataIndex }}">
                                                    <td > {{ old('resiNumber')[$i] }} </td>
                                                    <td > {{ old('itemName')[$i] }} </td>
                                                    <td > {{ old('senderName')[$i] }} </td>
                                                    <td > {{ old('receiverName')[$i] }} </td>
                                                    <td > {{ old('toBranchName')[$i] }} </td>
                                                    <td > {{ old('note')[$i] }} </td>
                                                    @if($model->status == DocumentTransferHeader::INCOMPLETE)
                                                    <td class="text-center">
                                                        @if($model->status == DocumentTransferHeader::INCOMPLETE)
                                                        @if(Gate::check('access', [$resource, 'insert']))
                                                        <a data-toggle="tooltip" class="btn btn-danger btn-xs delete-line" ><i class="fa fa-remove"></i></a>
                                                        @endif
                                                        @endif
                                                        <input type="hidden" name="resiHeaderId[]" value="{{ old('resiHeaderId')[$i] }}">
                                                        <input type="hidden" name="resiNumber[]" value="{{ old('resiNumber')[$i] }}">
                                                        <input type="hidden" name="itemName[]" value="{{ old('itemName')[$i] }}">
                                                        <input type="hidden" name="senderName[]" value="{{ old('senderName')[$i] }}">
                                                        <input type="hidden" name="receiverName[]" value="{{ old('receiverName')[$i] }}">
                                                        <input type="hidden" name="toBranchId[]" value="{{ old('toBranchId')[$i] }}">
                                                        <input type="hidden" name="toBranchName[]" value="{{ old('toWhCode')[$i] }}">
                                                        <input type="hidden" name="note[]" value="{{ old('note')[$i] }}">
                                                    </td>
                                                    @endif
                                                </tr>
                                                <?php $dataIndex++; ?>

                                                @endfor
                                                @else
                                                <?php
                                                $arrSem = \DB::table('op.trans_document_transfer_line')
                                                                ->select('trans_document_transfer_line.*')
                                                                ->leftJoin('op.trans_resi_header','trans_resi_header.resi_header_id', '=', 'trans_document_transfer_line.resi_header_id')
                                                                ->where('document_transfer_header_id', '=', $model->document_transfer_header_id)
                                                                ->orderBy('trans_resi_header.resi_number', 'asc')
                                                                ->get();
                                                
                                                ?>
                                                @foreach($arrSem as $line)
                                                <?php
                                                    $resi     = TransactionResiHeader::find($line->resi_header_id);
                                                ?>
                                                <tr data-index="{{ $dataIndex }}">
                                                    <td > {{ $resi !== null ? $resi->resi_number : '' }} </td>
                                                    <td > {{ $resi !== null ? $resi->item_name : '' }} </td>
                                                    <td > {{ $resi !== null ? $resi->sender_name : '' }} </td>
                                                    <td > {{ $resi !== null ? $resi->receiver_name : '' }} </td>
                                                    <td > {{ $resi->branch !== null ? $resi->branch->branch_code : '' }} </td>
                                                    <td > {{ $resi !== null ? $resi->description : '' }} </td>
                                                    @if($model->status == DocumentTransferHeader::INCOMPLETE)
                                                    <td class="text-center">
                                                        @if(Gate::check('access', [$resource, 'insert']))
                                                        <a data-toggle="tooltip" class="btn btn-danger btn-xs delete-line" ><i class="fa fa-remove"></i></a>
                                                        @endif
                                                        <input type="hidden" name="resiHeaderId[]" value="{{ $resi !== null ? $resi->resi_header_id : '' }}">
                                                        <input type="hidden" name="resiNumber[]" value="{{ $resi !== null ? $resi->resi_number : '' }}">
                                                        <input type="hidden" name="resiName[]" value="{{ $resi !== null ? $resi->item_name : '' }}">
                                                        <input type="hidden" name="senderName[]" value="{{ $resi !== null ? $resi->sender_name : '' }}">
                                                        <input type="hidden" name="receiverName[]" value="{{ $resi !== null ? $resi->receiver_name : '' }}">
                                                        <input type="hidden" name="toBranchId[]" value="{{ $resi !== null ? $resi->branch->branch_id : '' }}">
                                                        <input type="hidden" name="toBranchName[]" value="{{ $resi !== null ? $resi->branch->branch_code : '' }}">
                                                        <input type="hidden" name="note[]" value="{{ $resi !== null ? $resi->description : '' }}">
                                                    </td>
                                                    @endif
                                                @endforeach
                                                </tr>
                                                <?php $dataIndex++; ?>
                                                @endif
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-12 data-table-toolbar text-right">
                            <div class="form-group">
                                <a href="{{ URL($url) }}" class="btn btn-sm btn-warning"><i class="fa fa-reply"></i> {{ trans('shared/common.cancel') }}</a>
                                @if($model->status == DocumentTransferHeader::INCOMPLETE)
                                <button type="submit" name="btn-save" class="btn btn-sm btn-primary"><i class="fa fa-save"></i> {{ trans('shared/common.save') }}</button>
                                @endif
                                @if($model->status == DocumentTransferHeader::COMPLETE || $model->status == DocumentTransferHeader::INPROCESS)
                                <a href="{{ URL($url.'/print-pdf-detail/'.$model->document_transfer_header_id) }}" class="button btn btn-sm btn-success" target="_blank">
                                    <i class="fa fa-print"></i> {{ trans('shared/common.print') }}
                                </a>
                                @endif
                                @if(Gate::check('access', [$resource, 'transact']) && $model->status == DocumentTransferHeader::INCOMPLETE)
                                <button type="submit" name="btn-transact" class="btn btn-sm btn-info">
                                    <i class="fa fa-save"></i> {{ trans('shared/common.transact') }}
                                </button>
                                @endif
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
<div id="modal-driver" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title text-center">Driver List</h4>
            </div>
            <div class="modal-body">
                <table id="datatables-driver" class="table table-hover table-striped table-bordered" cellspacing="0" width="100%">
                   <thead>
                        <tr>
                            <th>{{ trans('operational/fields.driver-code') }}</th>
                            <th>{{ trans('operational/fields.driver-name') }}</th>
                            <th>{{ trans('shared/common.category') }}</th>
                            <th>{{ trans('shared/common.address') }}</th>
                            <th>{{ trans('shared/common.description') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                       @foreach ($optionDriver as $driver)
                        <tr style="cursor: pointer;" data-driver="{{ json_encode($driver) }}">
                            <td>{{ $driver->driver_code }}</td>
                            <td>{{ $driver->driver_name }}</td>
                            <td>{{ $driver->driver_category }}</td>
                            <td>{{ $driver->address }}</td>
                            <td>{{ $driver->description }}</td>
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
<div id="modal-truck" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title text-center">Truck List</h4>
            </div>
            <div class="modal-body">
                <table id="datatables-truck" class="table table-hover table-striped table-bordered" cellspacing="0" width="100%">
                    <thead>
                        <tr>
                            <th>{{ trans('operational/fields.brand') }}</th>
                            <th>{{ trans('operational/fields.type') }}</th>
                            <th>{{ trans('operational/fields.police-number') }}</th>
                            <th>{{ trans('operational/fields.owner-name') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($optionTruck as $truck)
                        <tr style="cursor: pointer;" data-truck="{{ json_encode($truck) }}">
                            <td>{{ $truck->vehicle_merk }}</td>
                            <td>{{ $truck->vehicle_type }}</td>
                            <td>{{ $truck->police_number }}</td>
                            <td>{{ $truck->owner_name }}</td>
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
@endsection

@section('script')
@parent()
<script type="text/javascript">
    var dataIndex = {{ $dataIndex }};
    $(document).on('ready', function(){
        $(".remove-truck").on('click', function() {
            $('#truckId').val('');
            $('#truckCode').val('');
        });
        
        $('.add-line').on('click', showLovResi);
        $('#searchResi').on('keyup', loadLovResi);
        $('#table-resi tbody').on('click', 'tr', selectResi);
        $('.delete-line').on('click', deleteLine);
        $('#clear-lines').on('click', clearLines);
        $('#toCity').on('change', function() {
            $('#table-line tbody').html('');
        });

        $("#datatables-resi").dataTable({
            "pageLength" : 10,
            "lengthChange": false
        });

        $('#datatables-resi tbody').on('click', 'tr', function () {
            var item = $(this).data('item');
            $('#resiHeaderId').val(item.item_id);
            $('#resiNumber').val(item.item_code);
            $('#itemName').val(item.description);
            $('#price').val(item.average_cost);
            $('#senderName').val(item.wh_id);
            $('#receiverName').val(item.wh_code);
            $('#stockItem').val(item.stock);
            $('#uom').val(item.uom_code);
            $('#uom_id').val(item.uom_id);

            $('#qtyNeed').autoNumeric('update', {mDec: 0, vMax: item.stock});

            $('#modal-resi').modal('hide');
        });

        $("#datatables-driver").dataTable({
            "pagelength" : 10,
            "lengthChange": false
        });

        $('#datatables-driver tbody').on('click', 'tr', function () {
            var driver = $(this).data('driver');

            $('#driverId').val(driver.driver_id);
            $('#driverName').val(driver.driver_name);

            $('#modal-driver').modal('hide');
        });

        $("#datatables-truck").dataTable({
            "pagelength" : 10,
            "lengthChange": false
        });

        $('#datatables-truck tbody').on('click', 'tr', function () {
            var truck = $(this).data('truck');
            $('#truckId').val(truck.truck_id);
            $('#truckCode').val(truck.truck_code);

            $('#modal-truck').modal('hide');
        });

        $("#datatables-service").dataTable({
            "pagelength" : 10,
            "lengthChange": false
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
            data: {search: $('#searchResi').val(), toCity: $('#toCity').val()},
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

        var error = false
        $('#table-line tbody tr').each(function (i, row) {
            if (data.resi_header_id == $(row).find('[name="resiHeaderId[]"]').val()) {
                $('#modal-alert').find('.alert-message').html('Resi already exist');
                $('#modal-alert').modal('show');
                error = true;
            }
        });

        if (data.branch_id == '{{ \Session::get('currentBranch')->branch_id }}') {
                $('#modal-alert').find('.alert-message').html('You can\'t choose this branch');
                $('#modal-alert').modal('show');
                error = true;
            }

        if (error) {
            return;
        }

        var htmlTr = '<td >' + data.resi_number + '</td>' +
            '<td >' + data.item_name + '</td>' +
            '<td >' + data.sender_name + '</td>' +
            '<td >' + data.receiver_name + '</td>' +
            '<td >' + data.branch_code + '</td>' +
            '<td >' + data.description + '</td>' +
            '<td class="text-center">' +
            '<a data-toggle="tooltip" class="btn btn-danger btn-xs delete-line" ><i class="fa fa-remove"></i></a>' +
            '<input type="hidden" name="resiHeaderId[]" value="' + data.resi_header_id + '">' +
            '<input type="hidden" name="resiNumber[]" value="' + data.resi_number + '">' +
            '<input type="hidden" name="itemName[]" value="' + data.item_name + '">' +
            '<input type="hidden" name="senderName[]" value="' + data.sender_name + '">' +
            '<input type="hidden" name="receiverName[]" value="' + data.receiver_name + '">' +
            '<input type="hidden" name="toBranchId[]" value="' + data.branch_id + '">' +
            '<input type="hidden" name="toBranchName[]" value="' + data.branch_code + '">' +
            '<input type="hidden" name="note[]" value="' + data.description + '">' +
            '</td>';


        $('#table-line tbody').append(
            '<tr data-index="' + dataIndex + '">' + htmlTr + '</tr>'
        );
        dataIndex++;

        $('.delete-line').on('click', deleteLine);

        dataIndex++;
        $('#modal-resi').modal("hide");
    };

    var clearLines = function() {
        $('#table-line tbody').html('');
    };

    var addLine = function() {
        $('#modal-resi').modal("show");
    };

    var deleteLine = function() {
        $(this).parent().parent().remove();
    };

</script>
@endsection
