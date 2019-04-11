@extends('layouts.master')

@section('title', trans('operational/menu.receipt-or-return-delivery-order'))
<?php use App\Modules\Operational\Model\Transaction\TransactionResiHeader; ?>
@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="widget">
            <div class="widget-header">
                <h2><i class="fa fa-truck"></i> {{ trans('operational/menu.receipt-or-return-delivery-order') }}</h2>
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
                                <label for="receiptOrReturnNumber" class="col-sm-4 control-label">{{ trans('operational/fields.receipt-or-return-number') }}</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="receiptOrReturnNumber" name="receiptOrReturnNumber" value="{{ !empty($filters['receiptOrReturnNumber']) ? $filters['receiptOrReturnNumber'] : '' }}">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="deliveryOrderNumber" class="col-sm-4 control-label">{{ trans('operational/fields.do-number') }}</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="deliveryOrderNumber" name="deliveryOrderNumber" value="{{ !empty($filters['deliveryOrderNumber']) ? $filters['deliveryOrderNumber'] : '' }}">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="policeNumber" class="col-sm-4 control-label">{{ trans('operational/fields.police-number') }}</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="policeNumber" name="policeNumber" value="{{ !empty($filters['policeNumber']) ? $filters['policeNumber'] : '' }}">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="vendor" class="col-sm-4 control-label">{{ trans('payable/fields.vendor') }}</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="vendor" name="vendor" value="{{ !empty($filters['vendor']) ? $filters['vendor'] : '' }}">
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6 portlets">
                            <div class="form-group">
                                <label for="driver" class="col-sm-4 control-label">{{ trans('operational/fields.driver') }}</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="driver" name="driver" value="{{ !empty($filters['driver']) ? $filters['driver'] : '' }}">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="assistant" class="col-sm-4 control-label">{{ trans('operational/fields.driver-assistant') }}</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="assistant" name="assistant" value="{{ !empty($filters['assistant']) ? $filters['assistant'] : '' }}">
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
                    <table class="table table-striped table-bordered" cellspacing="0" width="100%">
                        <thead>
                            <tr>
                                <th>{{ trans('shared/common.num') }}</th>
                                <th>{{ trans('operational/fields.receipt-or-return-number') }}<hr/>{{ trans('shared/common.date') }}</th>
                                <th>{{ trans('operational/fields.do-number') }}<hr/>{{ trans('shared/common.type') }}</th>
                                <th>{{ trans('operational/fields.truck') }}<hr/>{{ trans('shared/common.type') }}</th>
                                <th>{{ trans('operational/fields.driver') }}<hr/>{{ trans('operational/fields.driver-assistant') }}</th>
                                <th>{{ trans('shared/common.note') }}</th>
                                <th>{{ trans('shared/common.action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $no = ($models->currentPage() - 1) * $models->perPage() + 1; ?>
                            @foreach($models as $model)
                            <?php
                                 $createdDate = !empty($model->created_date) ? new \DateTime($model->created_date) : null;
                             ?>
                            <tr>
                                <td class="text-center">{{ $no++ }}</td>
                                <td>{{ $model->receipt_or_return_delivery_number }}<hr/>{{ !empty($createdDate) ? $createdDate->format('d-M-Y') : '' }}</td>
                                <td>{{ $model->delivery_order_number }}<hr/>{{ $model->delivery_order_type }}</td>
                                <td>{{ $model->police_number }}<hr/>{{ $model->truck_type }}</td>
                                <td>{{ $model->driver_code }} - {{ $model->driver_name }}<hr/>{{ $model->driver_assistant_code }} - {{ $model->driver_assistant_name }}</td>
                                <td>{{ $model->note }}</td>
                                <td class="text-center">
                                    <a href="{{ URL($url . '/edit/' . $model->receipt_or_return_delivery_header_id) }}" data-toggle="tooltip" class="btn btn-xs btn-warning" data-original-title="{{ trans('shared/common.edit') }}">
                                        <i class="fa fa-pencil"></i>
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="data-table-toolbar">
                    {!! $models->render() !!}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection