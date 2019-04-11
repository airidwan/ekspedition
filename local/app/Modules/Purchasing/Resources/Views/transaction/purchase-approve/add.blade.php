@extends('layouts.master')

@section('title', trans('purchasing/menu.purchase-approve'))

@section('content')
<?php
$inprocess = App\Modules\Purchasing\Model\Transaction\PurchaseOrderHeader::INPROCESS;
?>
<div class="row">
    <div class="col-md-12">
        <div class="widget">
            <div class="widget-header">
                <h2><i class="fa fa-truck"></i> <strong>{{ $title }}</strong> {{ trans('purchasing/menu.purchase-approve') }}</h2>
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
                                <a href="#tabApprove" data-toggle="tab">{{ trans('shared/common.approve') }} <span class="label label-success"></span></a>
                            </li>
                            <li class="">
                                <a href="#tabHeaders" data-toggle="tab">{{ trans('shared/common.headers') }} <span class="label label-primary"></span></a>
                            </li>
                            <li class="">
                                <a href="#tabLines" data-toggle="tab">{{ trans('shared/common.lines') }} <span class="badge badge-primary"></span></a>
                            </li>
                        </ul>
                        <div class="tab-content">
                            <div class="tab-pane fade active in" id="tabApprove">
                                <div class="col-sm-6 portlets">
                                    <div class="form-group {{ $errors->has('note') ? 'has-error' : '' }}">
                                        <label for="poNumber" class="col-sm-4 control-label">Note about Approve or Reject <span class="required">*</span></label>
                                        <div class="col-sm-8">
                                            <textarea type="text" class="form-control" id="note" name="note" rows="4">{{ count($errors) > 0 ? old('note') : '' }}</textarea>
                                            @if($errors->has('note'))
                                            <span class="help-block">{{ $errors->first('note') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="tabHeaders">
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
                                                <select class="form-control" id="supplier" name="supplier" disabled>
                                                    <?php $supplierId = count($errors) > 0 ? old('supplier') : $model->supplier_id ?>
                                                    @foreach($optionsSupplier as $supplier)
                                                    <option value="{{ $supplier->vendor_id }}" {{ $supplier->vendor_id == $supplierId ? 'selected' : '' }}>
                                                        {{ $supplier->vendor_name }}
                                                    </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            @if($errors->has('supplier'))
                                            <span class="help-block">{{ $errors->first('supplier') }}</span>
                                            @endif
                                        </div>
                                        <div class="form-group {{ $errors->has('type') ? 'has-error' : '' }}">
                                            <label for="type" class="col-sm-4 control-label">{{ trans('purchasing/fields.type') }} <span class="required">*</span></label>
                                            <div class="col-sm-8">
                                                <select class="form-control" id="type" name="type" disabled>
                                                    <?php $typeId = count($errors) > 0 ? old('type') : $model->type_id ?>
                                                    @foreach($optionsType as $type)
                                                    <option value="{{ $type->type_id }}" {{ $type->type_id == $typeId ? 'selected' : '' }}>{{ $type->type_name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            @if($errors->has('type'))
                                            <span class="help-block">{{ $errors->first('type') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="col-sm-12 portlets">
                                        <div class="form-group {{ $errors->has('description') ? 'has-error' : '' }}">
                                            <label for="description" class="col-sm-2 control-label">{{ trans('shared/common.deskripsi') }}</label>
                                            <div class="col-sm-10">
                                                <input type="text" class="form-control" id="description" name="description" value="{{ count($errors) > 0 ? old('description') : $model->description }}" disabled/>
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
                                    <div class="table-responsive">
                                        <table class="table table-striped table-bordered" id="table-line" cellspacing="0" width="100%">
                                            <thead>
                                                <tr>
                                                    <th>{{ trans('purchasing/fields.item') }}</th>
                                                    <th>{{ trans('purchasing/fields.item-description') }}</th>
                                                    <th>{{ trans('purchasing/fields.wh') }}</th>
                                                    <th>{{ trans('purchasing/fields.stock') }}</th>
                                                    <th>{{ trans('purchasing/fields.qty') }}</th>
                                                    <th>{{ trans('purchasing/fields.uom') }}</th>
                                                    <th>{{ trans('purchasing/fields.item-category') }}</th>
                                                    <th>{{ trans('purchasing/fields.unit-price') }}</th>
                                                    <th>{{ trans('purchasing/fields.amount') }}</th>
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
                                                    <td class="text-right"> {{ old('stock')[$i] }} </td>
                                                    <td class="text-right"> {{ old('qty')[$i] }} </td>
                                                    <td > {{ old('uom')[$i] }} </td>
                                                    <td > {{ old('itemCategory')[$i] }} </td>
                                                    <td class="text-right"> {{ old('unitPrice')[$i] }} </td>
                                                    <td class="text-right"> {{ old('amount')[$i] }} </td>
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
                                                ?>
                                                <tr data-index="{{ $dataIndex }}">
                                                    <td > {{ $item !== null ? $item->item_code : '' }} </td>
                                                    <td > {{ $item !== null ? $item->description : '' }} </td>
                                                    <td > {{ $warehouse !== null ? $warehouse->wh_code : '' }} </td>
                                                    <td class="text-right"> {{ number_format($line->stock) }} </td>
                                                    <td class="text-right"> {{ number_format($line->quantity_need) }} </td>
                                                    <td > {{ $uom !== null ? $uom->uom_code : '' }} </td>
                                                    <td > {{ $category !== null ? $category->description : '' }} </td>
                                                    <td class="text-right"> {{ number_format($line->unit_price) }} </td>
                                                    <td class="text-right"> {{ number_format($line->total_price) }} </td>
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
                                @if(Gate::check('access', [$resource, 'reject']) && $model->status == $inprocess)
                                <button type="submit" name="btn-reject" class="btn btn-sm btn-danger">
                                    <i class="fa fa-remove"></i> {{ trans('purchasing/fields.reject') }}
                                </button>
                                @endif
                                @if(Gate::check('access', [$resource, 'approve']) && $model->status == $inprocess)
                                <button type="submit" name="btn-approve" class="btn btn-sm btn-info">
                                    <i class="fa fa-save"></i> {{ trans('purchasing/fields.approve') }}
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

@section('script')
@parent()
<script type="text/javascript">

</script>
@endsection
