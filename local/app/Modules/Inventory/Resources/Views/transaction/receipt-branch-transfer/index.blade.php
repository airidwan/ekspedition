@extends('layouts.master')

@section('title', trans('inventory/menu.receipt-branch-transfer'))

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="widget">
            <div class="widget-header">
                <h2><i class="fa fa-truck"></i> {{ trans('inventory/menu.receipt-branch-transfer') }}</h2>
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
                        <div class="col-sm-6 portlets">
                            <div class="form-group">
                                <label for="branchTransferNumber" class="col-sm-4 control-label">{{ trans('inventory/fields.bt-number') }}</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="branchTransferNumber" name="branchTransferNumber" value="{{ !empty($filters['branchTransferNumber']) ? $filters['branchTransferNumber'] : '' }}">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="receiptNumber" class="col-sm-4 control-label">{{ trans('inventory/fields.receipt-number') }}</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="receiptNumber" name="receiptNumber" value="{{ !empty($filters['receiptNumber']) ? $filters['receiptNumber'] : '' }}">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="branch" class="col-sm-4 control-label"></label>
                                <div class="col-sm-8">
                                    <?php $jenis = !empty($filters['jenis']) ? $filters['jenis'] : 'headers' ?>
                                    <label class="radio-inline iradio">
                                        <input type="radio" name="jenis" id="radio1" value="headers" {{ $jenis == 'headers' ? 'checked' : '' }}> Headers
                                    </label>
                                    <label class="radio-inline iradio">
                                        <input type="radio" name="jenis" id="radio2" value="lines" {{ $jenis == 'lines' ? 'checked' : '' }}> Lines
                                    </label>
                                </div>
                            </div>
                        </div>
                        <!-- <div class="col-sm-4 portlets">
                            <div class="form-group">
                                <label for="warehouse" class="col-sm-4 control-label">{{ trans('operational/fields.warehouse') }}</label>
                                <div class="col-sm-8">
                                    <select class="form-control" id="warehouse" name="warehouse">
                                        <option value="">ALL</option>
                                        @foreach($optionWarehouse as $warehouse)
                                        <option value="{{ $warehouse->wh_id }}" {{ !empty($filters['warehouse']) && $filters['warehouse'] == $warehouse->wh_id ? 'selected' : '' }}>{{ $warehouse->wh_code  }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div> -->
                        <div class="col-sm-6 portlets">
                            <div class="form-group">
                                <label for="description" class="col-sm-4 control-label">{{ trans('shared/common.description') }}</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="description" name="description" value="{{ !empty($filters['description']) ? $filters['description'] : '' }}">
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
                                <button type="submit" class="btn btn-sm btn-info"><i class="fa fa-search"></i> {{ trans('shared/common.filter') }}</button>
                                @can('access', [$resource, 'insert'])
                                <a href="{{ URL($url . '/add') }}" class="btn btn-sm btn-primary"><i class="fa fa-plus-circle"></i> {{ trans('shared/common.add-new') }}</a>
                                @endcan
                            </div>
                        </div>
                    </form>
                </div>
                <div class="clearfix"></div>
                <hr>
                <div class="table-responsive">
                    @if (empty($filters['jenis']) || $filters['jenis'] == 'headers')
                        <table class="table table-striped table-bordered" cellspacing="0" width="100%">
                        <thead>
                            <tr>
                                <th width="50px">{{ trans('shared/common.num') }}</th>
                                <th>{{ trans('inventory/fields.receipt-number') }}</th>
                                <th>{{ trans('inventory/fields.receipt-date') }}</th>
                                <th>{{ trans('inventory/fields.bt-number') }}</th>
                                <th>{{ trans('shared/common.description') }}</th>
                                <th>{{ trans('shared/common.action') }}</th>    
                            </tr>
                        </thead>
                        <tbody>
                             <?php $no = ($models->currentPage() - 1) * $models->perPage() + 1; ?>
                             @foreach($models as $model)
                            <?php
                            $receiptDate = !empty($model->receipt_bt_date) ? new \DateTime($model->receipt_bt_date) : null;
                            ?>
                            <tr>
                                <td class="text-center">{{ $no++ }}</td>
                                <td>{{ $model->receipt_bt_number }}</td>
                                <td>{{ !empty($receiptDate) ? $receiptDate->format('d-M-Y') : '' }}</td>
                                <td>{{ $model->bt_number }}</td>
                                <td>{{ $model->description_bt }}</td>
                                <td class="text-center">
                                @can('access', [$resource, 'update'])
                                    <a href="{{ URL($url . '/edit/' . $model->receipt_bt_header_id) }}" data-toggle="tooltip" class="btn btn-xs btn-warning" data-original-title="{{ trans('shared/common.edit') }}">
                                    <i class="fa fa-pencil"></i>
                                    </a>
                                @endcan
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    @else
                    <table class="table table-striped table-bordered" cellspacing="0" width="100%">
                        <thead>
                            <tr>
                                <th width="50px">{{ trans('shared/common.num') }}</th>
                                <th>{{ trans('inventory/fields.receipt-number') }}</th>
                                <th>{{ trans('inventory/fields.receipt-date') }}</th>
                                <th>{{ trans('inventory/fields.quantity-receipt') }}</th>
                                <th>{{ trans('inventory/fields.uom') }}</th>
                                <th>{{ trans('inventory/fields.item') }}</th>
                                <th>{{ trans('shared/common.description') }}</th>
                                <th>{{ trans('inventory/fields.wh') }}</th>
                                <th>{{ trans('inventory/fields.bt-number') }}</th>
                                <th>{{ trans('shared/common.action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                             <?php $no = ($models->currentPage() - 1) * $models->perPage() + 1; ?>
                             @foreach($models as $model)
                            <?php
                            $receiptDate = !empty($model->created_date) ? new \DateTime($model->created_date) : null;
                            ?>
                            <tr>
                                <td class="text-center">{{ $no++ }}</td>
                                <td>{{ $model->receipt_bt_number }}</td>
                                <td>{{ !empty($receiptDate) ? $receiptDate->format('d-M-Y') : '' }}</td>
                                <td class="text-right">{{ $model->receipt_bt_quantity }}</td>
                                <td>{{ $model->description_uom }}</td>
                                <td>{{ $model->item_code }}</td>
                                <td>{{ $model->description_item }}</td>
                                <td>{{ $model->wh_code }}</td>
                                <td>{{ $model->bt_number }}</td>
                                <td class="text-center">
                                @can('access', [$resource, 'update'])
                                    <a href="{{ URL($url . '/edit/' . $model->receipt_bt_header_id) }}" data-toggle="tooltip" class="btn btn-xs btn-warning" data-original-title="{{ trans('shared/common.edit') }}">
                                    <i class="fa fa-pencil"></i>
                                    </a>
                                @endcan
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    @endif
                </div>
                <div class="data-table-toolbar">
                    {!! $models->render() !!}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
