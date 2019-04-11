@extends('layouts.master')

@section('title', trans('inventory/menu.adjustment-stock'))

<?php 
    use App\Modules\Inventory\Model\Master\MasterStock;
    use App\Modules\Inventory\Model\Transaction\AdjustmentStockHeader;
?>

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="widget">
            <div class="widget-header">
                <h2><i class="fa fa-briefcase"></i> <strong>{{ $title }}</strong> {{ trans('inventory/menu.adjustment-stock') }}</h2>
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
                        <input type="hidden" name="id" value="{{ $model->adjustment_header_id }}">
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
                                    <div class="form-group {{ $errors->has('adjustmentNumber') ? 'has-error' : '' }}">
                                        <label for="adjustmentNumber" class="col-sm-4 control-label">{{ trans('inventory/fields.adjustment-number') }} <span class="required">*</span></label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="adjustmentNumber" name="adjustmentNumber" value="{{ count($errors) > 0 ? old('adjustmentNumber') : $model->adjustment_number }}" readonly>
                                            @if($errors->has('adjustmentNumber'))
                                            <span class="help-block">{{ $errors->first('adjustmentNumber') }}</span>
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
                                    <div class="form-group {{ $errors->has('type') ? 'has-error' : '' }}">
                                        <label for="type" class="col-sm-4 control-label">{{ trans('shared/common.type') }} <span class="required">*</span></label>
                                        <div class="col-sm-8">
                                            <select class="form-control" id="type" name="type" {{ $model->status == AdjustmentStockHeader::COMPLETE ? 'disabled' : '' }}>
                                                <?php $typeString = count($errors) > 0 ? old('type') : $model->type ?>
                                                <option value="">{{ trans('shared/common.please-select') }}</option>
                                                @foreach($optionType as $type)
                                                <option value="{{ $type }}" {{ $typeString == $type ? 'selected' : '' }}>{{ $type }}</option>
                                                @endforeach
                                            </select>
                                            @if($errors->has('type'))
                                            <span class="help-block">{{ $errors->first('type') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    <?php
                                        $modelOfficial           = $model->officialReport;
                                        $officialReportId     = !empty($modelOfficial) ? $modelOfficial->official_report_id : '' ; 
                                        $officialReportNumber = !empty($modelOfficial) ? $modelOfficial->official_report_number : '' ; 
                                    ?>
                                    <div class="form-group {{ $errors->has('officialReportId') ? 'has-error' : '' }}">
                                        <label for="officialReportNumber" class="col-sm-4 control-label">{{ trans('operational/fields.official-report-number') }} <span class="required">*</span></label>
                                        <div class="col-sm-8">
                                            <div class="input-group">
                                            <input type="hidden" class="form-control" id="officialReportId" name="officialReportId" value="{{ count($errors) > 0 ? old('officialReportId') : $model->official_report_id }}">
                                            <input type="text" class="form-control" id="officialReportNumber" name="officialReportNumber" value="{{ count($errors) > 0 ? old('officialReportNumber') : $officialReportNumber }}" readonly>
                                            <span class="btn input-group-addon" id="modalService" data-toggle="{{ $model->status == AdjustmentStockHeader::INCOMPLETE ? 'modal' : '' }}" data-target="#modal-official"><i class="fa fa-search"></i></span>
                                            </div>
                                            @if($errors->has('officialReportId'))
                                            <span class="help-block">{{ $errors->first('officialReportId') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="form-group {{ $errors->has('description') ? 'has-error' : '' }}">
                                        <label for="description" class="col-sm-4 control-label">{{ trans('shared/common.description') }} <span class="required">*</span></label>
                                        <div class="col-sm-8">
                                            <textarea class="form-control" rows="4" id="description" name="description" {{ $model->status == AdjustmentStockHeader::COMPLETE ? 'disabled' : '' }}>{{ count($errors) > 0 ? old('description') : $model->description }}</textarea>
                                            @if($errors->has('description'))
                                            <span class="help-block">{{ $errors->first('description') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="tabLines">
                                <div class="col-sm-12 portlets">
                                    @if(Gate::check('access', [$resource, 'insert']) && $model->status == AdjustmentStockHeader::INCOMPLETE )
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
                                                    <th>{{ trans('inventory/fields.item-code') }}</th>
                                                    <th>{{ trans('operational/fields.item-name') }}</th>
                                                    <th>{{ trans('inventory/fields.warehouse') }}</th>
                                                    <th>{{ trans('inventory/fields.qty-adjustment') }}</th>
                                                    <th>{{ trans('inventory/fields.uom') }}</th>
                                                    <th>{{ trans('shared/common.price') }}</th>
                                                    <th>{{ trans('shared/common.description') }}</th>
                                                    <th style="min-width:60px;">{{ trans('shared/common.action') }}</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php $dataIndex = 0; ?>
                                                @if(count($errors) > 0)
                                                @for($i = 0; $i < count(old('lineId', [])); $i++)
                                                <tr data-index="{{ $dataIndex }}">
                                                    <td > {{ old('itemCode')[$i] }} </td>
                                                    <td > {{ old('itemName')[$i] }} </td>
                                                    <td > {{ old('warehouse')[$i] }} </td>
                                                    <td class="text-right"> {{ old('qtyAdjustment')[$i] }} </td>
                                                    <td > {{ old('uom')[$i] }} </td>
                                                    <td class="text-right"> {{ old('price')[$i] }} </td>
                                                    <td > {{ old('note')[$i] }} </td>
                                                    <td class="text-center">
                                                        @if($model->status == AdjustmentStockHeader::INCOMPLETE)
                                                        <a data-toggle="tooltip" class="btn btn-xs btn-warning edit-line" ><i class="fa fa-pencil"></i></a>
                                                        @if(Gate::check('access', [$resource, 'insert']))
                                                        <a data-toggle="tooltip" class="btn btn-danger btn-xs delete-line" ><i class="fa fa-remove"></i></a>
                                                        @endif
                                                        @endif
                                                        <input type="hidden" name="lineId[]" value="{{ old('lineId')[$i] }}">
                                                        <input type="hidden" name="itemId[]" value="{{ old('itemId')[$i] }}">
                                                        <input type="hidden" name="itemCode[]" value="{{ old('itemCode')[$i] }}">
                                                        <input type="hidden" name="itemName[]" value="{{ old('itemName')[$i] }}">
                                                        <input type="hidden" name="whId[]" value="{{ old('whId')[$i] }}">
                                                        <input type="hidden" name="warehouse[]" value="{{ old('warehouse')[$i] }}">
                                                        <input type="hidden" name="qtyAdjustment[]" value="{{ old('qtyAdjustment')[$i] }}">
                                                        <!-- <input type="hidden" name="uomId[]" value="{{ old('uomId')[$i] }}"> -->
                                                        <input type="hidden" name="uom[]" value="{{ old('uom')[$i] }}">
                                                        <input type="hidden" name="price[]" value="{{ old('price')[$i] }}">
                                                        <input type="hidden" name="note[]" value="{{ old('note')[$i] }}">
                                                    </td>
                                                </tr>
                                                <?php $dataIndex++; ?>

                                                @endfor
                                                @else
                                                @foreach($model->lines()->get() as $line)
                                                <?php
                                                    $item    = $line->item;
                                                    $uom     = $item->uom;
                                                    $wh      = $line->warehouse;
                                                    $stock   = MasterStock::where('item_id', '=', $item->item_id)
                                                                ->where('wh_id', '=', $wh->wh_id)->first();
                                                ?>
                                                <tr data-index="{{ $dataIndex }}">
                                                    <td > {{ $item !== null ? $item->item_code : '' }} </td>
                                                    <td > {{ $item !== null ? $item->description : '' }} </td>
                                                    <td > {{ $wh !== null ? $wh->wh_code : '' }} </td>
                                                    <td class="text-right"> {{ number_format($line->qty_adjustment) }} </td>
                                                    <td > {{ $uom !== null ? $uom->uom_code : '' }} </td>
                                                    <td class="text-right"> {{ number_format($line->price) }} </td>
                                                    <td > {{ $line->description }} </td>
                                                    <td class="text-center">
                                                    @if($model->status == AdjustmentStockHeader::INCOMPLETE)
                                                        <a data-toggle="tooltip" class="btn btn-xs btn-warning edit-line" ><i class="fa fa-pencil"></i></a>
                                                        @if(Gate::check('access', [$resource, 'insert']))
                                                        <a data-toggle="tooltip" class="btn btn-danger btn-xs delete-line" ><i class="fa fa-remove"></i></a>
                                                        @endif
                                                    @endif
                                                        <input type="hidden" name="lineId[]" value="{{ $line->mo_line_id }}">
                                                        <input type="hidden" name="itemId[]" value="{{ $item !== null ? $item->item_id : '' }}">
                                                        <input type="hidden" name="itemCode[]" value="{{ $item !== null ? $item->item_code : '' }}">
                                                        <input type="hidden" name="itemName[]" value="{{ $item !== null ? $item->description : '' }}">
                                                        <input type="hidden" name="whId[]" value="{{ $wh !== null ? $wh->wh_id : '' }}">
                                                        <input type="hidden" name="warehouse[]" value="{{ $wh !== null ? $wh->wh_code : '' }}">
                                                        <input type="hidden" name="stockItem[]" value="{{ $stock !== null ? $stock->stock : '' }}">
                                                        <input type="hidden" name="qtyAdjustment[]" value="{{ $line->qty_adjustment }}">
                                                        <input type="hidden" name="uomId[]" value="{{ $uom !== null ? $uom->uom_id : '' }}">
                                                        <input type="hidden" name="uom[]" value="{{ $uom !== null ? $uom->uom_code : '' }}">
                                                        <input type="hidden" name="price[]" value="{{ $line->price }}">
                                                        <input type="hidden" name="note[]" value="{{ $line->description }}">
                                                    </td>
                                                </tr>
                                                <?php $dataIndex++; ?>

                                                @endforeach
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
                                @if($model->status == AdjustmentStockHeader::INCOMPLETE)
                                <button type="submit" name="btn-save" class="btn btn-sm btn-primary"><i class="fa fa-save"></i> {{ trans('shared/common.save') }}</button>
                                @endif
                                @if(Gate::check('access', [$resource, 'transact']) && $model->status == AdjustmentStockHeader::INCOMPLETE)
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
<div id="modal-form-line" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title text-center"><span id="title-modal-line-detail">{{ trans('shared/common.add') }}</span> {{ trans('shared/common.line') }}</h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-sm-12">
                        <div id="horizontal-form">
                            <form  role="form" id="add-form" class="form-horizontal" method="post">
                                {{ csrf_field() }}
                                <div class="col-sm-12 portlets">
                                    <input type="hidden" name="dataIndexForm" id="dataIndexForm" value="">
                                    <input type="hidden" name="idDetail" id="idDetail" value="">
                                    <input type="hidden" name="lineId" id="lineId" value="">
                                    <div class="form-group {{ $errors->has('itemCode') ? 'has-error' : '' }}">
                                        <label for="itemCode" class="col-sm-4 control-label">{{ trans('inventory/fields.item-code') }} <span class="required">*</span></label>
                                        <div class="col-sm-8">
                                            <div class="input-group">
                                                <input type="hidden" name="itemId" id="itemId" value="">
                                                <input type="text" class="form-control" id="itemCode" name="itemCode" readonly> 
                                                <span id="modalItem" class="btn input-group-addon" data-toggle="modal" data-target="#modal-item"><i class="fa fa-search"></i></span>
                                                <span id="modalAllItem" class="btn input-group-addon" data-toggle="modal" data-target="#modal-all-item"><i class="fa fa-search"></i></span>
                                            </div>
                                            <span class="help-block"></span>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="itemName" class="col-sm-4 control-label">{{ trans('operational/fields.item-name') }} </label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="itemName" name="itemName" value="" disabled="disabled">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="warehouse" class="col-sm-4 control-label">{{ trans('inventory/fields.warehouse') }} </label>
                                        <div class="col-sm-8">
                                            <select class="form-control" name="whId" id="whId">
                                                <option value="">{{ trans('shared/common.please-select') }}</option>
                                                @foreach($optionWarehouse as $warehouse)
                                                <option value="{{ $warehouse->wh_id }}">{{ $warehouse->wh_code }}</option>
                                                @endforeach
                                            </select>
                                            <span class="help-block"></span>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="stockItem" class="col-sm-4 control-label">{{ trans('inventory/fields.stock') }} </label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control currency" id="stockItem" name="stockItem" value="" disabled="disabled">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="qtyAdjustment" class="col-sm-4 control-label">{{ trans('inventory/fields.qty-adjustment') }} <span class="required">*</span></label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control currency text-right" id="qtyAdjustment" name="qtyAdjustment" value="" >
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="uom" class="col-sm-4 control-label">{{ trans('inventory/fields.uom') }} </label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="uom" name="uom" value="0" disabled="disabled">
                                            <span class="help-block"></span>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="price" class="col-sm-4 control-label">{{ trans('shared/common.price') }} <span class="required">*</span></label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control currency text-right" id="price" name="price" value="0">
                                            <span class="help-block"></span>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="note" class="col-sm-4 control-label">{{ trans('shared/common.note') }} </label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="note" name="note" value="0">
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-sm btn-warning" id="cancel-save-line" data-dismiss="modal">{{ trans('shared/common.cancel') }}</button>
                <button type="button" class="btn btn-sm btn-primary" id="save-line">
                    <span id="submit-modal-line">{{ trans('shared/common.add') }}</span> {{ trans('shared/common.line') }}
                </button>
            </div>
        </div>
    </div>
</div>
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
                            <th>{{ trans('shared/common.description') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                       @foreach ($optionOfficial as $official)
                        <tr style="cursor: pointer;" data-official="{{ json_encode($official) }}">
                            <td>{{ $official->official_report_number }}</td>
                            <td>{{ $official->person_name }}</td>
                            <td>{{ substr($official->description, 0, 250) }}</td>
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
<div id="modal-item" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title text-center">Item List</h4>
            </div>
            <div class="modal-body">
                <table id="datatables-item" class="table table-hover table-striped table-bordered" cellspacing="0" width="100%">
                   <thead>
                        <tr>
                            <th>{{ trans('inventory/fields.item-code') }}</th>
                            <th>{{ trans('operational/fields.item-name') }}</th>
                            <th>{{ trans('inventory/fields.warehouse') }}</th>
                            <th>{{ trans('inventory/fields.stock') }}</th>
                            <th>{{ trans('shared/common.price') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                       @foreach ($optionItem as $item)
                        <tr style="cursor: pointer;" data-item="{{ json_encode($item) }}">
                            <td>{{ $item->item_code }}</td>
                            <td>{{ $item->description }}</td>
                            <td>{{ $item->wh_code }}</td>
                            <td>{{ $item->stock }}</td>
                            <td class="text-right">{{ number_format($item->average_cost) }}</td>
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

<div id="modal-all-item" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title text-center">Item List</h4>
            </div>
            <div class="modal-body">
                <table id="datatables-all-item" class="table table-hover table-striped table-bordered" cellspacing="0" width="100%">
                   <thead>
                        <tr>
                            <th>{{ trans('inventory/fields.item-code') }}</th>
                            <th>{{ trans('operational/fields.item-name') }}</th>
                            <th>{{ trans('shared/common.price') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                       @foreach ($optionAllItem as $item)
                        <tr style="cursor: pointer;" data-item="{{ json_encode($item) }}">
                            <td>{{ $item->item_code }}</td>
                            <td>{{ $item->description }}</td>
                            <td class="text-right">{{ number_format($item->average_cost) }}</td>
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
    var dataIndex = {{ $dataIndex }};
    $(document).on('ready', function(){

        $('#save-line').on('click', saveLine);
        $('.delete-line').on('click', deleteLine);
        $('.edit-line').on('click', editLine);
        $('#cancel-save-line').on('click', cancelSaveLine);
        $('#clear-lines').on('click', clearLines);
        $('.add-line').on('click', addLine);
        $('#type').on('change', changeType);
        disableForm();

        $("#datatables-official").dataTable({
            "pagelength" : 10,
            "lengthChange": false
        });

        $('#datatables-official tbody').on('click', 'tr', function () {
            var official = $(this).data('official');

            $('#officialReportId').val(official.official_report_id);
            $('#officialReportNumber').val(official.official_report_number);
            $('#description').val(official.description);

            $('#modal-official').modal('hide');
        });

        $("#datatables-item").dataTable({
            "pagelength" : 10,
            "lengthChange": false
        });

        $('#datatables-item tbody').on('click', 'tr', function () {
            var item = $(this).data('item');

            if(checkItemExist(item.item_id, item.wh_id)){
                $('#modal-alert').find('.alert-message').html('{{ trans('inventory/fields.item-exist') }}');
                $('#modal-alert').modal('show');
                return;
            }

            $('#itemId').val(item.item_id);
            $('#itemCode').val(item.item_code);
            $('#itemName').val(item.description);
            $('#price').val(item.average_cost);
            $('#whId').val(item.wh_id);
            $('#warehouse').val(item.wh_code);
            $('#stockItem').val(item.stock);
            $('#uom').val(item.uom_code);
            $('#uom_id').val(item.uom_id);

            $('#price').autoNumeric('update', {mDec: 0});


            $('#modal-item').modal('hide');
        });

        $("#datatables-all-item").dataTable({
            "pagelength" : 10,
            "lengthChange": false
        });

        $('#datatables-all-item tbody').on('click', 'tr', function () {
            var item = $(this).data('item');

            if(checkItemExist(item.item_id, item.wh_id)){
                $('#modal-alert').find('.alert-message').html('{{ trans('inventory/fields.item-exist') }}');
                $('#modal-alert').modal('show');
                return;
            }

            $('#itemId').val(item.item_id);
            $('#itemCode').val(item.item_code);
            $('#itemName').val(item.description);
            $('#price').val(item.average_cost);
            $('#stockItem').val(item.stock);
            $('#uom').val(item.uom_code);
            $('#uom_id').val(item.uom_id);

            $('#price').autoNumeric('update', {mDec: 0});


            $('#modal-all-item').modal('hide');
        });
    });

    var changeType = function() {
        clearLines();
        disableForm();
    }

    var disableForm = function() {
            $('#modalItem').addClass('disabled');
            $('#modalItem').removeClass('hidden');
            $('#modalAllItem').addClass('hidden');

            if ($('#type').val() == '{{ AdjustmentStockHeader::ADJUSTMENT_MIN }}' ) { 
                $('#formDP').removeClass('hidden');
                $('#modalItem').removeClass('disabled');
                $('#whId').prop('disabled', 'disabled');
            }

            if ($('#type').val() == '{{ AdjustmentStockHeader::ADJUSTMENT_PLUS }}' ) { 
                $('#modalAllItem').removeClass('hidden');
                $('#modalItem').addClass('hidden');
                $('#whId').prop('disabled', false);
            }
        };

    var checkItemExist = function(itemRequestId, whRequestId) {
        var exist = false;
        $('#table-line tbody tr').each(function (i, row) {
            var itemId = $(row).find('[name="itemId[]"]').val();
            var whId   = $(row).find('[name="whId[]"]').val();
            if (itemId == itemRequestId && whId == whRequestId) {
                exist = true;
            }
        });
        return exist;
    };

    var cancelSaveLine = function() {
        $('#modal-form-line').modal("hide");
    };

    var clearLines = function() {
        $('#table-line tbody').html('');
    };

    var addLine = function() {
        clearFormLine();
        $('#title-modal-line-detail').html('{{ trans('shared/common.add') }}');
        $('#submit-modal-line').html('{{ trans('shared/common.add') }}');

        $('#modal-form-line').modal("show");
    };

    var clearFormLine = function() {
        $('#dataIndexForm').val('');
        $('#lineId').val('');
        $('#itemId').val('');
        $('#itemCode').val('');
        $('#itemName').val('');
        $('#whId').val('');
        $('#warehouse').val('');
        $('#qtyAdjustment').val('');
        $('#stockItem').val('');
        $('#price').val('');
        $('#note').val('');
        $('#uom').val('');

        $('#itemCode').parent().parent().parent().removeClass('has-error');
        $('#itemCode').parent().parent().find('span.help-block').html('');
        $('#whId').parent().parent().removeClass('has-error');
        $('#whId').parent().find('span.help-block').html('');
        $('#qtyAdjustment').parent().parent().removeClass('has-error');
        $('#qtyAdjustment').parent().find('span.help-block').html('');
    };

    var saveLine = function() {
        var dataIndexForm = $('#dataIndexForm').val();
        var lineId = $('#lineId').val();
        var itemId = $('#itemId').val();
        var itemCode = $('#itemCode').val();
        var itemName = $('#itemName').val();
        var uom = $('#uom').val();
        var stockItem = $('#stockItem').val();
        var qtyAdjustment = $('#qtyAdjustment').val();
        var whId = $('#whId').val();
           var warehouse = $('#whId option:selected').html();
        var price = $('#price').val();
        var note = $('#note').val();
        var error = false;

        if (itemCode == '' || itemId == '') {
            $('#itemCode').parent().parent().parent().addClass('has-error');
            $('#itemCode').parent().parent().find('span.help-block').html('Choose item first');
            error = true;
        } else {
            $('#itemCode').parent().parent().parent().removeClass('has-error');
            $('#itemCode').parent().parent().find('span.help-block').html('');
        }

        if (warehouse == '' || whId == '') {
            $('#whId').parent().parent().addClass('has-error');
            $('#whId').parent().find('span.help-block').html('Choose warehouse first');
            error = true;
        } else {
            $('#whId').parent().parent().removeClass('has-error');
            $('#whId').parent().find('span.help-block').html('');
        }

        if (qtyAdjustment == '' || qtyAdjustment == 0) {
            $('#qtyAdjustment').parent().parent().addClass('has-error');
            $('#qtyAdjustment').parent().find('span.help-block').html('Quantity is required');
            error = true;
        } else {
            $('#qtyAdjustment').parent().parent().removeClass('has-error');
            $('#qtyAdjustment').parent().find('span.help-block').html('');
        }

        if (price == '' || price == 0) {
            $('#price').parent().parent().addClass('has-error');
            $('#price').parent().find('span.help-block').html('Quantity is required');
            error = true;
        } else {
            $('#price').parent().parent().removeClass('has-error');
            $('#price').parent().find('span.help-block').html('');
        }

        if (error) {
            return;
        }

        var htmlTr = '<td >' + itemCode + '</td>' +
            '<td >' + itemName + '</td>' +
            '<td >' + warehouse + '</td>' +
            '<td class="text-right">' + qtyAdjustment + '</td>' +
            '<td >' + uom + '</td>' +
            '<td class="text-right">' + price + '</td>' +
            '<td >' + note + '</td>' +
            '<td class="text-center">' +
            '<a data-toggle="tooltip" class="btn btn-xs btn-warning edit-line" ><i class="fa fa-pencil"></i></a> ' +
            '<a data-toggle="tooltip" class="btn btn-danger btn-xs delete-line" ><i class="fa fa-remove"></i></a>' +
            '<input type="hidden" name="lineId[]" value="'+ lineId + '">' +
            '<input type="hidden" name="itemId[]" value="' + itemId + '">' +
            '<input type="hidden" name="itemCode[]" value="' + itemCode + '">' +
            '<input type="hidden" name="itemName[]" value="' + itemName + '">' +
            '<input type="hidden" name="whId[]" value="' + whId + '">' +
            '<input type="hidden" name="warehouse[]" value="' + warehouse + '">' +
            '<input type="hidden" name="qtyAdjustment[]" value="' + qtyAdjustment + '">' +
            '<input type="hidden" name="stockItem[]" value="' + stockItem + '">' +
            '<input type="hidden" name="uom[]" value="' + uom + '">' +
            '<input type="hidden" name="price[]" value="' + price + '">' +
            '<input type="hidden" name="note[]" value="' + note + '">' +
            '</td>';

        if (dataIndexForm != '') {
            $('tr[data-index="' + dataIndexForm + '"]').html(htmlTr);
            dataIndex++;
        } else {
            $('#table-line tbody').append(
                '<tr data-index="' + dataIndex + '">' + htmlTr + '</tr>'
            );
            dataIndex++;
        }

        $('#price').autoNumeric('update', {mDec: 0});

        $('.edit-line').on('click', editLine);
        $('.delete-line').on('click', deleteLine);

        clearFormLine();

        dataIndex++;
        $('#modal-form-line').modal("hide");
    };

    var editLine = function() {
        var dataIndexForm = $(this).parent().parent().data('index');
        var lineId = $(this).parent().parent().find('[name="lineId[]"]').val();
        var itemId = $(this).parent().parent().find('[name="itemId[]"]').val();
        var itemCode = $(this).parent().parent().find('[name="itemCode[]"]').val();
        var itemName = $(this).parent().parent().find('[name="itemName[]"]').val();
        var whId = $(this).parent().parent().find('[name="whId[]"]').val();
        var warehouse = $(this).parent().parent().find('[name="warehouse[]"]').val();
        var price = $(this).parent().parent().find('[name="price[]"]').val();
        var stockItem = $(this).parent().parent().find('[name="stockItem[]"]').val();
        var qtyAdjustment = $(this).parent().parent().find('[name="qtyAdjustment[]"]').val();
        var uom = $(this).parent().parent().find('[name="uom[]"]').val();
        var note = $(this).parent().parent().find('[name="note[]"]').val();

        clearFormLine();
        $('#dataIndexForm').val(dataIndexForm);
        $('#lineId').val(lineId);
        $('#itemId').val(itemId);
        $('#itemCode').val(itemCode);
        $('#itemName').val(itemName);
        $('#whId').val(whId);
        $('#warehouse').val(warehouse);
        $('#price').val(price);
        $('#qtyAdjustment').val(qtyAdjustment);
        $('#stockItem').val(stockItem);
        $('#uom').val(uom);
        $('#note').val(note);

        $('#price').autoNumeric('update', {mDec: 0});

        $('#title-modal-line-detail').html('{{ trans('shared/common.edit') }}');
        $('#submit-modal-line').html('{{ trans('shared/common.edit') }}');

        $('#modal-form-line').modal("show");
    };

    var deleteLine = function() {
        $(this).parent().parent().remove();
    };

</script>
@endsection
