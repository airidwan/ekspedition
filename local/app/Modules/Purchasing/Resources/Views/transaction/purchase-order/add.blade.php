@extends('layouts.master')

@section('title', trans('purchasing/menu.purchase-order'))

@section('header')
@parent
<style type="text/css">
    #table-lov-manifest tbody tr{
        cursor: pointer;
    }
</style>
@endsection

@section('content')
<?php
use App\Modules\Purchasing\Model\Transaction\PurchaseOrderHeader;
use App\Modules\Purchasing\Model\Master\MasterTypePo;
$incomplete = PurchaseOrderHeader::INCOMPLETE;
?>
<div class="row">
    <div class="col-md-12">
        <div class="widget">
            <div class="widget-header">
                <h2><i class="fa fa-truck"></i> <strong>{{ $title }}</strong> {{ trans('purchasing/menu.purchase-order') }}</h2>
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
                        <input type="hidden" name="id" value="{{ $model->header_id }}">
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
                                <div class="col-sm-8 portlets">
                                    <div class="col-sm-6 portlets">
                                        <div class="form-group">
                                            <label for="poNumber" class="col-sm-4 control-label">{{ trans('purchasing/fields.po-number') }}</label>
                                            <div class="col-sm-8">
                                                <input type="text" class="form-control" id="poNumber" name="poNumber"  value="{{ !empty($model->po_number) ? $model->po_number : '' }}" disabled>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label for="branch" class="col-sm-4 control-label">{{ trans('shared/common.cabang') }}</label>
                                            <div class="col-sm-8">
                                                <?php $branch = $model->branch()->first() !== null ? $model->branch()->first() : Session::get('currentBranch'); ?>
                                                <input type="text" class="form-control" id="branch" name="branch"  value="{{ $branch->branch_name }}" disabled>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 portlets">
                                        <div class="form-group {{ $errors->has('supplier') ? 'has-error' : '' }}">
                                            <label for="supplier" class="col-sm-4 control-label">{{ trans('purchasing/fields.supplier') }} <span class="required">*</span></label>
                                            <div class="col-sm-8">
                                                <select class="form-control" id="supplier" name="supplier" {{ $model->status !== $incomplete ? 'disabled' : '' }}>
                                                    <?php $supplierId = count($errors) > 0 ? old('supplier') : $model->supplier_id ?>
                                                    <option value="">{{ trans('shared/common.please-select') }}</option>
                                                    @foreach($optionsSupplier as $supplier)
                                                    <option value="{{ $supplier->vendor_id }}" {{ $supplier->vendor_id == $supplierId ? 'selected' : '' }}>
                                                        {{ $supplier->vendor_name }}
                                                    </option>
                                                    @endforeach
                                                </select>
                                            @if($errors->has('supplier'))
                                            <span class="help-block">{{ $errors->first('supplier') }}</span>
                                            @endif
                                            </div>
                                        </div>
                                        <div class="form-group {{ $errors->has('type') ? 'has-error' : '' }}">
                                            <label for="type" class="col-sm-4 control-label">{{ trans('purchasing/fields.type') }} <span class="required">*</span></label>
                                            <div class="col-sm-8">
                                                <select class="form-control" id="type" name="type" {{ $model->status !== $incomplete ? 'disabled' : '' }}>
                                                    <?php $typeId = count($errors) > 0 ? old('type') : $model->type_id ?>
                                                    <option value="">{{ trans('shared/common.please-select') }}</option>
                                                    @foreach($optionsType as $type)
                                                    <option value="{{ $type->type_id }}" {{ $type->type_id == $typeId ? 'selected' : '' }}>{{ $type->type_name }}</option>
                                                    @endforeach
                                                </select>
                                            @if($errors->has('type'))
                                            <span class="help-block">{{ $errors->first('type') }}</span>
                                            @endif
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-12 portlets">
                                        <div class="form-group {{ $errors->has('description') ? 'has-error' : '' }}">
                                            <label for="description" class="col-sm-2 control-label">{{ trans('shared/common.deskripsi') }} <span class="required">*</span></label>
                                            <div class="col-sm-10">
                                                <input type="text" class="form-control" id="description" name="description" value="{{ count($errors) > 0 ? old('description') : $model->description }}" {{ $model->status !== $incomplete ? 'disabled' : '' }}/>
                                            </div>
                                            @if($errors->has('description'))
                                            <span class="help-block">{{ $errors->first('description') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-4 portlets">
                                    <div class="form-group">
                                        <label for="status" class="col-sm-4 control-label">{{ trans('shared/common.status') }}</label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="status" name="status"  value="{{ $model->status }}" disabled="disabled">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="poDate" class="col-sm-4 control-label">{{ trans('shared/common.tanggal') }}</label>
                                        <div class="col-sm-8">
                                            <div class="input-group">
                                                <?php $poDate = new \DateTime($model->po_date); ?>
                                                <input type="text" id="poDate" name="poDate" class="form-control datepicker-input" data-mask="99-99-9999" placeholder="dd-mm-yyyy" value="{{ $poDate->format('d-m-Y') }}" disabled>
                                                <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                            </div>
                                        </div>
                                        @if($errors->has('poDate'))
                                        <span class="help-block">{{ $errors->first('poDate') }}</span>
                                        @endif
                                    </div>
                                    <div class="form-group">
                                        <label for="totalPrice" class="col-sm-4 control-label">{{ trans('shared/common.total') }}</label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control currency text-right" id="totalPrice" name="totalPrice" value="{{ $model->total }}" disabled="disabled">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="tabLines">
                                <div class="col-sm-12 portlets">
                                    <div class="data-table-toolbar">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="toolbar-btn-action">
                                                    @if($model->status == $incomplete)
                                                    <a class="btn btn-sm btn-primary add-line">
                                                        <i class="fa fa-plus-circle"></i> {{ trans('shared/common.add-new') }}
                                                    </a>
                                                    <a id="clear-lines" href="#" class="btn btn-sm btn-danger">
                                                        <i class="fa fa-remove"></i> {{ trans('shared/common.clear') }}
                                                    </a>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="table-responsive">
                                        <table class="table table-striped table-bordered" id="table-line" cellspacing="0" width="100%">
                                            <thead>
                                                <tr>
                                                    <th>{{ trans('purchasing/fields.item') }}</th>
                                                    <th>{{ trans('purchasing/fields.item-description') }}</th>
                                                    <th>{{ trans('purchasing/fields.wh') }}</th>
                                                    <th>{{ trans('purchasing/fields.qty') }}</th>
                                                    <th>{{ trans('purchasing/fields.uom') }}</th>
                                                    <th>{{ trans('purchasing/fields.item-category') }}</th>
                                                    <th>{{ trans('shared/common.type') }}</th>
                                                    <th>{{ trans('asset/fields.service-number') }}</th>
                                                    <th>{{ trans('operational/fields.manifest-number') }}</th>
                                                    <th>{{ trans('operational/fields.do-number') }}</th>
                                                    <th>{{ trans('operational/fields.pickup-number') }}</th>
                                                    <th>{{ trans('purchasing/fields.unit-price') }}</th>
                                                    <th>{{ trans('purchasing/fields.amount') }}</th>
                                                    @if($model->status == $incomplete)
                                                    <th style="min-width:60px;">{{ trans('shared/common.action') }}</th>
                                                    @endif
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php $dataIndex = 0; ?>
                                                @if(count($errors) > 0)
                                                @for($i = 0; $i < count(old('lineId', [])); $i++)
                                                <tr data-index="{{ $dataIndex }}">
                                                    <td > {{ old('itemCode')[$i] }} </td>
                                                    <td > {{ old('itemDescription')[$i] }} </td>
                                                    <td > {{ old('warehouseCode')[$i] }} </td>
                                                    <td class="text-right"> {{ old('qty')[$i] }} </td>
                                                    <td > {{ old('uom')[$i] }} </td>
                                                    <td > {{ old('itemCategory')[$i] }} </td>
                                                    <td > {{ old('lineType')[$i] }} </td>
                                                    <td > {{ old('serviceNumber')[$i] }} </td>
                                                    <td > {{ old('manifestNumber')[$i] }} </td>
                                                    <td > {{ old('deliveryOrderNumber')[$i] }} </td>
                                                    <td > {{ old('pickupFormNumber')[$i] }} </td>
                                                    <td class="text-right"> {{ old('unitPrice')[$i] }} </td>
                                                    <td class="text-right"> {{ old('amount')[$i] }} </td>
                                                    @if($model->status == $incomplete)
                                                    <td class="text-center">
                                                        <a data-toggle="tooltip" class="btn btn-xs btn-warning edit-line" ><i class="fa fa-pencil"></i></a>
                                                        <a data-toggle="tooltip" class="btn btn-danger btn-xs delete-line" ><i class="fa fa-remove"></i></a>
                                                        <input type="hidden" name="lineId[]" value="{{ old('lineId')[$i] }}">
                                                        <input type="hidden" name="itemId[]" value="{{ old('itemId')[$i] }}">
                                                        <input type="hidden" name="itemCode[]" value="{{ old('itemCode')[$i] }}">
                                                        <input type="hidden" name="itemDescription[]" value="{{ old('itemDescription')[$i] }}">
                                                        <input type="hidden" name="warehouse[]" value="{{ old('warehouse')[$i] }}">
                                                        <input type="hidden" name="warehouseCode[]" value="{{ old('warehouseCode')[$i] }}">
                                                        <input type="hidden" name="qty[]" value="{{ old('qty')[$i] }}">
                                                        <input type="hidden" name="uom[]" value="{{ old('uom')[$i] }}">
                                                        <input type="hidden" name="itemCategory[]" value="{{ old('itemCategory')[$i] }}">
                                                        <input type="hidden" name="lineType[]" value="{{ old('lineType')[$i] }}">
                                                        <input type="hidden" name="serviceNumber[]" value="{{ old('serviceNumber')[$i] }}">
                                                        <input type="hidden" name="serviceId[]" value="{{ old('serviceId')[$i] }}">
                                                        <input type="hidden" name="manifestId[]" value="{{ old('manifestId')[$i] }}">
                                                        <input type="hidden" name="manifestNumber[]" value="{{ old('manifestNumber')[$i] }}">
                                                        <input type="hidden" name="deliveryOrderId[]" value="{{ old('deliveryOrderId')[$i] }}">
                                                        <input type="hidden" name="deliveryOrderNumber[]" value="{{ old('deliveryOrderNumber')[$i] }}">
                                                        <input type="hidden" name="pickupFormId[]" value="{{ old('pickupFormId')[$i] }}">
                                                        <input type="hidden" name="pickupFormNumber[]" value="{{ old('pickupFormNumber')[$i] }}">
                                                        <input type="hidden" name="unitPrice[]" value="{{ old('unitPrice')[$i] }}">
                                                        <input type="hidden" name="amount[]" value="{{ old('amount')[$i] }}">
                                                    </td>
                                                    @endif
                                                </tr>
                                                <?php $dataIndex++; ?>

                                                @endfor
                                                @else
                                                @foreach($model->purchaseOrderLines()->where('active', "=", 'Y')->get() as $line)
                                                <?php
                                                    $item = App\Modules\Inventory\Model\Master\MasterItem::find($line->item_id);
                                                    $warehouse = App\Modules\Inventory\Model\Master\MasterWarehouse::find($line->wh_id);
                                                    $uom = App\Modules\Inventory\Model\Master\MasterUom::find($item->uom_id);
                                                    $category = App\Modules\Inventory\Model\Master\MasterCategory::find($item->category_id);
                                                    $service  = App\Modules\Asset\Model\Transaction\ServiceAsset::find($line->service_asset_id);
                                                    $manifest = App\Modules\Operational\Model\Transaction\ManifestHeader::find($line->manifest_header_id);
                                                    $do = App\Modules\Operational\Model\Transaction\DeliveryOrderHeader::find($line->delivery_order_header_id);
                                                    $pickup = App\Modules\Operational\Model\Transaction\PickupFormHeader::find($line->pickup_form_header_id);

                                                ?>
                                                <tr data-index="{{ $dataIndex }}">
                                                    <td > {{ $item !== null ? $item->item_code : '' }} </td>
                                                    <td > {{ $item !== null ? $item->description : '' }} </td>
                                                    <td > {{ $warehouse !== null ? $warehouse->wh_code : '' }} </td>
                                                    <td class="text-right"> {{ number_format($line->quantity_need) }} </td>
                                                    <td > {{ $uom !== null ? $uom->uom_code : '' }} </td>
                                                    <td > {{ $category !== null ? $category->description : '' }} </td>
                                                    <td > {{ $line->type }} </td>
                                                    <td > {{ $service !== null ? $service->service_number : '' }} </td>
                                                    <td > {{ $manifest !== null ? $manifest->manifest_number : '' }} </td>
                                                    <td > {{ $do !== null ? $do->delivery_order_number : '' }} </td>
                                                    <td > {{ $pickup !== null ? $pickup->pickup_form_number : '' }} </td>
                                                    <td class="text-right"> {{ number_format($line->unit_price) }} </td>
                                                    <td class="text-right"> {{ number_format($line->total_price) }} </td>
                                                    @if($model->status == $incomplete)
                                                    <td class="text-center">
                                                        <a data-toggle="tooltip" class="btn btn-xs btn-warning edit-line" ><i class="fa fa-pencil"></i></a>
                                                        <a data-toggle="tooltip" class="btn btn-danger btn-xs delete-line" ><i class="fa fa-remove"></i></a>
                                                        <input type="hidden" name="lineId[]" value="{{ $line->line_id }}">
                                                        <input type="hidden" name="itemId[]" value="{{ $item !== null ? $item->item_id : '' }}">
                                                        <input type="hidden" name="itemCode[]" value="{{ $item !== null ? $item->item_code : '' }}">
                                                        <input type="hidden" name="itemDescription[]" value="{{ $item !== null ? $item->description : '' }}">
                                                        <input type="hidden" name="warehouse[]" value="{{ $warehouse !== null ? $warehouse->wh_id : '' }}">
                                                        <input type="hidden" name="warehouseCode[]" value="{{ $warehouse !== null ? $warehouse->wh_code : '' }}">
                                                        <input type="hidden" name="qty[]" value="{{ number_format($line->quantity_need) }}">
                                                        <input type="hidden" name="uom[]" value="{{ $uom !== null ? $uom->uom_code : '' }}">
                                                        <input type="hidden" name="itemCategory[]" value="{{ $category !== null ? $category->description : '' }}">
                                                        <input type="hidden" name="lineType[]" value="{{ $line->type }}">
                                                        <input type="hidden" name="serviceId[]" value="{{ $line->service_asset_id }}">
                                                        <input type="hidden" name="serviceNumber[]" value="{{ $service !== null ? $service->service_number : '' }}">
                                                        <input type="hidden" name="manifestId[]" value="{{ $line->manifest_header_id }}">
                                                        <input type="hidden" name="manifestNumber[]" value="{{ $manifest !== null ? $manifest->manifest_number : '' }}">
                                                        <input type="hidden" name="deliveryOrderId[]" value="{{ $line->delivery_order_header_id }}">
                                                        <input type="hidden" name="deliveryOrderNumber[]" value="{{ $do !== null ? $do->delivery_order_number : '' }}">
                                                        <input type="hidden" name="pickupFormId[]" value="{{ $line->pickup_form_header_id }}">
                                                        <input type="hidden" name="pickupFormNumber[]" value="{{ $pickup !== null ? $pickup->pickup_form_number : '' }}">
                                                        <input type="hidden" name="unitPrice[]" value="{{ number_format($line->unit_price) }}">
                                                        <input type="hidden" name="amount[]" value="{{ number_format($line->total_price) }}">
                                                    </td>
                                                    @endif
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
                                
                                @if($model->status == $incomplete)
                                <button type="submit" name="btn-save" class="btn btn-sm btn-primary"><i class="fa fa-save"></i> {{ trans('shared/common.save') }}</button>
                                @endif
                                
                                @if(Gate::check('access', [$resource, 'approveAdmin']) && $model->status == $incomplete)
                                <button type="submit" name="btn-approve-admin" class="btn btn-sm btn-info">
                                    <i class="fa fa-save"></i> {{ trans('purchasing/fields.approve-admin') }}
                                </button>
                                @endif

                                @if(Gate::check('access', [$resource, 'approveKacab']) && $model->status == $incomplete)
                                <button type="submit" name="btn-approve-kacab" class="btn btn-sm btn-info">
                                    <i class="fa fa-save"></i> {{ trans('purchasing/fields.approve-kacab') }}
                                </button>
                                @endif

                                @if($model->status == PurchaseOrderHeader::CLOSED || $model->status == PurchaseOrderHeader::APPROVED)
                                <a href="{{ URL($url.'/print-pdf-detail/'.$model->header_id) }}" class="button btn btn-sm btn-success" target="_blank">
                                    <i class="fa fa-print"></i> {{ trans('shared/common.print') }}
                                </a>
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
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title text-center"><span id="title-modal-line-detail">{{ trans('shared/common.add') }}</span> {{ trans('shared/common.line') }}</h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-sm-12">
                        <div id="horizontal-form">
                            <form  role="form" id="add-form" class="form-horizontal" method="post" action="{{ url('operasional/master/master-truk-kendaraan/save') }}">
                                {{ csrf_field() }}
                                <div class="col-sm-6 portlets">
                                    <input type="hidden" name="dataIndexForm" id="dataIndexForm" value="">
                                    <input type="hidden" name="idDetail" id="idDetail" value="">
                                    <input type="hidden" name="lineId" id="lineId" value="">
                                    <div class="form-group {{ $errors->has('item') ? 'has-error' : '' }}">
                                        <label for="item" class="col-sm-4 control-label">{{ trans('purchasing/fields.item') }} <span class="required">*</span></label>
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
                                        <label for="itemDescription" class="col-sm-4 control-label">{{ trans('purchasing/fields.item-description') }} </label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="itemDescription" name="itemDescription" value="" disabled="disabled">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="itemCategory" class="col-sm-4 control-label">{{ trans('purchasing/fields.item-category') }} </label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="itemCategory" name="itemCategory" value="" disabled="disabled">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="uom" class="col-sm-4 control-label">{{ trans('purchasing/fields.uom') }} </label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="uom" name="uom" value="" disabled="disabled">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="warehouse" class="col-sm-4 control-label">{{ trans('purchasing/fields.wh') }} <span class="required">*</span></label>
                                        <div class="col-sm-8">
                                            <select class="form-control" name="warehouse" id="warehouse">
                                                @foreach($optionsWarehouse as $warehouse)
                                                <option value="{{ $warehouse->wh_id }}">{{ $warehouse->wh_code }}</option>
                                                @endforeach
                                            </select>
                                            <span class="help-block"></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-6 portlets">
                                    <div class="form-group">
                                        <label for="lineType" class="col-sm-4 control-label">{{ trans('shared/common.type') }} <span class="required">*</span></label>
                                        <div class="col-sm-8">
                                            <select class="form-control" name="lineType" id="lineType">
                                                @foreach($optionLineType as $lineType)
                                                <option value="{{ $lineType }}">{{ $lineType }}</option>
                                                @endforeach
                                            </select>
                                            <span class="help-block"></span>
                                        </div>
                                    </div>
                                    <div id="modalManifest" class="form-group {{ $errors->has('manifestId') ? 'has-error' : '' }}">
                                        <label for="manifest" class="col-sm-4 control-label">{{ trans('operational/fields.manifest-number') }} <span class="required">*</span></label>
                                        <div class="col-sm-8">
                                            <div class="input-group">
                                                <input type="hidden" name="manifestId" id="manifestId" value="">
                                                <input type="text" class="form-control" id="manifestNumber" name="manifestNumber" readonly>
                                                <span class="btn input-group-addon" id="show-lov-manifest" ><i class="fa fa-search"></i></span>
                                            </div>
                                            <span class="help-block"></span>
                                        </div>
                                    </div>
                                    <div id="modalDeliveryOrder" class="form-group {{ $errors->has('deliveryOrderId') ? 'has-error' : '' }}">
                                        <label for="manifest" class="col-sm-4 control-label">{{ trans('operational/fields.do-number') }} <span class="required">*</span></label>
                                        <div class="col-sm-8">
                                            <div class="input-group">
                                                <input type="hidden" name="deliveryOrderId" id="deliveryOrderId" value="">
                                                <input type="text" class="form-control" id="deliveryOrderNumber" name="deliveryOrderNumber" readonly>
                                                <span class="btn input-group-addon" id="show-lov-do" ><i class="fa fa-search"></i></span>
                                            </div>
                                            <span class="help-block"></span>
                                        </div>
                                    </div>
                                    <div id="modalPickupForm" class="form-group {{ $errors->has('pickupFormId') ? 'has-error' : '' }}">
                                        <label for="pickup" class="col-sm-4 control-label">{{ trans('operational/fields.pickup-number') }} <span class="required">*</span></label>
                                        <div class="col-sm-8">
                                            <div class="input-group">
                                                <input type="hidden" name="pickupFormId" id="pickupFormId" value="">
                                                <input type="text" class="form-control" id="pickupFormNumber" name="pickupFormNumber" readonly>
                                                <span class="btn input-group-addon" id="show-lov-pickup-form" ><i class="fa fa-search"></i></span>
                                            </div>
                                            <span class="help-block"></span>
                                        </div>
                                    </div>
                                    <div id="modalService" class="form-group {{ $errors->has('serviceId') ? 'has-error' : '' }}">
                                        <label for="service" class="col-sm-4 control-label">{{ trans('asset/fields.service-number') }} <span class="required">*</span></label>
                                        <div class="col-sm-8">
                                            <div class="input-group">
                                                <input type="hidden" name="serviceId" id="serviceId" value="">
                                                <input type="text" class="form-control" id="serviceNumber" name="serviceNumber" readonly>
                                                <span class="btn input-group-addon" data-toggle="modal" data-target="#modal-service"><i class="fa fa-search"></i></span>
                                            </div>
                                            <span class="help-block"></span>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="qty" class="col-sm-4 control-label">{{ trans('purchasing/fields.qty') }} <span class="required">*</span></label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control currency text-right" id="qty" name="qty" value="0">
                                            <span class="help-block"></span>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="unitPrice" class="col-sm-4 control-label">{{ trans('purchasing/fields.unit-price') }} <span class="required">*</span></label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control currency text-right" id="unitPrice" name="unitPrice" value="0">
                                            <span class="help-block"></span>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="amount" class="col-sm-4 control-label">{{ trans('purchasing/fields.amount') }} </label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control currency text-right" id="amount" name="amount" value="0" disabled="disabled">
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
                <h4 class="modal-title text-center">LOV Item</h4>
            </div>
            <div class="modal-body">
                <table id="datatable-item" class="table table-hover table-striped table-bordered" cellspacing="0" width="100%">
                    <thead>
                        <tr>
                            <th>{{ trans('purchasing/fields.item') }}</th>
                            <th>{{ trans('purchasing/fields.item-description') }}</th>
                            <th>{{ trans('purchasing/fields.item-category') }}</th>
                            <th>{{ trans('purchasing/fields.uom') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($optionsItem as $item)
                        <tr style="cursor: pointer;">
                            <td>{{ $item->item_code }}</td>
                            <td>{{ $item->description }}</td>
                            <td>{{ $item->category_description }}</td>
                            <td class="text-center">
                                {{ $item->uom_code }}
                                <input type="hidden" name="lovItemId[]" value="{{ $item->item_id }}">
                                <input type="hidden" name="lovItemCode[]" value="{{ $item->item_code }}">
                                <input type="hidden" name="lovItemDescription[]" value="{{ $item->description }}">
                                <input type="hidden" name="lovItemCategory[]" value="{{ $item->category_description }}">
                                <input type="hidden" name="lovItemUom[]" value="{{ $item->uom_code }}">
                            </td>
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
<div id="modal-service" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title text-center">Service Request List</h4>
            </div>
            <div class="modal-body">
                <table id="datatable-service" class="table table-hover table-striped table-bordered" cellspacing="0" width="100%">
                    <thead>
                        <tr>
                            <th>{{ trans('asset/fields.service-number') }}</th>
                            <th>{{ trans('shared/common.category') }}</th>
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
<div id="modal-lov-manifest" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title text-center">{{ trans('operational/fields.manifest') }}</h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-sm-12">
                        <div class="form-group search-lov">
                            <label for="searchManifest" class="col-sm-1 col-sm-offset-8 control-label">{{ trans('shared/common.search') }}</label>
                            <div class="col-sm-3">
                                <input type="text" class="form-control input-xs" id="searchManifest" name="searchManifest">
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-12">
                        <table id="table-lov-manifest" class="table table-hover table-striped table-bordered" cellspacing="0" width="100%">
                            <thead>
                                <tr>
                                    <th>{{ trans('operational/fields.manifest-number') }}</th>
                                    <th>{{ trans('operational/fields.driver') }}</th>
                                    <th>{{ trans('operational/fields.assistant') }}</th>
                                    <th>{{ trans('operational/fields.route') }}</th>
                                    <th>{{ trans('operational/fields.police-number') }}</th>
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
<div id="modal-lov-do" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title text-center">{{ trans('operational/fields.delivery-order') }}</h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-sm-12">
                        <div class="form-group search-lov">
                            <label for="searchDeliveryOrder" class="col-sm-1 col-sm-offset-8 control-label">{{ trans('shared/common.search') }}</label>
                            <div class="col-sm-3">
                                <input type="text" class="form-control input-xs" id="searchDeliveryOrder" name="searchDeliveryOrder">
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-12">
                        <table id="table-lov-do" class="table table-hover table-striped table-bordered" cellspacing="0" width="100%">
                            <thead>
                                <tr>
                                    <th>{{ trans('operational/fields.manifest-number') }}</th>
                                    <th>{{ trans('shared/common.status') }}</th>
                                    <th>{{ trans('operational/fields.driver') }}</th>
                                    <th>{{ trans('operational/fields.assistant') }}</th>
                                    <th>{{ trans('operational/fields.police-number') }}</th>
                                    <th>{{ trans('operational/fields.partner') }}</th>
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
<div id="modal-lov-pickup-form" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title text-center">{{ trans('operational/fields.pickup-form') }}</h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-sm-12">
                        <div class="form-group search-lov">
                            <label for="searchPickupForm" class="col-sm-1 col-sm-offset-8 control-label">{{ trans('shared/common.search') }}</label>
                            <div class="col-sm-3">
                                <input type="text" class="form-control input-xs" id="searchPickupForm" name="searchPickupForm">
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-12">
                        <table id="table-lov-pickup-form" class="table table-hover table-striped table-bordered" cellspacing="0" width="100%">
                            <thead>
                                <tr>
                                    <th>{{ trans('operational/fields.pickup-number') }}</th>
                                    <th>{{ trans('operational/fields.driver') }}</th>
                                    <th>{{ trans('operational/fields.police-number') }}</th>
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
    $("#datatable-item").dataTable({"pageLength" : 10, "lengthChange": false});
    $('.add-line').on('click', addLine);
    $('.edit-line').on('click', editLine);
    $('#datatable-item tbody').on('click', 'tr', selectItem);
    $('#qty').on('keyup', hitungHarga);
    $('#unitPrice').on('keyup', hitungHarga);
    $('#save-line').on('click', saveLine);
    $('.delete-line').on('click', deleteLine);
    $('#cancel-save-line').on('click', cancleSaveLine);
    $('#clear-lines').on('click', clearLines);

    $("#datatable-service").dataTable({"pageLength" : 10, "lengthChange": false});
    $('#datatable-service tbody').on('click', 'tr', selectService);

    $('#show-lov-manifest').on('click', showLovManifest);
    $('#searchManifest').on('keyup', loadLovManifest);
    $('#table-lov-manifest tbody').on('click', 'tr', selectManifest);

    $('#show-lov-do').on('click', showLovDeliveryOrder);
    $('#searchDeliveryOrder').on('keyup', loadLovDeliveryOrder);
    $('#table-lov-do tbody').on('click', 'tr', selectDeliveryOrder);

    $('#show-lov-pickup-form').on('click', showLovPickupForm);
    $('#searchPickupForm').on('keyup', loadLovPickupForm);
    $('#table-lov-pickup-form tbody').on('click', 'tr', selectPickupForm);

    $('#type').on('change', function(){
        clearFormLine();
        disableForm();
        clearLines();
    });

    $('#lineType').on('change', function(){
        $('#serviceId').val('');
        $('#serviceNumber').val('');
        disableForm();
    });
});

var selectService = function(){
    var service = $(this).data('service');

    $('#serviceId').val(service.service_asset_id);
    $('#serviceNumber').val(service.service_number);

    $('#modal-service').modal('hide');
};

var showLovManifest = function() {
        $('#searchManifest').val('');
        loadLovManifest(function() {
            $('#modal-lov-manifest').modal('show');
        });
    };

var xhrManifest;
var loadLovManifest = function(callback) {
    if(xhrManifest && xhrManifest.readyState != 4){
        xhrManifest.abort();
    }
    xhrManifest = $.ajax({
        url: '{{ URL($url.'/get-json-manifest') }}',
        data: {search: $('#searchManifest').val(), typePo : '{{ MasterTypePo::TRUCK_RENT_PER_TRIP }}' },
        success: function(data) {
            $('#table-lov-manifest tbody').html('');
                data.forEach(function(item) {
                    $('#table-lov-manifest tbody').append(
                        '<tr data-json=\'' + JSON.stringify(item) + '\'>\
                            <td>' + item.manifest_number + '</td>\
                            <td>' + item.driver_name + '</td>\
                            <td>' + item.assistant_name + '</td>\
                            <td>' + item.route_code + '</td>\
                            <td>' + item.police_number + '</td>\
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

var selectManifest = function() {
    var data = $(this).data('json');

    var error = false
    $('#table-line tbody tr').each(function (i, row) {
        if (data.manifest_header_id == $(row).find('[name="manifestId[]"]').val()) {
            $('#modal-alert').find('.alert-message').html('Manifest already exist');
            $('#modal-alert').modal('show');
            error = true;
        }
    });

    if (error) {
        return;
    }

    $('#manifestId').val(data.manifest_header_id);
    $('#manifestNumber').val(data.manifest_number);
    
    $('#modal-lov-manifest').modal('hide');
};

var showLovDeliveryOrder = function() {
        $('#searchDeliveryOrder').val('');
        loadLovDeliveryOrder(function() {
            $('#modal-lov-do').modal('show');
        });
    };

var xhrDeliveryOrder;
var loadLovDeliveryOrder = function(callback) {
    if(xhrDeliveryOrder && xhrDeliveryOrder.readyState != 4){
        xhrDeliveryOrder.abort();
    }
    xhrDeliveryOrder = $.ajax({
        url: '{{ URL($url.'/get-json-do') }}',
        data: {search: $('#searchDeliveryOrder').val(), typePo : '{{ MasterTypePo::TRUCK_RENT_PER_TRIP_DO }}' },
        success: function(data) {
            $('#table-lov-do tbody').html('');
                data.forEach(function(item) {
                    assistant_name = item.assistant_name ? item.assistant_name : ''; 
                    partner_name = item.partner_name ? item.partner_name : ''; 
                    $('#table-lov-do tbody').append(
                        '<tr data-json=\'' + JSON.stringify(item) + '\'>\
                            <td>' + item.delivery_order_number + '</td>\
                            <td>' + item.status + '</td>\
                            <td>' + item.driver_name + '</td>\
                            <td>' + assistant_name + '</td>\
                            <td>' + item.police_number + '</td>\
                            <td>' + partner_name + '</td>\
                            <td>' + item.note + '</td>\
                        </tr>'
                    );
                });


            if (typeof(callback) == 'function') {
                callback();
            }
        }
    });
};

var selectDeliveryOrder = function() {
    var data = $(this).data('json');

    var error = false
    $('#table-line tbody tr').each(function (i, row) {
        if (data.delivery_order_header_id == $(row).find('[name="deliveryOrderId[]"]').val()) {
            $('#modal-alert').find('.alert-message').html('Delivery Order already exist');
            $('#modal-alert').modal('show');
            error = true;
        }
    });

    if (error) {
        return;
    }

    $('#deliveryOrderId').val(data.delivery_order_header_id);
    $('#deliveryOrderNumber').val(data.delivery_order_number);
    
    $('#modal-lov-do').modal('hide');
};

var showLovPickupForm = function() {
        $('#searchPickupForm').val('');
        loadLovPickupForm(function() {
            $('#modal-lov-pickup-form').modal('show');
        });
    };

var xhrPickupForm;
var loadLovPickupForm = function(callback) {
    if(xhrPickupForm && xhrPickupForm.readyState != 4){
        xhrPickupForm.abort();
    }
    xhrPickupForm = $.ajax({
        url: '{{ URL($url.'/get-json-pickup-form') }}',
        data: {search: $('#searchPickupForm').val(), typePo : '{{ MasterTypePo::TRUCK_RENT_PER_TRIP_PICKUP }}' },
        success: function(data) {
            $('#table-lov-pickup-form tbody').html('');
                data.forEach(function(item) {
                    $('#table-lov-pickup-form tbody').append(
                        '<tr data-json=\'' + JSON.stringify(item) + '\'>\
                            <td>' + item.pickup_form_number + '</td>\
                            <td>' + item.driver_name + '</td>\
                            <td>' + item.police_number + '</td>\
                            <td>' + item.note + '</td>\
                        </tr>'
                    );
                });


            if (typeof(callback) == 'function') {
                callback();
            }
        }
    });
};

var selectPickupForm = function() {
    var data = $(this).data('json');

    var error = false
    $('#table-line tbody tr').each(function (i, row) {
        if (data.pickup_form_header_id == $(row).find('[name="pickupFormId[]"]').val()) {
            $('#modal-alert').find('.alert-message').html('Pickup Form already exist');
            $('#modal-alert').modal('show');
            error = true;
        }
    });

    if (error) {
        return;
    }

    $('#pickupFormId').val(data.pickup_form_header_id);
    $('#pickupFormNumber').val(data.pickup_form_number);
    
    $('#modal-lov-pickup-form').modal('hide');
};

var disableForm = function(){
    var lineType = $('#lineType').val();

    $('#modalService').addClass('hidden');

    if (lineType == '{{ PurchaseOrderHeader::SERVICE }}') {
        $('#modalService').removeClass('hidden');
    }
};

var disableFormManifest = function(){
    var type  = $('#type').val();

    $('#modalManifest').addClass('hidden');
    $('#lineType').removeAttr('disabled', 'disabled');

    if (type == '{{ MasterTypePo::TICKET }}' || type == '{{ MasterTypePo::TRUCK_RENT_PER_TRIP }}') {
        $('#lineType').val('{{ PurchaseOrderHeader::GOODS }}');
        $('#modalManifest').removeClass('hidden');
        $('#lineType').attr('disabled', 'disabled');
    }
};

var disableFormDeliveryOrder = function(){
    var type  = $('#type').val();

    $('#modalDeliveryOrder').addClass('hidden');
    $('#lineType').removeAttr('disabled', 'disabled');

    if (type ==  '{{ MasterTypePo::TRUCK_RENT_PER_TRIP_DO }}') {
        $('#lineType').val('{{ PurchaseOrderHeader::GOODS }}');
        $('#modalDeliveryOrder').removeClass('hidden');
        $('#lineType').attr('disabled', 'disabled');
    }
};

var disableFormPickupForm = function(){
    var type  = $('#type').val();

    $('#modalPickupForm').addClass('hidden');
    $('#lineType').removeAttr('disabled', 'disabled');

    if (type ==  '{{ MasterTypePo::TRUCK_RENT_PER_TRIP_PICKUP }}') {
        $('#lineType').val('{{ PurchaseOrderHeader::GOODS }}');
        $('#modalPickupForm').removeClass('hidden');
        $('#lineType').attr('disabled', 'disabled');
    }
};

var selectItem = function () {
    var lovItemId = $(this).find('input[name="lovItemId[]"]').val();
    var lovItemCode = $(this).find('input[name="lovItemCode[]"]').val();
    var lovItemDescription = $(this).find('input[name="lovItemDescription[]"]').val();
    var lovItemCategory = $(this).find('input[name="lovItemCategory[]"]').val();
    var lovItemUom = $(this).find('input[name="lovItemUom[]"]').val();
    var lovItemStock = $(this).find('input[name="lovItemStock[]"]').val();

    $('#itemId').val(lovItemId);
    $('#itemCode').val(lovItemCode);
    $('#itemDescription').val(lovItemDescription);
    $('#itemCategory').val(lovItemCategory);
    $('#uom').val(lovItemUom);
    $('#stock').val(lovItemStock);

    $('#modal-item').modal('hide');
};

var addLine = function() {
    clearFormLine();
    disableForm();
    $('#title-modal-line-detail').html('{{ trans('shared/common.add') }}');
    $('#submit-modal-line').html('{{ trans('shared/common.add') }}');

    $('#modal-form-line').modal("show");
};

var clearLines = function() {
    $('#table-line tbody').html('');
    calculateTotal();
};

var editLine = function() {
    var dataIndexForm = $(this).parent().parent().data('index');
    var lineId = $(this).parent().parent().find('[name="lineId[]"]').val();
    var itemId = $(this).parent().parent().find('[name="itemId[]"]').val();
    var itemCode = $(this).parent().parent().find('[name="itemCode[]"]').val();
    var itemDescription = $(this).parent().parent().find('[name="itemDescription[]"]').val();
    var itemCategory = $(this).parent().parent().find('[name="itemCategory[]"]').val();
    var uom = $(this).parent().parent().find('[name="uom[]"]').val();
    var qty = $(this).parent().parent().find('[name="qty[]"]').val();
    var unitPrice = $(this).parent().parent().find('[name="unitPrice[]"]').val();
    var lineType = $(this).parent().parent().find('[name="lineType[]"]').val();
    var serviceId = $(this).parent().parent().find('[name="serviceId[]"]').val();
    var serviceNumber = $(this).parent().parent().find('[name="serviceNumber[]"]').val();
    var manifestId = $(this).parent().parent().find('[name="manifestId[]"]').val();
    var manifestNumber = $(this).parent().parent().find('[name="manifestNumber[]"]').val();
    var deliveryOrderId = $(this).parent().parent().find('[name="deliveryOrderId[]"]').val();
    var deliveryOrderNumber = $(this).parent().parent().find('[name="deliveryOrderNumber[]"]').val();
    var pickupFormId = $(this).parent().parent().find('[name="pickupFormId[]"]').val();
    var pickupFormNumber = $(this).parent().parent().find('[name="pickupFormNumber[]"]').val();
    var amount = $(this).parent().parent().find('[name="amount[]"]').val();
    var warehouse = $(this).parent().parent().find('[name="warehouse[]"]').val();
    var warehouseCode = $(this).parent().parent().find('[name="warehouse[]"] option:selected').html();
    var stock = $(this).parent().parent().find('[name="stock[]"]').val();
    clearFormLine();
    $('#dataIndexForm').val(dataIndexForm);
    $('#lineId').val(lineId);
    $('#itemId').val(itemId);
    $('#itemCode').val(itemCode);
    $('#itemDescription').val(itemDescription);
    $('#itemCategory').val(itemCategory);
    $('#uom').val(uom);
    $('#qty').val(qty);
    $('#lineType').val(lineType);
    $('#serviceId').val(serviceId);
    $('#serviceNumber').val(serviceNumber);
    $('#manifestId').val(manifestId);
    $('#manifestNumber').val(manifestNumber);
    $('#deliveryOrderId').val(deliveryOrderId);
    $('#deliveryOrderNumber').val(deliveryOrderNumber);
    $('#pickupFormId').val(pickupFormId);
    $('#pickupFormNumber').val(pickupFormNumber);
    $('#unitPrice').val(unitPrice);
    $('#amount').val(amount);
    $('#warehouse').val(warehouse);
    $('#warehouseCode').val(warehouseCode);
    $('#stock').val(stock);

    disableForm();

    $('#title-modal-line-detail').html('{{ trans('shared/common.edit') }}');
    $('#submit-modal-line').html('{{ trans('shared/common.edit') }}');

    $('#modal-form-line').modal("show");
};

var deleteLine = function() {
    $(this).parent().parent().remove();
    calculateTotal();
};

var checkItemExist = function(itemRequestId, whRequestId) {
        var exist = false;
        $('#table-line tbody tr').each(function (i, row) {
            var itemId = $(row).find('[name="itemId[]"]').val();
            var whId   = $(row).find('[name="warehouse[]"]').val();
            if ($("#dataIndexForm").val() != '' && $("#dataIndexForm").val() == $(row).data('index')) {
                return;
            }

            if (itemId == itemRequestId && whId == whRequestId) {
                exist = true;
            }
        });
        return exist;
    };

var saveLine = function() {
    var type = $('#type').val();
    var dataIndexForm = $('#dataIndexForm').val();
    var lineId = $('#lineId').val();
    var itemId = $('#itemId').val();
    var itemCode = $('#itemCode').val();
    var itemDescription = $('#itemDescription').val();
    var itemCategory = $('#itemCategory').val();
    var uom = $('#uom').val();
    var qty = $('#qty').val();
    var lineType = $('#lineType').val();
    var serviceId = $('#serviceId').val();
    var serviceNumber = $('#serviceNumber').val();
    var manifestId = $('#manifestId').val();
    var manifestNumber = $('#manifestNumber').val();
    var deliveryOrderId = $('#deliveryOrderId').val();
    var deliveryOrderNumber = $('#deliveryOrderNumber').val();
    var pickupFormId = $('#pickupFormId').val();
    var pickupFormNumber = $('#pickupFormNumber').val();
    var unitPrice = $('#unitPrice').val();
    var amount = $('#amount').val();
    var warehouse = $('#warehouse').val();
    var warehouseCode = $('#warehouse option:selected').html();
    var stock = $('#stock').val();
    var error = false;

    if(checkItemExist(itemId, warehouse)){
        $('#modal-alert').find('.alert-message').html('Item and warehouse selected on exist!');
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

    if (qty == '' || qty <= 0) {
        $('#qty').parent().parent().addClass('has-error');
        $('#qty').parent().find('span.help-block').html('Quantity is required');
        error = true;
    } else {
        $('#qty').parent().parent().removeClass('has-error');
        $('#qty').parent().find('span.help-block').html('');
    }

    if (unitPrice == '' || unitPrice <= 0) {
        $('#unitPrice').parent().parent().addClass('has-error');
        $('#unitPrice').parent().find('span.help-block').html('Unit Price is required');
        error = true;
    } else {
        $('#unitPrice').parent().parent().removeClass('has-error');
        $('#unitPrice').parent().find('span.help-block').html('');
    }

    if (warehouse == '' || warehouse == null) {
        $('#warehouse').parent().parent().addClass('has-error');
        $('#warehouse').parent().find('span.help-block').html('Warehouse is required');
        error = true;
    } else {
        $('#warehouse').parent().parent().removeClass('has-error');
        $('#warehouse').parent().find('span.help-block').html('');
    }

    if ((type == '{{ MasterTypePo::TICKET }}'  || type == '{{ MasterTypePo::TRUCK_RENT_PER_TRIP }}') && (manifestId == '' || manifestId == null)) {
        $('#manifestId').parent().parent().addClass('has-error');
        $('#manifestId').parent().find('span.help-block').html('Manifest is required');
        error = true;
    } else {
        $('#manifestId').parent().parent().removeClass('has-error');
        $('#manifestId').parent().find('span.help-block').html('');
    }

    if (type == '{{ MasterTypePo::TRUCK_RENT_PER_TRIP_DO }}' && (deliveryOrderId == '' || deliveryOrderId == null)) {
        $('#deliveryOrderId').parent().parent().addClass('has-error');
        $('#deliveryOrderId').parent().find('span.help-block').html('Delivery Order is required');
        error = true;
    } else {
        $('#deliveryOrderId').parent().parent().removeClass('has-error');
        $('#deliveryOrderId').parent().find('span.help-block').html('');
    }

    if (type == '{{ MasterTypePo::TRUCK_RENT_PER_TRIP_PICKUP }}' && (pickupFormId == '' || pickupFormId == null)) {
        $('#pickupFormId').parent().parent().addClass('has-error');
        $('#pickupFormId').parent().find('span.help-block').html('Pickup Form is required');
        error = true;
    } else {
        $('#pickupFormId').parent().parent().removeClass('has-error');
        $('#pickupFormId').parent().find('span.help-block').html('');
    }

    if (lineType == '{{ PurchaseOrderHeader::SERVICE }}' && (serviceId == '' || serviceId == null)) {
        $('#serviceId').parent().parent().addClass('has-error');
        $('#serviceId').parent().find('span.help-block').html('Service request is required');
        error = true;
    } else {
        $('#serviceId').parent().parent().removeClass('has-error');
        $('#serviceId').parent().find('span.help-block').html('');
    }

    if (error) {
        return;
    }

    var htmlTr = '<td >' + itemCode + '</td>' +
        '<td >' + itemDescription + '</td>' +
        '<td >' + warehouseCode + '</td>' +
        '<td class="text-right">' + qty + '</td>' +
        '<td >' + uom + '</td>' +
        '<td >' + itemCategory + '</td>' +
        '<td >' + lineType + '</td>' +
        '<td >' + serviceNumber + '</td>' +
        '<td >' + manifestNumber + '</td>' +
        '<td >' + deliveryOrderNumber + '</td>' +
        '<td >' + pickupFormNumber + '</td>' +
        '<td class="text-right">' + unitPrice + '</td>' +
        '<td class="text-right">' + amount + '</td>' +
        '<td class="text-center">' +
        '<a data-toggle="tooltip" class="btn btn-xs btn-warning edit-line" ><i class="fa fa-pencil"></i></a> ' +
        '<a data-toggle="tooltip" class="btn btn-danger btn-xs delete-line" ><i class="fa fa-remove"></i></a>' +
        '<input type="hidden" name="lineId[]" value="'+ lineId + '">' +
        '<input type="hidden" name="itemId[]" value="' + itemId + '">' +
        '<input type="hidden" name="itemCode[]" value="' + itemCode + '">' +
        '<input type="hidden" name="itemDescription[]" value="' + itemDescription + '">' +
        '<input type="hidden" name="warehouse[]" value="' + warehouse + '">' +
        '<input type="hidden" name="warehouseCode[]" value="' + warehouseCode + '">' +
        '<input type="hidden" name="qty[]" value="' + qty + '">' +
        '<input type="hidden" name="uom[]" value="' + uom + '">' +
        '<input type="hidden" name="itemCategory[]" value="' + itemCategory + '">' +
        '<input type="hidden" name="lineType[]" value="' + lineType + '">' +
        '<input type="hidden" name="serviceId[]" value="' + serviceId + '">' +
        '<input type="hidden" name="serviceNumber[]" value="' + serviceNumber + '">' +
        '<input type="hidden" name="manifestId[]" value="' + manifestId + '">' +
        '<input type="hidden" name="manifestNumber[]" value="' + manifestNumber + '">' +
        '<input type="hidden" name="deliveryOrderId[]" value="' + deliveryOrderId + '">' +
        '<input type="hidden" name="deliveryOrderNumber[]" value="' + deliveryOrderNumber + '">' +
        '<input type="hidden" name="pickupFormId[]" value="' + pickupFormId + '">' +
        '<input type="hidden" name="pickupFormNumber[]" value="' + pickupFormNumber + '">' +
        '<input type="hidden" name="unitPrice[]" value="' + unitPrice + '">' +
        '<input type="hidden" name="amount[]" value="' + amount + '">' +
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
    calculateTotal();

    dataIndex++;
    $('#modal-form-line').modal("hide");
};

var cancleSaveLine = function() {
    $('#modal-form-line').modal("hide");
};

var clearFormLine = function() {
    $('#dataIndexForm').val('');
    $('#lineId').val('');
    $('#itemId').val('');
    $('#itemCode').val('');
    $('#itemDescription').val('');
    $('#itemCategory').val('');
    $('#lineType').val('');
    $('#serviceId').val('');
    $('#serviceNumber').val('');
    $('#manifestId').val('');
    $('#manifestNumber').val('');
    $('#deliveryOrderId').val('');
    $('#deliveryOrderNumber').val('');
    $('#pickupFormId').val('');
    $('#pickupFormNumber').val('');
    $('#stock').val(0);
    $('#uom').val('');
    $('#qty').val(0);
    $('#unitPrice').val(0);
    $('#amount').val(0);
    $('#warehouse').val('');

    disableFormManifest();
    disableFormDeliveryOrder();
    disableFormPickupForm();

    $('#itemCode').parent().parent().parent().removeClass('has-error');
    $('#itemCode').parent().parent().find('span.help-block').html('');
    $('#qty').parent().parent().removeClass('has-error');
    $('#qty').parent().find('span.help-block').html('');
    $('#serviceNumber').parent().parent().removeClass('has-error');
    $('#serviceNumber').parent().find('span.help-block').html('');
    $('#manifestNumber').parent().parent().removeClass('has-error');
    $('#manifestNumber').parent().find('span.help-block').html('');
    $('#deliveryOrderNumber').parent().parent().removeClass('has-error');
    $('#deliveryOrderNumber').parent().find('span.help-block').html('');
    $('#pickupFormNumber').parent().parent().removeClass('has-error');
    $('#pickupFormNumber').parent().find('span.help-block').html('');
    $('#warehouse').parent().parent().removeClass('has-error');
    $('#warehouse').parent().find('span.help-block').html('');
    $('#lineType').parent().parent().removeClass('has-error');
    $('#lineType').parent().find('span.help-block').html('');
    $('#unitPrice').parent().parent().removeClass('has-error');
    $('#unitPrice').parent().find('span.help-block').html('');
};

var hitungHarga = function() {
    var qty = $('#qty').val().split(',').join('');
    var unitPrice = $('#unitPrice').val().split(',').join('');
    $('#amount').val(qty * unitPrice);
    $('#amount').autoNumeric('update', {mDec: 0});
};

var calculateTotal = function() {
        var totalPrice = 0;

        $('#table-line tbody tr').each(function (i, row) {
            var amount = parseFloat($(row).find('[name="amount[]"]').val().split(',').join(''));
            totalPrice += amount;
        });

        $('#totalPrice').val(totalPrice);
        $('#totalPrice').autoNumeric('update', {mDec: 0});
    };
</script>
@endsection
