@extends('layouts.master')

@section('title', trans('accountreceivables/menu.invoice-pickup'))

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="widget">
            <div class="widget-header">
                <h2><i class="fa fa-credit-card"></i> <strong>{{ $title }}</strong> {{ trans('accountreceivables/menu.invoice-pickup') }}</h2>
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
                        <input type="hidden" id="id" name="id" value="{{ $model->inv_ar_header_id }}">
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
                                        <label for="invoiceNumber" class="col-sm-4 control-label">{{ trans('accountreceivables/fields.invoice-number') }} </label>
                                        <div class="col-sm-8">
                                            <input type="text" class='form-control' id="invoiceNumber" name="invoiceNumber" value="{{ $model->inv_ar_number }}" disabled>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="invoiceDate" class="col-sm-4 control-label">{{ trans('shared/common.tanggal') }}</label>
                                        <div class="col-sm-8">
                                            <div class="input-group">
                                                <?php $invoiceDate = new \DateTime($model->created_date); ?>
                                                <input type="text" id="invoiceDate" name="invoiceDate" class="form-control datepicker-input" data-mask="99-99-9999" placeholder="dd-mm-yyyy" value="{{ $invoiceDate->format('d-m-Y') }}" disabled>
                                                <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                            </div>
                                        </div>
                                    </div>
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
                                </div>
                                <div class="col-sm-6 portlets">
                                    <div class="form-group">
                                        <label for="totalInvoice" class="col-sm-4 control-label">{{ trans('accountreceivables/fields.total-invoice') }} </label>
                                        <div class="col-sm-8">
                                            <input type="text" class='form-control currency' id="totalInvoice" name="totalInvoice" value="{{ count($errors) > 0 ? str_replace(',', '', old('totalInvoice')) : $model->totalInvoice() }}" readonly>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="totalDiscount" class="col-sm-4 control-label">{{ trans('accountreceivables/fields.total-discount') }} </label>
                                        <div class="col-sm-8">
                                            <input type="text" class='form-control currency' id="totalDiscount" name="totalDiscount" value="{{ count($errors) > 0 ? str_replace(',', '', old('totalDiscount')) : $model->totalDiscount() }}" readonly>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="totalExtraPrice" class="col-sm-4 control-label">{{ trans('accountreceivables/fields.total-extra-price') }} </label>
                                        <div class="col-sm-8">
                                            <input type="text" class='form-control currency' id="totalExtraPrice" name="totalExtraPrice" value="{{ count($errors) > 0 ? str_replace(',', '', old('totalExtraPrice')) : $model->totalExtraPrice() }}" readonly>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="total" class="col-sm-4 control-label">{{ trans('accountreceivables/fields.total') }} </label>
                                        <div class="col-sm-8">
                                            <input type="text" class='form-control currency' id="total" name="total" value="{{ count($errors) > 0 ? str_replace(',', '', old('total')) : $model->total() }}" readonly>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="status" class="col-sm-4 control-label">{{ trans('shared/common.status') }}</label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="status" name="status" value="{{ $model->status }}" readonly>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="requestApproveNote" class="col-sm-4 control-label">{{ trans('shared/common.request-approve-note') }}</label>
                                        <div class="col-sm-8">
                                            <textarea class="form-control" id="requestApproveNote" name="requestApproveNote" {{ !$model->isOpen() ? 'readonly' : '' }}>{{ count($errors) > 0 ? old('requestApproveNote') : $model->req_approve_note }}</textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="tabLine">
                                <div class="col-sm-12 portlets">
                                     @if ($model->isOpen())
                                    <div class="data-table-toolbar">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="toolbar-btn-action">
                                                    <a class="btn btn-sm btn-primary add-line">
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
                                                        {{ trans('operational/fields.resi-number') }}<hr/>
                                                        {{ trans('marketing/fields.pickup-request-number') }}
                                                    </th>
                                                    <th>
                                                        {{ trans('operational/fields.customer') }}<hr/>
                                                        {{ trans('operational/fields.sender') }}
                                                    </th>
                                                    <th>
                                                        {{ trans('operational/fields.customer') }}<hr/>
                                                        {{ trans('operational/fields.receiver') }}
                                                    </th>
                                                    <th>{{ trans('operational/fields.payment') }}</th>
                                                    <th>{{ trans('operational/fields.total-amount') }}</th>
                                                    <th>{{ trans('operational/fields.discount') }}</th>
                                                    <th>{{ trans('accountreceivables/fields.extra-price') }}</th>
                                                    <th>{{ trans('accountreceivables/fields.total') }}</th>

                                                    @if($model->isOpen())
                                                    <th width="60px">{{ trans('shared/common.action') }}</th>
                                                    @endif
                                                </tr>
                                            </thead>
                                            <tbody>
                                               <?php $indexLine = 0; ?>
                                                @if(count($errors) > 0)
                                                @for($i = 0; $i < count(old('lineId', [])); $i++)
                                                <tr data-index-line="{{ $indexLine }}">
                                                    <td >
                                                        {{ old('resiNumber')[$i] }}<hr/>
                                                        {{ old('pickupRequestNumber')[$i] }}
                                                    </td>
                                                    <td >
                                                        {{ old('customerLine')[$i] }}<hr/>
                                                        {{ old('sender')[$i] }}
                                                    </td>
                                                    <td >
                                                        {{ old('customerReceiverLine')[$i] }}<hr/>
                                                        {{ old('receiver')[$i] }}
                                                    </td>
                                                    <td > {{ old('payment')[$i] }} </td>
                                                    <td class="text-right"> {{ number_format(intval(old('amount')[$i])) }} </td>
                                                    <td class="text-right"> {{ number_format(intval(old('discount')[$i])) }} </td>
                                                    <td class="text-right"> {{ number_format(intval(old('extraPrice')[$i])) }} </td>
                                                    <td class="text-right"> {{ number_format(intval(old('totalAmount')[$i])) }} </td>
                                                    @if($model->isOpen())
                                                    <td class="text-center">
                                                        <a data-toggle="tooltip" class="btn btn-xs btn-warning edit-line" ><i class="fa fa-pencil"></i></a>
                                                        <a data-toggle="tooltip" class="btn btn-danger btn-xs delete-line" ><i class="fa fa-remove"></i></a>
                                                        <input type="hidden" name="lineId[]" value="{{ old('lineId')[$i] }}">
                                                        <input type="hidden" name="resiId[]" value="{{ old('resiId')[$i] }}">
                                                        <input type="hidden" name="resiNumber[]" value="{{ old('resiNumber')[$i] }}">
                                                        <input type="hidden" name="pickupRequestId[]" value="{{ old('pickupRequestId')[$i] }}">
                                                        <input type="hidden" name="pickupRequestNumber[]" value="{{ old('resiNumber')[$i] }}">
                                                        <input type="hidden" name="routeCode[]" value="{{ old('routeCode')[$i] }}">
                                                        <input type="hidden" name="customerLine[]" value="{{ old('customerLine')[$i] }}">
                                                        <input type="hidden" name="sender[]" value="{{ old('sender')[$i] }}">
                                                        <input type="hidden" name="senderAddress[]" value="{{ old('senderAddress')[$i] }}">
                                                        <input type="hidden" name="customerReceiverLine[]" value="{{ old('customerReceiverLine')[$i] }}">
                                                        <input type="hidden" name="receiver[]" value="{{ old('receiver')[$i] }}">
                                                        <input type="hidden" name="receiverAddress[]" value="{{ old('receiverAddress')[$i] }}">
                                                        <input type="hidden" name="payment[]" value="{{ old('payment')[$i] }}">
                                                        <input type="hidden" name="amount[]" value="{{ old('amount')[$i] }}">
                                                        <input type="hidden" name="discount[]" value="{{ old('discount')[$i] }}">
                                                        <input type="hidden" name="extraPrice[]" value="{{ old('extraPrice')[$i] }}">
                                                        <input type="hidden" name="totalAmount[]" value="{{ old('totalAmount')[$i] }}">
                                                    </td>
                                                    @endif
                                                </tr>
                                                <?php $indexLine++; ?>
                                                @endfor

                                                @else
                                                @foreach($model->lines()->get() as $line)
                                                <tr data-index-line="{{ $indexLine }}">
                                                    <td >
                                                        {{ !empty($line->resi) ? $line->resi->resi_number : '' }}<hr/>
                                                        {{ !empty($line->resi->pickupRequest) ? $line->resi->pickupRequest->pickup_request_number : '' }}
                                                    </td>
                                                    <td >
                                                        {{ !empty($line->resi->customer) ? $line->resi->customer->customer_name : '' }}<hr/>
                                                        {{ !empty($line->resi) ? $line->resi->sender_name : '' }}
                                                    </td>
                                                    <td >
                                                        {{ !empty($line->resi->customerReceiver) ? $line->resi->customerReceiver->customer_name : '' }}<hr/>
                                                        {{ !empty($line->resi) ? $line->resi->receiver_name : '' }}
                                                    </td>
                                                    <td > {{ !empty($line->resi) ? $line->resi->payment : '' }} </td>
                                                    <td class="text-right"> {{ number_format($line->amount) }} </td>
                                                    <td class="text-right"> {{ number_format($line->discount) }} </td>
                                                    <td class="text-right"> {{ number_format($line->extra_price) }} </td>
                                                    <td class="text-right"> {{ number_format($line->totalAmount()) }} </td>
                                                    @if($model->isOpen())
                                                    <td class="text-center">
                                                        <a data-toggle="tooltip" class="btn btn-xs btn-warning edit-line" ><i class="fa fa-pencil"></i></a>
                                                        <a data-toggle="tooltip" class="btn btn-danger btn-xs delete-line" ><i class="fa fa-remove"></i></a>
                                                        <input type="hidden" name="lineId[]" value="{{ $line->inv_ar_line_id }}">
                                                        <input type="hidden" name="resiId[]" value="{{ $line->resi_header_id }}">
                                                        <input type="hidden" name="resiNumber[]" value="{{ !empty($line->resi) ? $line->resi->resi_number : '' }}">
                                                        <input type="hidden" name="pickupRequestId[]" value="{{ $line->pickup_request_id }}">
                                                        <input type="hidden" name="pickupRequestNumber[]" value="{{ !empty($line->doLine->header) ? $line->doLine->header->do_number : '' }}">
                                                        <input type="hidden" name="routeCode[]" value="{{ !empty($line->resi->route) ? $line->resi->route->route_code : '' }}">
                                                        <input type="hidden" name="customerLine[]" value="{{ !empty($line->resi->customer) ? $line->resi->customer->customer_name : '' }}">
                                                        <input type="hidden" name="sender[]" value="{{ !empty($line->resi) ? $line->resi->sender_name : '' }}">
                                                        <input type="hidden" name="senderAddress[]" value="{{ !empty($line->resi) ? $line->resi->sender_address : '' }}">
                                                        <input type="hidden" name="customerReceiverLine[]" value="{{ !empty($line->resi->customerReceiver) ? $line->resi->customerReceiver->customer_name : '' }}">
                                                        <input type="hidden" name="receiver[]" value="{{ !empty($line->resi) ? $line->resi->receiver_name : '' }}">
                                                        <input type="hidden" name="receiverAddress[]" value="{{ !empty($line->resi) ? $line->resi->receiver_address : '' }}">
                                                        <input type="hidden" name="payment[]" value="{{ !empty($line->resi) ? $line->resi->payment : '' }}">
                                                        <input type="hidden" name="amount[]" value="{{ $line->amount }}">
                                                        <input type="hidden" name="discount[]" value="{{ $line->discount }}">
                                                        <input type="hidden" name="extraPrice[]" value="{{ $line->extra_price }}">
                                                        <input type="hidden" name="totalAmount[]" value="{{ $line->totalAmount() }}">
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

                                @if ($model->isOpen())
                                <button type="submit" class="btn btn-sm btn-primary"><i class="fa fa-save"></i> {{ trans('shared/common.save') }}</button>
                                @endif

                                @if ($model->isOpen())
                                <button type="submit" name="btn-request-approve" class="btn btn-sm btn-info"><i class="fa fa-share"></i> {{ trans('shared/common.request-approve') }}</button>
                                @endif

                                @if ($model->isOpen() && Gate::check('access', [$resource, 'approve']))
                                <button type="submit" name="btn-approve" class="btn btn-sm btn-success"><i class="fa fa-save"></i> {{ trans('shared/common.approve') }}</button>
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

<div id="modal-line" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title text-center"><span id="title-modal-line">{{ trans('shared/common.add') }}</span> {{ trans('shared/common.line') }}</h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-sm-12">
                        <div id="horizontal-form">
                            <form  role="form" id="add-form" class="form-horizontal" method="post">
                                <input type="hidden" name="indexFormLine" id="indexFormLine" value="">
                                <input type="hidden" name="lineId" id="lineId" value="">
                                <div class="col-sm-6 portlets">
                                    <div class="form-group">
                                        <label for="resiNumber" class="col-sm-4 control-label">
                                            {{ trans('operational/fields.resi-number') }} <span class="required">*</span>
                                        </label>
                                        <div class="col-sm-8">
                                            <div class="input-group">
                                                <input type="hidden" name="resiId" id="resiId" value="">
                                                <input type="text" class="form-control" id="resiNumber" name="resiNumber" readonly>
                                                <span class="btn input-group-addon" id="show-lov-resi"><i class="fa fa-search"></i></span>
                                            </div>
                                        </div>
                                        <span class="help-block"></span>
                                    </div>
                                    <div class="form-group">
                                        <label for="pickupRequestNumber" class="col-sm-4 control-label">
                                            {{ trans('marketing/fields.pickup-request-number') }} <span class="required">*</span>
                                        </label>
                                        <div class="col-sm-8">
                                            <input type="hidden" name="pickupRequestId" id="pickupRequestId" value="">
                                            <input type="text" class="form-control" id="pickupRequestNumber" name="pickupRequestNumber" readonly>
                                        </div>
                                        <span class="help-block"></span>
                                    </div>
                                    <div class="form-group">
                                        <label for="routeCode" class="col-sm-4 control-label">{{ trans('operational/fields.route-code') }}</label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="routeCode" name="routeCode" value="" readonly>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="customerLine" class="col-sm-4 control-label">{{ trans('operational/fields.customer') }}</label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="customerLine" name="customerLine" value="" readonly>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="sender" class="col-sm-4 control-label">{{ trans('operational/fields.sender') }}</label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="sender" name="sender" value="" readonly>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="senderAddress" class="col-sm-4 control-label">{{ trans('operational/fields.sender-address') }}</label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="senderAddress" name="senderAddress" value="" readonly>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="customerReceiverLine" class="col-sm-4 control-label">{{ trans('operational/fields.customer') }}</label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="customerReceiverLine" name="customerReceiverLine" value="" readonly>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="receiver" class="col-sm-4 control-label">{{ trans('operational/fields.receiver') }}</label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="receiver" name="receiver" value="" readonly>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="receiverAddress" class="col-sm-4 control-label">{{ trans('operational/fields.receiver-address') }}</label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="receiverAddress" name="receiverAddress" value="" readonly>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-6 portlets">
                                    <div class="form-group">
                                        <label for="payment" class="col-sm-4 control-label">{{ trans('operational/fields.payment') }}</label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control currency" id="payment" name="payment" readonly>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="amount" class="col-sm-4 control-label">{{ trans('operational/fields.amount') }}</label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control currency" id="amount" name="amount" readonly>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="discount" class="col-sm-4 control-label">{{ trans('operational/fields.discount') }}</label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control currency" id="discount" name="discount">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="extraPrice" class="col-sm-4 control-label">{{ trans('accountreceivables/fields.extra-price') }}</label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control currency" id="extraPrice" name="extraPrice">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="totalAmount" class="col-sm-4 control-label">{{ trans('operational/fields.total-amount') }}</label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control currency" id="totalAmount" name="totalAmount" readonly>
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

<div id="modal-lov-resi" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title text-center">{{ trans('operational/fields.resi') }}</h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-sm-12">
                        <div class="form-group search-lov">
                            <label for="searchResi" class="col-sm-1 col-sm-offset-8 control-label">{{ trans('shared/common.search') }}</label>
                            <div class="col-sm-3">
                                <input type="text" class="form-control input-xs" id="searchResi" name="searchResi">
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-12">
                        <table id="table-lov-resi" class="table table-hover table-striped table-bordered" cellspacing="0" width="100%">
                            <thead>
                                <tr>
                                    <th>{{ trans('operational/fields.resi-number') }}<hr/>{{ trans('marketing/fields.pickup-request-number') }}</th>
                                    <th>{{ trans('operational/fields.route-code') }}</th>
                                    <th>{{ trans('operational/fields.customer') }}<hr/>{{ trans('operational/fields.sender') }}</th>
                                    <th>{{ trans('operational/fields.customer') }}<hr/>{{ trans('operational/fields.receiver') }}</th>
                                    <th>{{ trans('operational/fields.pickup-cost') }}</th>
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
var indexLine = {{ $indexLine }};

$(document).on('ready', function(){
    /** HEADER **/
    $('#remove-customer').on('click', removeCustomer);
    $('#show-lov-customer').on('click', showLovCustomer);
    $('#searchCustomer').on('keyup', loadLovCustomer);
    $('#table-lov-customer tbody').on('click', 'tr', selectCustomer);

    /** LINE **/
    $('.add-line').on('click', addLine);
    $('#show-lov-resi').on('click', showLovResi);
    $('#searchResi').on('keyup', loadLovResi);
    $('#table-lov-resi tbody').on('click', 'tr', selectResi);
    $("#discount").on('keyup', calculateAmountLine);
    $("#extraPrice").on('keyup', calculateAmountLine);
    $("#save-line").on('click', saveLine);
    $('.edit-line').on('click', editLine);
    $('.delete-line').on('click', deleteLine);
});

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

    $('#modal-lov-customer').modal('hide');
};

