@extends('layouts.master')

@section('title', trans('accountreceivables/menu.cek-giro'))

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="widget">
            <div class="widget-header">
                <h2><i class="fa fa-credit-card"></i> <strong>{{ $title }}</strong> {{ trans('accountreceivables/menu.cek-giro') }}</h2>
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
                        <input type="hidden" id="id" name="id" value="{{ $model->cek_giro_header_id }}">
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
                                        <label for="cekGiroNumber" class="col-sm-4 control-label">{{ trans('accountreceivables/fields.cek-giro-number') }}</label>
                                        <div class="col-sm-8">
                                            <input type="text" class='form-control' id="cekGiroNumber" name="cekGiroNumber" value="{{ $model->cek_giro_number }}" disabled>
                                        </div>
                                    </div>
                                    <div class="form-group {{ $errors->has('cekGiroAccountNumber') ? 'has-error' : '' }}">
                                        <label for="cekGiroAccountNumber" class="col-sm-4 control-label">
                                            {{ trans('accountreceivables/fields.cek-giro-account-number') }} <span class="required">*</span>
                                        </label>
                                        <div class="col-sm-8">
                                            <input type="text" class='form-control' id="cekGiroAccountNumber" name="cekGiroAccountNumber" value="{{ count($errors) > 0 ? old('cekGiroAccountNumber') : $model->cek_giro_account_number }}" {{ !empty($model->cek_giro_header_id) ? 'readonly' : '' }}>
                                            @if($errors->has('cekGiroAccountNumber'))
                                                <span class="help-block">{{ $errors->first('cekGiroAccountNumber') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    <?php $type = count($errors) > 0 ? old('type') : $model->type ?>
                                    <div class="form-group {{ $errors->has('type') ? 'has-error' : '' }}">
                                        <label for="type" class="col-sm-4 control-label">{{ trans('shared/common.type') }} <span class="required">*</span></label>
                                        <div class="col-sm-8">
                                            <select id="type" name="type" class="form-control" {{ !empty($model->cek_giro_header_id) ? 'readonly' : '' }}>
                                                <option value="">Select {{ trans('shared/common.type') }}</option>
                                                @foreach($optionType as $option)
                                                    <option value="{{ $option }}" {{ $option == $type ? 'selected' : '' }}>{{ $option }}</option>
                                                @endforeach
                                            </select>
                                            @if($errors->has('type'))
                                                <span class="help-block">{{ $errors->first('type') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    <?php
                                    $strCekGiroDate = count($errors) > 0 ? old('cekGiroDate') : $model->cek_giro_date;
                                    $cekGiroDate    = count($errors) == 0 || !empty($strCekGiroDate) ? new \DateTime($strCekGiroDate) : null;
                                    ?>
                                    <div class="form-group {{ $errors->has('cekGiroDate') ? 'has-error' : '' }}">
                                        <label for="cekGiroDate" class="col-sm-4 control-label">{{ trans('shared/common.tanggal') }} <span class="required">*</span></label>
                                        <div class="col-sm-8">
                                            <div class="input-group">
                                                <input type="text" id="cekGiroDate" name="cekGiroDate" class="form-control datepicker-input" data-mask="99-99-9999" placeholder="dd-mm-yyyy" value="{{ $cekGiroDate !== null ? $cekGiroDate->format('d-m-Y') : '' }}" {{ !empty($model->cek_giro_header_id) ? 'disabled' : '' }}>
                                                <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                            </div>
                                            @if($errors->has('cekGiroDate'))
                                                <span class="help-block">{{ $errors->first('cekGiroDate') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    <?php
                                    $strDueDate = count($errors) > 0 ? old('dueDate') : $model->due_date;
                                    $dueDate    = count($errors) == 0 || !empty($strDueDate) ? new \DateTime($strDueDate) : null;
                                    ?>
                                    <div class="form-group {{ $errors->has('dueDate') ? 'has-error' : '' }}">
                                        <label for="dueDate" class="col-sm-4 control-label">{{ trans('accountreceivables/fields.due-date') }} <span class="required">*</span></label>
                                        <div class="col-sm-8">
                                            <div class="input-group">
                                                <input type="text" id="dueDate" name="dueDate" class="form-control datepicker-input" data-mask="99-99-9999" placeholder="dd-mm-yyyy" value="{{ $dueDate !== null ? $dueDate->format('d-m-Y') : '' }}" {{ !empty($model->cek_giro_header_id) ? 'disabled' : '' }}>
                                                <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                            </div>
                                            @if($errors->has('dueDate'))
                                                <span class="help-block">{{ $errors->first('dueDate') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    <?php
                                    $clearingDate = !empty($model->clearing_date) ? new \DateTime($model->clearing_date) : null;
                                    ?>
                                    <div class="form-group">
                                        <label for="clearingDate" class="col-sm-4 control-label">{{ trans('accountreceivables/fields.clearing-date') }}</label>
                                        <div class="col-sm-8">
                                            <div class="input-group">
                                                <?php $clearingDate = !empty($model->clearing_date) ? new \DateTime($model->clearing_date) : null; ?>
                                                <input type="text" id="clearingDate" name="clearingDate" class="form-control datepicker-input" data-mask="99-99-9999" placeholder="dd-mm-yyyy" value="{{ $clearingDate !== null ? $clearingDate->format('d-m-Y') : '' }}" disabled>
                                                <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group {{ $errors->has('bankName') ? 'has-error' : '' }}">
                                        <label for="bankName" class="col-sm-4 control-label">{{ trans('accountreceivables/fields.bank') }} <span class="required">*</span></label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="bankName" name="bankName" value="{{ count($errors) > 0 ? old('bankName') : $model->bank_name }}" {{ !empty($model->cek_giro_header_id) ? 'readonly' : '' }}>
                                            @if($errors->has('bankName'))
                                                <span class="help-block">{{ $errors->first('bankName') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="total" class="col-sm-4 control-label">{{ trans('accountreceivables/fields.total') }} </label>
                                        <div class="col-sm-8">
                                            <input type="text" class='form-control currency' id="total" name="total" value="{{ count($errors) > 0 ? str_replace(',', '', old('total')) : $model->totalAmount() }}" readonly>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-6 portlets">
                                    <?php
                                    $customerName = $model->customer !== null ? $model->customer->customer_name : '';
                                    ?>
                                    <div class="form-group">
                                        <label for="customerName" class="col-sm-4 control-label">{{ trans('accountreceivables/fields.customer') }}</label>
                                        <div class="col-sm-8">
                                            <div class="input-group">
                                                <input type="hidden" id="customerId" name="customerId" value="{{ count($errors) > 0 ? old('customerId') : $model->customer_id }}">
                                                <input type="text" class="form-control" id="customerName" name="customerName" value="{{ count($errors) > 0 ? old('customerName') : $customerName }}" readonly>
                                                <span class="btn input-group-addon {{ empty($model->cek_giro_header_id) ? 'remove-customer' : '' }}"><i class="fa fa-remove"></i></span>
                                                <span class="btn input-group-addon" id="{{ empty($model->cek_giro_header_id) ? 'show-lov-customer' : '' }}"><i class="fa fa-search"></i></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group {{ $errors->has('personName') ? 'has-error' : '' }}">
                                        <label for="personName" class="col-sm-4 control-label">{{ trans('shared/common.person-name') }} <span class="required">*</span></label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="personName" name="personName" value="{{ count($errors) > 0 ? old('personName') : $model->person_name }}" {{ !empty($model->cek_giro_header_id) ? 'readonly' : '' }}>
                                            @if($errors->has('personName'))
                                                <span class="help-block">{{ $errors->first('personName') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="form-group {{ $errors->has('address') ? 'has-error' : '' }}">
                                        <label for="address" class="col-sm-4 control-label">{{ trans('shared/common.address') }} <span class="required">*</span></label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="address" name="address" value="{{ count($errors) > 0 ? old('address') : $model->address }}" {{ !empty($model->cek_giro_header_id) ? 'readonly' : '' }}>
                                            @if($errors->has('address'))
                                                <span class="help-block">{{ $errors->first('address') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="form-group {{ $errors->has('phoneNumber') ? 'has-error' : '' }}">
                                        <label for="phoneNumber" class="col-sm-4 control-label">{{ trans('shared/common.phone') }} <span class="required">*</span></label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="phoneNumber" name="phoneNumber" value="{{ count($errors) > 0 ? old('phoneNumber') : $model->phone_number }}" {{ !empty($model->cek_giro_header_id) ? 'readonly' : '' }}>
                                            @if($errors->has('phoneNumber'))
                                                <span class="help-block">{{ $errors->first('phoneNumber') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="description" class="col-sm-4 control-label">{{ trans('shared/common.description') }}</label>
                                        <div class="col-sm-8">
                                            <textarea class="form-control" id="description" name="description" {{ !empty($model->cek_giro_header_id) ? 'readonly' : '' }}>{{ count($errors) > 0 ? old('description') : $model->description }}</textarea>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="status" class="col-sm-4 control-label">{{ trans('accountreceivables/fields.status') }} </label>
                                        <div class="col-sm-8">
                                            <input type="text" class='form-control currency' id="status" name="status" value="{{ $model->status }}" readonly>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="tabLine">
                                <div class="col-sm-12 portlets">
                                    @if(empty($model->cek_giro_header_id))
                                    <div class="data-table-toolbar">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="toolbar-btn-action" id="toolbar-action-line">
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
                                                    <th>{{ trans('operational/fields.amount') }}</th>

                                                    @if(empty($model->cek_giro_header_id))
                                                        <th width="60px">{{ trans('shared/common.action') }}</th>
                                                    @endif
                                                </tr>
                                            </thead>
                                            <tbody>
                                               <?php $indexLine = 0; ?>
                                                @if(count($errors) > 0)
                                                @for($i = 0; $i < count(old('invoiceId', [])); $i++)
                                                <tr data-index-line="{{ $indexLine }}">
                                                    <td > {{ old('invoiceNumber')[$i] }}<hr/>{{ old('invoiceType')[$i] }} </td>
                                                    <td > {{ old('resiNumber')[$i] }}<hr/>{{ old('route')[$i] }} </td>
                                                    <td > {{ old('customerSender')[$i] }}<hr/>{{ old('sender')[$i] }} </td>
                                                    <td > {{ old('customerReceiver')[$i] }}<hr/>{{ old('receiver')[$i] }} </td>
                                                    <td class="text-right"> {{ number_format(intval(old('amountLine')[$i])) }} </td>

                                                    @if(empty($model->cek_giro_header_id))
                                                    <td class="text-center">
                                                        <a data-toggle="tooltip" class="btn btn-warning btn-xs edit-line" ><i class="fa fa-pencil"></i></a>
                                                        <a data-toggle="tooltip" class="btn btn-danger btn-xs delete-line" ><i class="fa fa-remove"></i></a>
                                                        <input type="hidden" name="invoiceId[]" value="{{ old('invoiceId')[$i] }}">
                                                        <input type="hidden" name="invoiceNumber[]" value="{{ old('invoiceNumber')[$i] }}">
                                                        <input type="hidden" name="invoiceType[]" value="{{ old('invoiceType')[$i] }}">
                                                        <input type="hidden" name="resiNumber[]" value="{{ old('resiNumber')[$i] }}">
                                                        <input type="hidden" name="route[]" value="{{ old('route')[$i] }}">
                                                        <input type="hidden" name="customerSender[]" value="{{ old('customerSender')[$i] }}">
                                                        <input type="hidden" name="sender[]" value="{{ old('sender')[$i] }}">
                                                        <input type="hidden" name="customerReceiver[]" value="{{ old('customerReceiver')[$i] }}">
                                                        <input type="hidden" name="receiver[]" value="{{ old('receiver')[$i] }}">
                                                        <input type="hidden" name="amount[]" value="{{ old('amount')[$i] }}">
                                                        <input type="hidden" name="remainingFix[]" value="{{ old('remainingFix')[$i] }}">
                                                        <input type="hidden" name="remaining[]" value="{{ old('remaining')[$i] }}">
                                                        <input type="hidden" name="amountLine[]" value="{{ old('amountLine')[$i] }}">
                                                    </td>
                                                    @endif
                                                </tr>
                                                <?php $indexLine++; ?>
                                                @endfor

                                                @else
                                                @foreach($model->lines as $line)
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
                                                    <td class="text-right"> {{ number_format($line->amount) }} </td>

                                                    @if(empty($model->cek_giro_header_id))
                                                    <td class="text-center">
                                                        <a data-toggle="tooltip" class="btn btn-warning btn-xs edit-line" ><i class="fa fa-pencil"></i></a>
                                                        <a data-toggle="tooltip" class="btn btn-danger btn-xs delete-line" ><i class="fa fa-remove"></i></a>
                                                        <input type="hidden" name="invoiceId[]" value="{{ !empty($line->invoice) ? $line->invoice->invoice_id : '' }}">
                                                        <input type="hidden" name="invoiceNumber[]" value="{{ !empty($line->invoice) ? $line->invoice->invoice_number : '' }}">
                                                        <input type="hidden" name="invoiceType[]" value="{{ !empty($line->invoice) ? $line->invoice->type : '' }}">
                                                        <input type="hidden" name="resiNumber[]" value="{{ !empty($line->invoice->resi) ? $line->invoice->resi->resi_number : '' }}">
                                                        <input type="hidden" name="route[]" value="{{ !empty($line->invoice->resi->route) ? $line->invoice->resi->route->route_code : '' }}">
                                                        <input type="hidden" name="customerSender[]" value="{{ !empty($line->invoice->resi->customer) ? $line->invoice->resi->customer->customer_name : '' }}">
                                                        <input type="hidden" name="sender[]" value="{{ !empty($line->invoice->resi) ? $line->invoice->resi->sender_name : '' }}">
                                                        <input type="hidden" name="customerReceiver[]" value="{{ !empty($line->invoice->resi->customerReceiver) ? $line->invoice->resi->customerReceiver->customer_name : '' }}">
                                                        <input type="hidden" name="receiver[]" value="{{ !empty($line->invoice->resi) ? $line->invoice->resi->receiver_name : '' }}">
                                                        <input type="hidden" name="amount[]" value="{{ !empty($line->invoice) ? $line->invoice->totalInvoice() : 0 }}">
                                                        <input type="hidden" name="remainingFix[]" value="{{ !empty($line->invoice) ? $line->invoice->remaining() : 0 }}">
                                                        <input type="hidden" name="remaining[]" value="{{ !empty($line->invoice) ? $line->invoice->remaining() : 0 }}">
                                                        <input type="hidden" name="amountLine[]" value="{{ $line->amount }}">
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
                                @if(empty($model->cek_giro_header_id))
                                    <button type="submit" class="btn btn-sm btn-primary"><i class="fa fa-save"></i> {{ trans('shared/common.save') }}</button>
                                @endif
                                @if($model->isOpen() && !empty($model->cek_giro_header_id))
                                    <button type="submit" class="btn btn-sm btn-info" name="btn-clearing">
                                        <i class="fa fa-money"></i> {{ trans('accountreceivables/fields.clearing') }}
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
                <h4 class="modal-title text-center"> <span id="title-modal-line">{{ trans('shared/common.add') }}</span> {{ trans('shared/common.line') }}</h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-sm-12">
                        <div id="horizontal-form">
                            <form  role="form" id="add-form" class="form-horizontal" method="post">
                                <input type="hidden" name="indexFormLine" id="indexFormLine" value="">
                                <div class="col-sm-6 portlets">
                                    <div class="form-group">
                                        <label for="invoiceNumber" class="col-sm-4 control-label">
                                            {{ trans('accountreceivables/fields.invoice-number') }} <span class="required">*</span>
                                        </label>
                                        <div class="col-sm-8">
                                            <div class="input-group">
                                                <input type="hidden" name="invoiceId" id="invoiceId" value="">
                                                <input type="text" class="form-control" id="invoiceNumber" name="invoiceNumber" readonly>
                                                <span class="btn input-group-addon" id="show-lov-invoice"><i class="fa fa-search"></i></span>
                                            </div>
                                        </div>
                                        <span class="help-block"></span>
                                    </div>
                                    <div class="form-group">
                                        <label for="invoiceType" class="col-sm-4 control-label">{{ trans('shared/common.type') }}</label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="invoiceType" name="invoiceType" value="" readonly>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="resiNumber" class="col-sm-4 control-label">{{ trans('operational/fields.resi-number') }}</label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="resiNumber" name="resiNumber" value="" readonly>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="route" class="col-sm-4 control-label">{{ trans('operational/fields.route') }}</label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="route" name="route" value="" readonly>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="amount" class="col-sm-4 control-label">{{ trans('accountreceivables/fields.amount') }}</label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control currency" id="amount" name="amount" value="" readonly>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="remaining" class="col-sm-4 control-label">{{ trans('accountreceivables/fields.remaining') }}</label>
                                        <div class="col-sm-8">
                                            <input type="hidden" id="remainingFix" name="remainingFix" value="" readonly>
                                            <input type="text" class="form-control currency" id="remaining" name="remaining" value="" readonly>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-6 portlets">
                                    <div class="form-group">
                                        <label for="customerSender" class="col-sm-4 control-label">{{ trans('shared/common.customer') }}</label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="customerSender" name="customerSender" readonly>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="sender" class="col-sm-4 control-label">{{ trans('operational/fields.sender') }}</label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="sender" name="sender" value="" readonly>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="customerReceiver" class="col-sm-4 control-label">{{ trans('shared/common.customer') }}</label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="customerReceiver" name="customerReceiver" readonly>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="receiver" class="col-sm-4 control-label">{{ trans('operational/fields.receiver') }}</label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="receiver" name="receiver" value="" readonly>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="amountLine" class="col-sm-4 control-label">{{ trans('accountreceivables/fields.cek-giro-amount') }}</label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control currency" id="amountLine" name="amountLine" value="">
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

<div id="modal-lov-invoice" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title text-center">{{ trans('accountreceivables/fields.invoice-receivable') }}</h4>
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
                                    <th>{{ trans('accountreceivables/fields.invoice-number') }}<hr/>{{ trans('shared/common.type') }}</th>
                                    <th>{{ trans('operational/fields.resi-number') }}<hr/>{{ trans('operational/fields.route') }}</th>
                                    <th>{{ trans('shared/common.customer') }}<hr/>{{ trans('operational/fields.sender') }}</th>
                                    <th>{{ trans('shared/common.customer') }}<hr/>{{ trans('operational/fields.receiver') }}</th>
                                    <th>{{ trans('accountreceivables/fields.amount') }}</th>
                                    <th>{{ trans('accountreceivables/fields.remaining') }}</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
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
    $('#show-lov-customer').on('click', showLovCustomer);
    $(".remove-customer").on('click', removeCustomer);
    $('#searchCustomer').on('keyup', loadLovCustomer);
    $('#table-lov-customer tbody').on('click', 'tr', selectCustomer);

    /** LINE **/
    $('#clear-lines').on('click', clearLines);
    $('#add-line').on('click', addLine);
    $("#save-line").on('click', saveLine);
    $('.edit-line').on('click', editLine);
    $('.delete-line').on('click', deleteLine);

    $('#show-lov-invoice').on('click', showLovInvoice);
    $('#searchInvoice').on('keyup', loadLovInvoice);
    $('#table-lov-invoice tbody').on('click', 'tr', selectInvoice);
});

var clearLines = function() {
    $('#table-line tbody').html('');
    calculateTotal();
};

var showLovCustomer = function() {
    $('#searchCustomer').val('');
    loadLovCustomer(function() {
        $('#modal-lov-customer').modal('show');
    });
};

var removeCustomer = function() {
    $('#customerId').val('');
    $('#customerName').val('');
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
    $('#personName').val(data.customer_name);
    $('#address').val(data.address);
    $('#phoneNumber').val(data.phone_number);

    $('#table-line tbody').html('');
    calculateTotal();

    $('#modal-lov-customer').modal('hide');
};

var clearLines = function() {
    $('#table-line tbody').html('');
    calculateTotal();
};

var addLine = function() {
    clearFormLine();
    $('#title-modal-line').html('{{ trans('shared/common.add') }}');
    $('#submit-modal-line').html('{{ trans('shared/common.add') }}');

    $('#modal-line').modal('show');
};

var editLine = function() {
    clearFormLine();

    var $tr = $(this).parent().parent();
    var indexFormLine = $tr.data('index-line');
    var invoiceId = $tr.find('[name="invoiceId[]"]').val();
    var invoiceNumber = $tr.find('[name="invoiceNumber[]"]').val();
    var invoiceType = $tr.find('[name="invoiceType[]"]').val();
    var resiNumber = $tr.find('[name="resiNumber[]"]').val();
    var route = $tr.find('[name="route[]"]').val();
    var customerSender = $tr.find('[name="customerSender[]"]').val();
    var sender = $tr.find('[name="sender[]"]').val();
    var customerReceiver = $tr.find('[name="customerReceiver[]"]').val();
    var receiver = $tr.find('[name="receiver[]"]').val();
    var amount = currencyToInt($tr.find('[name="amount[]"]').val());
    var remainingFix = currencyToInt($tr.find('[name="remainingFix[]"]').val());
    var remaining = currencyToInt($tr.find('[name="remaining[]"]').val());
    var amountLine = currencyToInt($tr.find('[name="amountLine[]"]').val());

    $('#indexFormLine').val(indexFormLine);
    $('#invoiceId').val(invoiceId);
    $('#invoiceNumber').val(invoiceNumber);
    $('#invoiceType').val(invoiceType);
    $('#resiNumber').val(resiNumber);
    $('#route').val(route);
    $('#customerSender').val(customerSender);
    $('#sender').val(sender);
    $('#customerReceiver').val(customerReceiver);
    $('#receiver').val(receiver);
    $('#amount').val(amount.formatMoney(0));
    $('#remainingFix').val(remainingFix.formatMoney(0));
    $('#remaining').val(remaining.formatMoney(0));
    $('#amountLine').val(amountLine.formatMoney(0));

    $('#title-modal-line').html('{{ trans('shared/common.edit') }}');
    $('#submit-modal-line').html('{{ trans('shared/common.edit') }}');
    $('#modal-line').modal("show");
};

var clearFormLine = function() {
    $('#indexFormLine').val('');
    $('#invoiceId').val('');
    $('#invoiceNumber').val('');
    $('#invoiceType').val('');
    $('#resiNumber').val('');
    $('#route').val('');
    $('#customerSender').val('');
    $('#sender').val('');
    $('#customerReceiver').val('');
    $('#receiver').val('');
    $('#amount').val(0);
    $('#remainingFix').val(0);
    $('#remaining').val(0);
    $('#amountLine').val(0);
};

var saveLine = function() {
    var indexFormLine = $('#indexFormLine').val();
    var invoiceId     = $('#invoiceId').val();
    var amountLine    = $('#amountLine').val();
    var error         = false;

    if (invoiceId == '' || invoiceId <= 0) {
        $('#invoiceId').parent().parent().addClass('has-error');
        error = true;
    } else {
        $('#invoiceId').parent().parent().removeClass('has-error');
    }

    if (amountLine == '' || amountLine <= 0) {
        $('#amountLine').parent().parent().addClass('has-error');
        error = true;
    } else {
        $('#amountLine').parent().parent().removeClass('has-error');
    }

    if (error) {
        return;
    }

    var invoiceNumber = $('#invoiceNumber').val();
    var invoiceType = $('#invoiceType').val();
    var resiNumber = $('#resiNumber').val();
    var route = $('#route').val();
    var customerSender = $('#customerSender').val();
    var sender = $('#sender').val();
    var customerReceiver = $('#customerReceiver').val();
    var receiver = $('#receiver').val();
    var amount = currencyToInt($('#amount').val());
    var remainingFix = currencyToInt($('#remainingFix').val());
    var remaining = currencyToInt($('#remaining').val());
    var amountLine = currencyToInt($('#amountLine').val());

    var htmlTr = '<td >' + invoiceNumber + '<hr/>' + invoiceType + '</td>\
                    <td >' + resiNumber + '<hr/>' + route + '</td>\
                    <td >' + customerSender + '<hr/>' + sender + '</td>\
                    <td >' + customerReceiver + '<hr/>' + receiver + '</td>\
                    <td class="text-right">' + amountLine.formatMoney(0) + '</td>\
                    <td class="text-center" class="td-action-line">\
                        <a data-toggle="tooltip" class="btn btn-warning btn-xs edit-line" ><i class="fa fa-pencil"></i></a>\
                        <a data-toggle="tooltip" class="btn btn-danger btn-xs delete-line" ><i class="fa fa-remove"></i></a>\
                        <input type="hidden" name="invoiceId[]" value="' + invoiceId + '">\
                        <input type="hidden" name="invoiceNumber[]" value="' + invoiceNumber + '">\
                        <input type="hidden" name="invoiceType[]" value="' + invoiceType + '">\
                        <input type="hidden" name="resiNumber[]" value="' + resiNumber + '">\
                        <input type="hidden" name="route[]" value="' + route + '">\
                        <input type="hidden" name="customerSender[]" value="' + customerSender + '">\
                        <input type="hidden" name="sender[]" value="' + sender + '">\
                        <input type="hidden" name="customerReceiver[]" value="' + customerReceiver + '">\
                        <input type="hidden" name="receiver[]" value="' + receiver + '">\
                        <input type="hidden" name="amount[]" value="' + amount + '">\
                        <input type="hidden" name="remainingFix[]" value="' + remainingFix + '">\
                        <input type="hidden" name="remaining[]" value="' + remaining + '">\
                        <input type="hidden" name="amountLine[]" value="' + amountLine + '">\
                    </td>';

    if (indexFormLine != '') {
        $('tr[data-index-line="' + indexFormLine + '"]').html(htmlTr);
    } else {
        $('#table-line tbody').append(
            '<tr data-index-line="' + indexLine + '">' + htmlTr + '</tr>'
        );
        indexLine++;
    }

    calculateTotal();
    $('.edit-line').on('click', editLine);
    $('.delete-line').on('click', deleteLine);

    $('#modal-line').modal("hide");
};

var deleteLine = function() {
    $(this).parent().parent().remove();
    calculateTotal();
};

var showLovInvoice = function() {
    $('#searchInvoice').val('');
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
        data: {search: $('#searchInvoice').val(), customerId: $('#customerId').val()},
        success: function(data) {
            $('#table-lov-invoice tbody').html('');
            data.forEach(function(item) {
                var customerSenderName   = item.customer_sender_name != null ? item.customer_sender_name : '';
                var customerReceiverName = item.customer_receiver_name != null ? item.customer_receiver_name : '';
                $('#table-lov-invoice tbody').append(
                    '<tr data-json=\'' + JSON.stringify(item) + '\'>\
                        <td>' + item.invoice_number + '<hr/>' + item.type + '</td>\
                        <td>' + item.resi_number + '<hr/>' + item.route_code + '</td>\
                        <td>' + customerSenderName + '<hr/>' + item.sender_name + '</td>\
                        <td>' + customerReceiverName + '<hr/>' + item.receiver_name + '</td>\
                        <td class="text-right">' + parseInt(item.amount).formatMoney(0) + '</td>\
                        <td class="text-right">' + parseInt(item.remaining).formatMoney(0) + '</td>\
                    </tr>'
                );
            });

            if (typeof(callback) == 'function') {
                callback();
            }
        }
    });
};

var selectInvoice = function() {
    var data = $(this).data('json');
    var error = false;
    var customerSenderName = data.customer_sender_name != null ? data.customer_sender_name : '';
    var customerReceiverName = data.customer_receiver_name != null ? data.customer_receiver_name : '';

    $('#table-line tbody tr').each(function() {
        var invoiceId = $(this).find('[name="invoiceId[]"]').val();
        if (data.invoice_id == invoiceId) {
            $('#modal-alert').find('.alert-message').html('Invoice is already exist');
            $('#modal-alert').modal('show');
            error = true;
        }
    });

    if (error) {
        return;
    }

    $('#invoiceId').val(data.invoice_id);
    $('#invoiceNumber').val(data.invoice_number);
    $('#invoiceType').val(data.type);
    $('#resiNumber').val(data.resi_number);
    $('#route').val(data.route_code);
    $('#customerSender').val(customerSenderName);
    $('#sender').val(data.sender_name);
    $('#customerReceiver').val(customerReceiverName);
    $('#receiver').val(data.receiver_name);
    $('#amount').val(parseInt(data.amount).formatMoney(0));
    $('#remainingFix').val(parseInt(data.remaining).formatMoney(0));
    $('#remaining').val(0);
    $('#amountLine').val(parseInt(data.remaining).formatMoney(0));

    $('#modal-lov-invoice').modal('hide');
};

var calculateTotal = function() {
    var total = 0;

    $('#table-line tbody tr').each(function (i, row) {
        total += parseInt($(row).find('[name="amountLine[]"]').val());
    });

    $('#total').val(total.formatMoney(0));
};

</script>
@endsection
