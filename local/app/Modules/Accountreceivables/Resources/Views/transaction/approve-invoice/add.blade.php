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
                        <input type="hidden" name="id" value="{{ $model->invoice_id }}">
                        <ul id="demo1" class="nav nav-tabs">
                            <li class="active">
                                <a href="#tabApprove" data-toggle="tab">{{ trans('shared/common.approve') }} <span class="label label-success"></span></a>
                            </li>
                            <li>
                                <a href="#tabHeaders" data-toggle="tab">{{ trans('shared/common.headers') }} <span class="label label-success"></span></a>
                            </li>
                            <li class="">
                                <a href="#tabResi" data-toggle="tab">{{ trans('operational/fields.resi') }} <span class="badge badge-primary"></span></a>
                            </li>
                            <li class="">
                                <a href="#tabLineDetails" data-toggle="tab">{{ trans('operational/fields.line-detail') }} <span class="badge badge-primary"></span></a>
                            </li>
                            <li class="">
                                <a href="#tabLineUnits" data-toggle="tab">{{ trans('operational/fields.line-unit') }} <span class="badge badge-primary"></span></a>
                            </li>
                        </ul>
                        <div class="tab-content">
                            <div class="tab-pane fade active in" id="tabApprove">
                                <div class="col-sm-6 portlets">
                                    <div class="form-group {{ $errors->has('approveNote') ? 'has-error' : '' }}">
                                        <label for="approveNote" class="col-sm-4 control-label">{{ trans('shared/common.approve-note') }} <span class="required">*</span></label>
                                        <div class="col-sm-8">
                                            <textarea class="form-control" id="approveNote" name="approveNote">{{ count($errors) > 0 ? old('approveNote') : $model->approved_note }}</textarea>
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
                                    <div class="form-group">
                                        <label for="invoiceDate" class="col-sm-4 control-label">{{ trans('shared/common.tanggal') }}</label>
                                        <div class="col-sm-8">
                                            <?php $invoiceDate = new \DateTime($model->created_date); ?>
                                            <input type="text" id="invoiceDate" name="invoiceDate" class="form-control datepicker-input" data-mask="99-99-9999" placeholder="dd-mm-yyyy" value="{{ $invoiceDate->format('d-m-Y') }}" disabled>
                                        </div>
                                    </div>
                                    <?php
                                    $customerName = $model->customer !== null ? $model->customer->customer_name : '';
                                    ?>
                                    <div class="form-group">
                                        <label for="customerName" class="col-sm-4 control-label">{{ trans('accountreceivables/fields.customer') }}</label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="customerName" name="customerName" value="{{ $customerName }}" readonly>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="billTo" class="col-sm-4 control-label">{{ trans('accountreceivables/fields.bill-to') }}</label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="billTo" name="billTo" value="{{ $model->bill_to }}" readonly>
                                            @if($errors->has('billTo'))
                                                <span class="help-block">{{ $errors->first('billTo') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="billToAddress" class="col-sm-4 control-label">{{ trans('accountreceivables/fields.bill-to-address') }}</label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="billToAddress" name="billToAddress" value="{{ $model->bill_to_address }}" readonly>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="billToPhone" class="col-sm-4 control-label">{{ trans('accountreceivables/fields.bill-to-phone') }}</label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="billToPhone" name="billToPhone" value="{{ $model->bill_to_phone }}" readonly>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="description" class="col-sm-4 control-label">{{ trans('shared/common.description') }}</label>
                                        <div class="col-sm-8">
                                            <textarea class="form-control" id="description" name="description" readonly>{{ $model->description }}</textarea>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-6 portlets">
                                    <div class="form-group">
                                        <label for="totalInvoice" class="col-sm-4 control-label">{{ trans('accountreceivables/fields.total-invoice') }} </label>
                                        <div class="col-sm-8">
                                            <input type="text" class='form-control currency' id="totalInvoice" name="totalInvoice" value="{{ $model->amount }}" readonly>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="discount1" class="col-sm-4 control-label">{{ trans('accountreceivables/fields.discount-1') }} </label>
                                        <div class="col-sm-2">
                                            <input type="text" class='form-control currency' id="discountPersen1" name="discountPersen1" value="{{ $model->discount_persen_1 }}" readonly>
                                        </div>
                                        <label class="col-sm-1 control-label text-center" style="padding: 7px 0 0 0">% =</label>
                                        <div class="col-sm-5">
                                            <input type="text" class='form-control currency' id="discount1" name="discount1" value="{{ $model->discount_1 }}" readonly>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="discount2" class="col-sm-4 control-label">{{ trans('accountreceivables/fields.discount-2') }} </label>
                                        <div class="col-sm-2">
                                            <input type="text" class='form-control currency' id="discountPersen2" name="discountPersen2" value="{{ $model->discount_persen_2 }}" readonly>
                                        </div>
                                        <label class="col-sm-1 control-label text-center" style="padding: 7px 0 0 0">% =</label>
                                        <div class="col-sm-5">
                                            <input type="text" class='form-control currency' id="discount2" name="discount2" value="{{ $model->discount_2 }}" readonly>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="discount3" class="col-sm-4 control-label">{{ trans('accountreceivables/fields.discount-3') }} </label>
                                        <div class="col-sm-2">
                                            <input type="text" class='form-control currency' id="discountPersen3" name="discountPersen3" value="{{ $model->discount_persen_3 }}" readonly>
                                        </div>
                                        <label class="col-sm-1 control-label text-center" style="padding: 7px 0 0 0">% =</label>
                                        <div class="col-sm-5">
                                            <input type="text" class='form-control currency' id="discount3" name="discount3" value="{{ $model->discount_3 }}" readonly>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="total" class="col-sm-4 control-label">{{ trans('accountreceivables/fields.total') }} </label>
                                        <div class="col-sm-8">
                                            <input type="text" class='form-control currency' id="total" name="total" value="{{ count($errors) > 0 ? str_replace(',', '', old('total')) : $model->totalInvoice(false) }}" readonly>
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
                                        <label for="routeCode" class="col-sm-4 control-label">{{ trans('operational/fields.route') }} </label>
                                        <div class="col-sm-8">
                                            <input type="text" class='form-control' id="routeCode" name="routeCode" value="{{ !empty($model->resi->route) ? $model->resi->route->route_code : '' }}" disabled>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="pickupRequestNumber" class="col-sm-4 control-label">{{ trans('operational/fields.payment') }} </label>
                                        <div class="col-sm-8">
                                            <input type="text" class='form-control' id="pickupRequestNumber" name="pickupRequestNumber" value="{{ !empty($model->resi) ? $model->resi->payment : '' }}" disabled>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="itemName" class="col-sm-4 control-label">{{ trans('operational/fields.item-name') }} </label>
                                        <div class="col-sm-8">
                                            <input type="text" class='form-control' id="itemName" name="itemName" value="{{ !empty($model->resi) ? $model->resi->item_name : '' }}" disabled>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="remain" class="col-sm-4 control-label">{{ trans('accountreceivables/fields.remaining') }} </label>
                                        <div class="col-sm-8">
                                            <input type="text" class='form-control text-right' id="remain" name="remain" value="{{ number_format(!empty($model->resi) ? $model->resi->totalRemainingInvoice() : '') }}" disabled>
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
                            <div class="tab-pane fade" id="tabLineDetails">
                                <div class="col-sm-12 portlets">
                                    <div class="table-responsive">
                                        <table class="table table-striped table-bordered" id="table-line-detail" cellspacing="0" width="100%">
                                            <thead>
                                                <tr>
                                                    <th>{{ trans('operational/fields.item-name') }}</th>
                                                    <th>{{ trans('operational/fields.coly') }}</th>
                                                    <th>{{ trans('operational/fields.weight') }}</th>
                                                    <th>{{ trans('operational/fields.volume') }}</th>
                                                    <th>{{ trans('operational/fields.price-weight') }}</th>
                                                    <th>{{ trans('operational/fields.price-volume') }}</th>
                                                    <th>{{ trans('operational/fields.price') }}</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($model->resi->line()->whereNull('unit_id')->get() as $line)
                                                <tr>
                                                    <td > {{ $line->item_name }} </td>
                                                    <td class="text-right"> {{ number_format($line->coly) }} </td>
                                                    <td class="text-right"> {{ number_format($line->weight, 2) }} </td>
                                                    <td class="text-right"> {{ number_format($line->totalVolume(), 6) }} </td>
                                                    <td class="text-right"> {{ number_format($line->price_weight) }} </td>
                                                    <td class="text-right"> {{ number_format($line->totalPriceVolume()) }} </td>
                                                    <td class="text-right"> {{ number_format($line->total_price) }} </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="tabLineUnits">
                                <div class="col-sm-12 portlets">
                                    <div class="table-responsive">
                                        <table class="table table-striped table-bordered" id="table-line-unit" cellspacing="0" width="100%">
                                            <thead>
                                                <tr>
                                                    <th>{{ trans('operational/fields.item-unit') }}</th>
                                                    <th>{{ trans('operational/fields.total-unit') }}</th>
                                                    <th>{{ trans('operational/fields.price-unit') }}</th>
                                                    <th>{{ trans('operational/fields.total-price') }}</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($model->resi->line()->whereNotNull('unit_id')->get() as $line)
                                                <?php $line = TransactionResiLine::find($line->resi_line_id); ?>
                                                <tr>
                                                    <td > {{ $line->item_name }} </td>
                                                    <td class="text-right"> {{ number_format($line->total_unit) }} </td>
                                                    <td class="text-right"> {{ number_format($line->total_price / $line->total_unit) }} </td>
                                                    <td class="text-right"> {{ number_format($line->total_price) }} </td>
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
