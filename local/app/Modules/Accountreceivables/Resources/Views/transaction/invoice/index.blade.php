<?php
use App\Modules\Accountreceivables\Model\Transaction\Invoice;
?>

@extends('layouts.master')

@section('title', trans('accountreceivables/menu.invoice'))

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="widget">
            <div class="widget-header">
                <h2><i class="fa fa-credit-card"></i> {{ trans('accountreceivables/menu.invoice') }}</h2>
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
                                <label for="invoiceNumber" class="col-sm-4 control-label">{{ trans('accountreceivables/fields.invoice-number') }}</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="invoiceNumber" name="invoiceNumber" value="{{ !empty($filters['invoiceNumber']) ? $filters['invoiceNumber'] : '' }}">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="resiNumber" class="col-sm-4 control-label">{{ trans('operational/fields.resi-number') }}</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="resiNumber" name="resiNumber" value="{{ !empty($filters['resiNumber']) ? $filters['resiNumber'] : '' }}">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="customer" class="col-sm-4 control-label">{{ trans('operational/fields.customer') }}</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="customer" name="customer" value="{{ !empty($filters['customer']) ? $filters['customer'] : '' }}">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="dateTo" class="col-sm-4 control-label">{{ trans('operational/fields.payment') }}</label>
                                <div class="col-sm-8">
                                    <select class="form-control" name="payment">
                                        <option value="">ALL</option>
                                        @foreach($optionPayment as $payment)
                                            <option value="{{ $payment }}" {{ !empty($filters['payment']) && $filters['payment'] == $payment ? 'selected' : '' }}>{{ $payment }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="dateTo" class="col-sm-4 control-label">{{ trans('shared/common.type') }}</label>
                                <div class="col-sm-8">
                                    <select class="form-control" name="type">
                                        <option value="">ALL</option>
                                        @foreach($optionType as $type)
                                            <option value="{{ $type }}" {{ !empty($filters['type']) && $filters['type'] == $type ? 'selected' : '' }}>{{ $type }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6 portlets">
                            <div class="form-group">
                                <label for="route" class="col-sm-4 control-label">{{ trans('operational/fields.route') }}</label>
                                <div class="col-sm-8">
                                    <select class="form-control" id="route" name="route[]" multiple="multiple">
                                        <?php $routeIds = !empty($filters['route']) ? $filters['route'] : []; ?>
                                        @foreach($optionRoute as $route)
                                        <option value="{{ $route->route_id }}" {{ in_array($route->route_id, $routeIds) ? 'selected' : '' }}>{{ $route->route_code }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="region" class="col-sm-4 control-label">{{ trans('operational/fields.region') }}</label>
                                <div class="col-sm-8">
                                    <select class="form-control" id="region" name="region[]" multiple="multiple">
                                        <?php $regionIds = !empty($filters['region']) ? $filters['region'] : []; ?>
                                        @foreach($optionRegion as $region)
                                        <option value="{{ $region->region_id }}" {{ in_array($region->region_id, $regionIds) ? 'selected' : '' }}>{{ $region->region_name }}</option>
                                        @endforeach
                                    </select>
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
                            <div class="form-group">
                                <label for="dateTo" class="col-sm-4 control-label">{{ trans('shared/common.status') }}</label>
                                <div class="col-sm-8">
                                    <select class="form-control" name="status">
                                        <option value="">ALL</option>
                                        @foreach($optionStatus as $status)
                                            <option value="{{ $status }}" {{ !empty($filters['status']) && $filters['status'] == $status ? 'selected' : '' }}>{{ $status }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-12 data-table-toolbar text-right">
                            <div class="form-group">
                                <button type="submit" class="btn btn-sm btn-info"><i class="fa fa-search"></i> {{ trans('shared/common.filter') }}</button>
                                @can('access', [$resource, 'view'])
                                <a href="{{ URL($url.'/print-excel-index') }}" class="button btn btn-sm btn-success" target="_blank">
                                    <i class="fa fa-file-excel-o"></i> {{ trans('shared/common.print-excel') }}
                                </a>
                                @endcan
                                @can('access', [$resource, 'insert'])
                                    <a href="{{ URL($url . '/add-invoice-extra-cost') }}" class="btn btn-sm btn-primary"><i class="fa fa-plus-circle"></i> {{ trans('accountreceivables/fields.add-invoice-extra-cost') }}</a>
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
                                <th width="30px">{{ trans('shared/common.num') }}</th>
                                <th>
                                    {{ trans('accountreceivables/fields.invoice-number') }}<hr/>
                                    {{ trans('accountreceivables/fields.date') }}
                                </th>
                                <th>{{ trans('operational/fields.resi-number') }}<hr/>{{ trans('shared/common.type') }}</th>
                                <th>{{ trans('accountreceivables/fields.bill') }}<hr/>{{ trans('operational/fields.payment') }}</th>
                                <th>
                                    {{ trans('operational/fields.customer') }}<hr/>
                                    {{ trans('operational/fields.sender') }}
                                </th>
                                <th>
                                    {{ trans('operational/fields.customer') }}<hr/>
                                    {{ trans('operational/fields.receiver') }}
                                </th>
                                <th>{{ trans('accountreceivables/fields.total-invoice') }}</th>
                                <th>{{ trans('operational/fields.discount') }}</th>
                                <th>{{ trans('accountreceivables/fields.total') }}</th>
                                <th>{{ trans('accountreceivables/fields.receipt') }}<hr/>{{ trans('accountreceivables/fields.remaining') }}</th>
                                <th>{{ trans('shared/common.description') }}</th>
                                <th>{{ trans('shared/common.status') }}</th>
                                <th width="80px">{{ trans('shared/common.action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $no = ($models->currentPage() - 1) * $models->perPage() + 1; ?>
                            @foreach($models as $model)
                            <?php
                            $model = Invoice::find($model->invoice_id);
                            $invoiceDate = !empty($model->created_date) ? new \DateTime($model->created_date) : null;
                            ?>
                            <tr>
                                <td class="text-center">{{ $no++ }}</td>
                                <td class="text-center">
                                    {{ $model->invoice_number }}<hr/>
                                    {{ $invoiceDate !== null ? $invoiceDate->format('d-m-Y') : '' }}
                                </td>
                                <td>
                                    {{ !empty($model->resi) ? $model->resi->resi_number : '' }}<hr/>
                                    {{ $model->type }}
                                </td>
                                <td class="text-center">
                                    <i class="fa {{ $model->is_tagihan ? 'fa-check' : 'fa-remove' }}"></i><hr/>
                                    {{ !empty($model->resi) ? $model->resi->getSingkatanPayment() : '' }}
                                </td>
                                <td>
                                    {{ !empty($model->resi->customer) ? $model->resi->customer->customer_name : '' }}<hr/>
                                    {{ !empty($model->resi) ? $model->resi->sender_name : '' }}
                                </td>
                                <td>
                                    {{ !empty($model->resi->customerReceiver) ? $model->resi->customerReceiver->customer_name : '' }}<hr/>
                                    {{ !empty($model->resi) ? $model->resi->receiver_name : '' }}
                                </td>
                                <td class="text-right">{{ number_format($model->amount) }}</td>
                                <td class="text-right">{{ number_format($model->totalDiscount()) }}</td>
                                <td class="text-right">{{ number_format($model->totalInvoice()) }}</td>
                                <td class="text-right">{{ number_format($model->totalReceipt()) }}<hr/>{{ number_format($model->remaining()) }}</td>
                                <td>{{ $model->description }}</td>
                                <td>{{ $model->status }}</td>

                                <td class="text-center">
                                    <a href="{{ URL($url . '/edit/' . $model->invoice_id) }}" data-toggle="tooltip" class="btn btn-xs btn-warning" data-original-title="{{ trans('shared/common.edit') }}">
                                        <i class="fa fa-pencil"></i>
                                    </a>
                                    @if($model->isInvoiceResi() && ($model->isApproved() || $model->isClosed()))
                                    <a href="{{ URL($url . '/print-pdf/' . $model->invoice_id) }}" target="_blank" data-toggle="tooltip" class="btn btn-xs btn-success" data-original-title="{{ trans('shared/common.print') }}">
                                        <i class="fa fa-print"></i>
                                    </a>
                                    @endif
                                    @if(Gate::check('access', [$resource, 'cancel']) && $model->isApproved() && $model->isInvoiceResi() && $model->receipts()->count() == 0)
                                    <a data-id="{{ $model->invoice_id }}" data-label="{{ $model->invoice_number }}" data-modal="modal-cancel" data-toggle="tooltip" class="btn btn-xs btn-danger md-trigger cancel-action" data-original-title="{{ trans('shared/common.remove') }}">
                                        <i class="fa fa-remove"></i>
                                    </a>
                                    @endcan
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

@section('modal')
@parent
<div class="md-modal md-3d-flip-horizontal" id="modal-cancel">
    <div class="md-content">
        <h3 style="padding-bottom: 0px;"><strong>{{ trans('shared/common.cancel') }}</strong> {{ trans('accountreceivables/menu.invoice') }}</h3>
        <div>
            <div class="row">
                <div class="col-sm-12">
                    <h4 id="cancel-text">Are you sure want to cancel ?</h4>
                    <form id="form-cancel" role="form" method="post" action="{{ URL($url . '/cancel') }}">
                        {{ csrf_field() }}
                        <input type="hidden" id="cancel-id" name="id" >
                        <div class="form-group">
                            <h4 for="reason" class="col-sm-4 control-label">{{ trans('shared/common.reason') }} <span class="required">*</span></h4>
                            <div class="col-sm-8">
                                <textarea name="reason" class="form-control" rows="4"></textarea>
                            </div>
                            <span class="help-block text-center"></span>
                        </div>
                        <div class="form-group">
                            <div class="col-sm-12 text-right">
                                <br>
                                <a class="btn btn-danger md-close">{{ trans('shared/common.no') }}</a>
                                <button id="btn-cancel" type="submit" class="btn btn-success">{{ trans('shared/common.yes') }}</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
@parent
<script type="text/javascript">
$(document).on('ready', function(){
    $("#route").select2();
    $("#region").select2();
    $('.cancel-action').on('click', function() {
        $("#cancel-id").val($(this).data('id'));
        $("#cancel-text").html('{{ trans('shared/common.cancel-confirmation', ['variable' => trans('accountreceivables/menu.invoice')]) }} ' + $(this).data('label') + '?');
        clearFormCancel()
    });

    $('#btn-cancel').on('click', function(event) {
        event.preventDefault();
        if ($('textarea[name="reason"]').val() == '') {
            $(this).parent().parent().parent().addClass('has-error');
            $(this).parent().parent().parent().find('span.help-block').html('Reason is required');
            return
        } else {
            clearFormCancel()
        }

        $('#form-cancel').trigger('submit');
    });
});

var clearFormCancel = function() {
    $('#form-cancel').removeClass('has-error');
    $('#form-cancel').find('span.help-block').html('');
};
</script>
@endsection