var calculateAmountLine = function() {
    var amount = currencyToInt($('#amount').val());
    var discount = currencyToInt($('#discount').val());
    var extraPrice = currencyToInt($('#extraPrice').val());
    var totalAmount = amount - discount + extraPrice;

    $('#amount').val(amount.formatMoney(0));
    $('#discount').val(discount.formatMoney(0));
    $('#extraPrice').val(extraPrice.formatMoney(0));
    $('#totalAmount').val(totalAmount.formatMoney(0));
};

var addLine = function() {
    clearFormLine();
    $('#title-modal-line').html('{{ trans('shared/common.add') }}');
    $('#submit-modal-line').html('{{ trans('shared/common.add') }}');
    $('#modal-line').modal('show');
    calculateAmountLine();
    $('#resiNumber').focus();
};

var editLine = function() {
    clearFormLine();

    var $tr = $(this).parent().parent();
    var indexFormLine = $tr.data('index-line');
    var lineId = $tr.find('[name="lineId[]"]').val();
    var resiId = $tr.find('[name="resiId[]"]').val();
    var resiNumber = $tr.find('[name="resiNumber[]"]').val();
    var routeCode = $tr.find('[name="routeCode[]"]').val();
    var customerLine = $tr.find('[name="customerLine[]"]').val();
    var sender = $tr.find('[name="sender[]"]').val();
    var senderAddress = $tr.find('[name="senderAddress[]"]').val();
    var customerReceiverLine = $tr.find('[name="customerReceiverLine[]"]').val();
    var receiver = $tr.find('[name="receiver[]"]').val();
    var receiverAddress = $tr.find('[name="receiverAddress[]"]').val();
    var payment = $tr.find('[name="payment[]"]').val();
    var amount = parseInt($tr.find('[name="amount[]"]').val());
    var discount = parseInt($tr.find('[name="discount[]"]').val());
    var extraPrice = parseInt($tr.find('[name="extraPrice[]"]').val());
    var totalAmount = parseInt($tr.find('[name="totalAmount[]"]').val());

    $('#indexFormLine').val(indexFormLine);
    $('#lineId').val(lineId);
    $('#resiId').val(resiId);
    $('#resiNumber').val(resiNumber);
    $('#routeCode').val(routeCode);
    $('#customerLine').val(customerLine);
    $('#sender').val(sender);
    $('#senderAddress').val(senderAddress);
    $('#customerReceiverLine').val(customerReceiverLine);
    $('#receiver').val(receiver);
    $('#receiverAddress').val(receiverAddress);
    $('#payment').val(payment);
    $('#amount').val(amount.formatMoney(0));
    $('#discount').val(discount.formatMoney(0));
    $('#extraPrice').val(extraPrice.formatMoney(0));
    $('#totalAmount').val(totalAmount.formatMoney(0));

    $('#title-modal-line').html('{{ trans('shared/common.edit') }}');
    $('#submit-modal-line').html('{{ trans('shared/common.edit') }}');
    $('#modal-line').modal("show");
};

