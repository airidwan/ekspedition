@extends('layouts.master')

@section('title', trans('accountreceivables/menu.invoice'))

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="widget">
            <div class="widget-header">
                <h2><i class="fa fa-credit-card"></i> <strong>{{ $title }}</strong> {{ trans('accountreceivables/menu.invoice') }}</h2>
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
                        <input type="hidden" id="id" name="id" value="{{ $model->invoice_id }}">
                        <ul id="demo1" class="nav nav-tabs">
                            <li class="active">
                                <a href="#tabHeaders" data-toggle="tab">{{ trans('shared/common.headers') }} <span class="label label-success"></span></a>
                            </li>
                            <li class="">
                                <a href="#tabResi" data-toggle="tab">{{ trans('operational/fields.resi') }} <span class="badge badge-primary"></span></a>
                            </li>
                        </ul>
                        <div class="tab-content">
                            <div class="tab-pane fade active in" id="tabHeaders">
                                <div class="col-sm-6 portlets">
                                    <div class="form-group">
                                        <label for="invoiceNumber" class="col-sm-4 control-label">{{ trans('accountreceivables/fields.invoice-number') }} </label>
                                        <div class="col-sm-8">
                                            <input type="text" class='form-control' id="invoiceNumber" name="invoiceNumber" value="{{ $model->invoice_number }}" disabled>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="type" class="col-sm-4 control-label">{{ trans('shared/common.type') }} </label>
                                        <div class="col-sm-8">
                                            <input type="text" class='form-control' id="type" name="type" value="{{ $model->type }}" disabled>
                                        </div>
                                    </div>
                                    <?php $invoiceDate = new \DateTime($model->created_date); ?>
                                    <div class="form-group">
                                        <label for="invoiceDate" class="col-sm-4 control-label">{{ trans('shared/common.tanggal') }}</label>
                                        <div class="col-sm-8">
                                            <input type="text" id="invoiceDate" name="invoiceDate" class="form-control datepicker-input" data-mask="99-99-9999" placeholder="dd-mm-yyyy" value="{{ $invoiceDate->format('d-m-Y') }}" disabled>
                                        </div>
                                    </div>
                                    <?php
                                    $customerName = $model->customer !== null ? $model->customer->customer_name : '';
                                    ?>
                                    <div class="form-group">
                                        <label for="customerName" class="col-sm-4 control-label">{{ trans('accountreceivables/fields.customer') }}</label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="customerName" name="customerName" value="{{ $customerName }}" disabled>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="billTo" class="col-sm-4 control-label">{{ trans('accountreceivables/fields.bill-to') }} <span class="required">*</span></label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="billTo" name="billTo" value="{{ $model->bill_to }}" disabled>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="billToAddress" class="col-sm-4 control-label">{{ trans('accountreceivables/fields.bill-to-address') }} <span class="required">*</span></label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="billToAddress" name="billToAddress" value="{{ $model->bill_to_address }}" disabled>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="billToPhone" class="col-sm-4 control-label">{{ trans('accountreceivables/fields.bill-to-phone') }} <span class="required">*</span></label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="billToPhone" name="billToPhone" value="{{ $model->bill_to_phone }}" disabled>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-sm-4 control-label">{{ trans('accountreceivables/fields.bill') }}</label>
                                        <div class="col-sm-8">
                                            <label class="checkbox-inline icheckbox">
                                                <?php $isTagihan = $model->is_tagihan; ?>
                                                <input type="checkbox" id="isTagihan" name="isTagihan" value="1" {{ $isTagihan ? 'checked' : '' }} disabled>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="description" class="col-sm-4 control-label">{{ trans('shared/common.description') }}</label>
                                        <div class="col-sm-8">
                                            <textarea class="form-control" id="description" name="description" disabled>{{ $model->description }}</textarea>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-6 portlets">
                                    <div class="form-group">
                                        <label for="totalInvoice" class="col-sm-4 control-label">{{ trans('accountreceivables/fields.total-invoice') }} </label>
                                        <div class="col-sm-8">
                                            <input type="text" class='form-control currency' id="totalInvoice" name="totalInvoice" value="{{ $model->amount }}" disabled>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="discount1" class="col-sm-4 control-label">{{ trans('accountreceivables/fields.discount-1') }} </label>
                                        <div class="col-sm-2">
                                            <input type="text" class='form-control currency' id="discountPersen1" name="discountPersen1" value="{{$model->discount_persen_1 }}" disabled>
                                        </div>
                                        <div class="col-sm-2">
                                            <input type="text" class='form-control text-center' value="% =" disabled />
                                        </div>
                                        <div class="col-sm-4">
                                            <input type="text" class='form-control currency' id="discount1" name="discount1" value="{{ $model->discount_1 }}" disabled>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="discount2" class="col-sm-4 control-label">{{ trans('accountreceivables/fields.discount-2') }} </label>
                                        <div class="col-sm-2">
                                            <input type="text" class='form-control currency' id="discountPersen2" name="discountPersen2" value="{{ $model->discount_persen_2 }}" disabled>
                                        </div>
                                        <div class="col-sm-2">
                                            <input type="text" class='form-control text-center' value="% =" disabled />
                                        </div>
                                        <div class="col-sm-4">
                                            <input type="text" class='form-control currency' id="discount2" name="discount2" value="{{ $model->discount_2 }}" disabled>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="discount3" class="col-sm-4 control-label">{{ trans('accountreceivables/fields.discount-3') }} </label>
                                        <div class="col-sm-2">
                                            <input type="text" class='form-control currency' id="discountPersen3" name="discountPersen3" value="{{ $model->discount_persen_3 }}" disabled>
                                        </div>
                                        <div class="col-sm-2">
                                            <input type="text" class='form-control text-center' value="% =" disabled />
                                        </div>
                                        <div class="col-sm-4">
                                            <input type="text" class='form-control currency' id="discount3" name="discount3" value="{{ $model->discount_3 }}" disabled>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="total" class="col-sm-4 control-label">{{ trans('accountreceivables/fields.total') }} </label>
                                        <div class="col-sm-8">
                                            <input type="text" class='form-control currency' id="total" name="total" value="{{ $model->totalInvoice() }}" disabled>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="receipt" class="col-sm-4 control-label">{{ trans('accountreceivables/fields.receipt') }} </label>
                                        <div class="col-sm-8">
                                            <input type="text" class='form-control currency' id="receipt" name="receipt" value="{{ $model->totalReceipt() }}" disabled>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="remaining" class="col-sm-4 control-label">{{ trans('accountreceivables/fields.remaining') }} </label>
                                        <div class="col-sm-8">
                                            <input type="text" class='form-control currency' id="remaining" name="remaining" value="{{ $model->remaining() }}" disabled>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="status" class="col-sm-4 control-label">{{ trans('shared/common.status') }}</label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="status" name="status" value="{{ $model->status }}" disabled>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="requestApproveNote" class="col-sm-4 control-label">{{ trans('shared/common.request-approve-note') }}</label>
                                        <div class="col-sm-8">
                                            <textarea class="form-control" id="requestApproveNote" name="requestApproveNote" disabled>{{ $model->req_approve_note }}</textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="tabResi">
                                <div class="col-sm-6 portlets">
                                    <div class="form-group">
                                        <label for="resiNumber" class="col-sm-4 control-label">{{ trans('operational/fields.resi-number') }} </label>
                                        <div class="col-sm-8">
                                            <input type="text" class='form-control' id="resiNumber" name="resiNumber" value="{{ !empty($model->resi) ? $model->resi->resi_number : '' }}" disabled>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="doNumber" class="col-sm-4 control-label">{{ trans('operational/fields.do-number') }} </label>
                                        <div class="col-sm-8">
                                            <input type="text" class='form-control' id="doNumber" name="doNumber" value="{{ !empty($model->deliveryOrderLine->header) ? $model->deliveryOrderLine->header->delivery_order_number : '' }}" disabled>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="pickupRequestNumber" class="col-sm-4 control-label">{{ trans('operational/fields.pickup-number') }} </label>
                                        <div class="col-sm-8">
                                            <input type="text" class='form-control' id="pickupRequestNumber" name="pickupRequestNumber" value="{{ !empty($model->pickupRequest) ? $model->pickupRequest->pickup_request_number : '' }}" disabled>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="resiType" class="col-sm-4 control-label">{{ trans('shared/common.type') }} </label>
                                        <div class="col-sm-8">
                                            <input type="text" class='form-control' id="resiType" name="resiType" value="{{ !empty($model->resi) ? $model->resi->type : '' }}" disabled>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="pickupRequestNumber" class="col-sm-4 control-label">{{ trans('operational/fields.payment') }} </label>
                                        <div class="col-sm-8">
                                            <input type="text" class='form-control' id="pickupRequestNumber" name="pickupRequestNumber" value="{{ !empty($model->resi) ? $model->resi->payment : '' }}" disabled>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="routeCode" class="col-sm-4 control-label">{{ trans('operational/fields.route') }} </label>
                                        <div class="col-sm-8">
                                            <input type="text" class='form-control' id="routeCode" name="routeCode" value="{{ !empty($model->resi->route) ? $model->resi->route->route_code : '' }}" disabled>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-6 portlets">
                                    <div class="form-group">
                                        <label for="customerSender" class="col-sm-4 control-label">{{ trans('shared/common.customer') }} </label>
                                        <div class="col-sm-8">
                                            <input type="text" class='form-control' id="customerSender" name="customerSender" value="{{ !empty($model->resi->customer) ? $model->resi->customer->customer_name : '' }}" disabled>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="sender" class="col-sm-4 control-label">{{ trans('operational/fields.sender') }} </label>
                                        <div class="col-sm-8">
                                            <input type="text" class='form-control' id="sender" name="sender" value="{{ !empty($model->resi) ? $model->resi->sender_name : '' }}" disabled>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="senderAddress" class="col-sm-4 control-label">{{ trans('shared/common.address') }} </label>
                                        <div class="col-sm-8">
                                            <input type="text" class='form-control' id="senderAddress" name="senderAddress" value="{{ !empty($model->resi) ? $model->resi->sender_address : '' }}" disabled>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="customerReceiver" class="col-sm-4 control-label">{{ trans('shared/common.customer') }} </label>
                                        <div class="col-sm-8">
                                            <input type="text" class='form-control' id="customerReceiver" name="customerReceiver" value="{{ !empty($model->resi->customerReceiver) ? $model->resi->customerReceiver->customer_name : '' }}" disabled>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="receiver" class="col-sm-4 control-label">{{ trans('operational/fields.receiver') }} </label>
                                        <div class="col-sm-8">
                                            <input type="text" class='form-control' id="receiver" name="receiver" value="{{ !empty($model->resi) ? $model->resi->receiver_name : '' }}" disabled>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="receiverAddress" class="col-sm-4 control-label">{{ trans('shared/common.address') }} </label>
                                        <div class="col-sm-8">
                                            <input type="text" class='form-control' id="receiverAddress" name="receiverAddress" value="{{ !empty($model->resi) ? $model->resi->receiver_address : '' }}" disabled>
                                        </div>
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
