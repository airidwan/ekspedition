<?php
use App\Modules\Accountreceivables\Model\Transaction\Receipt;
?>

@extends('layouts.master')

@section('title', trans('accountreceivables/menu.cash-in'))

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="widget">
            <div class="widget-header">
                <h2><i class="fa fa-credit-card"></i> {{ trans('accountreceivables/menu.cash-in') }}</h2>
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
                                <th width="30px">{{ trans('shared/common.num') }}</th>
                                <th>
                                    {{ trans('accountreceivables/fields.receipt-number') }}<hr/>
                                    {{ trans('accountreceivables/fields.date') }}
                                </th>
                                <th>{{ trans('shared/common.type') }}<hr>
                                    {{ trans('shared/common.created-by') }}</th>
                                <th>
                                    {{ trans('accountreceivables/fields.invoice-number') }}<hr/>
                                    {{ trans('accountreceivables/fields.invoice-type') }}
                                </th>
                                <th>
                                    {{ trans('operational/fields.resi-number') }}<hr/>
                                    {{ trans('operational/fields.route') }}
                                </th>
                                <th>
                                    {{ trans('shared/common.customer') }}<hr/>
                                    {{ trans('operational/fields.sender') }}
                                </th>
                                <th>
                                    {{ trans('shared/common.customer') }}<hr/>
                                    {{ trans('operational/fields.receiver') }}
                                </th>
                                <th>{{ trans('accountreceivables/fields.receipt-method') }}<hr/>
                                    {{ trans('accountreceivables/fields.cash-or-bank') }}
                                </th>
                                <th>{{ trans('accountreceivables/fields.amount') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $no = 1; ?>
                            @foreach($models as $model)
                            <?php
                            $model = Receipt::find($model->receipt_id);
                            $createdDate = !empty($model->created_date) ? new \DateTime($model->created_date) : null;
                            ?>
                            <tr>
                                <td class="text-center">{{ $no++ }}</td>
                                <td class="text-center">
                                    {{ $model->receipt_number }}<hr/>
                                    {{ $createdDate !== null ? $createdDate->format('d-m-Y') : '' }}
                                </td>
                                <td>{{ $model->type }}<hr>
                                    {{ !empty($model->createdBy) ? $model->createdBy->full_name : '' }}</td>
                                <td>
                                    {{ !empty($model->invoice) ? $model->invoice->invoice_number : '' }}<hr/>
                                    {{ !empty($model->invoice) ? $model->invoice->type : '' }}
                                </td>
                                <td>
                                    {{ !empty($model->invoice->resi) ? $model->invoice->resi->resi_number : '' }}<hr/>
                                    {{ !empty($model->invoice->resi->route) ? $model->invoice->resi->route->route_code : '' }}
                                </td>
                                <td>
                                    {{ !empty($model->invoice->resi->customer) ? $model->invoice->resi->customer->customer_name : '' }}<hr/>
                                    {{ !empty($model->invoice->resi) ? $model->invoice->resi->sender_name : '' }}
                                </td>
                                <td>
                                    {{ !empty($model->invoice->resi->customerReceiver) ? $model->invoice->resi->customerReceiver->customer_name : '' }}<hr/>
                                    {{ !empty($model->invoice->resi) ? $model->invoice->resi->receiver_name : '' }}
                                </td>
                                <td>{{ $model->receipt_method }}<hr/>
                                    {{ !empty($model->bank) ? $model->bank->bank_name : '' }}
                                </td>
                                <td class="text-right">{{ number_format($model->amount) }}</td>
                            </tr>
                            @endforeach
                            @foreach($modelsGl as $model)
                             <?php
                                 $date  = !empty($model->journal_date) ? new \DateTime($model->journal_date) : null;
                             ?>
                            <tr>
                                <td class="text-center">{{ $no++ }}</td>
                                <td>{{ $model->journal_number }}<hr/>
                                    {{ !empty($date) ? $date->format('d-M-Y') : '' }}</td>
                                <td>{{ $model->full_name }}</td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td>{{ $model->description }}</td>
                                <td>{{ $model->coa_code }} <hr/> {{ $model->coa_description }}</td>
                                <td class="text-right">{{ number_format($model->debet) }}</td>
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

@section('script')
@parent
<script type="text/javascript">
    $(document).on('ready', function(){
    });
</script>
@endsection