var clearFormLine = function() {
    $("#indexFormLine").val('');
    $("#lineId").val('');
    $("#resiId").val('');
    $("#resiNumber").val('');
    $('#routeCode').val('');
    $('#customerLine').val('');
    $('#sender').val('');
    $('#senderAddress').val('');
    $('#customerReceiverLine').val('');
    $('#receiver').val('');
    $('#receiverAddress').val('');
    $('#payment').val('');
    $('#amount').val('');
    $('#discount').val('');
    $('#extraPrice').val('');
    $('#totalAmount').val('');
    $('#resiNumber').parent().parent().removeClass('has-error');
}

var showLovResi = function() {
    $('#searchResi').val('');
    loadLovResi(function() {
        $('#modal-lov-resi').modal('show');
    });
};

var xhrResi;
var loadLovResi = function(callback) {
    if(xhrResi && xhrResi.readyState != 4){
        xhrResi.abort();
    }
    xhrResi = $.ajax({
        url: '{{ URL($url.'/get-json-resi') }}',
        data: {search: $('#searchResi').val(), customerId: $('#customerId').val(), id: $('#id').val()},
        success: function(data) {
            $('#table-lov-resi tbody').html('');
            data.forEach(function(item) {
                var customerName = item.customer_name !== null ? item.customer_name : '&nbsp;';
                var customerReceiverName = item.customer_receiver_name !== null ? item.customer_receiver_name : '&nbsp;';
                $('#table-lov-resi tbody').append(
                    '<tr data-json=\'' + JSON.stringify(item) + '\'>\
                        <td>' + item.resi_number + '<hr/>' + item.pickup_request_number + '</td>\
                        <td>' + item.route_code + '</td>\
                        <td>' + customerName + '<hr/>' + item.sender_name + '</td>\
                        <td>' + customerReceiverName + '<hr/>' + item.receiver_name + '</td>\
                        <td class="text-right">' + item.pickup_cost.formatMoney(0) + '</td>\
                    </tr>'
                );
            });

            if (typeof(callback) == 'function') {
                callback();
            }
        }
    });
};

