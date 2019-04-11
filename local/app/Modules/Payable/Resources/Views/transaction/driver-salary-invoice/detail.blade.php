@extends('layouts.master')

@section('title', trans('payable/menu.driver-salary-invoice'))

<?php 
use App\Modules\Payable\Model\Transaction\InvoiceHeader;
use App\Modules\Payable\Model\Transaction\InvoiceLine;
?>

@section('header')
@parent
<style type="text/css">
    #table-lov-driver tbody tr{
        cursor: pointer;
    }
    #table-lov-manifest tbody tr{
        cursor: pointer;
    }
</style>
@endsection

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="widget">
            <div class="widget-header">
                <h2><i class="fa fa-usd"></i> <strong>{{ $title }}</strong> {{ trans('payable/menu.driver-salary-invoice') }}</h2>
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
                        <input type="hidden" name="driverPosition" id="driverPosition" value="{{ '' }}">
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
                                            <input type="text" class="form-control" id="invoiceNumber" name="invoiceNumber"  value="{{ !empty($model->invoice_number) ? $model->invoice_number : '' }}" disabled>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="status" class="col-sm-4 control-label">{{ trans('shared/common.status') }}</label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="status" name="status"  value="{{ !empty($model->status) ? $model->status : '' }}" disabled>
                                        </div>
                                    </div>
                                    <?php 
                                    $driver     = $model->driver;
                                    $driverId   = !empty($driver) ? $driver->driver_id : '';
                                    $driverCode = !empty($driver) ? $driver->driver_code : '';
                                    $driverName = !empty($driver) ? $driver->driver_name : '';
                                    $driverAddress = !empty($driver) ? $driver->address : '';
                                    ?>
                                    <div class="form-group {{ $errors->has('driverId') ? 'has-error' : '' }}">
                                        <label for="driverId" class="col-sm-4 control-label">{{ trans('operational/fields.driver-code') }} <span class="required">*</span></label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="driverCode" name="driverCode" value="{{ $driverCode }}" disabled>
                                        </div>
                                    </div>

                                    <div class="form-group {{ $errors->has('driverName') ? 'has-error' : '' }}">
                                        <label for="driverName" class="col-sm-4 control-label">{{ trans('operational/fields.driver-name') }} </label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="driverName" name="driverName" value="{{ $driverName }}" disabled>
                                        </div>
                                    </div>
                                    <div class="form-group {{ $errors->has('driverAddress') ? 'has-error' : '' }}">
                                        <label for="driverAddress" class="col-sm-4 control-label">{{ trans('operational/fields.driver-address') }} </label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="driverAddress" name="driverAddress" value="{{ $driverAddress }}" disabled>
                                        </div>
                                    </div>
                                    <div class="form-group {{ $errors->has('descriptionHeader') ? 'has-error' : '' }}">
                                        <label for="descriptionHeader" class="col-sm-4 control-label">{{ trans('shared/common.description') }}</label>
                                        <div class="col-sm-8">
                                            <textarea type="text" class="form-control" id="descriptionHeader" name="descriptionHeader" rows="3" disabled>{{ $model->description }}</textarea>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-6 portlets">
                                    <?php $invoiceDate = new \DateTime($model->created_date); ?>
                                    <div class="form-group">
                                        <label for="invoiceDate" class="col-sm-4 control-label">{{ trans('shared/common.tanggal') }}</label>
                                        <div class="col-sm-8">
                                            <input type="text" id="invoiceDate" name="invoiceDate" class="form-control datepicker-input" data-mask="99-99-9999" placeholder="dd-mm-yyyy" value="{{ $invoiceDate->format('d-m-Y') }}" disabled>
                                        </div>
                                    </div>
                                    <?php $branch = $model->branch()->first() !== null ? $model->branch()->first() : Session::get('currentBranch'); ?>
                                    <div class="form-group">
                                        <label for="branch" class="col-sm-4 control-label">{{ trans('shared/common.cabang') }}</label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="branch" name="branch"  value="{{ $branch->branch_name }}" disabled>
                                        </div>
                                    </div>
                                    <?php 
                                    $invoice = InvoiceHeader::find($model->header_id);
                                    $totalInvoice = !empty($invoice) ? $invoice->getTotalInvoice() : 0; 
                                    ?>
                                    <div class="form-group">
                                        <label for="totalInvoice" class="col-sm-4 control-label">{{ trans('shared/common.total-invoice') }}</label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control currency text-right" id="totalInvoice" name="totalInvoice" value="{{ $totalInvoice }}" disabled>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="tabLines">
                                <div class="col-sm-12 portlets">
                                    <div class="table-responsive">
                                        <table class="table table-striped table-bordered" id="table-line" cellspacing="0" width="100%">
                                            <thead>
                                                <tr>
                                                    <th>{{ trans('operational/fields.manifest-number') }}</th>
                                                    <th>{{ trans('shared/common.description') }}</th>
                                                    <th>{{ trans('shared/common.position') }}</th>
                                                    <th>{{ trans('purchasing/fields.amount') }}</th>
                                                    <th>{{ trans('payable/fields.tax') }}</th>
                                                    <th>{{ trans('payable/fields.amount-tax') }}</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($model->lines()->get() as $line)
                                                    <?php
                                                        $invoiceLine = InvoiceLine::find($line->line_id);
                                                        $manifest = App\Modules\Operational\Model\Transaction\ManifestHeader::find($line->manifest_id);
                                                        $combination = App\Modules\Generalledger\Model\Master\MasterAccountCombination::find($line->account_comb_id);
                                                        $account = $combination->account;
                                                        $fixAmount       = $line->amount + ($line->tax / 100 * $line->amount);
                                                        $amountHidden = $line->amount + $line->interest_bank ;
                                                    ?>
                                                    <tr>
                                                        <td > {{ $manifest !== null ? $manifest->manifest_number : '' }} </td>
                                                        <td > {{ $line->description }} </td>
                                                        <td > {{ $invoiceLine !== null ? $invoiceLine->getPositionMeaning() : '' }} </td>
                                                        <td class="text-right"> {{ number_format($line->amount) }} </td>
                                                        <td class="text-right"> {{ number_format($line->tax) }} </td>
                                                        <td class="text-right"> {{ number_format($fixAmount) }} </td>
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

                                @if($model->status == InvoiceHeader::APPROVED || $model->status == InvoiceHeader::CLOSED)
                                <a href="{{ URL($url.'/print-pdf-detail/'.$model->header_id) }}" class="button btn btn-sm btn-success" target="_blank">
                                    <i class="fa fa-print"></i> {{ trans('shared/common.print') }}
                                </a>
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
