@extends('layouts.master')

@section('title', trans('asset/menu.asset-maintenance'))

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="widget">
            <div class="widget-header">
                <h2><i class="fa fa-folder"></i> {{ trans('asset/menu.asset-maintenance') }}</h2>
                <div class="additional-btn">
                    <a href="#" class="widget-maximize"><i class="icon-resize-full-1"></i></a>
                    <a href="#" class="widget-toggle"><i class="icon-down-open-2"></i></a>
                    <a href="#" class="widget-help"><i class="icon-help-2"></i></a>
                </div>
            </div>
            <div class="widget-content padding">
                <div id="horizontal-form">
                    <form  role="form" id="add-form" class="form-horizontal" method="post" action="">
                        {{ csrf_field() }}
                        <div class="col-sm-4 portlets">
                            <div class="form-group">
                                <label for="assetNumber" class="col-sm-4 control-label">{{ trans('asset/fields.asset-number') }}</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="assetNumber" name="assetNumber" value="{{ !empty($filters['assetNumber']) ? $filters['assetNumber'] : '' }}">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="assetName" class="col-sm-4 control-label">{{ trans('asset/fields.asset-name') }}</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="assetName" name="assetName" value="{{ !empty($filters['assetName']) ? $filters['assetName'] : '' }}">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="policeNumber" class="col-sm-4 control-label">{{ trans('operational/fields.police-number') }}</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="policeNumber" name="policeNumber" value="{{ !empty($filters['policeNumber']) ? $filters['policeNumber'] : '' }}">
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-4 portlets">
                            <div class="form-group">
                                <label for="employee" class="col-sm-4 control-label">{{ trans('asset/fields.employee') }}</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="employee" name="employee" value="{{ !empty($filters['employee']) ? $filters['employee'] : '' }}">
                                </div>
                            </div>
                            <div class="form-group {{ $errors->has('category') ? 'has-error' : '' }}">
                                <label for="category" class="col-sm-4 control-label">{{ trans('shared/common.category') }}</label>
                                <div class="col-sm-8">
                                    <select class="form-control" id="category" name="category">
                                        <?php $stringCategory = !empty($filters['category']) ? $filters['category'] : ''; ?>
                                        <option value="">ALL</option>
                                        @foreach($optionCategory as $category)
                                        <option value="{{ $category->asset_category_id }}" {{ $category->asset_category_id == $stringCategory ? 'selected' : '' }}>{{ $category->category_name  }}</option>
                                        @endforeach
                                    </select>
                                    @if($errors->has('category'))
                                    <span class="help-block">{{ $errors->first('category') }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="form-group {{ $errors->has('type') ? 'has-error' : '' }}">
                                <label for="type" class="col-sm-4 control-label">{{ trans('shared/common.type') }}</label>
                                <div class="col-sm-8">
                                    <select class="form-control" id="type" name="type">
                                        <?php $stringType = !empty($filters['type']) ? $filters['type'] : ''; ?>
                                        <option value="">ALL</option>
                                        @foreach($optionType as $type)
                                        <option value="{{ $type }}" {{ $type == $stringType ? 'selected' : '' }}>{{ $type }}</option>
                                        @endforeach
                                    </select>
                                    @if($errors->has('type'))
                                    <span class="help-block">{{ $errors->first('type') }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-4 portlets">
                            <div class="form-group {{ $errors->has('status') ? 'has-error' : '' }}">
                                <label for="status" class="col-sm-4 control-label">{{ trans('shared/common.status') }}</label>
                                <div class="col-sm-8">
                                    <select class="form-control" id="status" name="status">
                                        <?php $statusId = !empty($filters['status']) ? $filters['status'] : ''; ?>
                                        <option value="">ALL</option>
                                        @foreach($optionStatus as $status)
                                        <option value="{{ $status->asset_status_id }}" {{ $status->asset_status_id == $statusId ? 'selected' : '' }}>{{ $status->status  }}</option>
                                        @endforeach
                                    </select>
                                    @if($errors->has('status'))
                                    <span class="help-block">{{ $errors->first('status') }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="dateFrom" class="col-sm-4 control-label">{{ trans('shared/common.date-from') }}</label>
                                <div class="col-sm-8">
                                    <div class="input-group">
                                        <input type="text" id="dateFrom" name="dateFrom" class="form-control datepicker-input" data-mask="99-99-9999" placeholder="dd-mm-yyyy" value="{{ !empty($filters['dateFrom']) ? $filters['dateFrom'] : '' }}">
                                        <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="dateTo" class="col-sm-4 control-label">{{ trans('shared/common.date-to') }}</label>
                                <div class="col-sm-8">
                                    <div class="input-group">
                                        <input type="text" id="dateTo" name="dateTo" class="form-control datepicker-input" data-mask="99-99-9999" placeholder="dd-mm-yyyy" value="{{ !empty($filters['dateTo']) ? $filters['dateTo'] : '' }}">
                                        <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-12 data-table-toolbar text-right">
                            <div class="form-group">
                                <button type="submit" class="btn btn-sm btn-info">
                                    <i class="fa fa-search"></i> {{ trans('shared/common.filter') }}
                                </button>
                                <a href="{{ URL($url.'/print-pdf') }}" class="button btn btn-sm btn-warning" target="_blank">
                                    <i class="fa fa-file-pdf-o"></i> {{ trans('shared/common.print-pdf') }}
                                </a>
                                <a href="{{ URL($url.'/print-excel') }}" class="button btn btn-sm btn-success" target="_blank">
                                    <i class="fa fa-file-excel-o"></i> {{ trans('shared/common.print-excel') }}
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="clearfix"></div>
                <hr>
                <h4>{{ trans('purchasing/menu.purchase-order') }}</h4>
                <div class="table-responsive">
                    <form class='form-horizontal' role='form' id="table-line">
                        <table class="table table-striped table-bordered" cellspacing="0" width="100%">
                            <thead>
                                <tr>
                                    <th width="50px">{{ trans('shared/common.num') }}</th>
                                    <th>{{ trans('asset/fields.asset-number') }}<hr/>
                                        {{ trans('asset/fields.asset-name') }}</th>
                                    <th>{{ trans('operational/fields.truck-code') }}<hr/>
                                        {{ trans('operational/fields.police-number') }}</th>
                                    <th>{{ trans('asset/fields.service-number') }}<hr/>
                                        {{ trans('shared/common.description') }}</th>
                                    <th>{{ trans('asset/fields.finish-date') }}</th>
                                    <th>{{ trans('purchasing/fields.po-number') }}<hr/>
                                        {{ trans('purchasing/fields.po-date') }}</th>
                                    <th>{{ trans('inventory/fields.item-code') }}<hr/>
                                        {{ trans('inventory/fields.item-description') }}</th>
                                    <th>{{ trans('inventory/fields.wh') }}</th>
                                    <th>{{ trans('inventory/fields.qty-need') }}<hr/>
                                        {{ trans('inventory/fields.uom') }}</th>
                                    <th>{{ trans('inventory/fields.cost') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $no=1;
                                $totalPurchaseOrder = 0; 
                                ?>
                                @foreach($purchaseOrder as $model)
                                 <?php
                                     $totalPurchaseOrder += $model->total_price; 
                                     $finishDate = !empty($model->finish_date) ? new \DateTime($model->finish_date) : null;
                                     $poDate     = !empty($model->po_date) ? new \DateTime($model->po_date) : null;
                                 ?>
                                <tr>
                                    <td class="text-center">{{ $no++ }}</td>
                                    <td>{{ $model->asset_number }}<hr/>
                                        {{ $model->asset_name }}</td>
                                    <td>{{ $model->truck_code }}<hr/>
                                        {{ $model->police_number }}</td>
                                    <td>{{ $model->service_number }}<hr/>
                                        {{ $model->service_description }}</td>
                                    <td>{{ !empty($finishDate) ? $finishDate->format('d-M-Y') : '' }}</td>
                                    <td>{{ $model->po_number }}<hr/>
                                        {{ !empty($poDate) ? $poDate->format('d-M-Y') : '' }}</td>
                                    <td>{{ $model->item_code }}<hr/>
                                        {{ $model->item_name }}</td>
                                    <td>{{ $model->wh_code }}</td>
                                    <td class="text-right">{{ number_format($model->quantity_need) }}<hr/>
                                        {{ $model->uom }}</td>
                                    <td class="text-right">{{ number_format($model->total_price) }}</td>
                                </tr>
                                @endforeach
                                <tr>
                                    <td colspan="9" class="text-center"><strong>{{ trans('shared/common.total') }}</strong></td>
                                    <td ><strong>{{ number_format($totalPurchaseOrder) }}</strong></td>
                                </tr>
                            </tbody>
                        </table>
                    </form>
                </div>
                <div class="clearfix"></div>
                <h4>{{ trans('inventory/menu.move-order') }}</h4>
                <div class="table-responsive">
                    <form class='form-horizontal' role='form' id="table-line">
                        <table class="table table-striped table-bordered" cellspacing="0" width="100%">
                            <thead>
                                <tr>
                                    <th width="50px">{{ trans('shared/common.num') }}</th>
                                    <th>{{ trans('asset/fields.asset-number') }}<hr/>
                                        {{ trans('asset/fields.asset-name') }}</th>
                                    <th>{{ trans('operational/fields.truck-code') }}<hr/>
                                        {{ trans('operational/fields.police-number') }}</th>
                                    <th>{{ trans('asset/fields.service-number') }}<hr/>
                                        {{ trans('shared/common.description') }}</th>
                                    <th>{{ trans('asset/fields.finish-date') }}</th>
                                    <th>{{ trans('inventory/fields.mo-number') }}<hr/>
                                        {{ trans('inventory/fields.mo-date') }}</th>
                                    <th>{{ trans('operational/fields.driver-code') }}<hr/>
                                        {{ trans('operational/fields.driver-name') }}</th>
                                    <th>{{ trans('inventory/fields.item-code') }}<hr/>
                                        {{ trans('inventory/fields.item-description') }}</th>
                                    <th>{{ trans('inventory/fields.wh') }}</th>
                                    <th>{{ trans('inventory/fields.qty-need') }}<hr/>
                                        {{ trans('inventory/fields.uom') }}</th>
                                    <th>{{ trans('inventory/fields.cost') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                    $no=1; 
                                    $totalMoveOrder = 0; 
                                ?>
                                @foreach($moveOrder as $model)
                                 <?php
                                     $totalMoveOrder += $model->cost; 
                                     $finishDate = !empty($model->finish_date) ? new \DateTime($model->finish_date) : null;
                                     $moDate     = !empty($model->mo_date) ? new \DateTime($model->mo_date) : null;
                                 ?>
                                <tr>
                                    <td class="text-center">{{ $no++ }}</td>
                                    <td>{{ $model->asset_number }}<hr/>
                                        {{ $model->asset_name }}</td>
                                    <td>{{ $model->truck_code }}<hr/>
                                        {{ $model->police_number }}</td>
                                    <td>{{ $model->service_number }}<hr/>
                                        {{ $model->service_description }}</td>
                                    <td>{{ !empty($finishDate) ? $finishDate->format('d-M-Y') : '' }}</td>
                                    <td>{{ $model->mo_number }}<hr/>
                                        {{ !empty($moDate) ? $moDate->format('d-M-Y') : '' }}</td>
                                    <td>{{ $model->driver_code }}<hr/>
                                        {{ $model->driver_name }}</td>
                                    <td>{{ $model->item_code }}<hr/>
                                        {{ $model->item_name }}</td>
                                    <td>{{ $model->wh_code }}</td>
                                    <td class="text-right">{{ number_format($model->qty_need) }}<hr/>
                                        {{ $model->uom }}</td>
                                    <td class="text-right">{{ number_format($model->cost) }}</td>
                                </tr>
                                @endforeach
                                <tr>
                                    <td colspan="10" class="text-center"><strong>{{ trans('shared/common.total') }}</strong></td>
                                    <td ><strong>{{ number_format($totalMoveOrder) }}</strong></td>
                                </tr>
                            </tbody>
                        </table>
                    </form>
                </div>
                <div class="clearfix"></div>
                <h4>{{ trans('payable/menu.service-invoice') }}</h4>
                <div class="table-responsive">
                    <form class='form-horizontal' role='form' id="table-line">
                        <table class="table table-striped table-bordered" cellspacing="0" width="100%">
                            <thead>
                                <tr>
                                    <th width="50px">{{ trans('shared/common.num') }}</th>
                                    <th>{{ trans('asset/fields.asset-number') }}<hr/>
                                        {{ trans('asset/fields.asset-name') }}</th>
                                    <th>{{ trans('operational/fields.truck-code') }}<hr/>
                                        {{ trans('operational/fields.police-number') }}</th>
                                    <th>{{ trans('asset/fields.service-number') }}<hr/>
                                        {{ trans('shared/common.description') }}</th>
                                    <th>{{ trans('asset/fields.finish-date') }}</th>
                                    <th>{{ trans('payable/fields.invoice-number') }}</th>
                                    <th>{{ trans('payable/fields.payment-number') }}</th>
                                    <th>{{ trans('payable/fields.payment-method') }}</th>
                                    <th>{{ trans('payable/fields.payment-date') }}</th>
                                    <th>{{ trans('payable/fields.total-amount') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                    $totalInvoice = 0;
                                    $no=1; 
                                ?>
                                @foreach($invoice as $model)
                                 <?php
                                     $totalInvoice += $model->amount; 
                                     $finishDate   = !empty($model->finish_date) ? new \DateTime($model->finish_date) : null;
                                     $paymentDate  = !empty($model->payment_date) ? new \DateTime($model->payment_date) : null;
                                 ?>
                                <tr>
                                    <td class="text-center">{{ $no++ }}</td>
                                    <td>{{ $model->asset_number }}<hr/>
                                        {{ $model->asset_name }}</td>
                                    <td>{{ $model->truck_code }}<hr/>
                                        {{ $model->police_number }}</td>
                                    <td>{{ $model->service_number }}<hr/>
                                        {{ $model->service_description }}</td>
                                    <td>{{ !empty($finishDate) ? $finishDate->format('d-M-Y') : '' }}</td>
                                    <td>{{ $model->invoice_number }}</td>
                                    <td>{{ $model->payment_number }}</td>
                                    <td>{{ $model->payment_method }}</td>
                                    <td>{{ !empty($paymentDate) ? $paymentDate->format('d-M-Y') : '' }}</td>
                                    <td class="text-right">{{ number_format($model->amount) }}</td>
                                </tr>
                                @endforeach
                                <tr>
                                    <td colspan="9" class="text-center"><strong>{{ trans('shared/common.total') }}</strong></td>
                                    <td ><strong>{{ number_format($totalInvoice) }}</strong></td>
                                </tr>
                            </tbody>
                        </table>
                    </form>
                </div>
                <div class="clearfix"></div>
                <!-- <hr>
                <?php 
                    $totalAmount = $totalPurchaseOrder + $totalMoveOrder + $totalInvoice;
                ?>
                <h4>
                {{ trans('payable/fields.total-amount') }} :
                {{ number_format($totalAmount) }}
                </h4> -->
            </div>
        </div>
    </div>
</div>
@endsection
