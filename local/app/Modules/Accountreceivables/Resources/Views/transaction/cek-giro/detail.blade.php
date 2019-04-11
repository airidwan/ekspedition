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
                                            {{ trans('accountreceivables/fields.cek-giro-account-number') }}
                                        </label>
                                        <div class="col-sm-8">
                                            <input type="text" class='form-control' id="cekGiroAccountNumber" name="cekGiroAccountNumber" value="{{ count($errors) > 0 ? old('cekGiroAccountNumber') : $model->cek_giro_account_number }}" disabled>
                                        </div>
                                    </div>
                                    <?php $type = count($errors) > 0 ? old('type') : $model->type ?>
                                    <div class="form-group {{ $errors->has('type') ? 'has-error' : '' }}">
                                        <label for="type" class="col-sm-4 control-label">{{ trans('shared/common.type') }}</label>
                                        <div class="col-sm-8">
                                            <select id="type" name="type" class="form-control" disabled>
                                                <option value="">Select {{ trans('shared/common.type') }}</option>
                                                @foreach($optionType as $option)
                                                    <option value="{{ $option }}" {{ $option == $type ? 'selected' : '' }}>{{ $option }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <?php
                                    $strCekGiroDate = count($errors) > 0 ? old('cekGiroDate') : $model->cek_giro_date;
                                    $cekGiroDate    = count($errors) == 0 || !empty($strCekGiroDate) ? new \DateTime($strCekGiroDate) : null;
                                    ?>
                                    <div class="form-group {{ $errors->has('cekGiroDate') ? 'has-error' : '' }}">
                                        <label for="cekGiroDate" class="col-sm-4 control-label">{{ trans('shared/common.tanggal') }}</label>
                                        <div class="col-sm-8">
                                            <input type="text" id="cekGiroDate" name="cekGiroDate" class="form-control datepicker-input" data-mask="99-99-9999" placeholder="dd-mm-yyyy" value="{{ $cekGiroDate !== null ? $cekGiroDate->format('d-m-Y') : '' }}" {{ !empty($model->cek_giro_header_id) ? 'disabled' : '' }}>
                                        </div>
                                    </div>
                                    <?php
                                    $strDueDate = count($errors) > 0 ? old('dueDate') : $model->due_date;
                                    $dueDate    = count($errors) == 0 || !empty($strDueDate) ? new \DateTime($strDueDate) : null;
                                    ?>
                                    <div class="form-group {{ $errors->has('dueDate') ? 'has-error' : '' }}">
                                        <label for="dueDate" class="col-sm-4 control-label">{{ trans('accountreceivables/fields.due-date') }}</label>
                                        <div class="col-sm-8">
                                            <input type="text" id="dueDate" name="dueDate" class="form-control datepicker-input" data-mask="99-99-9999" placeholder="dd-mm-yyyy" value="{{ $dueDate !== null ? $dueDate->format('d-m-Y') : '' }}" {{ !empty($model->cek_giro_header_id) ? 'disabled' : '' }}>
                                        </div>
                                    </div>
                                    <?php
                                    $clearingDate = !empty($model->clearing_date) ? new \DateTime($model->clearing_date) : null;
                                    ?>
                                    <div class="form-group">
                                        <label for="clearingDate" class="col-sm-4 control-label">{{ trans('accountreceivables/fields.clearing-date') }}</label>
                                        <div class="col-sm-8">
                                            <input type="text" id="clearingDate" name="clearingDate" class="form-control datepicker-input" data-mask="99-99-9999" placeholder="dd-mm-yyyy" value="{{ $clearingDate !== null ? $clearingDate->format('d-m-Y') : '' }}" disabled>
                                        </div>
                                    </div>
                                    <div class="form-group {{ $errors->has('bankName') ? 'has-error' : '' }}">
                                        <label for="bankName" class="col-sm-4 control-label">{{ trans('accountreceivables/fields.bank') }}</label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="bankName" name="bankName" value="{{ count($errors) > 0 ? old('bankName') : $model->bank_name }}" disabled>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="total" class="col-sm-4 control-label">{{ trans('accountreceivables/fields.total') }} </label>
                                        <div class="col-sm-8">
                                            <input type="text" class='form-control currency' id="total" name="total" value="{{ count($errors) > 0 ? str_replace(',', '', old('total')) : $model->totalAmount() }}" disabled>
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
                                            <input type="text" class="form-control" id="customerName" name="customerName" value="{{ count($errors) > 0 ? old('customerName') : $customerName }}" disabled>
                                        </div>
                                    </div>
                                    <div class="form-group {{ $errors->has('personName') ? 'has-error' : '' }}">
                                        <label for="personName" class="col-sm-4 control-label">{{ trans('shared/common.person-name') }}</label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="personName" name="personName" value="{{ count($errors) > 0 ? old('personName') : $model->person_name }}" disabled>
                                        </div>
                                    </div>
                                    <div class="form-group {{ $errors->has('address') ? 'has-error' : '' }}">
                                        <label for="address" class="col-sm-4 control-label">{{ trans('shared/common.address') }}</label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="address" name="address" value="{{ count($errors) > 0 ? old('address') : $model->address }}" disabled>
                                        </div>
                                    </div>
                                    <div class="form-group {{ $errors->has('phoneNumber') ? 'has-error' : '' }}">
                                        <label for="phoneNumber" class="col-sm-4 control-label">{{ trans('shared/common.phone') }}</label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="phoneNumber" name="phoneNumber" value="{{ count($errors) > 0 ? old('phoneNumber') : $model->phone_number }}" disabled>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="description" class="col-sm-4 control-label">{{ trans('shared/common.description') }}</label>
                                        <div class="col-sm-8">
                                            <textarea class="form-control" id="description" name="description" disabled>{{ count($errors) > 0 ? old('description') : $model->description }}</textarea>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="status" class="col-sm-4 control-label">{{ trans('accountreceivables/fields.status') }} </label>
                                        <div class="col-sm-8">
                                            <input type="text" class='form-control currency' id="status" name="status" value="{{ $model->status }}" disabled>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="tabLine">
                                <div class="col-sm-12 portlets">
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
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($model->lines as $line)
                                                <tr>
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
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-12 data-table-toolbar text-right">
                            <div class="form-group">
                                <a href="{{ URL($url) }}" class="btn btn-sm btn-warning"><i class="fa fa-reply"></i> {{ trans('shared/common.cancel') }}</a>
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
