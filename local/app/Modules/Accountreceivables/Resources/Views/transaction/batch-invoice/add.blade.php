@extends('layouts.master')

@section('title', trans('accountreceivables/menu.batch-invoice'))

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="widget">
            <div class="widget-header">
                <h2><i class="fa fa-credit-card"></i> <strong>{{ $title }}</strong> {{ trans('accountreceivables/menu.batch-invoice') }}</h2>
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
                        <input type="hidden" id="id" name="id" value="{{ $model->batch_invoice_header_id }}">
                        <ul id="demo1" class="nav nav-tabs">
                            <li class="active">
                                <a href="#tabHeaders" data-toggle="tab">{{ trans('shared/common.headers') }} <span class="label label-success"></span></a>
                            </li>
                            <li class="">
                                <a href="#tabLine" data-toggle="tab">{{ trans('shared/common.line') }} <span class="badge badge-primary"></span></a>
                            </li>
                        </ul>
                        <div class="tab-content">
                            <div class="tab-pane fade active in" id="tabHeaders">
                                <div class="col-sm-6 portlets">
                                    <div class="form-group">
                                        <label for="batchInvoiceNumber" class="col-sm-4 control-label">{{ trans('accountreceivables/fields.batch-invoice-number') }} </label>
                                        <div class="col-sm-8">
                                            <input type="text" class='form-control' id="batchInvoiceNumber" name="batchInvoiceNumber" value="{{ $model->batch_invoice_number }}" disabled>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="createdDate" class="col-sm-4 control-label">{{ trans('shared/common.tanggal') }}</label>
                                        <div class="col-sm-8">
                                            <div class="input-group">
                                                <?php $createdDate = new \DateTime($model->created_date); ?>
                                                <input type="text" id="createdDate" name="createdDate" class="form-control datepicker-input" data-mask="99-99-9999" placeholder="dd-mm-yyyy" value="{{ $createdDate->format('d-m-Y') }}" disabled>
                                                <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="totalAmount" class="col-sm-4 control-label">{{ trans('operational/fields.total-amount') }} </label>
                                        <div class="col-sm-8">
                                            <input type="text" class='form-control currency' id="totalAmount" name="totalAmount" value="{{ count($errors) > 0 ? str_replace(',', '', old('totalAmount')) : $model->totalAmount() }}" readonly>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="discountHeader" class="col-sm-4 control-label">{{ trans('accountreceivables/fields.discount') }} </label>
                                        <div class="col-sm-8">
                                            <input type="text" class='form-control currency' id="discountHeader" name="discountHeader" value="{{ count($errors) > 0 ? str_replace(',', '', old('discountHeader')) : $model->totalDiscount() }}" readonly>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="total" class="col-sm-4 control-label">{{ trans('accountreceivables/fields.total') }} </label>
                                        <div class="col-sm-8">
                                            <input type="text" class='form-control currency' id="total" name="total" value="{{ count($errors) > 0 ? str_replace(',', '', old('total')) : $model->total() }}" readonly>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="remaining" class="col-sm-4 control-label">{{ trans('accountreceivables/fields.remaining') }} </label>
                                        <div class="col-sm-8">
                                            <input type="text" class='form-control currency' id="remaining" name="remaining" value="{{ count($errors) > 0 ? str_replace(',', '', old('remaining')) : $model->remaining() }}" readonly>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="discountPersen" class="col-sm-4 control-label">{{ trans('accountreceivables/fields.discount-inprocess') }} </label>
                                        <div class="col-sm-2">
                                            <input type="text" class='form-control currency' id="discountPersen" name="discountPersen" value="{{ count($errors) > 0 ? str_replace(',', '', old('discountPersen')) : $model->discount_persen }}" {{ !$model->isOpen() ? 'readonly' : '' }}>
                                        </div>
                                        <div class="col-sm-2">
                                            <input type="text" class='form-control text-center' value="% =" readonly>
                                        </div>
                                        <div class="col-sm-4">
                                            <input type="text" class='form-control currency' id="totalDiscountInprocess" name="totalDiscountInprocess" value="{{ count($errors) > 0 ? str_replace(',', '', old('totalDiscountInprocess')) : $model->getTotalDiscountInprocess() }}" readonly>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="requestApproveNote" class="col-sm-4 control-label">{{ trans('operational/fields.requested-note') }}</label>
                                        <div class="col-sm-8">
                                            <textarea class="form-control" id="requestApproveNote" name="requestApproveNote" {{ !$model->isOpen() ? 'readonly' : '' }}>{{ count($errors) > 0 ? old('requestApproveNote') : $model->request_approve_note }}</textarea>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-6 portlets">
                                    <?php
                                    $customerName = $model->customer !== null ? $model->customer->customer_name : '';
                                    ?>
                                    <div class="form-group {{ $errors->has('customerId') ? 'has-error' : '' }}">
                                        <label for="customerName" class="col-sm-4 control-label">{{ trans('accountreceivables/fields.customer') }}</label>
                                        <div class="col-sm-8">
                                            <div class="input-group">
                                                <input type="hidden" id="customerId" name="customerId" value="{{ count($errors) > 0 ? old('customerId') : $model->customer_id }}">
                                                <input type="text" class="form-control" id="customerName" name="customerName" value="{{ count($errors) > 0 ? old('customerName') : $customerName }}" readonly>
                                                <span class="btn input-group-addon" id="{{ $model->isOpen() ? 'remove-customer' : '' }}"><i class="fa fa-remove"></i></span>
                                                <span class="btn input-group-addon" id="{{ $model->isOpen() ? 'show-lov-customer' : '' }}"><i class="fa fa-search"></i></span>
                                            </div>
                                            @if($errors->has('customerId'))
                                                <span class="help-block">{{ $errors->first('customerId') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="form-group {{ $errors->has('billTo') ? 'has-error' : '' }}">
                                        <label for="billTo" class="col-sm-4 control-label">{{ trans('accountreceivables/fields.bill-to') }} <span class="required">*</span></label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="billTo" name="billTo" value="{{ count($errors) > 0 ? old('billTo') : $model->bill_to }}" {{ !$model->isOpen() ? 'readonly' : '' }}>
                                            @if($errors->has('billTo'))
                                                <span class="help-block">{{ $errors->first('billTo') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="form-group {{ $errors->has('billToAddress') ? 'has-error' : '' }}">
                                        <label for="billToAddress" class="col-sm-4 control-label">{{ trans('accountreceivables/fields.bill-to-address') }} <span class="required">*</span></label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="billToAddress" name="billToAddress" value="{{ count($errors) > 0 ? old('billToAddress') : $model->bill_to_address }}" {{ !$model->isOpen() ? 'readonly' : '' }}>
                                            @if($errors->has('billToAddress'))
                                                <span class="help-block">{{ $errors->first('billToAddress') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="form-group {{ $errors->has('billToPhone') ? 'has-error' : '' }}">
                                        <label for="billToPhone" class="col-sm-4 control-label">{{ trans('accountreceivables/fields.bill-to-phone') }} <span class="required">*</span></label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="billToPhone" name="billToPhone" value="{{ count($errors) > 0 ? old('billToPhone') : $model->bill_to_phone }}" {{ !$model->isOpen() ? 'readonly' : '' }}>
                                            @if($errors->has('billToPhone'))
                                                <span class="help-block">{{ $errors->first('billToPhone') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="description" class="col-sm-4 control-label">{{ trans('shared/common.description') }}</label>
                                        <div class="col-sm-8">
                                            <textarea class="form-control" id="description" name="description" {{ !$model->isOpen() ? 'readonly' : '' }}>{{ count($errors) > 0 ? old('description') : $model->description }}</textarea>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="status" class="col-sm-4 control-label">{{ trans('accountreceivables/fields.status') }} </label>
                                        <div class="col-sm-8">
                                            <input type="text" class='form-control' id="status" name="status" value="{{ $model->status }}" readonly>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="tabLine">
                                <div class="col-sm-12 portlets">
                                    @if($model->isOpen())
                                    <div class="data-table-toolbar">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="toolbar-btn-action">
                                                    <a id="add-line" class="btn btn-sm btn-primary">
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
                                                    <th>
                                                        {{ trans('accountreceivables/fields.invoice-number') }}<hr/>
                                                        {{ trans('shared/common.type') }}
                                                    </th>
                                                    <th>{{ trans('operational/fields.resi-number') }}<hr/>{{ trans('operational/fields.route') }}</th>
                                                    <th>{{ trans('shared/common.customer') }}<hr/>{{ trans('operational/fields.sender') }}</th>
                                                    <th>{{ trans('shared/common.customer') }}<hr/>{{ trans('operational/fields.receiver') }}</th>
                                                    <th>{{ trans('operational/fields.amount') }} <hr/> {{ trans('operational/fields.discount') }}</th>
                                                    <th>{{ trans('accountreceivables/fields.discount-inprocess') }}</th>
                                                    <th>{{ trans('accountreceivables/fields.total') }} <hr/> {{ trans('accountreceivables/fields.remaining') }}</th>

                                                    @if($model->isOpen())
                                                        <th width="60px">{{ trans('shared/common.action') }}</th>
                                                    @endif
                                                </tr>
                                            </thead>
                                            <tbody>
                                               <?php $indexLine = 0; ?>
                                                @if(count($errors) > 0)
                                                @for($i = 0; $i < count(old('invoiceHeaderId', [])); $i++)
                                                <tr data-index-line="{{ $indexLine }}">
                                                    <td > {{ old('invoiceNumber')[$i] }}<hr/>{{ old('invoiceType')[$i] }} </td>
                                                    <td > {{ old('resiNumber')[$i] }}<hr/>{{ old('route')[$i] }} </td>
                                                    <td > {{ old('customerSender')[$i] }}<hr/>{{ old('sender')[$i] }} </td>
                                                    <td > {{ old('customerReceiver')[$i] }}<hr/>{{ old('receiver')[$i] }} </td>
                                                    <td class="text-right"> {{ number_format(intval(old('amountLine')[$i])) }} <hr/> {{ number_format(intval(old('discount')[$i])) }} </td>
                                                    <td class="text-right discountInprocessLine"> {{ number_format(intval(old('discountInprocess')[$i])) }} </td>
                                                    <td class="text-right"> {{ number_format(intval(old('totalLine')[$i])) }} <hr/> {{ number_format(intval(old('remainingLine')[$i])) }} </td>

                                                    @if($model->isOpen())
                                                    <td class="text-center">
                                                        <a data-toggle="tooltip" class="btn btn-danger btn-xs delete-line" ><i class="fa fa-remove"></i></a>
                                                        <input type="hidden" name="invoiceHeaderId[]" value="{{ old('invoiceHeaderId')[$i] }}">
                                                        <input type="hidden" name="invoiceNumber[]" value="{{ old('invoiceNumber')[$i] }}">
                                                        <input type="hidden" name="invoiceType[]" value="{{ old('invoiceType')[$i] }}">
                                                        <input type="hidden" name="resiNumber[]" value="{{ old('resiNumber')[$i] }}">
                                                        <input type="hidden" name="route[]" value="{{ old('route')[$i] }}">
                                                        <input type="hidden" name="customerSender[]" value="{{ old('customerSender')[$i] }}">
                                                        <input type="hidden" name="sender[]" value="{{ old('sender')[$i] }}">
                                                        <input type="hidden" name="customerReceiver[]" value="{{ old('customerReceiver')[$i] }}">
                                                        <input type="hidden" name="receiver[]" value="{{ old('receiver')[$i] }}">
                                                        <input type="hidden" name="amountLine[]" value="{{ old('amountLine')[$i] }}">
                                                        <input type="hidden" name="discount[]" value="{{ old('discount')[$i] }}">
                                                        <input type="hidden" name="canAddDiscount[]" value="{{ old('canAddDiscount')[$i] }}">
                                                        <input type="hidden" name="discountInprocess[]" value="{{ old('discountInprocess')[$i] }}">
                                                        <input type="hidden" name="totalLine[]" value="{{ old('totalLine')[$i] }}">
                                                        <input type="hidden" name="remainingLine[]" value="{{ old('remainingLine')[$i] }}">
                                                    </td>
                                                    @endif
                                                </tr>
                                                <?php $indexLine++; ?>
                                                @endfor

                                                @else
                                                @foreach($model->lines()->get() as $line)
                                                <tr data-index-line="{{ $indexLine }}">
                                                    <td >
                                                        {{ !empty($line->invoice) ? $line->invoice->invoice_number : '' }}<hr/>
                                                        {{ !empty($line->invoice) ? $line->invoice->type : '' }}
                                                    </td>
                                                    <td >
                                                        {{ !empty($line->invoice->resi) ? $line->invoice->resi->resi_number : '' }}<hr/>
                                                        {{ !empty($line->invoice->resi->route) ? $line->invoice->resi->route->route_code : '' }}
                                                    </td>
                                                    <td >
                                                        {{ !empty($line->invoice->resi->customer) ? $line->invoice->resi->customer->customer_name : '' }}<hr/>
                                                        {{ !empty($line->invoice->resi) ? $line->invoice->resi->sender_name : '' }}
                                                    </td>
                                                    <td >
                                                        {{ !empty($line->invoice->resi->customerReceiver) ? $line->invoice->resi->customerReceiver->customer_name : '' }}<hr/>
                                                        {{ !empty($line->invoice->resi) ? $line->invoice->resi->receiver_name : '' }}
                                                    </td>
                                                    <td class="text-right"> {{ !empty($line->invoice) ? number_format($line->invoice->amount) : 0 }} <hr/> {{ !empty($line->invoice) ? number_format($line->invoice->totalDiscount()) : 0 }} </td>
                                                    <td class="text-right discountInprocessLine"> {{ !empty($line->invoice) ? number_format($line->invoice->getDiscountInprocess()) : 0 }} </td>
                                                    <td class="text-right"> {{ !empty($line->invoice) ? number_format($line->invoice->totalInvoice()) : 0 }} <hr/> {{ !empty($line->invoice) ? number_format($line->invoice->remaining()) : 0 }} </td>

                                                    @if($model->isOpen())
                                                    <td class="text-center">
                                                        <a data-toggle="tooltip" class="btn btn-danger btn-xs delete-line" ><i class="fa fa-remove"></i></a>
                                                        <input type="hidden" name="invoiceHeaderId[]" value="{{ !empty($line->invoice) ? $line->invoice->invoice_id : '' }}">
                                                        <input type="hidden" name="invoiceNumber[]" value="{{ !empty($line->invoice) ? $line->invoice->invoice_number : '' }}">
                                                        <input type="hidden" name="invoiceType[]" value="{{ !empty($line->invoice) ? $line->invoice->type : '' }}">
                                                        <input type="hidden" name="resiNumber[]" value="{{ !empty($line->invoice->resi) ? $line->invoice->resi->resi_number : '' }}">
                                                        <input type="hidden" name="route[]" value="{{ !empty($line->invoice->resi->route) ? $line->invoice->resi->route->route_code : '' }}">
                                                        <input type="hidden" name="customerSender[]" value="{{ !empty($line->invoice->resi->customer) ? $line->invoice->resi->customer->customer_name : '' }}">
                                                        <input type="hidden" name="sender[]" value="{{ !empty($line->invoice->resi) ? $line->invoice->resi->sender_name : '' }}">
                                                        <input type="hidden" name="customerReceiver[]" value="{{ !empty($line->invoice->resi->customerReceiver) ? $line->invoice->resi->customerReceiver->customer_name : '' }}">
                                                        <input type="hidden" name="receiver[]" value="{{ !empty($line->invoice->resi) ? $line->invoice->resi->receiver_name : '' }}">
                                                        <input type="hidden" name="amountLine[]" value="{{ !empty($line->invoice) ? $line->invoice->amount : '' }}">
                                                        <input type="hidden" name="discount[]" value="{{ !empty($line->invoice) ? $line->invoice->discount : '' }}">
                                                        <input type="hidden" name="canAddDiscount[]" value="{{ !empty($line->invoice) && $line->invoice->canAddDiscount() ? 1 : 0 }}">
                                                        <input type="hidden" name="discountInprocess[]" value="{{ !empty($line->invoice) ? $line->invoice->getDiscountInprocess() : '' }}">
                                                        <input type="hidden" name="totalLine[]" value="{{ !empty($line->invoice) ? $line->invoice->totalInvoice() : '' }}">
                                                        <input type="hidden" name="remainingLine[]" value="{{ !empty($line->invoice) ? $line->invoice->remaining() : '' }}">
                                                    </td>
                                                    @endif
                                                </tr>
                                                <?php $indexLine++; ?>

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

                                @if(!empty($model->batch_invoice_header_id) && ($model->isOpen() || $model->isClosed()))
                                    <a href="{{ URL($url . '/print-pdf/' . $model->batch_invoice_header_id) }}" target="_blank" data-toggle="tooltip" class="btn btn-sm btn-success" data-original-title="{{ trans('shared/common.print') }}">
                                        <i class="fa fa-print"></i> {{ trans('shared/common.print') }}
                                    </a>
                                @endif

                                @if($model->isOpen())
                                    <button type="submit" class="btn btn-sm btn-primary"><i class="fa fa-save"></i> {{ trans('shared/common.save') }}</button>
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
<div id="modal-lov-customer" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title text-center">{{ trans('shared/common.customer') }}</h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-sm-12">
                        <div class="form-group search-lov">
                            <label for="searchCustomer" class="col-sm-1 col-sm-offset-8 control-label">{{ trans('shared/common.search') }}</label>
                            <div class="col-sm-3">
                                <input type="text" class="form-control input-xs" id="searchCustomer" name="searchCustomer">
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-12">
                        <table id="table-lov-customer" class="table table-hover table-striped table-bordered" cellspacing="0" width="100%">
                            <thead>
                                <tr>
                                    <th>{{ trans('operational/fields.customer-name') }}</th>
                                    <th>{{ trans('shared/common.address') }}</th>
                                    <th>{{ trans('shared/common.phone') }}</th>
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

<div id="modal-lov-invoice" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title text-center">{{ trans('accountreceivables/fields.invoice') }}</h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-sm-12">
                        <div class="form-group search-lov">
                            <label for="searchInvoice" class="col-sm-1 col-sm-offset-8 control-label">{{ trans('shared/common.search') }}</label>
                            <div class="col-sm-3">
                                <input type="text" class="form-control input-xs" id="searchInvoice" name="searchInvoice">
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-12">
                        <table id="table-lov-invoice" class="table table-hover table-striped table-bordered" cellspacing="0" width="100%">
                            <thead>
                                <tr>
                                    <th width="50px`"><input type="checkbox" id="check-all-invoice"></th>
                                    <th>{{ trans('accountreceivables/fields.invoice') }}<hr/>{{ trans('shared/common.type') }}</th>
                                    <th>{{ trans('operational/fields.resi-number') }}<hr/>{{ trans('operational/fields.route') }}</th>
                                    <th>{{ trans('shared/common.customer') }}<hr/>{{ trans('operational/fields.sender') }}</th>
                                    <th>{{ trans('shared/common.customer') }}<hr/>{{ trans('operational/fields.receiver') }}</th>
                                    <th>{{ trans('operational/fields.amount') }}<hr/>{{ trans('operational/fields.discount') }}</th>
                                    <th>{{ trans('accountreceivables/fields.total') }}<hr/>{{ trans('accountreceivables/fields.remaining') }}</th>
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
                <button type="button" class="btn btn-sm btn-primary" id="select-invoice">{{ trans('shared/common.submit') }}</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
@parent()
<script type="text/javascript">
var indexLine = {{ $indexLine }};

$(document).on('ready', function(){
    /** HEADER **/
    $('#discountPersen').on('keyup', keyupDiscountPersen);

    $('#remove-customer').on('click', removeCustomer);
    $('#show-lov-customer').on('click', showLovCustomer);
    $('#searchCustomer').on('keyup', loadLovCustomer);
    $('#table-lov-customer tbody').on('click', 'tr', selectCustomer);

    /** LINE **/
    $('#clear-lines').on('click', clearLines);
    $('#add-line').on('click', showLovInvoice);
    $('#searchInvoice').on('keyup', loadLovInvoice);
    $('#select-invoice').on('click', selectInvoice);
    $('.delete-line').on('click', deleteLine);
});

var keyupDiscountPersen = function() {
    var discountPersen = parseInt($('#discountPersen').val().split(',').join('')) || 0;
    var totalDiscountInprocess = 0;

    $('#table-line tbody tr').each(function() {
        var canAddDiscount = parseInt($(this).find('[name="canAddDiscount[]"]').val());
        if (canAddDiscount) {
            var amountLine = parseInt($(this).find('[name="amountLine[]"]').val());
            var discountInprocess = Math.ceil(discountPersen / 10000 * amountLine) * 100; // persentase dibulatkan keatas kelipatan 100
            totalDiscountInprocess += discountInprocess;

            $(this).find('[name="discountInprocess[]"]').val(discountInprocess);
            $(this).find('.discountInprocessLine').html(discountInprocess.formatMoney(0));
        }
    });

    $('#totalDiscountInprocess').val(totalDiscountInprocess.formatMoney(0));
};

var clearLines = function() {
    $('#table-line tbody').html('');
    calculateTotal();
};

var removeCustomer = function() {
    $('#customerId').val('');
    $('#customerName').val('');
};

var showLovCustomer = function() {
    $('#searchCustomer').val('');
    loadLovCustomer(function() {
        $('#modal-lov-customer').modal('show');
    });
};

var xhrCustomer;
var loadLovCustomer = function(callback) {
    if(xhrCustomer && xhrCustomer.readyState != 4){
        xhrCustomer.abort();
    }
    xhrCustomer = $.ajax({
        url: '{{ URL($url.'/get-json-customer') }}',
        data: {search: $('#searchCustomer').val()},
        success: function(data) {
            $('#table-lov-customer tbody').html('');
            data.forEach(function(item) {
                $('#table-lov-customer tbody').append(
                    '<tr data-json=\'' + JSON.stringify(item) + '\'>\
                        <td>' + item.customer_name + '</td>\
                        <td>' + item.address + '</td>\
                        <td>' + item.phone_number + '</td>\
                    </tr>'
                );
            });

            if (typeof(callback) == 'function') {
                callback();
            }
        }
    });
};

var selectCustomer = function() {
    var data = $(this).data('json');
    $('#customerId').val(data.customer_id);
    $('#customerName').val(data.customer_name);
    $('#billTo').val(data.customer_name);
    $('#billToAddress').val(data.address);
    $('#billToPhone').val(data.phone_number);
    $('#table-line tbody').html('');
    calculateTotal()

    $('#modal-lov-customer').modal('hide');
};

var addLine = function() {
    clearFormLine();
    $('#title-modal-line').html('{{ trans('shared/common.add') }}');
    $('#submit-modal-line').html('{{ trans('shared/common.add') }}');
    $('#modal-line').modal('show');
};

var clearFormLine = function() {
    $('#indexFormLine').val('');
    $('#lineId').val('');
    $('#invoiceHeaderId').val('');
    $('#invoiceNumber').val('');
    $('#invoiceType').val('');
    $('#customerLine').val('');
    $('#billToLine').val('');
    $('#amountLine').val('');
    $('#discount').val('');
    $('#totalLine').val('');
    $('#invoiceNumber').parent().parent().removeClass('has-error');
}

var showLovInvoice = function() {
    $('#searchInvoice').val('');
    $('#check-all-invoice').iCheck('uncheck');
    loadLovInvoice(function() {
        $('#modal-lov-invoice').modal('show');
    });
};

var xhrInvoice;
var loadLovInvoice = function(callback) {
    if(xhrInvoice && xhrInvoice.readyState != 4){
        xhrInvoice.abort();
    }
    xhrInvoice = $.ajax({
        url: '{{ URL($url.'/get-json-invoice') }}',
        data: {search: $('#searchInvoice').val(), customerId: $('#customerId').val(), id: $('#id').val()},
        success: function(data) {
            $('#table-lov-invoice tbody').html('');
            data.forEach(function(item) {
                var customerSender = item.customer_sender_name !== null ? item.customer_sender_name : '&nbsp;';
                var customerReceiver = item.customer_receiver_name !== null ? item.customer_receiver_name : '&nbsp;';
                $('#table-lov-invoice tbody').append(
                    '<tr data-json=\'' + JSON.stringify(item) + '\'>\
                        <td class="text-center"><input type="checkbox" name="checkInvoice[]"></td>\
                        <td>' + item.invoice_number + '<hr/>' + item.type + '</td>\
                        <td>' + item.resi_number + '<hr/>' + item.route_code + '</td>\
                        <td>' + customerSender + '<hr/>' + item.sender_name + '</td>\
                        <td>' + customerReceiver + '<hr/>' + item.receiver_name + '</td>\
                        <td class="text-right">' + item.total_invoice.formatMoney(0) + '<hr/>' + item.total_discount.formatMoney(0) + '</td>\
                        <td class="text-right">' + item.total.formatMoney(0) + '<hr/>' + item.remaining.formatMoney(0) + '</td>\
                    </tr>'
                );
            });

            $('input[name="checkInvoice[]"]').iCheck({checkboxClass: 'icheckbox_square-aero', radioClass: 'iradio_square-aero'});
            $('#check-all-invoice').on('ifChanged', function(){
                var $inputs = $(this).parent().parent().parent().parent().parent().find('input[type="checkbox"]');
                if (!$(this).parent('[class*="icheckbox"]').hasClass("checked")) {
                    $inputs.iCheck('check');
                } else {
                    $inputs.iCheck('uncheck');
                }
            });

            if (typeof(callback) == 'function') {
                callback();
            }
        }
    });
};

var selectInvoice = function() {
    $('#table-lov-invoice tbody tr').each(function (i, row) {
        var data  = $(row).data('json');
        var exist = false;

        if (!$(row).find('input[name="checkInvoice[]"]').parent('[class*="icheckbox"]').hasClass("checked")) {
            return;
        }

        $('#table-line tbody tr').each(function (i, rowLine) {
            if (data.invoice_id == $(rowLine).find('[name="invoiceHeaderId[]"]').val()) {
                exist = true;
            }
        });

        if(exist) {
            return;
        }

        var customerSender = data.customer_sender_name !== null ? data.customer_sender_name : '&nbsp;';
        var customerReceiver = data.customer_receiver_name !== null ? data.customer_receiver_name : '&nbsp;';
        var canAddDiscount = data.can_add_discount ? 1 : 0;

        var htmlTr = '<td >' + data.invoice_number + '<hr/>' + data.type + '</td>\
                        <td >' + data.resi_number + '<hr/>' + data.route_code + '</td>\
                        <td >' + customerSender + '<hr/>' + data.sender_name + '</td>\
                        <td >' + customerReceiver + '<hr/>' + data.receiver_name + '</td>\
                        <td class="text-right"> ' + data.total_invoice.formatMoney(0) + ' <hr/> ' + data.total_discount.formatMoney(0) + ' </td>\
                        <td class="text-right discountInprocessLine"> ' + 0 + ' </td>\
                        <td class="text-right"> ' + data.total.formatMoney(0) + ' <hr/> ' + data.remaining.formatMoney(0) + ' </td>\
                        <td class="text-center">\
                            <a data-toggle="tooltip" class="btn btn-danger btn-xs delete-line" ><i class="fa fa-remove"></i></a>\
                            <input type="hidden" name="invoiceHeaderId[]" value="' + data.invoice_id + '">\
                            <input type="hidden" name="invoiceNumber[]" value="' + data.invoice_number + '">\
                            <input type="hidden" name="invoiceType[]" value="' + data.type + '">\
                            <input type="hidden" name="resiNumber[]" value="' + data.resi_number + '">\
                            <input type="hidden" name="route[]" value="' + data.route_code + '">\
                            <input type="hidden" name="customerSender[]" value="' + customerSender + '">\
                            <input type="hidden" name="sender[]" value="' + data.sender_name + '">\
                            <input type="hidden" name="customerReceiver[]" value="' + customerReceiver + '">\
                            <input type="hidden" name="receiver[]" value="' + data.receiver_name + '">\
                            <input type="hidden" name="amountLine[]" value="' + data.total_invoice + '">\
                            <input type="hidden" name="discount[]" value="' + data.total_discount + '">\
                            <input type="hidden" name="canAddDiscount[]" value="' + canAddDiscount + '">\
                            <input type="hidden" name="discountInprocess[]" value="' + 0 + '">\
                            <input type="hidden" name="totalLine[]" value="' + data.total + '">\
                            <input type="hidden" name="remainingLine[]" value="' + data.remaining + '">\
                        </td>';

        $('#table-line tbody').append(
            '<tr>' + htmlTr + '</tr>'
        );

        $('.delete-line').on('click', deleteLine);

        keyupDiscountPersen();
        calculateTotal();
    });

    $('#modal-lov-invoice').modal('hide');
};

var deleteLine = function() {
    $(this).parent().parent().remove();
    calculateTotal();
};

var calculateTotal = function() {
    var total = 0;

    $('#table-line tbody tr').each(function (i, row) {
        total += parseInt($(row).find('[name="totalLine[]"]').val());
    });

    $('#total').val(total.formatMoney(0));
};

</script>
@endsection
