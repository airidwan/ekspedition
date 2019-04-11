<?php
use App\Modules\Accountreceivables\Model\Transaction\BatchInvoiceHeader;
?>

@extends('layouts.master')

@section('title', trans('accountreceivables/menu.batch-invoice'))

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="widget">
            <div class="widget-header">
                <h2><i class="fa fa-credit-card"></i> {{ trans('accountreceivables/menu.batch-invoice') }}</h2>
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
                                <label for="batchInvoiceNumber" class="col-sm-4 control-label">{{ trans('accountreceivables/fields.batch-invoice-number') }}</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="batchInvoiceNumber" name="batchInvoiceNumber" value="{{ !empty($filters['batchInvoiceNumber']) ? $filters['batchInvoiceNumber'] : '' }}">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="customer" class="col-sm-4 control-label">{{ trans('operational/fields.customer') }}</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="customer" name="customer" value="{{ !empty($filters['customer']) ? $filters['customer'] : '' }}">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="billTo" class="col-sm-4 control-label">{{ trans('accountreceivables/fields.bill-to') }}</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="billTo" name="billTo" value="{{ !empty($filters['billTo']) ? $filters['billTo'] : '' }}">
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6 portlets">
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
                                <label for="status" class="col-sm-4 control-label">{{ trans('shared/common.status') }}</label>
                                <div class="col-sm-8">
                                    <select id="status" name="status" class="form-control">
                                        <option value="">ALL</option>
                                        @foreach($optionStatus as $option)
                                            <option value="{{ $option }}" {{ !empty($filters['status']) && $filters['status'] == $option ? 'selected' : '' }}>{{ $option }}</option>
                                        @endforeach
                                    </select>
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
                                <th width="30px">{{ trans('shared/common.num') }}</th>
                                <th>{{ trans('accountreceivables/fields.batch-invoice-number') }}</th>
                                <th>{{ trans('shared/common.date') }}</th>
                                <th>{{ trans('shared/common.customer') }}</th>
                                <th>{{ trans('accountreceivables/fields.bill-to') }}</th>
                                <th>{{ trans('accountreceivables/fields.total') }}</th>
                                <th>{{ trans('accountreceivables/fields.remaining') }}</th>
                                <th>{{ trans('shared/common.description') }}</th>
                                <th>{{ trans('shared/common.status') }}</th>
                                <th width="90px">{{ trans('shared/common.action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $no = ($models->currentPage() - 1) * $models->perPage() + 1; ?>
                            @foreach($models as $model)
                            <?php
                            $model = BatchInvoiceHeader::find($model->batch_invoice_header_id);
                            $createdDate = !empty($model->created_date) ? new \DateTime($model->created_date) : null;
                            ?>
                            <tr>
                                <td class="text-center">{{ $no++ }}</td>
                                <td>{{ $model->batch_invoice_number }}</td>
                                <td>{{ $createdDate !== null ? $createdDate->format('d-m-Y') : '' }}</td>
                                <td>{{ $model->customer !== null ? $model->customer->customer_name : '' }}</td>
                                <td>{{ $model->bill_to }}</td>
                                <td class="text-right">{{ number_format($model->total()) }}</td>
                                <td class="text-right">{{ number_format($model->remaining()) }}</td>
                                <td>{{ $model->description }}</td>
                                <td>{{ $model->status }}</td>

                                <td class="text-center">
                                    @can('access', [$resource, 'update'])
                                    <a href="{{ URL($url . '/edit/' . $model->batch_invoice_header_id) }}" data-toggle="tooltip" class="btn btn-xs btn-warning" data-original-title="{{ trans('shared/common.edit') }}">
                                        <i class="fa fa-pencil"></i>
                                    </a>
                                    @endcan
                                    @if($model->isOpen() || $model->isClosed())
                                    <a href="{{ URL($url . '/print-pdf/' . $model->batch_invoice_header_id) }}" target="_blank" data-toggle="tooltip" class="btn btn-xs btn-success" data-original-title="{{ trans('shared/common.print') }}">
                                        <i class="fa fa-print"></i>
                                    </a>
                                    @endif
                                    @if($model->isOpen())
                                    <a data-id="{{ $model->batch_invoice_header_id }}" data-label="{{ $model->batch_invoice_number }}" data-modal="modal-cancel" data-toggle="tooltip" class="btn btn-xs btn-danger md-trigger cancel-action" data-original-title="{{ trans('shared/common.remove') }}">
                                        <i class="fa fa-remove"></i>
                                    </a>
                                    @endif
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
        <h3 style="padding-bottom: 0px;"><strong>{{ trans('shared/common.cancel') }}</strong> {{ trans('accountreceivables/menu.batch-invoice') }}</h3>
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
