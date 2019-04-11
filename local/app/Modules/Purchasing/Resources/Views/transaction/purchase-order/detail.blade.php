@extends('layouts.master')

@section('title', trans('purchasing/menu.purchase-order'))

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
                                        <?php $branch = $model->branch()->first() !== null ? $model->branch()->first() : Session::get('currentBranch'); ?>
                                        <div class="form-group">
                                            <label for="branch" class="col-sm-4 control-label">{{ trans('shared/common.cabang') }}</label>
                                            <div class="col-sm-8">
                                                <input type="text" class="form-control" id="branch" name="branch"  value="{{ $branch->branch_name }}" disabled>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 portlets">
                                        <?php $supplierId = $model->supplier_id ?>
                                        <div class="form-group {{ $errors->has('supplier') ? 'has-error' : '' }}">
                                            <label for="supplier" class="col-sm-4 control-label">{{ trans('purchasing/fields.supplier') }}</label>
                                            <div class="col-sm-8">
                                                <select class="form-control" id="supplier" name="supplier" disabled>
                                                    <option value="">{{ trans('shared/common.please-select') }}</option>
                                                    @foreach($optionsSupplier as $supplier)
                                                        <option value="{{ $supplier->vendor_id }}" {{ $supplier->vendor_id == $supplierId ? 'selected' : '' }}>
                                                            {{ $supplier->vendor_name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <?php $typeId = $model->type_id ?>
                                        <div class="form-group {{ $errors->has('type') ? 'has-error' : '' }}">
                                            <label for="type" class="col-sm-4 control-label">{{ trans('purchasing/fields.type') }}</label>
                                            <div class="col-sm-8">
                                                <select class="form-control" id="type" name="type" disabled>
                                                    <option value="">{{ trans('shared/common.please-select') }}</option>
                                                    @foreach($optionsType as $type)
                                                        <option value="{{ $type->type_id }}" {{ $type->type_id == $typeId ? 'selected' : '' }}>{{ $type->type_name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-12 portlets">
                                        <div class="form-group {{ $errors->has('description') ? 'has-error' : '' }}">
                                            <label for="description" class="col-sm-2 control-label">{{ trans('shared/common.deskripsi') }}</label>
                                            <div class="col-sm-10">
                                                <input type="text" class="form-control" id="description" name="description" value="{{ $model->description }}" disabled/>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-4 portlets">
                                    <div class="form-group">
                                        <label for="status" class="col-sm-4 control-label">{{ trans('shared/common.status') }}</label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="status" name="status"  value="{{ $model->status }}" disabled>
                                        </div>
                                    </div>
                                    <?php $poDate = new \DateTime($model->po_date); ?>
                                    <div class="form-group">
                                        <label for="poDate" class="col-sm-4 control-label">{{ trans('shared/common.tanggal') }}</label>
                                        <div class="col-sm-8">
                                            <input type="text" id="poDate" name="poDate" class="form-control datepicker-input" data-mask="99-99-9999" placeholder="dd-mm-yyyy" value="{{ $poDate->format('d-m-Y') }}" disabled>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="totalPrice" class="col-sm-4 control-label">{{ trans('shared/common.total') }}</label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control currency text-right" id="totalPrice" name="totalPrice" value="{{ $model->total }}" disabled>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="tabLines">
                                <div class="col-sm-12 portlets">
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
                                                    <th>{{ trans('purchasing/fields.unit-price') }}</th>
                                                    <th>{{ trans('purchasing/fields.amount') }}</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($model->purchaseOrderLines()->where('active', "=", 'Y')->get() as $line)
                                                <?php
                                                    $item = App\Modules\Inventory\Model\Master\MasterItem::find($line->item_id);
                                                    $warehouse = App\Modules\Inventory\Model\Master\MasterWarehouse::find($line->wh_id);
                                                    $uom = App\Modules\Inventory\Model\Master\MasterUom::find($item->uom_id);
                                                    $category = App\Modules\Inventory\Model\Master\MasterCategory::find($item->category_id);
                                                    $service  = App\Modules\Asset\Model\Transaction\ServiceAsset::find($line->service_asset_id);
                                                    $manifest = App\Modules\Operational\Model\Transaction\ManifestHeader::find($line->manifest_header_id);

                                                ?>
                                                <tr>
                                                    <td > {{ $item !== null ? $item->item_code : '' }} </td>
                                                    <td > {{ $item !== null ? $item->description : '' }} </td>
                                                    <td > {{ $warehouse !== null ? $warehouse->wh_code : '' }} </td>
                                                    <td class="text-right"> {{ number_format($line->quantity_need) }} </td>
                                                    <td > {{ $uom !== null ? $uom->uom_code : '' }} </td>
                                                    <td > {{ $category !== null ? $category->description : '' }} </td>
                                                    <td > {{ $line->type }} </td>
                                                    <td > {{ $service !== null ? $service->service_number : '' }} </td>
                                                    <td > {{ $manifest !== null ? $manifest->manifest_number : '' }} </td>
                                                    <td class="text-right"> {{ number_format($line->unit_price) }} </td>
                                                    <td class="text-right"> {{ number_format($line->total_price) }} </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-12 data-table-toolbar text-right">
                            <div class="form-group">
                                <a href="{{ URL($url) }}" class="btn btn-sm btn-warning"><i class="fa fa-reply"></i> {{ trans('shared/common.cancel') }}</a>
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
