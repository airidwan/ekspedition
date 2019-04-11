@extends('layouts.master')

@section('title', trans('payable/menu.do-partner-invoice'))

<?php 
    use App\Modules\Payable\Model\Transaction\InvoiceHeader;
?>

@section('header')
@parent
<style type="text/css">
    #table-lov-do tbody tr{
        cursor: pointer;
    }
</style>
@endsection
@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="widget">
            <div class="widget-header">
                <h2><i class="fa fa-usd"></i> <strong>{{ $title }}</strong> {{ trans('payable/menu.do-partner-invoice') }}</h2>
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
                        <input type="hidden" name="id" id="id" value="{{ $model->header_id }}">
                        <input type="hidden" name="driverPosition" id="driverPosition" value="{{ count($errors) > 0 ? old('driverPosition') : '' }}">
                        <ul id="demo1" class="nav nav-tabs">
                            <li class="active">
                                <a href="#tabHeaders" data-toggle="tab">{{ trans('shared/common.headers') }} <span class="label label-success"></span></a>
                            </li>
                            <li class="">
                                <a href="#tabLines" data-toggle="tab">{{ trans('shared/common.lines') }} <span class="badge badge-primary"></span></a>
                            </li>
                        </ul>
                        <div class="tab-content">
                            <div class="tab-pane fade active in" id="tabHeaders">
                                <div class="col-sm-6 portlets">
                                    <div class="form-group">
                                        <label for="invoiceNumber" class="col-sm-4 control-label">{{ trans('payable/fields.invoice-number') }}</label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="invoiceNumber" name="invoiceNumber"  value="{{ !empty($model->invoice_number) ? $model->invoice_number : '' }}" readonly>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="status" class="col-sm-4 control-label">{{ trans('shared/common.status') }}</label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="status" name="status"  value="{{ !empty($model->status) ? $model->status : '' }}" readonly>
                                        </div>
                                    </div>
                                    <?php 
                                    $do             = $model->deliveryOrder;
                                    $doHeaderId     = !empty($do) ? $do->delivery_order_header_id : '';
                                    $deliveryOrderNumber   = !empty($do) ? $do->delivery_order_number : '';
                                    $vendor         = $model->vendor;
                                    $vendorId       = !empty($vendor) ? $vendor->vendor_id : '';
                                    $vendorName     = !empty($vendor) ? $vendor->vendor_name : '';
                                    $vendorAddress  = !empty($vendor) ? $vendor->address : '';
                                    ?>
                                    <div class="form-group {{ $errors->has('doHeaderId') ? 'has-error' : '' }}">
                                        <label for="doHeaderId" class="col-sm-4 control-label">{{ trans('operational/fields.do-number') }} <span class="required">*</span></label>
                                        <div class="col-sm-8">
                                            <div class="input-group">
                                                <input type="hidden" class="form-control" id="doHeaderId" name="doHeaderId" value="{{ count($errors) > 0 ? old('doHeaderId') : $doHeaderId }}" readonly>
                                                <input type="text" class="form-control" id="deliveryOrderNumber" name="deliveryOrderNumber" value="{{ count($errors) > 0 ? old('deliveryOrderNumber') : $deliveryOrderNumber }}" readonly>
                                                <span class="btn input-group-addon" id="{{ $model->isIncomplete() ? 'show-lov-do' : '' }}"><i class="fa fa-search"></i></span>
                                            </div>
                                            @if($errors->has('doHeaderId'))
                                            <span class="help-block">{{ $errors->first('doHeaderId') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="form-group {{ $errors->has('vendorName') ? 'has-error' : '' }}">
                                        <label for="vendorName" class="col-sm-4 control-label">{{ trans('payable/fields.partner') }} </label>
                                        <div class="col-sm-8">
                                            <input type="hidden" class="form-control" id="vendorId" name="vendorId" value="{{ count($errors) > 0 ? old('vendorId') : $vendorId }}" readonly>
                                            <input type="text" class="form-control" id="vendorName" name="vendorName" value="{{ count($errors) > 0 ? old('vendorName') : $vendorName }}" readonly>
                                            @if($errors->has('vendorName'))
                                            <span class="help-block">{{ $errors->first('vendorName') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="form-group {{ $errors->has('vendorAddress') ? 'has-error' : '' }}">
                                        <label for="vendorAddress" class="col-sm-4 control-label">{{ trans('shared/common.address') }} </label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="vendorAddress" name="vendorAddress" value="{{ count($errors) > 0 ? old('vendorAddress') : $vendorAddress }}" readonly>
                                            @if($errors->has('vendorAddress'))
                                            <span class="help-block">{{ $errors->first('vendorAddress') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="form-group {{ $errors->has('descriptionHeader') ? 'has-error' : '' }}">
                                        <label for="descriptionHeader" class="col-sm-4 control-label">{{ trans('shared/common.description') }} <span class="required">*</span></label>
                                        <div class="col-sm-8">
                                            <textarea type="text" class="form-control" id="descriptionHeader" name="descriptionHeader" rows="3" {{ $model->status == InvoiceHeader::INCOMPLETE ? '' : 'disabled' }}>{{ count($errors) > 0 ? old('descriptionHeader') : $model->description }}</textarea>
                                            @if($errors->has('descriptionHeader'))
                                            <span class="help-block">{{ $errors->first('descriptionHeader') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-6 portlets">
                                    <div class="form-group">
                                        <label for="invoiceDate" class="col-sm-4 control-label">{{ trans('shared/common.tanggal') }}</label>
                                        <div class="col-sm-8">
                                            <div class="input-group">
                                                <?php $invoiceDate = new \DateTime($model->created_date); ?>
                                                <input type="text" id="invoiceDate" name="invoiceDate" class="form-control datepicker-input" data-mask="99-99-9999" placeholder="dd-mm-yyyy" value="{{ $invoiceDate->format('d-m-Y') }}" disabled>
                                                <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                            </div>
                                        </div>
                                        @if($errors->has('invoiceDate'))
                                        <span class="help-block">{{ $errors->first('invoiceDate') }}</span>
                                        @endif
                                    </div>
                                    <div class="form-group">
                                        <label for="branch" class="col-sm-4 control-label">{{ trans('shared/common.cabang') }}</label>
                                        <div class="col-sm-8">
                                            <?php $branch = $model->branch()->first() !== null ? $model->branch()->first() : Session::get('currentBranch'); ?>
                                            <input type="text" class="form-control" id="branch" name="branch"  value="{{ $branch->branch_name }}" readonly>
                                        </div>
                                    </div>
                                    <?php 
                                    $invoice = InvoiceHeader::find($model->header_id);
                                    $totalAmount = !empty($invoice) ? $invoice->getTotalAmount() : 0; 
                                    $totalTax = !empty($invoice) ? $invoice->getTotalTax() : 0; 
                                    $totalInvoice = !empty($invoice) ? $invoice->getTotalInvoice() : 0; 
                                    ?>
                                    <div class="form-group">
                                        <label for="totalAmount" class="col-sm-4 control-label">{{ trans('shared/common.total-amount') }}</label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control currency text-right" id="totalAmount" name="totalAmount" value="{{ count($errors) > 0 ? str_replace(',', '', old('totalAmount')) : $totalAmount }}" readonly>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="totalTax" class="col-sm-4 control-label">{{ trans('shared/common.total-tax') }}</label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control currency text-right" id="totalTax" name="totalTax" value="{{ count($errors) > 0 ? str_replace(',', '', old('totalTax')) : $totalTax }}" readonly>
                                        </div>
                                    </div>
                                    <div class="form-group"  {{ $errors->has('totalInvoice') ? 'has-error' : '' }}>
                                        <label for="totalInvoice" class="col-sm-4 control-label">{{ trans('shared/common.total-invoice') }}</label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control currency text-right" id="totalInvoice" name="totalInvoice" value="{{ count($errors) > 0 ? str_replace(',', '', old('totalInvoice')) : $totalInvoice }}" readonly>
                                        </div>
                                        @if($errors->has('totalInvoice'))
                                            <span class="help-block">{{ $errors->first('totalInvoice') }}</span>
                                            @endif
                                    </div>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="tabLines">
                                <div class="col-sm-12 portlets">
                                    <div class="table-responsive">
                                        <table class="table table-striped table-bordered" id="table-line" cellspacing="0" width="100%">
                                            <thead>
                                                <tr>
                                                    <th>{{ trans('operational/fields.resi-number') }}</th>
                                                    <th>{{ trans('operational/fields.total-coly') }}</th>
                                                    <th>{{ trans('operational/fields.total-weight') }}</th>
                                                    <th>{{ trans('operational/fields.total-volume') }}</th>
                                                    <th>{{ trans('operational/fields.total-unit') }}</th>
                                                    <th>{{ trans('operational/fields.coly-send') }}</th>
                                                    <th>{{ trans('shared/common.description') }}</th>
                                                    <th>{{ trans('purchasing/fields.amount') }}</th>
                                                    <th>{{ trans('payable/fields.tax') }}</th>
                                                    <th>{{ trans('payable/fields.amount-tax') }}</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php $dataIndex = 0; ?>
                                                @if(count($errors) > 0)
                                                @for($i = 0; $i < count(old('lineId', [])); $i++)
                                                <tr data-index="{{ $dataIndex }}">
                                                    <td > {{ old('resiNumber')[$i] }} </td>
                                                    <td class="text-right"> {{ number_format(old('totalColy')[$i]) }} </td>
                                                    <td class="text-right"> {{ number_format(old('totalWeight')[$i]) }} </td>
                                                    <td class="text-right"> {{ number_format(old('totalVolume')[$i]) }} </td>
                                                    <td class="text-right"> {{ number_format(old('totalUnit')[$i]) }} </td>
                                                    <td class="text-right"> {{ number_format(old('colySend')[$i]) }} </td>
                                                    <td><input type="text" class="form-control" name="description[]" value="{{ old('description')[$i] }}"></td>
                                                    <td><input type="text" autocomplete="off" class="form-control currency amount" name="amount[]" value="{{ str_replace(',', '', old('amount')[$i]) }}"></td>
                                                    <td>
                                                        <select class="form-control tax" id="tax" name="tax[]">
                                                            <option value="">Please select tax</option>
                                                            @foreach($optionTax as $tax)
                                                            <option value="{{ $tax }}" {{ $tax == old('tax')[$i] ? 'selected' : '' }}>{{ $tax }} %</option>
                                                            @endforeach
                                                        </select>
                                                    </td>
                                                    <td class="text-right"> <input type="text" class="form-control currency fixAmount" name="fixAmount[]" value="{{ old('fixAmount')[$i] }}" readonly></td>
                                                    <input type="hidden" name="lineId[]" value="{{ old('lineId')[$i] }}">
                                                    <input type="hidden" name="doLineId[]" value="{{ old('doLineId')[$i] }}">
                                                    <input type="hidden" name="resiNumber[]" value="{{ old('resiNumber')[$i] }}">
                                                    <input type="hidden" name="totalColy[]" value="{{ old('totalColy')[$i] }}">
                                                    <input type="hidden" name="totalWeight[]" value="{{ old('totalWeight')[$i] }}">
                                                    <input type="hidden" name="totalVolume[]" value="{{ old('totalVolume')[$i] }}">
                                                    <input type="hidden" name="totalUnit[]" value="{{ old('totalUnit')[$i] }}">
                                                    <input type="hidden" name="colySend[]" value="{{ old('colySend')[$i] }}">
                                                    <input type="hidden" name="accountCodeId[]" value="{{ old('accountCodeId')[$i] }}">
                                                    <input type="hidden" name="accountCode[]" value="{{ old('accountCode')[$i] }}">
                                                    <input type="hidden" name="amountHidden[]" value="{{ old('amountHidden')[$i] }}">
                                                </tr>
                                                <?php $dataIndex++; ?>
                                                @endfor
                                                @else
                                                @foreach($model->lines()->get() as $line)
                                                <?php
                                                    $do = App\Modules\Operational\Model\Transaction\DeliveryOrderLine::find($line->do_line_id);
                                                    $combination = App\Modules\Generalledger\Model\Master\MasterAccountCombination::find($line->account_comb_id);
                                                    $resi = !empty($do) ? $do->resi : null;
                                                    $account = $combination->account;
                                                    $fixAmount       = $line->amount + ($line->tax / 100 * $line->amount);
                                                    $amountHidden = $line->amount + $line->interest_bank ;
                                                ?>
                                                <tr data-index="{{ $dataIndex }}">
                                                    <td> {{ $resi !== null ? $resi->resi_number : '' }} </td>
                                                    <td class="text-right"> {{ number_format($resi !== null ? $resi->totalColy() : '') }} </td>
                                                    <td class="text-right"> {{ number_format($resi !== null ? $resi->totalWeight() : '', 2) }} </td>
                                                    <td class="text-right"> {{ number_format($resi !== null ? $resi->totalVolume() : '', 4) }} </td>
                                                    <td class="text-right"> {{ number_format($resi !== null ? $resi->totalUnit() : '') }} </td>
                                                    <td class="text-right"> {{ number_format($do !== null ? $do->total_coly : '') }} </td>
                                                    <td><input type="text" class="form-control" name="description[]" value="{{ $line->description }}" {{ $model->status == InvoiceHeader::INCOMPLETE ? '' : 'disabled' }}></td>
                                                    <td><input type="text" autocomplete="off" class="form-control currency amount" name="amount[]" value="{{ $line->amount }}" {{ $model->status == InvoiceHeader::INCOMPLETE ? '' : 'disabled' }}></td>
                                                    <td>
                                                        <select class="form-control tax" id="tax" name="tax[]" {{ $model->status == InvoiceHeader::INCOMPLETE ? '' : 'disabled' }}>
                                                            <option value="">Please select tax</option>
                                                            @foreach($optionTax as $tax)
                                                            <option value="{{ $tax }}" {{ $tax == $line->tax ? 'selected' : '' }}>{{ $tax }} %</option>
                                                            @endforeach
                                                        </select></td>
                                                    <td class="text-right"> <input type="text" class="form-control currency fixAmount" name="fixAmount[]" value="{{ $fixAmount }}" readonly></td>
                                                        <input type="hidden" name="lineId[]" value="{{ $line->line_id }}">
                                                        <input type="hidden" name="doLineId[]" value="{{ $line->do_line_id }}">
                                                        <input type="hidden" name="resiNumber[]" value="{{ $resi !== null ? $resi->resi_number : '' }}">
                                                        <input type="hidden" name="totalColy[]" value="{{ $resi !== null ? $resi->totalColy() : '' }}">
                                                        <input type="hidden" name="totalWeight[]" value="{{ $resi !== null ? $resi->totalWeight() : '' }}">
                                                        <input type="hidden" name="totalVolume[]" value="{{ $resi !== null ? $resi->totalVolume() : '' }}">
                                                        <input type="hidden" name="totalUnit[]" value="{{ $resi !== null ? $resi->totalUnit() : '' }}">
                                                        <input type="hidden" name="colySend[]" value="{{  $do !== null ? $do->total_coly : '' }}">
                                                        <input type="hidden" name="accountCodeId[]" value="{{ $account !== null ? $account->coa_id : '' }}">
                                                        <input type="hidden" name="accountCode[]" value="{{ $account !== null ? $account->coa_code : '' }}">
                                                        <input type="hidden" name="description[]" value="{{ $line->description }}">
                                                        <input type="hidden" name="amountHidden[]" value="{{ number_format($amountHidden) }}">
                                                        <input type="hidden" name="fixAmount[]" value="{{ number_format($fixAmount) }}">
                                                        <input type="hidden" name="tax[]" value="{{ $line->tax }}">
                                                </tr>
                                                <?php $dataIndex++; ?>

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
                                 @if($model->status == InvoiceHeader::APPROVED || $model->status == InvoiceHeader::CLOSED)
                                <a href="{{ URL($url.'/print-pdf-detail/'.$model->header_id) }}" class="button btn btn-sm btn-success" target="_blank">
                                    <i class="fa fa-print"></i> {{ trans('shared/common.print') }}
                                </a>
                                @endif
                                @if(Gate::check('access', [$resource, 'insert']) && $model->status == InvoiceHeader::INCOMPLETE)
                                <button type="submit" class="btn btn-sm btn-primary">
                                    <i class="fa fa-save"></i> {{ trans('shared/common.save') }}
                                </button>
                                @endif
                                @if(Gate::check('access', [$resource, 'approve']) && $model->status == InvoiceHeader::INCOMPLETE)
                                <button type="submit" name="btn-approve" class="btn btn-sm btn-info">
                                    <i class="fa fa-save"></i> {{ trans('shared/common.approve') }}
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
<div id="modal-lov-do" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title text-center">{{ trans('operational/fields.delivery-order') }}</h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-sm-12">
                        <div class="form-group search-lov">
                            <label for="searchDo" class="col-sm-1 col-sm-offset-8 control-label">{{ trans('shared/common.search') }}</label>
                            <div class="col-sm-3">
                                <input type="text" class="form-control input-xs" id="searchDo" name="searchDo">
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-12">
                        <table id="table-lov-do" class="table table-hover table-striped table-bordered" cellspacing="0" width="100%">
                            <thead>
                                <tr>
                                    <th>{{ trans('operational/fields.do-number') }}</th>
                                    <th>{{ trans('shared/common.date') }}</th>
                                    <th>{{ trans('payable/fields.partner') }}</th>
                                    <th>{{ trans('payable/fields.partner-code') }}</th>
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
@endsection

@section('script')
@parent()
<script type="text/javascript">
    var dataIndex = {{ $dataIndex }};
    $(document).on('ready', function(){

        $('#show-lov-do').on('click', showLovDo);
        $('#searchDo').on('keyup', loadLovDo);
        $('#table-lov-do tbody').on('click', 'tr', selectDo);

        $(".tax").on('change', calculateAmountLine);
        $(".amount").on('keyup', calculateAmountLine);
   
    });

    var showLovDo = function() {
        $('#searchDo').val('');
        loadLovDo(function() {
            $('#modal-lov-do').modal('show');
        });
    };

    var xhrDo;
    var loadLovDo = function(callback) {
        if(xhrDo && xhrDo.readyState != 4){
            xhrDo.abort();
        }
        xhrDo = $.ajax({
            url: '{{ URL($url.'/get-json-do') }}',
            data: {search: $('#searchDo').val()},
            success: function(data) {
                $('#table-lov-do tbody').html('');
                data.forEach(function(item) {
                    $('#table-lov-do tbody').append(
                        '<tr data-json=\'' + JSON.stringify(item) + '\'>\
                            <td>' + item.delivery_order_number + '</td>\
                            <td>' + item.created_date + '</td>\
                            <td>' + item.vendor_code + '</td>\
                            <td>' + item.vendor_name + '</td>\
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

    var selectDo = function() {
        var data = $(this).data('json');
        $('#doHeaderId').val(data.delivery_order_header_id);
        $('#deliveryOrderNumber').val(data.delivery_order_number);
        $('#vendorId').val(data.vendor_id);
        $('#vendorName').val(data.vendor_name);
        $('#vendorAddress').val(data.address);
        $('#table-line tbody').html('');

        data.lines.forEach(function(line){
        $('#table-line tbody').append(
            '<tr>'+
                '<td >' + line.resi_number + '</td>' +
                '<td >' + line.total_coly_resi + '</td>' +
                '<td >' + line.total_weight + '</td>' +
                '<td >' + line.total_volume + '</td>' +
                '<td >' + line.total_unit + '</td>' +
                '<td >' + line.total_coly + '</td>' +
                '<td ><input type="text" class="form-control" name="description[]" value="'+ line.item_name +'"></td>' +
                '<td ><input type="text" autocomplete="off" class="form-control currency amount" name="amount[]"></td>' +
                '<td >'+
                    '<select class="form-control tax" id="tax" name="tax[]">'+
                        '<option value="">Please select tax</option>'+
                        @foreach($optionTax as $tax)
                        '<option value="{{ $tax }}" >{{ $tax }} %</option>'+
                        @endforeach
                    '</select></td>' +
                '<td class="text-right"><input type="text" class="form-control currency fixAmount" name="fixAmount[]" readonly></td>' +
                '<input type="hidden" name="lineId[]">' +
                '<input type="hidden" name="doLineId[]" value="' + line.delivery_order_line_id + '">' +
                '<input type="hidden" name="doNumber[]" value="' + line.delivery_order_number + '">' +
                '<input type="hidden" name="resiNumber[]" value="' + line.resi_number + '">' +
                '<input type="hidden" name="totalColy[]" value="' + line.total_coly + '">' +
                '<input type="hidden" name="totalWeight[]" value="' + line.total_weight + '">' +
                '<input type="hidden" name="totalVolume[]" value="' + line.total_volume + '">' +
                '<input type="hidden" name="totalUnit[]" value="' + line.total_unit + '">' +
                '<input type="hidden" name="colySend[]" value="' + line.total_coly + '">' +
                '<input type="hidden" name="accountCodeId[]" >' +
                '<input type="hidden" name="accountCode[]" >' +
                '<input type="hidden" name="amountHidden[]" >' +
            '</tr>'
            );
        });

        $('.currency').autoNumeric('init', {mDec: 0});
        $('.amount').on('keyup', calculateAmountLine);
        $('.tax').on('change', calculateAmountLine);

        $('#modal-lov-do').modal('hide');
    };

    var calculateAmountLine = function() {
        var $tr = $(this).parent().parent();
        var amount = currencyToInt($tr.find('.amount').val());
        var tax = currencyToInt($tr.find('.tax').val());
        var fixAmount = amount + (tax * amount / 100);

        $tr.find('.fixAmount').val(fixAmount.formatMoney(0));

        calculateTotal()
    };


    var calculateTotal = function() {
        var totalAmount = 0;
        var totalTax = 0;
        var totalInvoice = 0;

        $('#table-line tbody tr').each(function (i, row) {
            var amount = parseFloat($(row).find('[name="amount[]"]').val().split(',').join(''));
            var fixAmount = parseFloat($(row).find('[name="fixAmount[]"]').val().split(',').join(''));
            var tax = currencyToInt($(this).find('.tax').val());

            totalAmount += amount;
            totalTax += amount * tax / 100;
            totalInvoice += fixAmount;
        });

        $('#totalAmount').val(totalAmount);
        $('#totalAmount').autoNumeric('update', {mDec: 0});
        $('#totalTax').val(totalTax);
        $('#totalTax').autoNumeric('update', {mDec: 0});
        $('#totalInvoice').val(totalInvoice);
        $('#totalInvoice').autoNumeric('update', {mDec: 0});
    };
</script>
@endsection

