@extends('layouts.master')

@section('title', trans('payable/menu.payment'))

<?php
    use App\Modules\Payable\Model\Transaction\InvoiceHeader;
    use App\Modules\Payable\Model\Transaction\Payment;
?>

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="widget">
            <div class="widget-header">
                <h2><i class="fa fa-usd"></i> {{ trans('payable/menu.payment') }}</h2>
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
                                <label for="paymentNumber" class="col-sm-4 control-label">{{ trans('payable/fields.payment-number') }}</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="paymentNumber" name="paymentNumber" value="{{ !empty($filters['paymentNumber']) ? $filters['paymentNumber'] : '' }}">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="invoiceNumber" class="col-sm-4 control-label">{{ trans('payable/fields.invoice-number') }}</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="invoiceNumber" name="invoiceNumber" value="{{ !empty($filters['invoiceNumber']) ? $filters['invoiceNumber'] : '' }}">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="vendorCode" class="col-sm-4 control-label">{{ trans('payable/fields.trading-code') }}</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="vendorCode" name="vendorCode" value="{{ !empty($filters['vendorCode']) ? $filters['vendorCode'] : '' }}">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="vendor" class="col-sm-4 control-label">{{ trans('payable/fields.trading-name') }}</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="vendor" name="vendor" value="{{ !empty($filters['vendor']) ? $filters['vendor'] : '' }}">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="createdBy" class="col-sm-4 control-label">{{ trans('shared/common.created-by') }}</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="createdBy" name="createdBy" value="{{ !empty($filters['createdBy']) ? $filters['createdBy'] : '' }}">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="description" class="col-sm-4 control-label">{{ trans('shared/common.description') }}</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="description" name="description" value="{{ !empty($filters['description']) ? $filters['description'] : '' }}">
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6 portlets">
                            <div class="form-group">
                                <label for="branch" class="col-sm-4 control-label">{{ trans('shared/common.type') }}</label>
                                <div class="col-sm-8">
                                    <select class="form-control" id="type" name="type">
                                        <option value="">ALL</option>
                                        @foreach($optionType as $type)
                                        <option value="{{ $type->type_id }}" {{ !empty($filters['type']) && $filters['type'] == $type->type_id ? 'selected' : '' }}>{{ $type->type_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="paymentMethod" class="col-sm-4 control-label">{{ trans('payable/fields.payment-method') }}</label>
                                <div class="col-sm-8">
                                    <select class="form-control" name="paymentMethod">
                                        <option value="">ALL</option>
                                        @foreach($optionPaymentMethod as $paymentMethod)
                                            <option value="{{ $paymentMethod }}" {{ !empty($filters['paymentMethod']) && $filters['paymentMethod'] == $paymentMethod ? 'selected' : '' }}>{{ $paymentMethod }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="branch" class="col-sm-4 control-label">{{ trans('shared/common.status') }}</label>
                                <div class="col-sm-8">
                                    <select class="form-control" id="status" name="status">
                                        <option value="">ALL</option>
                                        @foreach($optionStatus as $status)
                                        <option value="{{ $status }}" {{ !empty($filters['status']) && $filters['status'] == $status ? 'selected' : '' }}>{{ $status }}</option>
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
                        </div>
                        <div class="col-sm-12 data-table-toolbar text-right">
                            <div class="form-group">
                                <button type="submit" class="btn btn-sm btn-info"><i class="fa fa-search"></i> {{ trans('shared/common.filter') }}</button>
                                @can('access', [$resource, 'view'])
                                <a href="{{ URL($url.'/print-pdf-index') }}" class="button btn btn-sm btn-success" target="_blank">
                                    <i class="fa fa-file-pdf-o"></i> {{ trans('shared/common.print-pdf') }}
                                </a>
                                @endcan
                                @can('access', [$resource, 'view'])
                                <a href="{{ URL($url.'/print-excel-index') }}" class="button btn btn-sm btn-success" target="_blank">
                                    <i class="fa fa-file-excel-o"></i> {{ trans('shared/common.print-excel') }}
                                </a>
                                @endcan
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
                                <th width="50px">{{ trans('shared/common.num') }}</th>
                                <th>{{ trans('payable/fields.payment-number') }}<hr/>
                                    {{ trans('payable/fields.invoice-number') }}</th>
                                <th>{{ trans('shared/common.type') }}<hr>
                                    {{ trans('shared/common.date') }}</th>
                                <th>{{ trans('payable/fields.trading-code') }}<hr/>
                                    {{ trans('payable/fields.trading-name') }}</th>
                                <th>{{ trans('shared/common.address') }}</th>
                                <th>{{ trans('payable/fields.payment-method') }}<hr/>
                                    {{ trans('accountreceivables/fields.cash-or-bank') }}</th>
                                <th>{{ trans('payable/fields.total-amount') }}<hr/>
                                    {{ trans('payable/fields.total-interest') }}</th>
                                <th>{{ trans('payable/fields.total-payment') }}</th>
                                <th>{{ trans('shared/common.status') }}<hr/>
                                    {{ trans('shared/common.created-by') }}</th>
                                <th>{{ trans('shared/common.description') }}</th>
                                <th width="80px">{{ trans('shared/common.action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $no = ($models->currentPage() - 1) * $models->perPage() + 1; ?>
                            @foreach($models as $model)
                             <?php
                                 $date  = !empty($model->created_date) ? new \DateTime($model->created_date) : null;
                             ?>
                            <tr>
                                <td class="text-center">{{ $no++ }}</td>
                                <td>{{ $model->payment_number }}<hr/>
                                    {{ $model->invoice_number }}</td>
                                <td>{{ $model->type_name }}<hr>
                                    {{ !empty($date) ? $date->format('d-M-Y') : '' }}</td>
                                @if( in_array($model->type_id, InvoiceHeader::VENDOR_TYPE))
                                <td>{{ $model->vendor_code }}<hr/>
                                    {{ $model->vendor_name }}</td>
                                <td>{{ $model->vendor_address }}</td>
                                @elseif(in_array($model->type_id, InvoiceHeader::DRIVER_TYPE))
                                <td>{{ $model->driver_code }}<hr/>
                                    {{ $model->driver_name }}</td>
                                <td>{{ $model->driver_address }}</td>
                                @else
                                <td></td>
                                <td></td>
                                <td></td>
                                @endif
                                <td>{{ $model->payment_method }}<hr/>
                                    {{ $model->bank_name }}</td>
                                <td class="text-right">{{ number_format($model->total_amount) }}<hr/>
                                    {{ number_format($model->total_interest) }}</td>
                                <td class="text-right">{{ number_format($model->total_amount + $model->total_interest) }}</td>
                                <td>{{ $model->status }}<hr/>
                                    {{ $model->full_name }}</td>
                                <td>{{ $model->note }}</td>
                                <td class="text-center" >
                                    <a href="{{ URL($url . '/edit/' . $model->payment_id) }}" data-toggle="tooltip" class="btn btn-xs btn-warning" data-original-title="{{ trans('shared/common.edit') }}">
                                        <i class="fa fa-pencil"></i>
                                    </a>

                                    @if(Gate::check('access', [$resource, 'cancel']) && ($model->status == Payment::INCOMPLETE))
                                    <a data-id="{{ $model->payment_id }}" data-label="{{ $model->payment_number }}" data-toggle="tooltip" class="btn btn-danger btn-xs md-trigger cancel-action" data-original-title="{{ trans('shared/common.cancel') }}" data-modal="modal-cancel">
                                        <i class="fa fa-remove"></i>
                                    </a>
                                    @endif

                                    @if($model->status == Payment::APPROVED)
                                    <a href="{{ URL($url . '/print-pdf/' . $model->payment_id) }}" target="_blank" data-toggle="tooltip" class="btn btn-xs btn-success" data-original-title="{{ trans('shared/common.print') }}">
                                        <i class="fa fa-print"></i>
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
        <h3 style="padding-bottom: 0px;"><strong>{{ trans('shared/common.cancel') }}</strong> {{ trans('payable/menu.payment') }}</h3>
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
                                <button id="btn-cancel-po" type="submit" class="btn btn-success">{{ trans('shared/common.yes') }}</button>
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
        $('.delete-action').on('click', function() {
            $("#delete-id").val($(this).data('id'));
            $("#delete-text").html('{{ trans('shared/common.delete-confirmation', ['variable' => trans('payable/menu.payment')]) }} ' + $(this).data('label') + '?');
        });

        $('.cancel-action').on('click', function() {
            $("#cancel-id").val($(this).data('id'));
            $("#cancel-text").html('{{ trans('purchasing/fields.cancel-confirmation', ['variable' => trans('payable/menu.payment')]) }} ' + $(this).data('label') + '?');
            clearFormCancel()
        });

        $('#btn-cancel-po').on('click', function(event) {
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
