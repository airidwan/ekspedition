@extends('layouts.master')

@section('title', trans('inventory/menu.warehouse-transfer'))

<?php 
    use App\Modules\Inventory\Model\Master\MasterStock;
    use App\Modules\Inventory\Model\Transaction\WarehouseTransferHeader;
?>

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="widget">
            <div class="widget-header">
                <h2><i class="fa fa-briefcase"></i> <strong>{{ $title }}</strong> {{ trans('inventory/menu.warehouse-transfer') }}</h2>
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
                        <input type="hidden" name="id" value="{{ $model->wht_header_id }}">
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
                                    <div class="form-group {{ $errors->has('whtNumber') ? 'has-error' : '' }}">
                                        <label for="whtNumber" class="col-sm-4 control-label">{{ trans('inventory/fields.wht-number') }} <span class="required">*</span></label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="whtNumber" name="whtNumber" value="{{ count($errors) > 0 ? old('whtNumber') : $model->wht_number }}" readonly>
                                            @if($errors->has('whtNumber'))
                                            <span class="help-block">{{ $errors->first('whtNumber') }}</span>
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
                                    <div class="form-group {{ $errors->has('pic') ? 'has-error' : '' }}">
                                        <label for="pic" class="col-sm-4 control-label">{{ trans('inventory/fields.pic') }} <span class="required">*</span></label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="pic" name="pic" value="{{ count($errors) > 0 ? old('pic') : $model->pic }}" {{ $model->status != WarehouseTransferHeader::INCOMPLETE ? 'readonly' : '' }}>
                                            @if($errors->has('pic'))
                                            <span class="help-block">{{ $errors->first('pic') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="form-group {{ $errors->has('description') ? 'has-error' : '' }}">
                                        <label for="description" class="col-sm-4 control-label">{{ trans('shared/common.description') }} <span class="required">*</span></label>
                                        <div class="col-sm-8">
                                            <textarea class="form-control" rows="4" id="description" name="description" {{ $model->status != WarehouseTransferHeader::INCOMPLETE ? 'disabled' : '' }}>{{ count($errors) > 0 ? old('description') : $model->description }}</textarea>
                                            @if($errors->has('description'))
                                            <span class="help-block">{{ $errors->first('description') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="tabLines">
                                <div class="col-sm-12 portlets">
                                    @if(Gate::check('access', [$resource, 'insert']) && $model->status == WarehouseTransferHeader::INCOMPLETE )
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
                                                    <th>{{ trans('inventory/fields.from-wh') }}</th>
                                                    <th>{{ trans('inventory/fields.qty-need') }}</th>
                                                    <th>{{ trans('inventory/fields.uom') }}</th>
                                                    <th>{{ trans('inventory/fields.to-wh') }}</th>
                                                    <!-- <th>{{ trans('general-ledger/fields.coa') }}</th> -->
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
                                                    <td > {{ old('fromWhCode')[$i] }} </td>
                                                    <td class="text-right"> {{ old('qtyNeed')[$i] }} </td>
                                                    <td > {{ old('uom')[$i] }} </td>
                                                    <td > {{ old('toWhCode')[$i] }} </td>
                                                    <!-- <td class="text-right"> {{ old('coaCode')[$i] }} </td> -->
                                                    <td > {{ old('note')[$i] }} </td>
                                                    <td class="text-center">
                                                        @if($model->status == WarehouseTransferHeader::INCOMPLETE)
                                                        <a data-toggle="tooltip" class="btn btn-xs btn-warning edit-line" ><i class="fa fa-pencil"></i></a>
                                                        @if(Gate::check('access', [$resource, 'insert']))
                                                        <a data-toggle="tooltip" class="btn btn-danger btn-xs delete-line" ><i class="fa fa-remove"></i></a>
                                                        @endif
                                                        @endif
                                                        <input type="hidden" name="lineId[]" value="{{ old('lineId')[$i] }}">
                                                        <input type="hidden" name="itemId[]" value="{{ old('itemId')[$i] }}">
                                                        <input type="hidden" name="itemCode[]" value="{{ old('itemCode')[$i] }}">
                                                        <input type="hidden" name="itemName[]" value="{{ old('itemName')[$i] }}">
                                                        <input type="hidden" name="fromWhId[]" value="{{ old('fromWhId')[$i] }}">
                                                        <input type="hidden" name="fromWhCode[]" value="{{ old('fromWhCode')[$i] }}">
                                                        <input type="hidden" name="toWhId[]" value="{{ old('toWhId')[$i] }}">
                                                        <input type="hidden" name="toWhCode[]" value="{{ old('toWhCode')[$i] }}">
                                                        <input type="hidden" name="qtyNeed[]" value="{{ old('qtyNeed')[$i] }}">
                                                        <input type="hidden" name="uomId[]" value="{{ old('uomId')[$i] }}">
                                                        <input type="hidden" name="uom[]" value="{{ old('uom')[$i] }}">
                                                        <input type="hidden" name="price[]" value="{{ old('price')[$i] }}">
                                                        <input type="hidden" name="coaId[]" value="{{ old('coaId')[$i] }}">
                                                        <input type="hidden" name="coaCode[]" value="{{ old('coaCode')[$i] }}">
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
                                                    $fromWh  = $line->fromWarehouse;
                                                    $toWh    = $line->toWarehouse;
                                                    $coaComb = $line->coaCombination;
                                                    $coa     = !empty($coaComb) ? $coaComb->account : null;
                                                    $stock   = MasterStock::where('item_id', '=', $item->item_id)
                                                                ->where('wh_id', '=', $fromWh->wh_id)->first();
                                                ?>
                                                <tr data-index="{{ $dataIndex }}">
                                                    <td > {{ $item !== null ? $item->item_code : '' }} </td>
                                                    <td > {{ $item !== null ? $item->description : '' }} </td>
                                                    <td > {{ $fromWh !== null ? $fromWh->wh_code : '' }} </td>
                                                    <td class="text-right"> {{ number_format($line->qty_need) }} </td>
                                                    <td > {{ $uom !== null ? $uom->uom_code : '' }} </td>
                                                    <td > {{ $toWh !== null ? $toWh->wh_code : '' }} </td>
                                                    <!-- <td class="text-right"> {{ $coa !== null ? $coa->coa_code : '' }} </td> -->
                                                    <td > {{ $line->description }} </td>
                                                    <td class="text-center">
                                                     @if($model->status == WarehouseTransferHeader::INCOMPLETE)
                                                        <a data-toggle="tooltip" class="btn btn-xs btn-warning edit-line" ><i class="fa fa-pencil"></i></a>
                                                        @if(Gate::check('access', [$resource, 'insert']))
                                                        <a data-toggle="tooltip" class="btn btn-danger btn-xs delete-line" ><i class="fa fa-remove"></i></a>
                                                        @endif
                                                    @endif
                                                        <input type="hidden" name="lineId[]" value="{{ $line->mo_line_id }}">
                                                        <input type="hidden" name="itemId[]" value="{{ $item !== null ? $item->item_id : '' }}">
                                                        <input type="hidden" name="itemCode[]" value="{{ $item !== null ? $item->item_code : '' }}">
                                                        <input type="hidden" name="itemName[]" value="{{ $item !== null ? $item->description : '' }}">
                                                        <input type="hidden" name="fromWhId[]" value="{{ $fromWh !== null ? $fromWh->wh_id : '' }}">
                                                        <input type="hidden" name="fromWhCode[]" value="{{ $fromWh !== null ? $fromWh->wh_code : '' }}">
                                                        <input type="hidden" name="stockItem[]" value="{{ $stock !== null ? $stock->stock : '' }}">
                                                        <input type="hidden" name="qtyNeed[]" value="{{ $line->qty_need }}">
                                                        <input type="hidden" name="uomId[]" value="{{ $uom !== null ? $uom->uom_id : '' }}">
                                                        <input type="hidden" name="uom[]" value="{{ $uom !== null ? $uom->uom_code : '' }}">
                                                        <input type="hidden" name="toWhId[]" value="{{ $toWh !== null ? $toWh->wh_id : '' }}">
                                                        <input type="hidden" name="toWhCode[]" value="{{ $toWh !== null ? $toWh->wh_code : '' }}">
                                                        <input type="hidden" name="coaId[]" value="{{ $coa !== null ? $coa->coa_id : '' }}">
                                                        <input type="hidden" name="coaCode[]" value="{{ $coa !== null ? $coa->coa_code : '' }}">
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
                                @if($model->status == WarehouseTransferHeader::INCOMPLETE)
                                <button type="submit" name="btn-save" class="btn btn-sm btn-primary"><i class="fa fa-save"></i> {{ trans('shared/common.save') }}</button>
                                @endif
                                @if(Gate::check('access', [$resource, 'transact']) && $model->status == WarehouseTransferHeader::INCOMPLETE)
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
                                                <span class="btn input-group-addon" data-toggle="modal" data-target="#modal-item"><i class="fa fa-search"></i></span>
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
                                        <label for="fromWhCode" class="col-sm-4 control-label">{{ trans('inventory/fields.from-wh') }} </label>
                                        <div class="col-sm-8">
                                            <input type="hidden" class="form-control" id="fromWhId" name="fromWhId" value="" disabled="disabled">
                                            <input type="text" class="form-control" id="fromWhCode" name="fromWhCode" value="" disabled="disabled">
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
                                        <label for="qtyNeed" class="col-sm-4 control-label">{{ trans('inventory/fields.qty-need') }} <span class="required">*</span></label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control currency text-right" id="qtyNeed" name="qtyNeed" value="" >
                                            <span class="help-block"></span>
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
                                        <label for="toWhId" class="col-sm-4 control-label">{{ trans('inventory/fields.to-wh') }} </label>
                                        <div class="col-sm-8">
                                            <select class="form-control" name="toWhId" id="toWhId">
                                                <option value="">{{ trans('shared/common.please-select') }}</option>
                                                @foreach($optionWarehouse as $warehouse)
                                                <option value="{{ $warehouse->wh_id }}">{{ $warehouse->wh_code }}</option>
                                                @endforeach
                                            </select>
                                            <span class="help-block"></span>
                                        </div>
                                    </div>
                                    <!-- <div class="form-group {{ $errors->has('coaCode') ? 'has-error' : '' }}">
                                        <label for="coaCode" class="col-sm-4 control-label">{{ trans('general-ledger/fields.account') }} <span class="required">*</span></label>
                                        <div class="col-sm-8">
                                            <div class="input-group">
                                                <input type="hidden" name="coaId" id="coaId" value="">
                                                <input type="text" class="form-control" id="coaCode" name="coaCode" readonly>
                                                <span class="btn input-group-addon" data-toggle="modal" data-target="#modal-coa"><i class="fa fa-search"></i></span>
                                            </div>
                                            <span class="help-block"></span>
                                        </div>
                                    </div> -->
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
                        </tr>
                    </thead>
                    <tbody>
                       @foreach ($optionItem as $item)
                        <tr style="cursor: pointer;" data-item="{{ json_encode($item) }}">
                            <td>{{ $item->item_code }}</td>
                            <td>{{ $item->description }}</td>
                            <td>{{ $item->wh_code }}</td>
                            <td>{{ $item->stock }}</td>
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
<div id="modal-coa" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title text-center">Coa Account List</h4>
            </div>
            <div class="modal-body">
                <table id="datatables-coa" class="table table-hover table-striped table-bordered" cellspacing="0" width="100%">
                    <thead>
                        <tr>
                            <th>{{ trans('general-ledger/fields.coa-code') }}</th>
                            <th>{{ trans('shared/common.description') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($optionCoa as $coa)
                        <tr style="cursor: pointer;" data-coa="{{ json_encode($coa) }}">
                            <td>{{ $coa->coa_code }}</td>
                            <td>{{ $coa->description }}</td>
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

        $("#datatables-item").dataTable({
            "pageLength" : 10,
            "lengthChange": false
        });

        $('#datatables-item tbody').on('click', 'tr', function () {
            var item = $(this).data('item');

            

            $('#itemId').val(item.item_id);
            $('#itemCode').val(item.item_code);
            $('#itemName').val(item.description);
            $('#price').val(item.average_cost);
            $('#fromWhId').val(item.wh_id);
            $('#fromWhCode').val(item.wh_code);
            $('#stockItem').val(item.stock);
            $('#uom').val(item.uom_code);
            $('#uom_id').val(item.uom_id);

            $('#qtyNeed').autoNumeric('update', {mDec: 0, vMax: item.stock});

            $('#modal-item').modal('hide');
        });

        $("#datatables-coa").dataTable({
            "pagelength" : 10,
            "lengthChange": false
        });

        $('#datatables-coa tbody').on('click', 'tr', function () {
            var coa = $(this).data('coa');
            
            $('#coaId').val(coa.coa_id);
            $('#coaCode').val(coa.coa_code);

            $('#modal-coa').modal('hide');
        });

    });

    

    var cancelSaveLine = function() {
        $('#modal-form-line').modal("hide");
    };

    var clearLines = function() {
        $('#table-line tbody').html('');
        calculateTotal();
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
        $('#fromWhId').val('');
        $('#fromWhCode').val('');
        $('#toWhId').val('');
        $('#toWhCode').val('');
        $('#qtyNeed').val('');
        $('#stockItem').val('');
        $('#note').val('');
        $('#uom').val('');
        $('#coaId').val('');
        $('#coaCode').val('');

        $('#qtyNeed').parent().parent().removeClass('has-error');
        $('#qtyNeed').parent().find('span.help-block').html('');
        $('#toWhId').parent().parent().removeClass('has-error');
        $('#toWhId').parent().find('span.help-block').html('');
        $('#itemCode').parent().parent().parent().removeClass('has-error');
        $('#itemCode').parent().parent().find('span.help-block').html('');
        $('#coaCode').parent().parent().removeClass('has-error');
        $('#coaCode').parent().find('span.help-block').html('');
    };

    var checkItemExist = function(itemRequestId, whRequestId, whToId) {
        var exist = false;
        $('#table-line tbody tr').each(function (i, row) {
            var itemId = $(row).find('[name="itemId[]"]').val();
            var fromWhId   = $(row).find('[name="fromWhId[]"]').val();
            var toWhId   = $(row).find('[name="toWhId[]"]').val();

            if ($("#dataIndexForm").val() != '' && $("#dataIndexForm").val() == $(row).data('index')) {
                return;
            }
            
            if (itemId == itemRequestId && fromWhId == whRequestId && toWhId == whToId) {
                exist = true;
            }
        });
        return exist;
    };

    var saveLine = function() {
        var dataIndexForm = $('#dataIndexForm').val();
        var lineId = $('#lineId').val();
        var itemId = $('#itemId').val();
        var itemCode = $('#itemCode').val();
        var itemName = $('#itemName').val();
        var fromWhId = $('#fromWhId').val();
        var fromWhCode = $('#fromWhCode').val();
        var toWhId = $('#toWhId').val();
        var toWhCode = $('#toWhId option:selected').html();
        var uom = $('#uom').val();
        var stockItem = $('#stockItem').val();
        var qtyNeed = $('#qtyNeed').val();
        var coaId = $('#coaId').val();
        var coaCode = $('#coaCode').val();
        var note = $('#note').val();
        var error = false;

        if(checkItemExist(itemId, fromWhId, toWhId)){
                $('#modal-alert').find('.alert-message').html('Item and warehouse (WH From and WH to) selected on exist! ');
                $('#modal-alert').modal('show');
                return;
            }

        if (itemCode == '' || itemId == '') {
            $('#itemCode').parent().parent().parent().addClass('has-error');
            $('#itemCode').parent().parent().find('span.help-block').html('Choose item first');
            error = true;
        } else {
            $('#itemCode').parent().parent().parent().removeClass('has-error');
            $('#itemCode').parent().parent().find('span.help-block').html('');
        }

        // if (coaCode == '' || coaId == '') {
        //     $('#coaCode').parent().parent().addClass('has-error');
        //     $('#coaCode').parent().find('span.help-block').html('Account is required');
        //     error = true;
        // } else {
        //     $('#coaCode').parent().parent().removeClass('has-error');
        //     $('#coaCode').parent().find('span.help-block').html('');
        // }

        if (qtyNeed == '' || qtyNeed == 0) {
            $('#qtyNeed').parent().parent().addClass('has-error');
            $('#qtyNeed').parent().find('span.help-block').html('Quantity is required');
            error = true;
        } else {
            $('#qtyNeed').parent().parent().removeClass('has-error');
            $('#qtyNeed').parent().find('span.help-block').html('');
        }

        if (toWhCode == '' || toWhId == '') {
            $('#toWhId').parent().parent().addClass('has-error');
            $('#toWhId').parent().find('span.help-block').html('Warehouse is required');
            error = true;
        } else {
            $('#toWhId').parent().parent().removeClass('has-error');
            $('#toWhId').parent().find('span.help-block').html('');
        }

        if (toWhCode == fromWhCode || toWhId == fromWhId) {
            $('#toWhId').parent().parent().addClass('has-error');
            $('#toWhId').parent().find('span.help-block').html('Warehouse must be different with from warehouse');
            error = true;
        } else if (!(toWhCode == '' || toWhId == '')){
            $('#toWhId').parent().parent().removeClass('has-error');
            $('#toWhId').parent().find('span.help-block').html('');
        }

        if (error) {
            return;
        }

        var htmlTr = '<td >' + itemCode + '</td>' +
            '<td >' + itemName + '</td>' +
            '<td >' + fromWhCode + '</td>' +
            '<td class="text-right">' + qtyNeed + '</td>' +
            '<td >' + uom + '</td>' +
            '<td >' + toWhCode + '</td>' +
            // '<td >' + coaCode + '</td>' +
            '<td >' + note + '</td>' +
            '<td class="text-center">' +
            '<a data-toggle="tooltip" class="btn btn-xs btn-warning edit-line" ><i class="fa fa-pencil"></i></a> ' +
            '<a data-toggle="tooltip" class="btn btn-danger btn-xs delete-line" ><i class="fa fa-remove"></i></a>' +
            '<input type="hidden" name="lineId[]" value="'+ lineId + '">' +
            '<input type="hidden" name="itemId[]" value="' + itemId + '">' +
            '<input type="hidden" name="itemCode[]" value="' + itemCode + '">' +
            '<input type="hidden" name="itemName[]" value="' + itemName + '">' +
            '<input type="hidden" name="fromWhId[]" value="' + fromWhId + '">' +
            '<input type="hidden" name="fromWhCode[]" value="' + fromWhCode + '">' +
            '<input type="hidden" name="toWhId[]" value="' + toWhId + '">' +
            '<input type="hidden" name="toWhCode[]" value="' + toWhCode + '">' +
            '<input type="hidden" name="qtyNeed[]" value="' + qtyNeed + '">' +
            '<input type="hidden" name="stockItem[]" value="' + stockItem + '">' +
            '<input type="hidden" name="uom[]" value="' + uom + '">' +
            '<input type="hidden" name="coaId[]" value="' + coaId + '">' +
            '<input type="hidden" name="coaCode[]" value="' + coaCode + '">' +
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
        var fromWhId = $(this).parent().parent().find('[name="fromWhId[]"]').val();
        var fromWhCode = $(this).parent().parent().find('[name="fromWhCode[]"]').val();
        var toWhId = $(this).parent().parent().find('[name="toWhId[]"]').val();
        var toWhCode = $(this).parent().parent().find('[name="toWhId[]"] option:selected').html();
        var coaId = $(this).parent().parent().find('[name="coaId[]"]').val();
        var stockItem = $(this).parent().parent().find('[name="stockItem[]"]').val();
        var qtyNeed = $(this).parent().parent().find('[name="qtyNeed[]"]').val();
        var uom = $(this).parent().parent().find('[name="uom[]"]').val();
        var coaId = $(this).parent().parent().find('[name="coaId[]"]').val();
        var coaCode = $(this).parent().parent().find('[name="coaCode[]"]').val();
        var note = $(this).parent().parent().find('[name="note[]"]').val();

        clearFormLine();
        $('#dataIndexForm').val(dataIndexForm);
        $('#lineId').val(lineId);
        $('#itemId').val(itemId);
        $('#itemCode').val(itemCode);
        $('#itemName').val(itemName);
        $('#fromWhId').val(fromWhId);
        $('#fromWhCode').val(fromWhCode);
        $('#toWhId').val(toWhId);
        $('#toWhCode').val(toWhCode);
        $('#coaId').val(coaId);
        $('#coaCode').val(coaCode);
        $('#qtyNeed').val(qtyNeed);
        $('#stockItem').val(stockItem);
        $('#uom').val(uom);
        $('#note').val(note);

        $('#qtyNeed').autoNumeric('update', {mDec: 0, vMax: stockItem});

        $('#title-modal-line-detail').html('{{ trans('shared/common.edit') }}');
        $('#submit-modal-line').html('{{ trans('shared/common.edit') }}');

        $('#modal-form-line').modal("show");
    };

    var deleteLine = function() {
        $(this).parent().parent().remove();
    };

</script>
@endsection
