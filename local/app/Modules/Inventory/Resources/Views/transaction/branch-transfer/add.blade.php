@extends('layouts.master')

@section('title', trans('inventory/menu.branch-transfer'))

<?php 
    use App\Modules\Inventory\Model\Master\MasterStock;
    use App\Modules\Inventory\Model\Transaction\BranchTransferHeader;
?>

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="widget">
            <div class="widget-header">
                <h2><i class="fa fa-briefcase"></i> <strong>{{ $title }}</strong> {{ trans('inventory/menu.branch-transfer') }}</h2>
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
                        <input type="hidden" name="id" value="{{ $model->bt_header_id }}">
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
                                    <div class="form-group {{ $errors->has('btNumber') ? 'has-error' : '' }}">
                                        <label for="btNumber" class="col-sm-4 control-label">{{ trans('inventory/fields.bt-number') }} <span class="required">*</span></label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="btNumber" name="btNumber" value="{{ count($errors) > 0 ? old('btNumber') : $model->bt_number }}" readonly>
                                            @if($errors->has('btNumber'))
                                            <span class="help-block">{{ $errors->first('btNumber') }}</span>
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
                                            <input type="text" class="form-control" id="pic" name="pic" value="{{ count($errors) > 0 ? old('pic') : $model->pic }}" {{ $model->status != BranchTransferHeader::INCOMPLETE ? 'readonly' : '' }}>
                                            @if($errors->has('pic'))
                                            <span class="help-block">{{ $errors->first('pic') }}</span>
                                            @endif
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
                                            <span class="btn input-group-addon" id="modalDriver" data-toggle="{{ $model->status == BranchTransferHeader::INCOMPLETE ? 'modal' : '' }}" data-target="#modal-driver"><i class="fa fa-search"></i></span>
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
                                            <span class="btn input-group-addon {{ $model->status == BranchTransferHeader::INCOMPLETE ? 'remove-truck' : '' }}"><i class="fa fa-remove"></i></span>
                                            <span class="btn input-group-addon" id="modalTruck" data-toggle="{{ $model->status == BranchTransferHeader::INCOMPLETE ? 'modal' : '' }}" data-target="#modal-truck"><i class="fa fa-search"></i></span>
                                            </div>
                                            @if($errors->has('truckCode'))
                                            <span class="help-block">{{ $errors->first('truckCode') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="form-group {{ $errors->has('description') ? 'has-error' : '' }}">
                                        <label for="description" class="col-sm-4 control-label">{{ trans('shared/common.description') }} <span class="required">*</span></label>
                                        <div class="col-sm-8">
                                            <textarea class="form-control" rows="4" id="description" name="description" {{ $model->status != BranchTransferHeader::INCOMPLETE ? 'disabled' : '' }}>{{ count($errors) > 0 ? old('description') : $model->description }}</textarea>
                                            @if($errors->has('description'))
                                            <span class="help-block">{{ $errors->first('description') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="tabLines">
                                <div class="col-sm-12 portlets">
                                    @if(Gate::check('access', [$resource, 'insert']) && $model->status == BranchTransferHeader::INCOMPLETE )
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
                                                    <th>{{ trans('inventory/fields.item-name') }}</th>
                                                    <th>{{ trans('inventory/fields.from-wh') }}</th>
                                                    <th>{{ trans('inventory/fields.qty-need') }}</th>
                                                    <th>{{ trans('inventory/fields.uom') }}</th>
                                                    <th>{{ trans('inventory/fields.to-branch') }}</th>
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
                                                    <td > {{ old('toBranchName')[$i] }} </td>
                                                    <td > {{ old('toWhCode')[$i] }} </td>
                                                    <!-- <td class="text-right"> {{ old('coaCode')[$i] }} </td> -->
                                                    <td > {{ old('note')[$i] }} </td>
                                                    <td class="text-center">
                                                        @if($model->status == BranchTransferHeader::INCOMPLETE)
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
                                                        <input type="hidden" name="toBranchName[]" value="{{ old('toWhCode')[$i] }}">
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
                                                    $item     = $line->item;
                                                    $uom      = !empty($item) ? $item->uom : null;
                                                    $fromWh   = $line->fromWarehouse;
                                                    $toWh     = $line->toWarehouse;
                                                    $toBranch = !empty($toWh) ? $toWh->branch : null;
                                                    $coaComb  = $line->coaCombination;
                                                    $coa      = !empty($coaComb) ? $coaComb->account : null;
                                                    $stock    = MasterStock::where('item_id', '=', $item->item_id)
                                                                ->where('wh_id', '=', $fromWh->wh_id)->first();
                                                ?>
                                                <tr data-index="{{ $dataIndex }}">
                                                    <td > {{ $item !== null ? $item->item_code : '' }} </td>
                                                    <td > {{ $item !== null ? $item->description : '' }} </td>
                                                    <td > {{ $fromWh !== null ? $fromWh->wh_code : '' }} </td>
                                                    <td class="text-right"> {{ number_format($line->qty_need) }} </td>
                                                    <td > {{ $uom !== null ? $uom->uom_code : '' }} </td>
                                                    <td > {{ $toBranch !== null ? $toBranch->branch_name : '' }} </td>
                                                    <td > {{ $toWh !== null ? $toWh->wh_code : '' }} </td>
                                                    <!-- <td class="text-right"> {{ $coa !== null ? $coa->coa_code : '' }} </td> -->
                                                    <td > {{ $line->description }} </td>
                                                    <td class="text-center">
                                                    @if($model->status == BranchTransferHeader::INCOMPLETE)
                                                        <a data-toggle="tooltip" class="btn btn-xs btn-warning edit-line" ><i class="fa fa-pencil"></i></a>
                                                        @if(Gate::check('access', [$resource, 'insert']))
                                                        <a data-toggle="tooltip" class="btn btn-danger btn-xs delete-line" ><i class="fa fa-remove"></i></a>
                                                        @endif
                                                        @endif
                                                        <input type="hidden" name="lineId[]" value="{{ $line->bt_line_id }}">
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
                                                        <input type="hidden" name="toBranchName[]" value="{{ $toBranch !== null ? $toBranch->branch_name : '' }}">
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
                                @if($model->status == BranchTransferHeader::INCOMPLETE)
                                <button type="submit" name="btn-save" class="btn btn-sm btn-primary"><i class="fa fa-save"></i> {{ trans('shared/common.save') }}</button>
                                @endif
                                @if($model->status == BranchTransferHeader::COMPLETE || $model->status == BranchTransferHeader::INPROCESS)
                                <a href="{{ URL($url.'/print-pdf/'.$model->bt_header_id) }}" class="button btn btn-sm btn-success" target="_blank">
                                    <i class="fa fa-print"></i> {{ trans('shared/common.print') }}
                                </a>
                                @endif
                                @if(Gate::check('access', [$resource, 'transact']) && $model->status == BranchTransferHeader::INCOMPLETE)
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
                                        <label for="itemName" class="col-sm-4 control-label">{{ trans('inventory/fields.item-name') }} </label>
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
                                    <div class="form-group {{ $errors->has('toBranchName') ? 'has-error' : '' }}">
                                        <label for="toBranchName" class="col-sm-4 control-label">{{ trans('inventory/fields.to-branch') }} <span class="required">*</span></label>
                                        <div class="col-sm-8">
                                            <div class="input-group">
                                                <input type="text" class="form-control" id="toBranchName" name="toBranchName" readonly> 
                                                <span class="btn input-group-addon" data-toggle="modal" data-target="#modal-warehouse"><i class="fa fa-search"></i></span>
                                            </div>
                                            <span class="help-block"></span>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="toWhCode" class="col-sm-4 control-label">{{ trans('inventory/fields.to-wh') }} </label>
                                        <div class="col-sm-8">
                                            <input type="hidden" name="toWhId" id="toWhId" value="">
                                            <input type="text" class="form-control" id="toWhCode" name="toWhCode" value="0" disabled="disabled">
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
                            <th>{{ trans('operational/fields.nickname') }}</th>
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
                            <td>{{ $driver->driver_nickname }}</td>
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
<div id="modal-warehouse" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title text-center">Warehouse List</h4>
            </div>
            <div class="modal-body">
                <table id="datatables-warehouse" class="table table-hover table-striped table-bordered" cellspacing="0" width="100%">
                    <thead>
                        <tr>
                            <th>{{ trans('operational/fields.branch-code') }}</th>
                            <th>{{ trans('operational/fields.branch-name') }}</th>
                            <th>{{ trans('operational/fields.warehouse-code') }}</th>
                            <th>{{ trans('shared/common.description') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($optionWarehouse as $warehouse)
                        <tr style="cursor: pointer;" data-warehouse="{{ json_encode($warehouse) }}">
                            <td>{{ $warehouse->branch_code }}</td>
                            <td>{{ $warehouse->branch_name }}</td>
                            <td>{{ $warehouse->wh_code }}</td>
                            <td>{{ $warehouse->description }}</td>
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
                            <th>{{ trans('inventory/fields.item-name') }}</th>
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
        $(".remove-truck").on('click', function() {
            $('#truckId').val('');
            $('#truckCode').val('');
        });

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

        $("#datatables-warehouse").dataTable({
            "pagelength" : 10,
            "lengthChange": false
        });

        $('#datatables-warehouse tbody').on('click', 'tr', function () {
            var warehouse = $(this).data('warehouse');
            
            $('#toWhId').val(warehouse.wh_id);
            $('#toWhCode').val(warehouse.wh_code);
            $('#toBranchName').val(warehouse.branch_name);

            $('#modal-warehouse').modal('hide');
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
        $('#toBranchName').val('');
        $('#toBranchId').val('');
        $('#toWhId').val('');
        $('#toWhCode').val('');
        $('#qtyNeed').val('');
        $('#stockItem').val('');
        $('#note').val('');
        $('#uom').val('');
        $('#coaId').val('');
        $('#coaCode').val('');

        $('#itemCode').parent().parent().parent().removeClass('has-error');
        $('#itemCode').parent().parent().find('span.help-block').html('');
        $('#toBranchName').parent().parent().removeClass('has-error');
        $('#toBranchName').parent().find('span.help-block').html('');
        $('#toWhCode').parent().parent().removeClass('has-error');
        $('#toWhCode').parent().find('span.help-block').html('');
        $('#qtyNeed').parent().parent().removeClass('has-error');
        $('#qtyNeed').parent().find('span.help-block').html('');
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
        var toWhCode = $('#toWhCode').val();
        var toBranchName = $('#toBranchName').val();
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
            $('#toBranchName').parent().parent().addClass('has-error');
            $('#toBranchName').parent().find('span.help-block').html('Warehouse is required');
            error = true;
        } else {
            $('#toBranchName').parent().parent().removeClass('has-error');
            $('#toBranchName').parent().find('span.help-block').html('');
        }

        if (toWhCode == fromWhCode || toWhId == fromWhId) {
            $('#toWhId').parent().parent().addClass('has-error');
            $('#toWhId').parent().find('span.help-block').html('Warehouse must be different with from warehouse');
            error = true;
        } else {
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
            '<td >' + toBranchName + '</td>' +
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
            '<input type="hidden" name="toBranchName[]" value="' + toBranchName + '">' +
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
        var toWhCode = $(this).parent().parent().find('[name="toWhCode[]"]').val();
        var toBranchName = $(this).parent().parent().find('[name="toBranchName[]"]').val();
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
        $('#toBranchName').val(toBranchName);
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
