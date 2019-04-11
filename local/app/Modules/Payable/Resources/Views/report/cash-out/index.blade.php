@extends('layouts.master')

@section('title', trans('payable/menu.cash-out'))

<?php
    use App\Modules\Payable\Model\Transaction\InvoiceHeader;
    use App\Modules\Payable\Model\Transaction\Payment;
?>

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="widget">
            <div class="widget-header">
                <h2><i class="fa fa-usd"></i> {{ trans('payable/menu.cash-out') }}</h2>
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
                            <?php
                            $branchId  = '';
                            if (!empty(old('branchId'))) {
                                $branchId = old('branchId');
                            } elseif (!empty($filters['branchId'])) {
                                $branchId = $filters['branchId'];
                            }
                            ?>
                            <div class="form-group {{ $errors->has('branchId') ? 'has-error' : '' }}">
                                <label for="branchId" class="col-sm-4 control-label">Cabang </label>
                                <div class="col-sm-8">
                                    <select class="form-control" name="branchId" id="branchId">
                                        <option value="" >All Branch</option>
                                        @foreach($optionBranch as $branch)
                                        <option value="{{ $branch->branch_id }}" {{ $branchId == $branch->branch_id ? 'selected' : '' }}>{{ $branch->branch_name }}</option>
                                        @endforeach
                                    </select>
                                    @if($errors->has('branchId'))
                                        <span class="help-block">{{ $errors->first('branchId') }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="description" class="col-sm-4 control-label">{{ trans('shared/common.description') }}</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="description" name="description" value="{{ !empty($filters['description']) ? $filters['description'] : '' }}">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="createdBy" class="col-sm-4 control-label">{{ trans('shared/common.created-by') }}</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="createdBy" name="createdBy" value="{{ !empty($filters['createdBy']) ? $filters['createdBy'] : '' }}">
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
                                <th>{{ trans('payable/fields.payment-number') }}<hr/>
                                    {{ trans('payable/fields.invoice-number') }}</th>
                                <th>{{ trans('shared/common.type') }}<hr>
                                    {{ trans('shared/common.date') }}</th>
                                <th>{{ trans('payable/fields.trading-code') }}<hr/>
                                    {{ trans('payable/fields.trading-name') }}</th>
                                <th>{{ trans('shared/common.address') }}</th>
                                <th>{{ trans('payable/fields.payment-method') }}<hr/>
                                    {{ trans('accountreceivables/fields.cash-or-bank') }}</th>
                                <th>{{ trans('payable/fields.total-amount') }}<hr/>
                                    {{ trans('payable/fields.total-interest') }}</th>
                                <th>{{ trans('payable/fields.total-payment') }}</th>
                                <th>{{ trans('shared/common.status') }}<hr/>
                                    {{ trans('shared/common.created-by') }}</th>
                                <th>{{ trans('shared/common.description') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $no = 1 ?>
                            @foreach($modelsPayment as $model)
                             <?php
                                 $date  = !empty($model->created_date) ? new \DateTime($model->created_date) : null;
                             ?>
                            <tr>
                                <td class="text-center">{{ $no++ }}</td>
                                <td>{{ $model->payment_number }}<hr/>
                                    {{ $model->invoice_number }}</td>
                                <td>{{ $model->type_name }}<hr>
                                    {{ !empty($date) ? $date->format('d-M-Y') : '' }}</td>
                                @if( in_array($model->type_id, InvoiceHeader::VENDOR_TYPE))
                                <td>{{ $model->vendor_code }}<hr/>
                                    {{ $model->vendor_name }}</td>
                                <td>{{ $model->vendor_address }}</td>
                                @elseif(in_array($model->type_id, InvoiceHeader::DRIVER_TYPE))
                                <td>{{ $model->driver_code }}<hr/>
                                    {{ $model->driver_name }}</td>
                                <td>{{ $model->driver_address }}</td>
                                @else
                                <td></td>
                                <td></td>
                                <td></td>
                                @endif
                                <td>{{ $model->payment_method }}<hr/>
                                    {{ $model->bank_name }}</td>
                                <td class="text-right">{{ number_format($model->total_amount) }}<hr/>
                                    {{ number_format($model->total_interest) }}</td>
                                <td class="text-right">{{ number_format($model->total_amount + $model->total_interest) }}</td>
                                <td>{{ $model->status }}<hr/>
                                    {{ $model->full_name }}</td>
                                <td>{{ $model->note }}</td>
                            </tr>
                            @endforeach
                            @foreach($modelsGl as $model)
                             <?php
                                 $date  = !empty($model->journal_date) ? new \DateTime($model->journal_date) : null;
                             ?>
                            <tr>
                                <td class="text-center">{{ $no++ }}</td>
                                <td>{{ $model->journal_number }}</td>
                                <td>{{ !empty($date) ? $date->format('d-M-Y') : '' }}</td>
                                <td></td>
                                <td></td>
                                <td>{{ $model->coa_code }} <hr/> {{ $model->coa_description }}</td>
                                <td class="text-right"></td>
                                <td class="text-right">{{ number_format($model->credit) }}</td>
                                <td>{{ $model->full_name }}</td>
                                <td>{{ $model->description }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection


@section('modal')
@parent

@endsection

@section('script')
@parent

@endsection
