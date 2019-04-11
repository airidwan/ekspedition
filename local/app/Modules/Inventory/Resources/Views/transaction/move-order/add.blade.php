@extends('layouts.master')

@section('title', trans('inventory/menu.move-order'))

<?php 
    use App\Service\Penomoran; 
    use App\Modules\Inventory\Model\Master\MasterStock;
    use App\Modules\Inventory\Model\Transaction\MoveOrderHeader;
?>

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="widget">
            <div class="widget-header">
                <h2><i class="fa fa-briefcase"></i> <strong>{{ $title }}</strong> {{ trans('inventory/menu.move-order') }}</h2>
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
                        <input type="hidden" name="id" value="{{ $model->mo_header_id }}">
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
                                    <div class="form-group {{ $errors->has('moNumber') ? 'has-error' : '' }}">
                                        <label for="moNumber" class="col-sm-4 control-label">{{ trans('inventory/fields.mo-number') }} <span class="required">*</span></label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="moNumber" name="moNumber" value="{{ count($errors) > 0 ? old('moNumber') : $model->mo_number }}" readonly>
                                            @if($errors->has('moNumber'))
                                            <span class="help-block">{{ $errors->first('moNumber') }}</span>
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
                                            <select class="form-control" id="type" name="type" {{ $model->status == MoveOrderHeader::COMPLETE ? 'disabled' : '' }}>
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
                                    <!-- <div class="form-group {{ $errors->has('pic') ? 'has-error' : '' }}">
                                        <label for="pic" class="col-sm-4 control-label">{{ trans('inventory/fields.pic') }} <span class="required">*</span></label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="pic" name="pic" value="{{ count($errors) > 0 ? old('pic') : $model->pic }}">
                                            @if($errors->has('pic'))
                                            <span class="help-block">{{ $errors->first('pic') }}</span>
                                            @endif
                                        </div>
                                    </div>  -->
                                    <div class="form-group {{ $errors->has('description') ? 'has-error' : '' }}">
                                        <label for="description" class="col-sm-4 control-label">{{ trans('shared/common.description') }} <span class="required">*</span></label>
                                        <div class="col-sm-8">
                                            <textarea class="form-control" rows="4" id="description" name="description" {{ $model->status == MoveOrderHeader::COMPLETE ? 'disabled' : '' }}>{{ count($errors) > 0 ? old('description') : $model->description }}</textarea>
                                            @if($errors->has('description'))
                                            <span class="help-block">{{ $errors->first('description') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-6 portlets">
                                    <?php
                                        $modelVendor = $model->vendor;
                                        $vendorId    = !empty($modelVendor) ? $modelVendor->vendor_id : '' ; 
                                        $vendorName  = !empty($modelVendor) ? $modelVendor->vendor_name : $model->pic ; 
                                    ?>
                                    <div class="form-group {{ $errors->has('vendorId') ? 'has-error' : '' }}">
                                        <label for="vendorName" class="col-sm-4 control-label">{{ trans('inventory/fields.pic') }}</label>
                                        <div class="col-sm-8">
                                            <div class="input-group">
                                            <input type="hidden" class="form-control" id="vendorId" name="vendorId" value="{{ count($errors) > 0 ? old('vendorId') : $model->vendor_id }}">
                                            <input type="text" class="form-control" id="vendorName" name="vendorName" value="{{ count($errors) > 0 ? old('vendorName') : $vendorName }}" readonly>
                                            <span class="btn input-group-addon {{ $model->status == MoveOrderHeader::INCOMPLETE ? 'remove-vendor' : '' }}"><i class="fa fa-remove"></i></span>
                                            <span class="btn input-group-addon" id="modalVendor" data-toggle="{{ $model->status == MoveOrderHeader::INCOMPLETE ? 'modal' : '' }}" data-target="#modal-vendor"><i class="fa fa-search"></i></span>
                                            </div>
                                            @if($errors->has('vendorId'))
                                            <span class="help-block">{{ $errors->first('vendorId') }}</span>
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
                                            <span class="btn input-group-addon {{ $model->status == MoveOrderHeader::INCOMPLETE ? 'remove-truck' : '' }}"><i class="fa fa-remove"></i></span>
                                            <span class="btn input-group-addon" id="modalTruck" data-toggle="{{ $model->status == MoveOrderHeader::INCOMPLETE ? 'modal' : '' }}" data-target="#modal-truck"><i class="fa fa-search"></i></span>
                                            </div>
                                            @if($errors->has('truckCode'))
                                            <span class="help-block">{{ $errors->first('truckCode') }}</span>
                                            @endif
                                        </div>
                                    </div> 
                                    <?php
                                        $modelDriver = $model->driver;
                                        $driverId    = !empty($modelDriver) ? $modelDriver->driver_id : '' ; 
                                        $driverName  = !empty($modelDriver) ? $modelDriver->driver_name : '' ; 
                                    ?>
                                    <div class="form-group {{ $errors->has('driverName') ? 'has-error' : '' }}">
                                        <label for="driverName" class="col-sm-4 control-label">{{ trans('operational/fields.driver') }} </label>
                                        <div class="col-sm-8">
                                            <div class="input-group">
                                            <input type="hidden" class="form-control" id="driverId" name="driverId" value="{{ count($errors) > 0 ? old('driverId') : $model->driver_id }}">
                                            <input type="text" class="form-control" id="driverName" name="driverName" value="{{ count($errors) > 0 ? old('driverName') : $driverName }}" readonly>
                                            <span class="btn input-group-addon {{ $model->status == MoveOrderHeader::INCOMPLETE ? 'remove-driver' : '' }}"><i class="fa fa-remove"></i></span>
                                            <span class="btn input-group-addon" id="modalDriver" data-toggle="{{ $model->status == MoveOrderHeader::INCOMPLETE ? 'modal' : '' }}" data-target="#modal-driver"><i class="fa fa-search"></i></span>
                                            </div>
                                            @if($errors->has('driverName'))
                                            <span class="help-block">{{ $errors->first('driverName') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    <?php
                                        $modelService  = $model->service;
                                        $serviceId     = !empty($modelService) ? $modelService->service_asset_id : '' ; 
                                        $serviceNumber = !empty($modelService) ? $modelService->service_number : '' ; 
                                    ?>
                                    <div id="formServiceNumber" class="form-group {{ $errors->has('serviceNumber') ? 'has-error' : '' }}">
                                        <label for="serviceNumber" class="col-sm-4 control-label">{{ trans('asset/fields.service-number') }} <span class="required">*</span></label>
                                        <div class="col-sm-8">
                                            <div class="input-group">
                                            <input type="hidden" class="form-control" id="serviceId" name="serviceId" value="{{ count($errors) > 0 ? old('serviceId') : $model->service_asset_id }}">
                                            <input type="text" class="form-control" id="serviceNumber" name="serviceNumber" value="{{ count($errors) > 0 ? old('serviceNumber') : $serviceNumber }}" readonly>
                                            <span class="btn input-group-addon" id="modalService" data-toggle="{{ $model->status == MoveOrderHeader::INCOMPLETE ? 'modal' : '' }}" data-target="#modal-service"><i class="fa fa-search"></i></span>
                                            </div>
                                            @if($errors->has('serviceNumber'))
                                            <span class="help-block">{{ $errors->first('serviceNumber') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="tabLines">
                                <div class="col-sm-12 portlets">
                                    @if(Gate::check('access', [$resource, 'insert']) && $model->status == MoveOrderHeader::INCOMPLETE )
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
                                                    <th>{{ trans('inventory/fields.warehouse') }}</th>
                                                    <th>{{ trans('inventory/fields.qty-need') }}</th>
                                                    <th>{{ trans('inventory/fields.uom') }}</th>
                                                    <!-- <th>{{ trans('general-ledger/fields.cost') }}</th> -->
                                                    <th>{{ trans('general-ledger/fields.coa') }}</th>
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
                                                    <td class="text-right"> {{ old('qtyNeed')[$i] }} </td>
                                                    <td > {{ old('uom')[$i] }} </td>
                                                    <!-- <td class="text-right"> {{ old('cost')[$i] }} </td> -->
                                                    <td> {{ old('coaDesc')[$i] }} </td>
                                                    <td > {{ old('note')[$i] }} </td>
                                                    <td class="text-center">
                                                        @if($model->status == MoveOrderHeader::INCOMPLETE)
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
                                                        <input type="hidden" name="qtyNeed[]" value="{{ old('qtyNeed')[$i] }}">
                                                        <input type="hidden" name="uomId[]" value="{{ old('uomId')[$i] }}">
                                                        <input type="hidden" name="uom[]" value="{{ old('uom')[$i] }}">
                                                        <input type="hidden" name="cost[]" value="{{ old('cost')[$i] }}">
                                                        <input type="hidden" name="averageCost[]" value="{{ old('averageCost')[$i] }}">
                                                        <input type="hidden" name="coaId[]" value="{{ old('coaId')[$i] }}">
                                                        <input type="hidden" name="coaDesc[]" value="{{ old('coaDesc')[$i] }}">
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
                                                    $driver  = $line->driver;
                                                    $truck   = $line->truck;
                                                    $coaComb = $line->coaCombination;
                                                    $coa     = !empty($coaComb) ? $coaComb->account : null;
                                                    $stock   = MasterStock::where('item_id', '=', $item->item_id)
                                                                ->where('wh_id', '=', $wh->wh_id)->first();
                                                ?>
                                                <tr data-index="{{ $dataIndex }}">
                                                    <td > {{ $item !== null ? $item->item_code : '' }} </td>
                                                    <td > {{ $item !== null ? $item->description : '' }} </td>
                                                    <td > {{ $wh !== null ? $wh->wh_code : '' }} </td>
                                                    <td class="text-right"> {{ $line->qty_need }} </td>
                                                    <td > {{ $uom !== null ? $uom->uom_code : '' }} </td>
                                                    <!-- <td class="text-right"> {{ number_format($line->cost) }} </td> -->
                                                    <td > {{ $coa !== null ? $coa->description : '' }} </td>
                                                    <td > {{ $line->description }} </td>
                                                    <td class="text-center">
                                                    @if($model->status == MoveOrderHeader::INCOMPLETE)
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
                                                        <input type="hidden" name="qtyNeed[]" value="{{ $line->qty_need }}">
                                                        <input type="hidden" name="uomId[]" value="{{ $uom !== null ? $uom->uom_id : '' }}">
                                                        <input type="hidden" name="uom[]" value="{{ $uom !== null ? $uom->uom_code : '' }}">
                                                        <input type="hidden" name="cost[]" value="{{ $line->cost }}">
                                                        <input type="hidden" name="averageCost[]" value="{{ $stock !== null ? $stock->average_cost : '' }}">
                                                        <input type="hidden" name="coaId[]" value="{{ $coa !== null ? $coa->coa_id : '' }}">
                                                        <input type="hidden" name="coaDesc[]" value="{{ $coa !== null ? $coa->description : '' }}">
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
                                @if($model->status == MoveOrderHeader::COMPLETE)
                                <a href="{{ URL($url.'/print-pdf/'.$model->mo_header_id) }}" class="button btn btn-sm btn-success" target="_blank">
                                    <i class="fa fa-print"></i> {{ trans('shared/common.print') }}
                                </a>
                                @endif
                                @if($model->status == MoveOrderHeader::INCOMPLETE)
                                <button type="submit" name="btn-save" class="btn btn-sm btn-primary"><i class="fa fa-save"></i> {{ trans('shared/common.save') }}</button>
                                @endif
                                @if(Gate::check('access', [$resource, 'transact']) && $model->status == MoveOrderHeader::INCOMPLETE)
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
                                        <label for="warehouse" class="col-sm-4 control-label">{{ trans('inventory/fields.warehouse') }} </label>
                                        <div class="col-sm-8">
                                            <input type="hidden" class="form-control" id="whId" name="whId" value="" disabled="disabled">
                                            <input type="text" class="form-control" id="warehouse" name="warehouse" value="" disabled="disabled">
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
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="uom" class="col-sm-4 control-label">{{ trans('inventory/fields.uom') }} </label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="uom" name="uom" value="0" disabled="disabled">
                                            <span class="help-block"></span>
                                        </div>
                                    </div>
                                    <!-- <div class="form-group">
                                        <label for="cost" class="col-sm-4 control-label">{{ trans('general-ledger/fields.cost') }} </label>
                                        <div class="col-sm-8">
                                            <input type="hidden" class="form-control currency text-right" id="averageCost" name="averageCost" value="" >
                                            <input type="text" class="form-control currency text-right" id="cost" name="cost" value="" >
                                        </div>
                                    </div> -->
                                    <div class="form-group {{ $errors->has('coaDesc') ? 'has-error' : '' }}">
                                        <label for="coaDesc" class="col-sm-4 control-label">{{ trans('general-ledger/fields.account') }} <span class="required">*</span></label>
                                        <div class="col-sm-8">
                                            <div class="input-group">
                                                <input type="hidden" name="coaId" id="coaId" value="">
                                                <input type="text" class="form-control" id="coaDesc" name="coaDesc" readonly>
                                                <span class="btn input-group-addon" data-toggle="modal" data-target="#modal-coa"><i class="fa fa-search"></i></span>
                                            </div>
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
<div id="modal-service" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title text-center">Service Asset List</h4>
            </div>
            <div class="modal-body">
                <table id="datatables-service" class="table table-hover table-striped table-bordered" cellspacing="0" width="100%">
                   <thead>
                        <tr>
                            <th>{{ trans('asset/fields.service-number') }}</th>
                            <th>{{ trans('shared/common.type') }}</th>
                            <th>{{ trans('asset/fields.asset-number') }}</th>
                            <th>{{ trans('inventory/fields.item') }}</th>
                            <th>{{ trans('operational/fields.police-number') }}</th>
                            <th>{{ trans('operational/fields.owner-name') }}</th>
                            <th>{{ trans('shared/common.description') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                       @foreach ($optionService as $service)
                        <tr style="cursor: pointer;" data-service="{{ json_encode($service) }}">
                            <td>{{ $service->service_number }}</td>
                            <td>{{ $service->service_type }}</td>
                            <td>{{ $service->asset_number }}</td>
                            <td>{{ $service->item_description }}</td>
                            <td>{{ $service->police_number }}</td>
                            <td>{{ $service->owner_name }}</td>
                            <td>{{ $service->note }}</td>
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
                            <th>{{ trans('general-ledger/fields.cost') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                       @foreach ($optionItem as $item)
                        <tr style="cursor: pointer;" data-item="{{ json_encode($item) }}">
                            <td>{{ $item->item_code }}</td>
                            <td>{{ $item->description }}</td>
                            <td>{{ $item->wh_code }}</td>
                            <td class="text-right">{{ number_format($item->stock) }}</td>
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
<div id="modal-vendor" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title text-center">Vendor List</h4>
            </div>
            <div class="modal-body">
                <table id="datatables-vendor" class="table table-hover table-striped table-bordered" cellspacing="0" width="100%">
                   <thead>
                        <tr>
                            <th>{{ trans('payable/fields.vendor-code') }}</th>
                            <th>{{ trans('payable/fields.vendor-name') }}</th>
                            <th>{{ trans('shared/common.address') }}</th>
                            <th>{{ trans('shared/common.description') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                       @foreach ($optionVendor->get() as $vendor)
                        <tr style="cursor: pointer;" data-vendor="{{ json_encode($vendor) }}">
                            <td>{{ $vendor->vendor_code }}</td>
                            <td>{{ $vendor->vendor_name }}</td>
                            <td>{{ $vendor->address }}</td>
                            <td>{{ $vendor->description }}</td>
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
                            <td>{{ $coa->description }}</td>
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
        $('#qtyNeed').on('keyup', function(){
            var qtyNeed     = $('#qtyNeed').val();
            var averageCost = $('#averageCost').val();

            $('#cost').val(qtyNeed * averageCost);
            $('#cost').autoNumeric('update', {mDec: 0});
        });

        if ($('#type').val() == '{{ MoveOrderHeader::SERVICE }}') {
            $('#formServiceNumber').removeClass('hidden');
        }else{
            $('#formServiceNumber').addClass('hidden');
        }

        $('#type').on('change', function(){
            $('#serviceId').val('');
            $('#serviceNumber').val('');
            if ($('#type').val() == '{{ MoveOrderHeader::SERVICE }}') {
                $('#formServiceNumber').removeClass('hidden');
            }else{
                $('#formServiceNumber').addClass('hidden');
            }
        });

        $(".remove-vendor").on('click', function() {
            $('#vendorId').val('');
            $('#vendorName').val('');
        });

        $(".remove-truck").on('click', function() {
            $('#truckId').val('');
            $('#truckCode').val('');
        });

        $(".remove-driver").on('click', function() {
            $('#driverId').val('');
            $('#driverName').val('');
        });
        
        $('#save-line').on('click', saveLine);
        $('.delete-line').on('click', deleteLine);
        $('.edit-line').on('click', editLine);
        $('#cancel-save-line').on('click', cancelSaveLine);
        $('#clear-lines').on('click', clearLines);
        $('.add-line').on('click', addLine);


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

        $("#datatables-vendor").dataTable({
            "pagelength" : 10,
            "lengthChange": false
        });

        $('#datatables-vendor tbody').on('click', 'tr', function () {
            var vendor = $(this).data('vendor');

            $('#vendorId').val(vendor.vendor_id);
            $('#vendorName').val(vendor.vendor_name);
            
            $('#modal-vendor').modal('hide');
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

        $('#datatables-service tbody').on('click', 'tr', function () {
            var service = $(this).data('service');

            $('#serviceId').val(service.service_asset_id);
            $('#serviceNumber').val(service.service_number);
            $('#truckId').val(service.truck_id);
            $('#truckCode').val(service.truck_code);

            $('#modal-service').modal('hide');
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
            $('#whId').val(item.wh_id);
            $('#warehouse').val(item.wh_code);
            $('#stockItem').val(item.stock);
            $('#uom').val(item.uom_code);
            $('#uom_id').val(item.uom_id);
            $('#averageCost').val(item.average_cost);

            $('#qtyNeed').val('');

            $('#stockItem').autoNumeric('update', {mDec: 0});
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
            $('#coaDesc').val(coa.description);

            $('#modal-coa').modal('hide');
        });

    });

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
        $('#whId').val('');
        $('#warehouse').val('');
        $('#qtyNeed').val('');
        $('#cost').val('');
        $('#averageCost').val('');
        $('#stockItem').val('');
        $('#note').val('');
        $('#uom').val('');
        $('#coaId').val('');
        $('#coaDesc').val('');

        $('#itemCode').parent().parent().parent().removeClass('has-error');
        $('#itemCode').parent().parent().find('span.help-block').html('');
        $('#coaDesc').parent().parent().removeClass('has-error');
        $('#coaDesc').parent().find('span.help-block').html('');
    };

    var saveLine = function() {
        var dataIndexForm = $('#dataIndexForm').val();
        var lineId = $('#lineId').val();
        var itemId = $('#itemId').val();
        var itemCode = $('#itemCode').val();
        var itemName = $('#itemName').val();
        var whId = $('#whId').val();
        var uom = $('#uom').val();
        var stockItem = $('#stockItem').val();
        var qtyNeed = $('#qtyNeed').val();
        var warehouse = $('#warehouse').val();
        var cost = $('#cost').val();
        var averageCost = $('#averageCost').val();
        var coaId = $('#coaId').val();
        var coaDesc = $('#coaDesc').val();
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

        if (coaDesc == '' || coaId == '') {
            $('#coaDesc').parent().parent().addClass('has-error');
            $('#coaDesc').parent().find('span.help-block').html('Account is required');
            error = true;
        } else {
            $('#coaDesc').parent().parent().removeClass('has-error');
            $('#coaDesc').parent().find('span.help-block').html('');
        }

        if (qtyNeed == '') {
            $('#qtyNeed').parent().parent().addClass('has-error');
            $('#qtyNeed').parent().find('span.help-block').html('Quantity is required');
            error = true;
        } else {
            $('#qtyNeed').parent().parent().removeClass('has-error');
            $('#qtyNeed').parent().find('span.help-block').html('');
        }

        if (error) {
            return;
        }

        var htmlTr = '<td >' + itemCode + '</td>' +
            '<td >' + itemName + '</td>' +
            '<td >' + warehouse + '</td>' +
            '<td class="text-right">' + qtyNeed + '</td>' +
            '<td >' + uom + '</td>' +
            '<td>' + coaDesc + '</td>' +
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
            '<input type="hidden" name="qtyNeed[]" value="' + qtyNeed + '">' +
            '<input type="hidden" name="stockItem[]" value="' + stockItem + '">' +
            '<input type="hidden" name="uom[]" value="' + uom + '">' +
            '<input type="hidden" name="cost[]" value="' + cost + '">' +
            '<input type="hidden" name="averageCost[]" value="' + averageCost + '">' +
            '<input type="hidden" name="coaId[]" value="' + coaId + '">' +
            '<input type="hidden" name="coaDesc[]" value="' + coaDesc + '">' +
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
        var whId = $(this).parent().parent().find('[name="whId[]"]').val();
        var warehouse = $(this).parent().parent().find('[name="warehouse[]"]').val();
        var cost = $(this).parent().parent().find('[name="cost[]"]').val();
        var averageCost = $(this).parent().parent().find('[name="averageCost[]"]').val();
        var coaId = $(this).parent().parent().find('[name="coaId[]"]').val();
        var stockItem = $(this).parent().parent().find('[name="stockItem[]"]').val();
        var qtyNeed = $(this).parent().parent().find('[name="qtyNeed[]"]').val();
        var uom = $(this).parent().parent().find('[name="uom[]"]').val();
        var coaId = $(this).parent().parent().find('[name="coaId[]"]').val();
        var coaDesc = $(this).parent().parent().find('[name="coaDesc[]"]').val();
        var note = $(this).parent().parent().find('[name="note[]"]').val();

        clearFormLine();
        $('#dataIndexForm').val(dataIndexForm);
        $('#lineId').val(lineId);
        $('#itemId').val(itemId);
        $('#itemCode').val(itemCode);
        $('#itemName').val(itemName);
        $('#whId').val(whId);
        $('#warehouse').val(warehouse);
        $('#cost').val(cost);
        $('#averageCost').val(averageCost);
        $('#coaId').val(coaId);
        $('#coaDesc').val(coaDesc);
        $('#qtyNeed').val(qtyNeed);
        $('#stockItem').val(stockItem);
        $('#uom').val(uom);
        $('#note').val(note);

        $('#qtyNeed').autoNumeric('update', {mDec: 0, vMax: stockItem});
        $('#cost').autoNumeric('update', {mDec: 0});
        $('#stockItem').autoNumeric('update', {mDec: 0});

        $('#title-modal-line-detail').html('{{ trans('shared/common.edit') }}');
        $('#submit-modal-line').html('{{ trans('shared/common.edit') }}');

        $('#modal-form-line').modal("show");
    };

    var deleteLine = function() {
        $(this).parent().parent().remove();
    };

</script>
@endsection
