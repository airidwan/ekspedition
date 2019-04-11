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
    #table-lov-pickup tbody tr{
        cursor: pointer;
    }
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
                                    $driver     = $model->driver;
                                    $driverId   = !empty($driver) ? $driver->driver_id : '';
                                    $driverCode = !empty($driver) ? $driver->driver_code : '';
                                    $driverName = !empty($driver) ? $driver->driver_name : '';
                                    $driverAddress = !empty($driver) ? $driver->address : '';
                                    ?>
                                    <div class="form-group {{ $errors->has('driverId') ? 'has-error' : '' }}">
                                        <label for="driverId" class="col-sm-4 control-label">{{ trans('operational/fields.driver-code') }} <span class="required">*</span></label>
                                        <div class="col-sm-8">
                                            <div class="input-group">
                                                <input type="hidden" class="form-control" id="driverId" name="driverId" value="{{ count($errors) > 0 ? old('driverId') : $driverId }}" readonly>
                                                <input type="text" class="form-control" id="driverCode" name="driverCode" value="{{ count($errors) > 0 ? old('driverCode') : $driverCode }}" readonly>
                                                <span class="btn input-group-addon" id="{{ $model->isIncomplete() ? 'show-lov-driver' : '' }}"><i class="fa fa-search"></i></span>
                                            </div>
                                            @if($errors->has('driverId'))
                                            <span class="help-block">{{ $errors->first('driverId') }}</span>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="form-group {{ $errors->has('driverName') ? 'has-error' : '' }}">
                                        <label for="driverName" class="col-sm-4 control-label">{{ trans('operational/fields.driver-name') }} </label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="driverName" name="driverName" value="{{ count($errors) > 0 ? old('driverName') : $driverName }}" readonly>
                                            @if($errors->has('driverName'))
                                            <span class="help-block">{{ $errors->first('driverName') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="form-group {{ $errors->has('driverAddress') ? 'has-error' : '' }}">
                                        <label for="driverAddress" class="col-sm-4 control-label">{{ trans('operational/fields.driver-address') }} </label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="driverAddress" name="driverAddress" value="{{ count($errors) > 0 ? old('driverAddress') : $driverAddress }}" readonly>
                                            @if($errors->has('driverAddress'))
                                            <span class="help-block">{{ $errors->first('driverAddress') }}</span>
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
                                    $totalInvoice = !empty($invoice) ? $invoice->getTotalInvoice() : 0; 
                                    ?>
                                    <div class="form-group">
                                        <label for="totalInvoice" class="col-sm-4 control-label">{{ trans('shared/common.total-invoice') }}</label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control currency text-right" id="totalInvoice" name="totalInvoice" value="{{ count($errors) > 0 ? str_replace(',', '', old('totalInvoice')) : $totalInvoice }}" readonly>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="tabLines">
                                <div class="col-sm-12 portlets">
                                    @if($model->status == InvoiceHeader::INCOMPLETE)
                                    <div class="data-table-toolbar">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="toolbar-btn-action">
                                                    <a class="btn btn-sm btn-primary add-line disabled" data-toggle="modal" data-target="#modal-form-line">
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
                                                    <th>{{ trans('shared/common.type') }}</th>
                                                    <th>{{ trans('operational/fields.manifest-number') }}</th>
                                                    <th>{{ trans('operational/fields.pickup-number') }}</th>
                                                    <th>{{ trans('operational/fields.do-number') }}</th>
                                                    <th>{{ trans('shared/common.description') }}</th>
                                                    <th>{{ trans('shared/common.position') }}</th>
                                                    <th>{{ trans('purchasing/fields.amount') }}</th>
                                                    <th>{{ trans('payable/fields.tax') }}</th>
                                                    <th>{{ trans('payable/fields.amount-tax') }}</th>
                                                    <th style="min-width:60px;">{{ trans('shared/common.action') }}</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php $dataIndex = 0; ?>
                                                @if(count($errors) > 0)
                                                @for($i = 0; $i < count(old('lineId', [])); $i++)
                                                <tr data-index="{{ $dataIndex }}">
                                                    <td > {{ old('type')[$i] }} </td>
                                                    <td > {{ old('manifestNumber')[$i] }} </td>
                                                    <td > {{ old('pickupNumber')[$i] }} </td>
                                                    <td > {{ old('doNumber')[$i] }} </td>
                                                    <td > {{ old('description')[$i] }} </td>
                                                    <td > {{ old('positionMeaning')[$i] }} </td>
                                                    <td class="text-right"> {{ old('amount')[$i] }} </td>
                                                    <td class="text-right"> {{ old('tax')[$i] === null ? old('tax')[$i] : 0 }} </td>
                                                    <td class="text-right"> {{ old('fixAmount')[$i] }} </td>
                                                    <td class="text-center">
                                                        @if($model->status == InvoiceHeader::INCOMPLETE)
                                                        <a data-toggle="tooltip" class="btn btn-xs btn-warning edit-line" ><i class="fa fa-pencil"></i></a>
                                                        <a data-toggle="tooltip" class="btn btn-danger btn-xs delete-line" ><i class="fa fa-remove"></i></a>
                                                        @endif
                                                        <input type="hidden" name="lineId[]" value="{{ old('lineId')[$i] }}">
                                                        <input type="hidden" name="type[]" value="{{ old('type')[$i] }}">
                                                        <input type="hidden" name="manifestId[]" value="{{ old('manifestId')[$i] }}">
                                                        <input type="hidden" name="manifestNumber[]" value="{{ old('manifestNumber')[$i] }}">
                                                        <input type="hidden" name="pickupId[]" value="{{ old('pickupId')[$i] }}">
                                                        <input type="hidden" name="pickupNumber[]" value="{{ old('pickupNumber')[$i] }}">
                                                        <input type="hidden" name="doId[]" value="{{ old('doId')[$i] }}">
                                                        <input type="hidden" name="doNumber[]" value="{{ old('doNumber')[$i] }}">
                                                        <input type="hidden" name="position[]" value="{{ old('position')[$i] }}">
                                                        <input type="hidden" name="positionMeaning[]" value="{{ old('positionMeaning')[$i] }}">
                                                        <input type="hidden" name="accountCodeId[]" value="{{ old('accountCodeId')[$i] }}">
                                                        <input type="hidden" name="accountCode[]" value="{{ old('accountCode')[$i] }}">
                                                        <input type="hidden" name="description[]" value="{{ old('description')[$i] }}">
                                                        <input type="hidden" name="amount[]" value="{{ old('amount')[$i] }}">
                                                        <input type="hidden" name="amountHidden[]" value="{{ old('amountHidden')[$i] }}">
                                                        <input type="hidden" name="fixAmount[]" value="{{ old('fixAmount')[$i] }}">
                                                        <input type="hidden" name="tax[]" value="{{ old('tax')[$i] }}">
                                                    </td>
                                                </tr>
                                                <?php $dataIndex++; ?>
                                                @endfor
                                                @else
                                                @foreach($model->lines()->get() as $line)
                                                <?php
                                                    $invoiceLine = InvoiceLine::find($line->line_id);
                                                    $combination = App\Modules\Generalledger\Model\Master\MasterAccountCombination::find($line->account_comb_id);
                                                    $account = $combination->account;
                                                    $fixAmount       = $line->amount + ($line->tax / 100 * $line->amount);
                                                    $amountHidden = $line->amount + $line->interest_bank ;
                                                ?>
                                                <tr data-index="{{ $dataIndex }}">
                                                    <td > {{ $line->type }} </td>
                                                    <td > {{ !empty($line->manifest) ? $line->manifest->manifest_number : '' }} </td>
                                                    <td > {{ !empty($line->pickup) ? $line->pickup->pickup_form_number : '' }} </td>
                                                    <td > {{ !empty($line->deliveryOrder) ? $line->deliveryOrder->delivery_order_number : '' }} </td>
                                                    <td > {{ $line->description }} </td>
                                                    <td > {{ $invoiceLine !== null ? $invoiceLine->getPositionMeaning() : '' }} </td>
                                                    <td class="text-right"> {{ number_format($line->amount) }} </td>
                                                    <td class="text-right"> {{ number_format($line->tax) }} </td>
                                                    <td class="text-right"> {{ number_format($fixAmount) }} </td>
                                                    <td class="text-center">
                                                        @if($model->status == InvoiceHeader::INCOMPLETE)
                                                        <a data-toggle="tooltip" class="btn btn-xs btn-warning edit-line" ><i class="fa fa-pencil"></i></a>
                                                        <a data-toggle="tooltip" class="btn btn-danger btn-xs delete-line" ><i class="fa fa-remove"></i></a>
                                                        @endif
                                                        <input type="hidden" name="lineId[]" value="{{ $line->line_id }}">
                                                        <input type="hidden" name="type[]" value="{{ $line->type }}">
                                                        <input type="hidden" name="manifestId[]" value="{{ !empty($line->manifest) ? $line->manifest->manifest_header_id : '' }}">
                                                        <input type="hidden" name="manifestNumber[]" value="{{ !empty($line->manifest) ? $line->manifest->manifest_number : '' }}">
                                                        <input type="hidden" name="pickupId[]" value="{{ !empty($line->pickup) ? $line->pickup->pickup_form_header_id : '' }}">
                                                        <input type="hidden" name="pickupNumber[]" value="{{ !empty($line->pickup) ? $line->pickup->pickup_form_number : '' }}">
                                                        <input type="hidden" name="doId[]" value="{{ !empty($line->deliveryOrder) ? $line->deliveryOrder->delivery_order_header_id : '' }}">
                                                        <input type="hidden" name="doNumber[]" value="{{ !empty($line->deliveryOrder) ? $line->deliveryOrder->delivery_order_number : '' }}">
                                                        <input type="hidden" name="position[]" value="{{ $line->position }}">
                                                        <input type="hidden" name="positionMeaning[]" value="{{ $invoiceLine !== null ? $invoiceLine->getPositionMeaning() : '' }}">
                                                        <input type="hidden" name="accountCodeId[]" value="{{ $account !== null ? $account->coa_id : '' }}">
                                                        <input type="hidden" name="accountCode[]" value="{{ $account !== null ? $account->coa_code : '' }}">
                                                        <input type="hidden" name="description[]" value="{{ $line->description }}">
                                                        <input type="hidden" name="amount[]" value="{{ number_format($line->amount) }}">
                                                        <input type="hidden" name="amountHidden[]" value="{{ number_format($amountHidden) }}">
                                                        <input type="hidden" name="fixAmount[]" value="{{ number_format($fixAmount) }}">
                                                        <input type="hidden" name="tax[]" value="{{ $line->tax }}">
                                                    </td>
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
                                @if($model->status == InvoiceHeader::APPROVED)
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
<div id="modal-form-line" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title text-center"><span id="title-modal-line-detail">{{ trans('shared/common.add') }}</span> {{ trans('shared/common.line') }}</h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-sm-12">
                        <div id="horizontal-form">
                            <form  role="form" id="add-form" class="form-horizontal" method="post">
                                {{ csrf_field() }}
                                <div class="col-sm-12 portlets">
                                    <input type="hidden" name="dataIndexForm" id="dataIndexForm" value="">
                                    <input type="hidden" name="lineId" id="lineId" value="">
                                    <div class="form-group">
                                        <label for="type" class="col-sm-4 control-label">{{ trans('shared/common.type') }} <span class="required">*</span></label>
                                        <div class="col-sm-8">
                                            <select class="form-control" name="type" id="type">
                                                @foreach($optionType as $type)
                                                <option value="{{ $type }}">{{ $type }}</option>
                                                @endforeach
                                            </select>
                                            <span class="help-block"></span>
                                        </div>
                                    </div>
                                    <div id="formManifest" class="form-group {{ $errors->has('manifestId') ? 'has-error' : '' }}">
                                        <label for="po" class="col-sm-4 control-label">{{ trans('operational/fields.manifest-number') }} <span id="spanPO" class="required">*</span></label>
                                        <div class="col-sm-8">
                                            <div class="input-group">
                                                <input type="hidden" name="manifestId" id="manifestId" value="">
                                                <input type="text" class="form-control" id="manifestNumber" name="manifestNumber" readonly>
                                                <span class="btn input-group-addon" id="show-lov-manifest"><i class="fa fa-search"></i></span>
                                            </div>
                                            <span class="help-block"></span>
                                        </div>
                                    </div>
                                    <div id="formPickup" class="form-group {{ $errors->has('pickupId') ? 'has-error' : '' }}">
                                        <label for="po" class="col-sm-4 control-label">{{ trans('operational/fields.pickup-number') }} <span id="spanPO" class="required">*</span></label>
                                        <div class="col-sm-8">
                                            <div class="input-group">
                                                <input type="hidden" name="pickupId" id="pickupId" value="">
                                                <input type="text" class="form-control" id="pickupNumber" name="pickupNumber" readonly>
                                                <span class="btn input-group-addon" id="show-lov-pickup"><i class="fa fa-search"></i></span>
                                            </div>
                                            <span class="help-block"></span>
                                        </div>
                                    </div>
                                    <div id="formDo" class="form-group {{ $errors->has('doId') ? 'has-error' : '' }}">
                                        <label for="po" class="col-sm-4 control-label">{{ trans('operational/fields.do-number') }} <span id="spanPO" class="required">*</span></label>
                                        <div class="col-sm-8">
                                            <div class="input-group">
                                                <input type="hidden" name="doId" id="doId" value="">
                                                <input type="text" class="form-control" id="doNumber" name="doNumber" readonly>
                                                <span class="btn input-group-addon" id="show-lov-do"><i class="fa fa-search"></i></span>
                                            </div>
                                            <span class="help-block"></span>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="description" class="col-sm-4 control-label">{{ trans('shared/common.description') }} <span class="required">*</span></label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="description" name="description" value="">
                                            <span class="help-block"></span>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="position" class="col-sm-4 control-label">{{ trans('shared/common.position') }} </label>
                                        <div class="col-sm-8">
                                            <input type="hidden" class="form-control" id="position" name="position" value="">
                                            <input type="text" class="form-control" id="positionMeaning" name="positionMeaning" value="" readonly>
                                            <span class="help-block"></span>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="amount" class="col-sm-4 control-label">{{ trans('purchasing/fields.amount') }} <span class="required">*</span></label>
                                        <div class="col-sm-8">
                                            <input type="hidden" class="form-control currency" id="amountHidden" name="amountHidden" value="" >
                                            <input type="text" class="form-control currency" id="amount" name="amount" value="" >
                                            <span class="help-block"></span>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="tax" class="col-sm-4 control-label">{{ trans('payable/fields.tax') }} </label>
                                        <div class="col-sm-8">
                                            <select class="form-control" id="tax" name="tax">
                                                <option value="">{{ trans('shared/common.please-select') }} {{ trans('payable/fields.tax') }}</option>
                                                @foreach($optionTax as $tax)
                                                <option value="{{ $tax }}" >{{ $tax }} %</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                     <div class="form-group">
                                        <label for="fixAmount" class="col-sm-4 control-label">{{ trans('purchasing/fields.amount') }} <span class="required">*</span></label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control currency" id="fixAmount" name="fixAmount" value="" disabled>
                                            <span class="help-block"></span>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-sm btn-warning" id="cancel-save-line" data-dismiss="modal">{{ trans('shared/common.cancel') }}</button>
                <button type="button" class="btn btn-sm btn-primary" id="save-line">
                    <span id="submit-modal-line">{{ trans('shared/common.add') }}</span> {{ trans('shared/common.line') }}
                </button>
            </div>
        </div>
    </div>
</div>
<div id="modal-lov-driver" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title text-center">{{ trans('shared/common.driver') }}</h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-sm-12">
                        <div class="form-group search-lov">
                            <label for="searchDriver" class="col-sm-1 col-sm-offset-8 control-label">{{ trans('shared/common.search') }}</label>
                            <div class="col-sm-3">
                                <input type="text" class="form-control input-xs" id="searchDriver" name="searchDriver">
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-12">
                        <table id="table-lov-driver" class="table table-hover table-striped table-bordered" cellspacing="0" width="100%">
                            <thead>
                                <tr>
                                    <th>{{ trans('operational/fields.driver-code') }}</th>
                                    <th>{{ trans('operational/fields.driver-name') }}</th>
                                    <th>{{ trans('operational/fields.nickname') }}</th>
                                    <th>{{ trans('shared/common.address') }}</th>
                                    <th>{{ trans('shared/common.phone') }}</th>
                                    <th>{{ trans('shared/common.position') }}</th>
                                    <th>{{ trans('shared/common.type') }}</th>
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

<div id="modal-lov-manifest" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title text-center">{{ trans('operational/fields.manifest') }}</h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-sm-12">
                        <div class="form-group search-lov">
                            <label for="searchManifest" class="col-sm-1 col-sm-offset-8 control-label">{{ trans('shared/common.search') }}</label>
                            <div class="col-sm-3">
                                <input type="text" class="form-control input-xs" id="searchManifest" name="searchManifest">
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-12">
                        <table id="table-lov-manifest" class="table table-hover table-striped table-bordered" cellspacing="0" width="100%">
                            <thead>
                                <tr>
                                    <th>{{ trans('operational/fields.manifest-number') }}</th>
                                    <th>{{ trans('shared/common.description') }}</th>
                                    <th>{{ trans('operational/fields.route') }}</th>
                                    <th>{{ trans('operational/fields.police-number') }}</th>
                                    <th>{{ trans('operational/fields.truck-type') }}</th>
                                    <th>{{ trans('operational/fields.position') }}</th>
                                    <th>{{ trans('operational/fields.salary') }}</th>
                                    <th>{{ trans('shared/common.remain') }}</th>
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

<div id="modal-lov-pickup" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title text-center">{{ trans('operational/fields.pickup') }}</h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-sm-12">
                        <div class="form-group search-lov">
                            <label for="searchPickup" class="col-sm-1 col-sm-offset-8 control-label">{{ trans('shared/common.search') }}</label>
                            <div class="col-sm-3">
                                <input type="text" class="form-control input-xs" id="searchPickup" name="searchPickup">
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-12">
                        <table id="table-lov-pickup" class="table table-hover table-striped table-bordered" cellspacing="0" width="100%">
                            <thead>
                                <tr>
                                    <th>{{ trans('operational/fields.pickup-number') }}</th>
                                    <th>{{ trans('shared/common.description') }}</th>
                                    <th>{{ trans('operational/fields.delivery-area') }}</th>
                                    <th>{{ trans('operational/fields.police-number') }}</th>
                                    <th>{{ trans('operational/fields.truck-type') }}</th>
                                    <th>{{ trans('operational/fields.position') }}</th>
                                    <th>{{ trans('operational/fields.salary') }}</th>
                                    <th>{{ trans('shared/common.remain') }}</th>
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
                                    <th>{{ trans('shared/common.description') }}</th>
                                    <th>{{ trans('operational/fields.delivery-area') }}</th>
                                    <th>{{ trans('operational/fields.police-number') }}</th>
                                    <th>{{ trans('operational/fields.truck-type') }}</th>
                                    <th>{{ trans('operational/fields.position') }}</th>
                                    <th>{{ trans('operational/fields.salary') }}</th>
                                    <th>{{ trans('shared/common.remain') }}</th>
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
        $('#show-lov-driver').on('click', showLovDriver);
        $('#searchDriver').on('keyup', loadLovDriver);
        $('#table-lov-driver tbody').on('click', 'tr', selectDriver);

        $('.add-line').on('click', addLine);

        $('#show-lov-manifest').on('click', showLovManifest);
        $('#searchManifest').on('keyup', loadLovManifest);
        $('#table-lov-manifest tbody').on('click', 'tr', selectManifest);

        $('#show-lov-pickup').on('click', showLovPickup);
        $('#searchPickup').on('keyup', loadLovPickup);
        $('#table-lov-pickup tbody').on('click', 'tr', selectPickup);

        $('#show-lov-do').on('click', showLovDo);
        $('#searchDo').on('keyup', loadLovDo);
        $('#table-lov-do tbody').on('click', 'tr', selectDo);

        $("#tax").on('change', calculateAmountLine);
        $("#amount").on('keyup', calculateAmountLine);
        $("#save-line").on('click', saveLine);
        $('.edit-line').on('click', editLine);
        $('.delete-line').on('click', deleteLine);
        disableForm();
        disableModal();
    });

    $('#type').on('change', function(){
        clearFormLine();
        disableModal();
    });

    var disableModal = function(){
        var type = $('#type').val();

        $('#formManifest').addClass('hidden');
        $('#formPickup').addClass('hidden');
        $('#formDo').addClass('hidden');

        if (type == '{{ InvoiceLine::MANIFEST_SALARY }}') {
            $('#formManifest').removeClass('hidden');
        }else if (type == '{{ InvoiceLine::PICKUP_SALARY }}') {
            $('#formPickup').removeClass('hidden');
        }else if (type == '{{ InvoiceLine::DELIVERY_ORDER_SALARY }}') {
            $('#formDo').removeClass('hidden');
        }
    };

    var disableForm = function() {
            if ($('#type').val() != '' && $('#driverName').val() != '' && $('#driverId').val() != '' ){
                $('.add-line').removeClass('disabled');
            }else{
                $('.add-line').addClass('disabled');
            }
        };

    var showLovDriver = function() {
        $('#searchDriver').val('');
        loadLovDriver(function() {
            $('#modal-lov-driver').modal('show');
        });
    };

    var xhrDriver;
    var loadLovDriver = function(callback) {
        if(xhrDriver && xhrDriver.readyState != 4){
            xhrDriver.abort();
        }
        xhrDriver = $.ajax({
            url: '{{ URL($url.'/get-json-driver') }}',
            data: {search: $('#searchDriver').val()},
            success: function(data) {
                $('#table-lov-driver tbody').html('');
                data.forEach(function(item) {
                    $('#table-lov-driver tbody').append(
                        '<tr data-json=\'' + JSON.stringify(item) + '\'>\
                            <td>' + item.driver_code + '</td>\
                            <td>' + item.driver_name + '</td>\
                            <td>' + item.driver_nickname + '</td>\
                            <td>' + item.address + '</td>\
                            <td>' + item.phone_number + '</td>\
                            <td>' + item.position + '</td>\
                            <td>' + item.type + '</td>\
                        </tr>'
                    );
                });

                if (typeof(callback) == 'function') {
                    callback();
                }
            }
        });
    };

    var selectDriver = function() {
        var data = $(this).data('json');
        $('#driverId').val(data.driver_id);
        $('#driverCode').val(data.driver_code);
        $('#driverName').val(data.driver_name);
        $('#driverAddress').val(data.address);
        $('#table-line tbody').html('');

        clearLines();
        disableForm();

        $('#modal-lov-driver').modal('hide');
    };

    var showLovManifest = function() {
        $('#searchManifest').val('');
        loadLovManifest(function() {
            $('#modal-lov-manifest').modal('show');
        });
    };

    var xhrManifest;
    var loadLovManifest = function(callback) {
        if(xhrManifest && xhrManifest.readyState != 4){
            xhrManifest.abort();
        }
        xhrManifest = $.ajax({
            url: '{{ URL($url.'/get-json-manifest') }}',
            data: {search: $('#searchManifest').val(), driverId: $('#driverId').val(), id: $('#id').val(), type: $('#type').val()},
            success: function(data) {
                $('#table-lov-manifest tbody').html('');
                data.forEach(function(item) {
                    $('#table-lov-manifest tbody').append(
                        '<tr data-json=\'' + JSON.stringify(item) + '\'>\
                            <td>' + item.manifest_number + '</td>\
                            <td>' + item.description + '</td>\
                            <td>' + item.route_code + '</td>\
                            <td>' + item.police_number + '</td>\
                            <td>' + item.truck_type + '</td>\
                            <td>' + item.position_meaning + '</td>\
                            <td class="text-right">' + parseInt(item.salary).formatMoney(0) + '</td>\
                            <td class="text-right">' + parseInt(item.total_remain).formatMoney(0) + '</td>\
                        </tr>'
                    );
                });

                if (typeof(callback) == 'function') {
                    callback();
                }
            }
        });
    };

    var selectManifest = function() {
        var data = $(this).data('json');

        var error = false
        $('#table-line tbody tr').each(function (i, row) {
            if (data.manifest_header_id == $(row).find('[name="manifestId[]"]').val()) {
                $('#modal-alert').find('.alert-message').html('Manifest already exist');
                $('#modal-alert').modal('show');
                error = true;
            }
        });

        if (error) {
            return;
        }

        $('#manifestId').val(data.manifest_header_id);
        $('#manifestNumber').val(data.manifest_number);
        $('#position').val(data.position);
        $('#positionMeaning').val(data.position_meaning);
        $('#manifestNumber').val(data.manifest_number);
        $('#description').val(data.description);
        $('#amount').val(parseInt(data.total_remain).formatMoney(0));
        calculateAmountLine();

        $('#modal-lov-manifest').modal('hide');
    };

    var showLovPickup = function() {
        $('#searchPickup').val('');
        loadLovPickup(function() {
            $('#modal-lov-pickup').modal('show');
        });
    };

    var xhrPickup;
    var loadLovPickup = function(callback) {
        if(xhrPickup && xhrPickup.readyState != 4){
            xhrPickup.abort();
        }
        xhrPickup = $.ajax({
            url: '{{ URL($url.'/get-json-pickup') }}',
            data: {search: $('#searchPickup').val(), driverId: $('#driverId').val(), id: $('#id').val(), type: $('#type').val()},
            success: function(data) {
                $('#table-lov-pickup tbody').html('');
                data.forEach(function(item) {
                    $('#table-lov-pickup tbody').append(
                        '<tr data-json=\'' + JSON.stringify(item) + '\'>\
                            <td>' + item.pickup_form_number + '</td>\
                            <td>' + item.note + '</td>\
                            <td>' + item.delivery_area_name + '</td>\
                            <td>' + item.police_number + '</td>\
                            <td>' + item.truck_type + '</td>\
                            <td>' + item.position_meaning + '</td>\
                            <td class="text-right">' + parseInt(item.driver_salary).formatMoney(0) + '</td>\
                            <td class="text-right">' + parseInt(item.total_remain).formatMoney(0) + '</td>\
                        </tr>'
                    );
                });

                if (typeof(callback) == 'function') {
                    callback();
                }
            }
        });
    };

    var selectPickup = function() {
        var data = $(this).data('json');

        var error = false
        $('#table-line tbody tr').each(function (i, row) {
            if (data.pickup_form_header_id == $(row).find('[name="pickupId[]"]').val()) {
                $('#modal-alert').find('.alert-message').html('Pickup already exist');
                $('#modal-alert').modal('show');
                error = true;
            }
        });

        if (error) {
            return;
        }

        $('#pickupId').val(data.pickup_form_header_id);
        $('#pickupNumber').val(data.pickup_form_number);
        $('#position').val(data.position);
        $('#positionMeaning').val(data.position_meaning);
        $('#description').val(data.note);
        $('#amount').val(parseInt(data.total_remain).formatMoney(0));
        calculateAmountLine();

        $('#modal-lov-pickup').modal('hide');
    };

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
            data: {search: $('#searchDo').val(), driverId: $('#driverId').val(), id: $('#id').val(), type: $('#type').val()},
            success: function(data) {
                $('#table-lov-do tbody').html('');
                data.forEach(function(item) {
                    $('#table-lov-do tbody').append(
                        '<tr data-json=\'' + JSON.stringify(item) + '\'>\
                            <td>' + item.delivery_order_number + '</td>\
                            <td>' + item.note + '</td>\
                            <td>' + item.delivery_area_name + '</td>\
                            <td>' + item.police_number + '</td>\
                            <td>' + item.truck_type + '</td>\
                            <td>' + item.position_meaning + '</td>\
                            <td class="text-right">' + parseInt(item.salary).formatMoney(0) + '</td>\
                            <td class="text-right">' + parseInt(item.total_remain).formatMoney(0) + '</td>\
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

        var error = false
        $('#table-line tbody tr').each(function (i, row) {
            if (data.delivery_order_header_id == $(row).find('[name="doId[]"]').val()) {
                $('#modal-alert').find('.alert-message').html('Do already exist');
                $('#modal-alert').modal('show');
                error = true;
            }
        });

        if (error) {
            return;
        }

        $('#doId').val(data.delivery_order_header_id);
        $('#doNumber').val(data.delivery_order_number);
        $('#position').val(data.position);
        $('#positionMeaning').val(data.position_meaning);
        $('#description').val(data.note);
        $('#amount').val(parseInt(data.total_remain).formatMoney(0));
        calculateAmountLine();

        $('#modal-lov-do').modal('hide');
    };

    var addLine = function() {
        clearFormLine();
        $('#title-modal-line').html('{{ trans('shared/common.add') }}');
        $('#submit-modal-line').html('{{ trans('shared/common.add') }}');
        $('#modal-line').modal('show');
        calculateAmountLine();
    };

    var saveLine = function() {
        var type           = $('#type').val();
        var dataIndexForm  = $('#dataIndexForm').val();
        var lineId         = $('#lineId').val();
        var manifestId     = $('#manifestId').val();
        var manifestNumber = $('#manifestNumber').val();
        var pickupId       = $('#pickupId').val();
        var pickupNumber   = $('#pickupNumber').val();
        var doId           = $('#doId').val();
        var doNumber       = $('#doNumber').val();
        var position       = $('#position').val();
        var positionMeaning = $('#positionMeaning').val();
        var description    = $('#description').val();
        var amount         = $('#amount').val();
        var fixAmount      = $('#fixAmount').val();
        var tax            = $('#tax').val();
        var accountCodeId  = $('#accountCodeId').val();
        var accountCode    = $('#accountCode').val();
        var amountHidden   = $('#amountHidden').val();
      
        var error = false;

        if (description == '') { // all
            $('#description').parent().parent().addClass('has-error');
            $('#description').parent().find('span.help-block').html('Description is required');
            error = true;
        } else {
            $('#description').parent().parent().removeClass('has-error');
            $('#description').parent().find('span.help-block').html('');
        }

        if (amount == '' || amount <= 0) {
            $('#amount').parent().parent().addClass('has-error');
            $('#amount').parent().find('span.help-block').html('Amount is required');
            error = true;
        } else {
            $('#amount').parent().parent().removeClass('has-error');
            $('#amount').parent().find('span.help-block').html('');
        }
        
        if (type == '{{ InvoiceLine::MANIFEST_SALARY }}' && (manifestId == '' || manifestNumber == '')) { 
            $('#manifestNumber').parent().parent().parent().addClass('has-error');
            $('#manifestNumber').parent().parent().find('span.help-block').html('Choose Manifest first');
            error = true;
        } else {
            $('#manifestNumber').parent().parent().parent().removeClass('has-error');
            $('#manifestNumber').parent().parent().find('span.help-block').html('');
        }

        if (type == '{{ InvoiceLine::PICKUP_SALARY }}' && (pickupId == '' || pickupNumber == '')) { 
            $('#pickupNumber').parent().parent().parent().addClass('has-error');
            $('#pickupNumber').parent().parent().find('span.help-block').html('Choose Pickup first');
            error = true;
        } else {
            $('#pickupNumber').parent().parent().parent().removeClass('has-error');
            $('#pickupNumber').parent().parent().find('span.help-block').html('');
        }

        if (type == '{{ InvoiceLine::DELIVERY_ORDER_SALARY }}' && (doId == '' || doNumber == '')) { 
            $('#doNumber').parent().parent().parent().addClass('has-error');
            $('#doNumber').parent().parent().find('span.help-block').html('Choose Do first');
            error = true;
        } else {
            $('#doNumber').parent().parent().parent().removeClass('has-error');
            $('#doNumber').parent().parent().find('span.help-block').html('');
        }
       
        if (error) {
            return;
        }

        if (tax ===null) { tax = 0;}

        var htmlTr = '<td >' + type + '</td>' +
            '<td >' + manifestNumber + '</td>' +
            '<td >' + pickupNumber + '</td>' +
            '<td >' + doNumber + '</td>' +
            '<td >' + description + '</td>' +
            '<td >' + positionMeaning + '</td>' +
            '<td class="text-right">' + amount + '</td>' +
            '<td class="text-right">' + tax + '</td>' +
            '<td class="text-right">' + fixAmount + '</td>' +
            '<td class="text-center">' +
            '<a data-toggle="tooltip" class="btn btn-xs btn-warning edit-line" ><i class="fa fa-pencil"></i></a> ' +
            '<a data-toggle="tooltip" class="btn btn-danger btn-xs delete-line" ><i class="fa fa-remove"></i></a>' +
            '<input type="hidden" name="lineId[]" value="'+ lineId + '">' +
            '<input type="hidden" name="type[]" value="' + type + '">' +
            '<input type="hidden" name="manifestId[]" value="' + manifestId + '">' +
            '<input type="hidden" name="manifestNumber[]" value="' + manifestNumber + '">' +
            '<input type="hidden" name="pickupId[]" value="' + pickupId + '">' +
            '<input type="hidden" name="pickupNumber[]" value="' + pickupNumber + '">' +
            '<input type="hidden" name="doId[]" value="' + doId + '">' +
            '<input type="hidden" name="doNumber[]" value="' + doNumber + '">' +
            '<input type="hidden" name="position[]" value="' + position + '">' +
            '<input type="hidden" name="positionMeaning[]" value="' + positionMeaning + '">' +
            '<input type="hidden" name="accountCodeId[]" value="' + accountCodeId + '">' +
            '<input type="hidden" name="accountCode[]" value="' + accountCode + '">' +
            '<input type="hidden" name="description[]" value="' + description + '">' +
            '<input type="hidden" name="amount[]" value="' + amount + '">' +
            '<input type="hidden" name="fixAmount[]" value="' + fixAmount + '">' +
            '<input type="hidden" name="amountHidden[]" value="' + amountHidden + '">' +
            '<input type="hidden" name="tax[]" value="' + tax + '">' +
            '</td>';

        if (dataIndexForm != '') {
            $('tr[data-index="' + dataIndexForm + '"]').html(htmlTr);
            dataIndex++;
        } else {
            $('#table-line tbody').append(
                '<tr data-index="' + dataIndex + '">' + htmlTr + '</tr>'
            );
            dataIndex++;
        }

        $('.edit-line').on('click', editLine);
        $('.delete-line').on('click', deleteLine);

        clearFormLine();
        calculateTotal();

        dataIndex++;
        $('#modal-form-line').modal("hide");
    };

    var editLine = function() {
        var dataIndexForm  = $(this).parent().parent().data('index');
        var type           = $(this).parent().parent().find('[name="type[]"]').val();
        var lineId         = $(this).parent().parent().find('[name="lineId[]"]').val();
        var manifestId     = $(this).parent().parent().find('[name="manifestId[]"]').val();
        var manifestNumber = $(this).parent().parent().find('[name="manifestNumber[]"]').val();
        var pickupId       = $(this).parent().parent().find('[name="pickupId[]"]').val();
        var pickupNumber   = $(this).parent().parent().find('[name="pickupNumber[]"]').val();
        var doId           = $(this).parent().parent().find('[name="doId[]"]').val();
        var doNumber       = $(this).parent().parent().find('[name="doNumber[]"]').val();
        var position       = $(this).parent().parent().find('[name="position[]"]').val();
        var positionMeaning = $(this).parent().parent().find('[name="positionMeaning[]"]').val();
        var description    = $(this).parent().parent().find('[name="description[]"]').val();
        var accountCodeId  = $(this).parent().parent().find('[name="accountCodeId[]"]').val();
        var accountCode    = $(this).parent().parent().find('[name="accountCode[]"]').val();
        var tax            = $(this).parent().parent().find('[name="tax[]"]').val();
        var amount         = $(this).parent().parent().find('[name="amount[]"]').val();
        var amountHidden   = $(this).parent().parent().find('[name="amountHidden[]"]').val();
        var fixAmount      = $(this).parent().parent().find('[name="fixAmount[]"]').val();

        clearFormLine();
        $('#dataIndexForm').val(dataIndexForm);
        $('#lineId').val(lineId);
        $('#type').val(type);
        $('#manifestId').val(manifestId);
        $('#manifestNumber').val(manifestNumber);
        $('#pickupId').val(pickupId);
        $('#pickupNumber').val(pickupNumber);
        $('#doNumber').val(doNumber);
        $('#doId').val(doId);
        $('#position').val(position);
        $('#positionMeaning').val(positionMeaning);
        $('#description').val(description);
        $('#accountCodeId').val(accountCodeId);
        $('#accountCode').val(accountCode);
        $('#amount').val(amount);
        $('#amountHidden').val(amountHidden);
        $('#fixAmount').val(fixAmount);
        $('#tax').val(tax);
   
        $('#amount').autoNumeric('update', {mDec: 0});

        $('#title-modal-line-detail').html('{{ trans('shared/common.edit') }}');
        $('#submit-modal-line').html('{{ trans('shared/common.edit') }}');

        $('#modal-form-line').modal("show");
    };

    var calculateAmountLine = function() {
        var amount = currencyToInt($('#amount').val());
        var tax = currencyToInt($('#tax').val());
        var fixAmount = amount + (tax * amount / 100);

        $('#amount').val(amount.formatMoney(0));
        $('#tax').val(tax.formatMoney(0));
        $('#fixAmount').val(fixAmount.formatMoney(0));
    };

    var deleteLine = function() {
        $(this).parent().parent().remove();
        calculateTotal();
    };

    var clearLines = function() {
        $('#table-line tbody').html('');
        calculateTotal();
    };

    var calculateTotal = function() {
        var totalAmount = 0;
        var totalTax = 0;
        var totalInvoice = 0;

        $('#table-line tbody tr').each(function (i, row) {
            var amount = parseFloat($(row).find('[name="amount[]"]').val().split(',').join(''));
            var fixAmount = parseFloat($(row).find('[name="fixAmount[]"]').val().split(',').join(''));
            var tax = parseFloat($(row).find('[name="tax[]"]').val().split(',').join(''));
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

    var clearFormLine = function() {
        $('#dataIndexForm').val('');
        $('#lineId').val('');
        $('#manifestId').val('');
        $('#manifestNumber').val('');
        $('#pickupId').val('');
        $('#pickupNumber').val('');
        $('#doId').val('');
        $('#doNumber').val('');
        $('#position').val('');
        $('#positionMeaning').val('');
        $('#accountCodeId').val();
        $('#accountCode').val('');
        $('#description').val('');
        $('#amount').val('');
        $('#fixAmount').val('');
        $('#tax').val('');

        $('#manifestNumber').parent().parent().parent().removeClass('has-error');
        $('#manifestNumber').parent().parent().find('span.help-block').html('');
        $('#pickupNumber').parent().parent().parent().removeClass('has-error');
        $('#pickupNumber').parent().parent().find('span.help-block').html('');
        $('#doNumber').parent().parent().parent().removeClass('has-error');
        $('#doNumber').parent().parent().find('span.help-block').html('');
        $('#downPayment').parent().parent().parent().removeClass('has-error');
        $('#downPayment').parent().parent().find('span.help-block').html('');
        $('#description').parent().parent().removeClass('has-error');
        $('#description').parent().find('span.help-block').html('');
        $('#amount').parent().parent().removeClass('has-error');
        $('#amount').parent().find('span.help-block').html('');
        $('#fixAmount').parent().parent().removeClass('has-error');
        $('#fixAmount').parent().find('span.help-block').html('');
    };

</script>
@endsection