var selectResi = function() {
    var data = $(this).data('json');

    var error = false
    $('#table-line tbody tr').each(function (i, row) {
        if (data.resi_header_id == $(row).find('[name="resiId[]"]').val()) {
            $('#modal-alert').find('.alert-message').html('Resi already exist');
            $('#modal-alert').modal('show');
            error = true;
        }
    });

    if (error) {
        return;
    }

    $('#resiId').val(data.resi_header_id);
    $('#resiNumber').val(data.resi_number);
    $('#pickupRequestId').val(data.pickup_request_id);
    $('#pickupRequestNumber').val(data.pickup_request_number);
    $('#routeCode').val(data.route_code);
    $('#customerLine').val(data.customer_name);
    $('#sender').val(data.sender_name);
    $('#senderAddress').val(data.sender_address);
    $('#customerReceiverLine').val(data.customer_receiver_name);
    $('#receiver').val(data.receiver_name);
    $('#receiverAddress').val(data.receiver_address);
    $('#payment').val(data.payment);
    $('#amount').val(data.pickup_cost.formatMoney(0));
    calculateAmountLine();

    $('#modal-lov-resi').modal('hide');
};

var saveLine = function() {
    var indexFormLine = $('#indexFormLine').val();
    var resiId = $('#resiId').val();
    var error = false;

    if (resiId == '' || resiId <= 0) {
        $('#resiNumber').parent().parent().addClass('has-error');
        error = true;
    } else {
        $('#resiNumber').parent().parent().removeClass('has-error');
    }

    var resiNumber = $('#resiNumber').val();
    var pickupRequestId = $('#pickupRequestId').val();
    var pickupRequestNumber = $('#pickupRequestNumber').val();
    var routeCode = $('#routeCode').val();
    var customerLine = $('#customerLine').val();
    var sender = $('#sender').val();
    var senderAddress = $('#senderAddress').val();
    var customerReceiverLine = $('#customerReceiverLine').val();
    var receiver = $('#receiver').val();
    var receiverAddress = $('#receiverAddress').val();
    var payment = $('#payment').val();
    var amount = currencyToInt($('#amount').val());
    var discount = currencyToInt($('#discount').val());
    var extraPrice = currencyToInt($('#extraPrice').val());
    var totalAmount = currencyToInt($('#totalAmount').val());

    var htmlTr = '<td >' + resiNumber + ' <hr/> ' + pickupRequestNumber + '</td>\
                    <td>' + customerLine + ' <hr/> ' + sender + '</td>\
                    <td>' + customerReceiverLine + ' <hr/> ' + receiver + '</td>\
                    <td>' + payment + '</td>\
                    <td class="text-right">' + amount.formatMoney(0) + '</td>\
                    <td class="text-right">' + discount.formatMoney(0) + '</td>\
                    <td class="text-right">' + extraPrice.formatMoney(0) + '</td>\
                    <td class="text-right">' + totalAmount.formatMoney(0) + '</td>\
                    <td class="text-center">\
                        <a data-toggle="tooltip" class="btn btn-xs btn-warning edit-line" ><i class="fa fa-pencil"></i></a> \
                        <a data-toggle="tooltip" class="btn btn-danger btn-xs delete-line" ><i class="fa fa-remove"></i></a>\
                        <input type="hidden" name="lineId[]" value="">\
                        <input type="hidden" name="resiId[]" value="' + resiId + '">\
                        <input type="hidden" name="resiNumber[]" value="' + resiNumber + '">\
                        <input type="hidden" name="pickupRequestId[]" value="' + pickupRequestId + '">\
                        <input type="hidden" name="pickupRequestNumber[]" value="' + pickupRequestNumber + '">\
                        <input type="hidden" name="routeCode[]" value="' + routeCode + '">\
                        <input type="hidden" name="customerLine[]" value="' + customerLine + '">\
                        <input type="hidden" name="sender[]" value="' + sender + '">\
                        <input type="hidden" name="senderAddress[]" value="' + senderAddress + '">\
                        <input type="hidden" name="customerReceiverLine[]" value="' + customerReceiverLine + '">\
                        <input type="hidden" name="receiver[]" value="' + receiver + '">\
                        <input type="hidden" name="receiverAddress[]" value="' + receiverAddress + '">\
                        <input type="hidden" name="payment[]" value="' + payment + '">\
                        <input type="hidden" name="amount[]" value="' + amount + '">\
                        <input type="hidden" name="discount[]" value="' + discount + '">\
                        <input type="hidden" name="extraPrice[]" value="' + extraPrice + '">\
                        <input type="hidden" name="totalAmount[]" value="' + totalAmount + '">\
                    </td>';

    if (indexFormLine != '') {
        $('tr[data-index-line="' + indexFormLine + '"]').html(htmlTr);
        indexLine++;
    } else {
        $('#table-line tbody').append(
            '<tr data-index-line="' + indexLine + '">' + htmlTr + '</tr>'
        );
        indexLine++;
    }

    $('.edit-line').on('click', editLine);
    $('.delete-line').on('click', deleteLine);
    calculateTotal();

    indexLine++;
    $('#modal-line').modal("hide");
};

var deleteLine = function() {
    $(this).parent().parent().remove();
    calculateTotal();
};

var calculateTotal = function() {
    var totalInvoice = 0;
    var totalDiscount = 0;
    var totalExtraPrice = 0;
    var total = 0;

    $('#table-line tbody tr').each(function (i, row) {
        totalInvoice += parseInt($(row).find('[name="amount[]"]').val());
        totalDiscount += parseInt($(row).find('[name="discount[]"]').val());
        totalExtraPrice += parseInt($(row).find('[name="extraPrice[]"]').val());
    });

    $('#totalInvoice').val(totalInvoice.formatMoney(0));
    $('#totalDiscount').val(totalDiscount.formatMoney(0));
    $('#totalExtraPrice').val(totalExtraPrice.formatMoney(0));
    $('#total').val((totalInvoice - totalDiscount + totalExtraPrice).formatMoney(0));
};

</script>
@endsection
