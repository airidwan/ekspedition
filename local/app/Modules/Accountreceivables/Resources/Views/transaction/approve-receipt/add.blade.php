<?php
use App\Modules\Accountreceivables\Model\Transaction\ReceiptArHeader;
use App\Modules\Accountreceivables\Model\Transaction\invoiceArHeader;
?>

@extends('layouts.master')

@section('title', trans('accountreceivables/menu.approve-receipt'))

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="widget">
            <div class="widget-header">
                <h2><i class="fa fa-credit-card"></i> {{ trans('accountreceivables/menu.approve-receipt') }}</h2>
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
                        <input type="hidden" id="id" name="id" value="{{ $model->receipt_ar_header_id }}">
                        <ul id="demo1" class="nav nav-tabs">
                            <li class="active">
                                <a href="#tabApprove" data-toggle="tab">{{ trans('shared/common.approve') }} <span class="label label-success"></span></a>
                            </li>
                            <li>
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
                                        <label for="receiptNumber" class="col-sm-4 control-label">{{ trans('accountreceivables/fields.receipt-number') }} </label>
                                        <div class="col-sm-8">
                                            <input type="text" class='form-control' id="receiptNumber" name="receiptNumber" value="{{ $model->receipt_ar_number }}" disabled>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="receiptDate" class="col-sm-4 control-label">{{ trans('shared/common.tanggal') }}</label>
                                        <div class="col-sm-8">
                                            <div class="input-group">
                                                <?php $receiptDate = new \DateTime($model->created_date); ?>
                                                <input type="text" id="receiptDate" name="receiptDate" class="form-control datepicker-input" data-mask="99-99-9999" placeholder="dd-mm-yyyy" value="{{ $receiptDate->format('d-m-Y') }}" disabled>
                                                <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                            </div>
                                        </div>
                                    </div>
                                    <?php
                                    $invoiceNumber = !empty($model->invoiceArHeader) ? $model->invoiceArHeader->inv_ar_number : '';
                                    ?>
                                    <div class="form-group">
                                        <label for="customerName" class="col-sm-4 control-label">{{ trans('accountreceivables/fields.invoice-number') }} <span class="required">*</span></label>
                                        <div class="col-sm-8">
                                                <input type="hidden" id="invoiceId" name="invoiceId" value="{{ $model->inv_ar_header_id }}">
                                                <input type="text" class="form-control" id="invoiceNumber" name="invoiceNumber" value="{{ count($errors) > 0 ? old('invoiceNumber') : $invoiceNumber }}" readonly>
                                        </div>
                                    </div>
                                    <?php
                                    $customerName = !empty($model->invoiceArHeader->customer) ? $model->invoiceArHeader->customer->customer_name : '';
                                    ?>
                                    <div class="form-group">
                                        <label for="customerName" class="col-sm-4 control-label">{{ trans('accountreceivables/fields.customer') }}</label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="customerName" name="customerName" value="{{ $customerName }}" readonly>
                                        </div>
                                    </div>
                                    <?php
                                    $billTo = !empty($model->invoiceArHeader) ? $model->invoiceArHeader->bill_to : '';
                                    ?>
                                    <div class="form-group">
                                        <label for="billTo" class="col-sm-4 control-label">{{ trans('accountreceivables/fields.bill-to') }}</label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="billTo" name="billTo" value="{{ $billTo }}" readonly>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="receiptMethod" class="col-sm-4 control-label">{{ trans('accountreceivables/fields.receipt-method') }}</label>
                                        <div class="col-sm-8">
                                            <select id="receiptMethod" name="receiptMethod" class="form-control" disabled>
                                                @foreach($optionReceiptMethod as $option)
                                                    <option value="{{ $option }}" {{ $option == $model->receipt_method ? 'selected' : '' }}>{{ $option }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <?php
                                    $bankName = !empty($model->bank) ? $model->bank->bank_name . ' - ' . $model->bank->account_number : '';
                                    ?>
                                    <div class="form-group {{ $model->receipt_method != ReceiptArHeader::TRANSFER ? 'hidden' : '' }}" id="form-group-bank">
                                        <label for="bankName" class="col-sm-4 control-label">{{ trans('accountreceivables/fields.bank') }}</label>
                                        <div class="col-sm-8">
                                            <input type="hidden" id="bankId" name="bankId" value="{{ $model->bank_id }}">
                                            <input type="text" class="form-control" id="bankName" name="bankName" value="{{ $bankName }}" readonly>
                                        </div>
                                    </div>
                                    <?php
                                    $cekGiroNumber = '';
                                    ?>
                                    <div class="form-group {{ $model->receipt_method != ReceiptArHeader::CEK_GIRO ? 'hidden' : '' }}" id="form-group-cek-giro">
                                        <label for="cekGiroNumber" class="col-sm-4 control-label">{{ trans('accountreceivables/fields.cek-giro') }}</label>
                                        <div class="col-sm-8">
                                            <input type="hidden" id="cekGiroId" name="cekGiroId" value="{{ count($errors) > 0 ? old('cekGiroId') : $model->cek_giro_id }}">
                                            <input type="text" class="form-control" id="cekGiroNumber" name="cekGiroNumber" value="{{ count($errors) > 0 ? old('cekGiroNumber') : $cekGiroNumber }}" readonly>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="description" class="col-sm-4 control-label">{{ trans('shared/common.description') }}</label>
                                        <div class="col-sm-8">
                                            <textarea class="form-control" id="description" name="description" disabled>{{ $model->description }}</textarea>
                                        </div>
                                    </div>
                                </div>
                                <?php
                                $totalInvoice = !empty($model->invoiceArHeader) ? $model->invoiceArHeader->totalInvoice() : 0;
                                ?>
                                <div class="col-sm-6 portlets">
                                    <div class="form-group">
                                        <label for="totalInvoice" class="col-sm-4 control-label">{{ trans('accountreceivables/fields.total-invoice') }} </label>
                                        <div class="col-sm-8">
                                            <input type="text" class='form-control currency' id="totalInvoice" name="totalInvoice" value="{{ $totalInvoice }}" readonly>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="totalReceipt" class="col-sm-4 control-label">{{ trans('accountreceivables/fields.total-receipt') }} </label>
                                        <div class="col-sm-8">
                                            <input type="text" class='form-control currency' id="totalReceipt" name="totalReceipt" value="{{ $model->totalReceipt() }}" readonly>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="totalDiscount" class="col-sm-4 control-label">{{ trans('accountreceivables/fields.total-discount') }} </label>
                                        <div class="col-sm-8">
                                            <input type="text" class='form-control currency' id="totalDiscount" name="totalDiscount" value="{{ $model->totalDiscount() }}" readonly>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="total" class="col-sm-4 control-label">{{ trans('accountreceivables/fields.total') }} </label>
                                        <div class="col-sm-8">
                                            <input type="text" class='form-control currency' id="total" name="total" value="{{ $model->total() }}" readonly>
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
                                            <textarea class="form-control" id="requestApproveNote" name="requestApproveNote" readonly>{{ $model->req_approve_note }}</textarea>
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
                                                        {{ trans('operational/fields.resi-number') }}<hr/>
                                                        {{ trans('operational/fields.route-code') }}
                                                    </th>
                                                    <th>
                                                        {{ trans('operational/fields.customer') }}<hr/>
                                                        {{ trans('operational/fields.sender') }}
                                                    </th>
                                                    <th>
                                                        {{ trans('operational/fields.customer') }}<hr/>
                                                        {{ trans('operational/fields.receiver') }}
                                                    </th>
                                                    <th>
                                                        {{ trans('operational/fields.amount') }}
                                                    </th>
                                                    <th>
                                                        {{ trans('accountreceivables/fields.discount-invoice') }}<hr/>
                                                        {{ trans('accountreceivables/fields.extra-price-invoice') }}
                                                    </th>
                                                    <th>
                                                        {{ trans('accountreceivables/fields.amount-receipt') }}<hr/>
                                                        {{ trans('accountreceivables/fields.discount-receipt') }}
                                                    </th>
                                                    <th>{{ trans('accountreceivables/fields.receipt') }}</th>
                                                    <th>{{ trans('operational/fields.discount') }}</th>
                                                    <th>{{ trans('operational/fields.amount') }}</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                $invoiceId = $model->inv_ar_header_id;
                                                $invoiceArHeader = invoiceArHeader::find($invoiceId);
                                                ?>

                                                @if($invoiceArHeader !== null)
                                                @foreach($invoiceArHeader->lines as $invoiceArLine)

                                                <?php
                                                $invoiceArLineId = $invoiceArLine->inv_ar_line_id;
                                                $checkLine = $model->isLineExist($invoiceArLineId);
                                                $receiptLine = $model->getAmountLine($invoiceArLineId);
                                                $discountLine = $model->getDiscountLine($invoiceArLineId);
                                                ?>
                                                <tr>
                                                    <td>
                                                        {{ !empty($invoiceArLine->resi) ? $invoiceArLine->resi->resi_number : '' }}<hr/>
                                                        {{ !empty($invoiceArLine->resi->route) ? $invoiceArLine->resi->route->route_code : '' }}
                                                    </td>
                                                    <td>
                                                        {{ !empty($invoiceArLine->resi->customer) ? $invoiceArLine->resi->customer->customer_name : '' }}<hr/>
                                                        {{ !empty($invoiceArLine->resi) ? $invoiceArLine->resi->sender_name : '' }}
                                                    </td>
                                                    <td>
                                                        {{ !empty($invoiceArLine->resi->customerReceiver) ? $invoiceArLine->resi->customerReceiver->customer_name : '' }}<hr/>
                                                        {{ !empty($invoiceArLine->resi) ? $invoiceArLine->resi->receiver_name : '' }}
                                                    </td>
                                                    <td class="text-right">{{ number_format($invoiceArLine->amount) }}</td>
                                                    <td class="text-right">
                                                        {{ number_format($invoiceArLine->discount) }}<hr/>
                                                        {{ number_format($invoiceArLine->extra_price) }}
                                                    </td>
                                                    <td class="text-right">
                                                        {{ $invoiceArHeader->totalAmountReceiptLine($invoiceArLine->inv_ar_line_id, $model->receipt_ar_header_id) }}<hr/>
                                                        {{ $invoiceArHeader->totalDiscountReceiptLine($invoiceArLine->inv_ar_line_id, $model->receipt_ar_header_id) }}
                                                    </td>
                                                    <td class="text-right" width="150px">{{ number_format($receiptLine) }}</td>
                                                    <td class="text-right" width="150px">{{ number_format($discountLine) }}</td>
                                                    <td class="text-right" width="150px">{{ number_format($receiptLine + $discountLine) }}</td>
                                                </tr>

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
                                <button type="submit" name="btn-approve" class="btn btn-sm btn-success"><i class="fa fa-save"></i> {{ trans('shared/common.approve') }}</button>
                                <button type="submit" name="btn-reject" class="btn btn-sm btn-danger"><i class="fa fa-remove"></i> {{ trans('shared/common.reject') }}</button>
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
