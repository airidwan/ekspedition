<?php
use App\Modules\Accountreceivables\Model\Transaction\Receipt;
?>

@extends('layouts.master')

@section('title', trans('accountreceivables/menu.receipt-other'))

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="widget">
            <div class="widget-header">
                <h2><i class="fa fa-credit-card"></i> {{ trans('accountreceivables/menu.receipt-other') }}</h2>
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
                                <label for="type" class="col-sm-4 control-label">{{ trans('shared/common.type') }}</label>
                                <div class="col-sm-8">
                                    <select class="form-control" name="type">
                                        <option value="">ALL</option>
                                        @foreach($optionType as $type)
                                            <option value="{{ $type }}" {{ !empty($filters['type']) && $filters['type'] == $type ? 'selected' : '' }}>{{ $type }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="invoiceApNumber" class="col-sm-4 control-label">{{ trans('payable/fields.invoice-number') }}</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="invoiceApNumber" name="invoiceApNumber" value="{{ !empty($filters['invoiceApNumber']) ? $filters['invoiceApNumber'] : '' }}">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="personName" class="col-sm-4 control-label">{{ trans('shared/common.person-name') }}</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="personName" name="personName" value="{{ !empty($filters['personName']) ? $filters['personName'] : '' }}">
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6 portlets">
                            <div class="form-group">
                                <label for="description" class="col-sm-4 control-label">{{ trans('shared/common.description') }}</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="description" name="description" value="{{ !empty($filters['description']) ? $filters['description'] : '' }}">
                                </div>
                            </div>
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
                                @can('access', [$resource, 'view'])
                                <a href="{{ URL($url.'/print-pdf-index') }}" class="button btn btn-sm btn-success" target="_blank">
                                    <i class="fa fa-file-pdf-o"></i> {{ trans('shared/common.print-pdf') }}
                                </a>
                                @endcan
                                @can('access', [$resource, 'view'])
                                <a href="{{ URL($url.'/print-excel-index') }}" class="button btn btn-sm btn-success" target="_blank">
                                    <i class="fa fa-file-excel-o"></i> {{ trans('shared/common.print-excel') }}
                                </a>
                                @endcan
                                @can('access', [$resource, 'insert'])
                                <a href="{{ URL($url . '/add') }}" class="btn btn-sm btn-primary"><i class="fa fa-plus-circle"></i> {{ trans('shared/common.add-new') }}</a>
                                @endcan
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
                                <th>{{ trans('accountreceivables/fields.receipt-number') }}<hr/>{{ trans('shared/common.type') }}</th>
                                <th>{{ trans('accountreceivables/fields.date') }}</th>
                                <th>{{ trans('payable/fields.invoice-number') }}<hr/>{{ trans('payable/fields.trading') }}</th>
                                <th>{{ trans('asset/fields.asset-number') }}<hr/>{{ trans('inventory/fields.item') }}</th>
                                <th>{{ trans('shared/common.person-name') }}</th>
                                <th>{{ trans('shared/common.description') }}</th>
                                <th>{{ trans('accountreceivables/fields.receipt-method') }}<hr/>{{ trans('accountreceivables/fields.cash-or-bank') }}</th>
                                <th>{{ trans('accountreceivables/fields.amount') }}</th>
                                <th width="60px">{{ trans('shared/common.action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $no = ($models->currentPage() - 1) * $models->perPage() + 1; ?>
                            @foreach($models as $model)
                            <?php
                            $modelReceipt = Receipt::find($model->receipt_id);
                            $createdDate = !empty($model->created_date) ? new \DateTime($model->created_date) : null;
                            ?>
                            <tr>
                                <td class="text-center">{{ $no++ }}</td>
                                <td >{{ $model->receipt_number }}<hr/>{{ $model->type }}</td>
                                <td class="text-center">{{ $createdDate !== null ? $createdDate->format('d-m-Y') : '' }}</td>
                                <td >
                                    {{ !empty($modelReceipt->invoiceApHeader) ? $modelReceipt->invoiceApHeader->invoice_number : '' }}<hr/>
                                    {{ !empty($modelReceipt->invoiceApHeader) ? $modelReceipt->invoiceApHeader->getTradingKasbonCode() . ' - ' . $modelReceipt->invoiceApHeader->getTradingKasbonName() : '' }}
                                </td>
                                <td >
                                    {{ !empty($modelReceipt->additionAsset) ? $modelReceipt->additionAsset->asset_number : '' }}<hr/>
                                    {{ !empty($modelReceipt->additionAsset) ? $modelReceipt->additionAsset->item->description : '' }}
                                </td>
                                <td>{{ $modelReceipt->person_name }}</td>
                                <td>{{ $modelReceipt->description }}</td>
                                <td>{{ $modelReceipt->receipt_method }}<hr/>{{ !empty($modelReceipt->bank) ? $modelReceipt->bank->bank_name : '' }}</td>
                                <td class="text-right">{{ number_format($modelReceipt->amount) }}</td>

                                <td class="text-center">
                                    <a href="{{ URL($url . '/edit/' . $modelReceipt->receipt_id) }}" data-toggle="tooltip" class="btn btn-xs btn-warning" data-original-title="{{ trans('shared/common.edit') }}">
                                        <i class="fa fa-pencil"></i>
                                    </a>
                                    <a href="{{ URL($url . '/print-pdf/' . $modelReceipt->receipt_id) }}" target="_blank" data-toggle="tooltip" class="btn btn-xs btn-success" data-original-title="{{ trans('shared/common.print') }}">
                                        <i class="fa fa-print"></i>
                                    </a>
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
