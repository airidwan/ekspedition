<?php
use App\Modules\Accountreceivables\Model\Transaction\ReceiptArHeader;
?>

@extends('layouts.master')

@section('title', trans('accountreceivables/menu.receipt'))

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="widget">
            <div class="widget-header">
                <h2><i class="fa fa-credit-card"></i> {{ trans('accountreceivables/menu.receipt') }}</h2>
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
                                <label for="receiptNumber" class="col-sm-4 control-label">{{ trans('accountreceivables/fields.receipt-number') }}</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="receiptNumber" name="receiptNumber" value="{{ !empty($filters['receiptNumber']) ? $filters['receiptNumber'] : '' }}">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="invoiceNumber" class="col-sm-4 control-label">{{ trans('accountreceivables/fields.invoice-number') }}</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="invoiceNumber" name="invoiceNumber" value="{{ !empty($filters['invoiceNumber']) ? $filters['invoiceNumber'] : '' }}">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="customer" class="col-sm-4 control-label">{{ trans('operational/fields.customer') }}</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="customer" name="customer" value="{{ !empty($filters['customer']) ? $filters['customer'] : '' }}">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="billTo" class="col-sm-4 control-label">{{ trans('accountreceivables/fields.bill-to') }}</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="billTo" name="billTo" value="{{ !empty($filters['billTo']) ? $filters['billTo'] : '' }}">
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6 portlets">
                            <div class="form-group">
                                <label for="receiptMethod" class="col-sm-4 control-label">{{ trans('accountreceivables/fields.receipt-method') }}</label>
                                <div class="col-sm-8">
                                    <select class="form-control" name="receiptMethod">
                                        <option value="">ALL</option>
                                        @foreach($optionReceiptMethod as $receiptMethod)
                                            <option value="{{ $receiptMethod }}" {{ !empty($filters['receiptMethod']) && $filters['receiptMethod'] == $receiptMethod ? 'selected' : '' }}>{{ $receiptMethod }}</option>
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
                                <th width="30px">{{ trans('shared/common.num') }}</th>
                                <th>
                                    {{ trans('accountreceivables/fields.receipt-number') }}<hr/>
                                    {{ trans('accountreceivables/fields.date') }}
                                </th>
                                <th>{{ trans('accountreceivables/fields.receipt-method') }}</th>
                                <th>
                                    {{ trans('accountreceivables/fields.invoice-number') }}<hr/>
                                    {{ trans('accountreceivables/fields.bill-to') }}
                                </th>
                                <th>{{ trans('accountreceivables/fields.total-receipt') }}</th>
                                <th>{{ trans('accountreceivables/fields.total-discount') }}</th>
                                <th>{{ trans('accountreceivables/fields.total') }}</th>
                                <th>{{ trans('shared/common.status') }}</th>
                                <th width="50px">{{ trans('shared/common.action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $no = ($models->currentPage() - 1) * $models->perPage() + 1; ?>
                            @foreach($models as $model)
                            <?php
                            $model = ReceiptArHeader::find($model->receipt_ar_header_id);
                            $createdDate = !empty($model->created_date) ? new \DateTime($model->created_date) : null;
                            ?>
                            <tr>
                                <td class="text-center">{{ $no++ }}</td>
                                <td class="text-center">
                                    {{ $model->receipt_ar_number }}<hr/>
                                    {{ $createdDate !== null ? $createdDate->format('d-m-Y') : '' }}
                                </td>
                                <td>{{ $model->receipt_method }}</td>
                                <td>
                                    {{ !empty($model->invoiceArHeader->customer) ? $model->invoiceArHeader->customer->customer_name : '' }}<hr/>
                                    {{ !empty($model->invoiceArHeader) ? $model->invoiceArHeader->bill_to : '' }}
                                </td>
                                <td class="text-right">{{ number_format($model->totalReceipt()) }}</td>
                                <td class="text-right">{{ number_format($model->totalDiscount()) }}</td>
                                <td class="text-right">{{ number_format($model->total()) }}</td>
                                <td>{{ $model->status }}</td>

                                <td class="text-center">
                                    @can('access', [$resource, 'update'])
                                    <a href="{{ URL($url . '/edit/' . $model->receipt_ar_header_id) }}" data-toggle="tooltip" class="btn btn-xs btn-warning" data-original-title="{{ trans('shared/common.edit') }}">
                                        <i class="fa fa-pencil"></i>
                                    </a>
                                    @endcan
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

@section('script')
@parent
<script type="text/javascript">
    $(document).on('ready', function(){
    });
</script>
@endsection
