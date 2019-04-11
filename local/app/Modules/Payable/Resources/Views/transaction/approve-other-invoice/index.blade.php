@extends('layouts.master')

@section('title', trans('payable/menu.approve-other-invoice'))

<?php 
    use App\Modules\Payable\Model\Transaction\InvoiceHeader;
?>

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="widget">
            <div class="widget-header">
                <h2><i class="fa fa-usd"></i> {{ trans('payable/menu.approve-other-invoice') }}</h2>
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
                                <label for="invoiceNumber" class="col-sm-4 control-label">{{ trans('payable/fields.invoice-number') }}</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="invoiceNumber" name="invoiceNumber" value="{{ !empty($filters['invoiceNumber']) ? $filters['invoiceNumber'] : '' }}">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="vendorCode" class="col-sm-4 control-label">{{ trans('payable/fields.trading-code') }}</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="vendorCode" name="vendorCode" value="{{ !empty($filters['vendorCode']) ? $filters['vendorCode'] : '' }}">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="vendor" class="col-sm-4 control-label">{{ trans('payable/fields.trading') }}</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="vendor" name="vendor" value="{{ !empty($filters['vendor']) ? $filters['vendor'] : '' }}">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="description" class="col-sm-4 control-label">{{ trans('shared/common.description') }}</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="description" name="description" value="{{ !empty($filters['description']) ? $filters['description'] : '' }}">
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6 portlets">
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
                                <th width="50px">{{ trans('shared/common.num') }}</th>
                                <th>{{ trans('payable/fields.invoice-number') }}</th>
                                <th>{{ trans('shared/common.type') }}</th>
                                <th>{{ trans('payable/fields.trading-code') }}</th>
                                <th>{{ trans('payable/fields.trading') }}</th>
                                <th width="10%">{{ trans('shared/common.address') }}</th>
                                <th>{{ trans('shared/common.total-amount') }}</th>
                                <th>{{ trans('shared/common.total-tax') }}</th>
                                <th>{{ trans('shared/common.total-invoice') }}</th>
                                <th style="min-width:80px;">{{ trans('shared/common.date') }}</th>
                                <th width="30%" >{{ trans('shared/common.description') }}</th>
                                <th>{{ trans('shared/common.status') }}</th>
                                <th style="min-width:50px;">{{ trans('shared/common.action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $no = ($models->currentPage() - 1) * $models->perPage() + 1; ?>
                            @foreach($models as $model)
                             <?php
                                 $invoice = InvoiceHeader::find($model->header_id);
                                 $date    = !empty($model->created_date) ? new \DateTime($model->created_date) : null;
                             ?>
                            <tr>
                                <td class="text-center">{{ $no++ }}</td>
                                <td>{{ $model->invoice_number }}</td>
                                <td>{{ $model->type_name }}</td>
                                @if($model->type_id == InvoiceHeader::OTHER_VENDOR)
                                <td>{{ $model->vendor_code }}</td>
                                <td>{{ $model->vendor_name }}</td>
                                <td>{{ $model->vendor_address }}</td>
                                @else
                                <td>{{ $model->driver_code }}</td>
                                <td>{{ $model->driver_name }}</td>
                                <td>{{ $model->driver_address }}</td>
                                @endif
                                <td class="text-right">{{ number_format($invoice->getTotalAmount()) }}</td>
                                <td class="text-right">{{ number_format($invoice->getTotalTax()) }}</td>
                                <td class="text-right">{{ number_format($invoice->getTotalInvoice()) }}</td>
                                <td>{{ !empty($date) ? $date->format('d-M-Y') : '' }}</td>
                                <td>{{ $model->description }}</td>
                                <td>{{ $model->status }}</td>
                                <td class="text-center" >
                                    @can('access', [$resource, 'update'])
                                    <a href="{{ URL($url . '/edit/' . $model->header_id) }}" data-toggle="tooltip" class="btn btn-xs btn-warning" data-original-title="{{ trans('shared/common.edit') }}">
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


