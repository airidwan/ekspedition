<?php
use App\Modules\Operational\Model\Transaction\TransactionResiLine;
?>

@extends('layouts.master')

@section('title', trans('accountreceivables/menu.approve-invoice'))

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="widget">
            <div class="widget-header">
                <h2><i class="fa fa-credit-card"></i> {{ trans('accountreceivables/menu.approve-invoice') }}</h2>
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
                        <input type="hidden" name="id" value="{{ $model->batch_invoice_header_id }}">
                        <ul id="demo1" class="nav nav-tabs">
                            <li class="active">
                                <a href="#tabApprove" data-toggle="tab">{{ trans('shared/common.approve') }} <span class="label label-success"></span></a>
                            </li>
                            <li class="">
                                <a href="#tabHeaders" data-toggle="tab">{{ trans('shared/common.headers') }} <span class="label label-success"></span></a>
                            </li>
                            <li class="">
                                <a href="#tabLine" data-toggle="tab">{{ trans('shared/common.line') }} <span class="badge badge-primary"></span></a>
                            </li>
                        </ul>
                        <div class="tab-content">
                            <div class="tab-pane fade active in" id="tabApprove">
                                <div class="col-sm-6 portlets">
                                    <div class="form-group {{ $errors->has('approveNote') ? 'has-error' : '' }}">
                                        <label for="approveNote" class="col-sm-4 control-label">{{ trans('shared/common.approve-note') }} <span class="required">*</span></label>
                                        <div class="col-sm-8">
                                            <textarea class="form-control" id="approveNote" name="approveNote">{{ $model->approved_note }}</textarea>
                                            @if($errors->has('approveNote'))
                                            <span class="help-block">{{ $errors->first('approveNote') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="tabHeaders">
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
                                            <input type="text" class='form-control currency' id="totalAmount" name="totalAmount" value="{{ $model->totalAmount() }}" readonly>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="discountHeader" class="col-sm-4 control-label">{{ trans('accountreceivables/fields.discount') }} </label>
                                        <div class="col-sm-8">
                                            <input type="text" class='form-control currency' id="discountHeader" name="discountHeader" value="{{ $model->totalDiscount() }}" readonly>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="total" class="col-sm-4 control-label">{{ trans('accountreceivables/fields.total') }} </label>
                                        <div class="col-sm-8">
                                            <input type="text" class='form-control currency' id="total" name="total" value="{{ $model->total() }}" readonly>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="remaining" class="col-sm-4 control-label">{{ trans('accountreceivables/fields.remaining') }} </label>
                                        <div class="col-sm-8">
                                            <input type="text" class='form-control currency' id="remaining" name="remaining" value="{{ $model->remaining() }}" readonly>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="discountPersen" class="col-sm-4 control-label">{{ trans('accountreceivables/fields.discount-inprocess') }} </label>
                                        <div class="col-sm-2">
                                            <input type="text" class='form-control currency' id="discountPersen" name="discountPersen" value="{{ $model->discount_persen }}" readonly>
                                        </div>
                                        <div class="col-sm-2">
                                            <input type="text" class='form-control text-center' value="% =" readonly>
                                        </div>
                                        <div class="col-sm-4">
                                            <input type="text" class='form-control currency' id="totalDiscountInprocess" name="totalDiscountInprocess" value="{{ $model->getTotalDiscountInprocess() }}" readonly>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="requestApproveNote" class="col-sm-4 control-label">{{ trans('operational/fields.requested-note') }}</label>
                                        <div class="col-sm-8">
                                            <textarea class="form-control" id="requestApproveNote" name="requestApproveNote" readonly>{{ $model->request_approve_note }}</textarea>
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
                                                <input type="hidden" id="customerId" name="customerId" value="{{ $model->customer_id }}">
                                                <input type="text" class="form-control" id="customerName" name="customerName" value="{{ $customerName }}" readonly>
                                                <span class="btn input-group-addon" id="{{ $model->isOpen() ? 'remove-customer' : '' }}"><i class="fa fa-remove"></i></span>
                                                <span class="btn input-group-addon" id="{{ $model->isOpen() ? 'show-lov-customer' : '' }}"><i class="fa fa-search"></i></span>
                                            </div>
                                            @if($errors->has('customerId'))
                                                <span class="help-block">{{ $errors->first('customerId') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="form-group {{ $errors->has('billTo') ? 'has-error' : '' }}">
                                        <label for="billTo" class="col-sm-4 control-label">{{ trans('accountreceivables/fields.bill-to') }}</label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="billTo" name="billTo" value="{{ $model->bill_to }}" readonly>
                                            @if($errors->has('billTo'))
                                                <span class="help-block">{{ $errors->first('billTo') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="form-group {{ $errors->has('billToAddress') ? 'has-error' : '' }}">
                                        <label for="billToAddress" class="col-sm-4 control-label">{{ trans('accountreceivables/fields.bill-to-address') }}</label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="billToAddress" name="billToAddress" value="{{ $model->bill_to_address }}" readonly>
                                            @if($errors->has('billToAddress'))
                                                <span class="help-block">{{ $errors->first('billToAddress') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="form-group {{ $errors->has('billToPhone') ? 'has-error' : '' }}">
                                        <label for="billToPhone" class="col-sm-4 control-label">{{ trans('accountreceivables/fields.bill-to-phone') }}</label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="billToPhone" name="billToPhone" value="{{ $model->bill_to_phone }}" readonly>
                                            @if($errors->has('billToPhone'))
                                                <span class="help-block">{{ $errors->first('billToPhone') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="description" class="col-sm-4 control-label">{{ trans('shared/common.description') }}</label>
                                        <div class="col-sm-8">
                                            <textarea class="form-control" id="description" name="description" readonly>{{ $model->description }}</textarea>
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
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($model->lines()->get() as $line)
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
                                                    <td class="text-right"> {{ !empty($line->invoice) ? number_format($line->invoice->amount) : 0 }} <hr/> {{ !empty($line->invoice) ? number_format($line->invoice->totalDiscount()) : 0 }} </td>
                                                    <td class="text-right discountInprocessLine"> {{ !empty($line->invoice) ? number_format($line->invoice->getDiscountInprocess()) : 0 }} </td>
                                                    <td class="text-right"> {{ !empty($line->invoice) ? number_format($line->invoice->totalInvoice()) : 0 }} <hr/> {{ !empty($line->invoice) ? number_format($line->invoice->remaining()) : 0 }} </td>
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
                                <button type="submit" name="btn-reject" class="btn btn-sm btn-danger"><i class="fa fa-remove"></i> {{ trans('shared/common.reject') }}</button>
                                <button type="submit" name="btn-approve" class="btn btn-sm btn-info"><i class="fa fa-save"></i> {{ trans('shared/common.approve') }}</button>
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
