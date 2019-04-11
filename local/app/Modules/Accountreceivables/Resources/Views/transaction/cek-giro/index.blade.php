<?php
use App\Modules\Accountreceivables\Model\Transaction\CekGiroHeader;
?>

@extends('layouts.master')

@section('title', trans('accountreceivables/menu.cek-giro'))

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="widget">
            <div class="widget-header">
                <h2><i class="fa fa-credit-card"></i> {{ trans('accountreceivables/menu.cek-giro') }}</h2>
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
                                <label for="cekGiroNumber" class="col-sm-4 control-label">{{ trans('accountreceivables/fields.cek-giro-number') }}</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="cekGiroNumber" name="cekGiroNumber" value="{{ !empty($filters['cekGiroNumber']) ? $filters['cekGiroNumber'] : '' }}">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="cekGiroAccountNumber" class="col-sm-4 control-label">{{ trans('accountreceivables/fields.cek-giro-account-number') }}</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="cekGiroAccountNumber" name="cekGiroAccountNumber" value="{{ !empty($filters['cekGiroAccountNumber']) ? $filters['cekGiroAccountNumber'] : '' }}">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="customer" class="col-sm-4 control-label">{{ trans('operational/fields.customer') }}</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="customer" name="customer" value="{{ !empty($filters['customer']) ? $filters['customer'] : '' }}">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="bankName" class="col-sm-4 control-label">{{ trans('accountreceivables/fields.bank') }}</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="bankName" name="bankName" value="{{ !empty($filters['bankName']) ? $filters['bankName'] : '' }}">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="type" class="col-sm-4 control-label">{{ trans('shared/common.type') }}</label>
                                <div class="col-sm-8">
                                    <select id="type" name="type" class="form-control">
                                        <option value="">ALL</option>
                                        @foreach($optionType as $option)
                                            <option value="{{ $option }}" {{ !empty($filters['type']) && $filters['type'] == $option ? 'selected' : '' }}>{{ $option }}</option>
                                        @endforeach
                                    </select>
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
                            <div class="form-group">
                                <label for="dueDateFrom" class="col-sm-4 control-label">{{ trans('accountreceivables/fields.due-date-from') }}</label>
                                <div class="col-sm-8">
                                    <div class="input-group">
                                        <input type="text" id="dueDateFrom" name="dueDateFrom" class="form-control datepicker-input" data-mask="99-99-9999" placeholder="dd-mm-yyyy" value="{{ !empty($filters['dueDateFrom']) ? $filters['dueDateFrom'] : '' }}">
                                        <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="dueDateTo" class="col-sm-4 control-label">{{ trans('accountreceivables/fields.due-date-to') }}</label>
                                <div class="col-sm-8">
                                    <div class="input-group">
                                        <input type="text" id="dueDateTo" name="dueDateTo" class="form-control datepicker-input" data-mask="99-99-9999" placeholder="dd-mm-yyyy" value="{{ !empty($filters['dueDateTo']) ? $filters['dueDateTo'] : '' }}">
                                        <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="status" class="col-sm-4 control-label">{{ trans('shared/common.status') }}</label>
                                <div class="col-sm-8">
                                    <select id="status" name="status" class="form-control">
                                        <option value="">ALL</option>
                                        @foreach($optionStatus as $option)
                                            <option value="{{ $option }}" {{ !empty($filters['status']) && $filters['status'] == $option ? 'selected' : '' }}>{{ $option }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-12 data-table-toolbar text-right">
                            <div class="form-group">
                                <button type="submit" class="btn btn-sm btn-info"><i class="fa fa-search"></i> {{ trans('shared/common.filter') }}</button>
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
                                <th>
                                    {{ trans('accountreceivables/fields.cek-giro-number') }}<hr/>
                                    {{ trans('accountreceivables/fields.cek-giro-account-number') }}<hr/>
                                    {{ trans('shared/common.type') }}
                                </th>
                                <th>
                                    {{ trans('shared/common.date') }}<hr/>
                                    {{ trans('accountreceivables/fields.due-date') }}<hr/>
                                    {{ trans('accountreceivables/fields.clearing-date') }}
                                </th>
                                <th>{{ trans('shared/common.customer') }}<hr/>{{ trans('shared/common.person-name') }}</th>
                                <th>{{ trans('shared/common.address') }}<hr/>{{ trans('shared/common.phone') }}</th>
                                <th>{{ trans('accountreceivables/fields.bank') }}</th>
                                <th>{{ trans('accountreceivables/fields.total') }}</th>
                                <th>{{ trans('shared/common.status') }}</th>
                                <th width="80px">{{ trans('shared/common.action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $no = ($models->currentPage() - 1) * $models->perPage() + 1; ?>
                            @foreach($models as $model)
                            <?php
                            $model = CekGiroHeader::find($model->cek_giro_header_id);
                            $cekGiroDate = !empty($model->cek_giro_date) ? new \DateTime($model->cek_giro_date) : null;
                            $dueDate = !empty($model->due_date) ? new \DateTime($model->due_date) : null;
                            $clearingDate = !empty($model->clearing_date) ? new \DateTime($model->clearing_date) : null;
                            ?>
                            <tr>
                                <td class="text-center">{{ $no++ }}</td>
                                <td>
                                    {{ $model->cek_giro_number }}<hr/>
                                    {{ $model->cek_giro_account_number }}<hr/>
                                    {{ $model->type }}
                                </td>
                                <td>
                                    {{ $cekGiroDate !== null ? $cekGiroDate->format('d-m-Y') : '' }}<hr/>
                                    {{ $dueDate !== null ? $dueDate->format('d-m-Y') : '' }}<hr/>
                                    {{ $clearingDate !== null ? $clearingDate->format('d-m-Y') : '' }}
                                </td>
                                <td>{{ $model->customer !== null ? $model->customer->customer_name : '' }}<hr/>{{ $model->person_name }}</td>
                                <td>{{ $model->address }}<hr/>{{ $model->phone_number }}</td>
                                <td>{{ $model->bank_name }}</td>
                                <td class="text-right">{{ number_format($model->totalAmount()) }}</td>
                                <td>{{ $model->status }}</td>

                                <td class="text-center">
                                    <a href="{{ URL($url . '/edit/' . $model->cek_giro_header_id) }}" data-toggle="tooltip" class="btn btn-xs btn-warning" data-original-title="{{ trans('shared/common.edit') }}">
                                        <i class="fa fa-pencil"></i>
                                    </a>

                                    @if(Gate::check('access', [$resource, 'cancel']) && $model->isOpen())
                                    <a data-id="{{ $model->cek_giro_header_id }}" data-label="{{ $model->cek_giro_number }}" data-toggle="tooltip" class="btn btn-danger btn-xs md-trigger cancel-action" data-original-title="{{ trans('shared/common.cancel') }} PR" data-modal="modal-cancel">
                                        <i class="fa fa-remove"></i>
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

@section('modal')
@parent
<div class="md-modal md-3d-flip-horizontal" id="modal-cancel">
    <div class="md-content">
        <h3 style="padding-bottom: 0px;"><strong>{{ trans('shared/common.cancel') }}</strong> {{ trans('accountreceivables/menu.cek-giro') }}</h3>
        <div>
            <div class="row">
                <div class="col-sm-12">
                    <h4 id="cancel-text">Are you sure want to cancel ?</h4>
                    <form id="form-cancel" role="form" method="post" action="{{ URL($url . '/cancel') }}">
                        {{ csrf_field() }}
                        <input type="hidden" id="id" name="id" >
                        <div class="form-group">
                            <h4 for="reason" class="col-sm-4 control-label">{{ trans('shared/common.reason') }} <span class="required">*</span></h4>
                            <div class="col-sm-8">
                                <textarea name="reason" class="form-control" rows="4"></textarea>
                            </div>
                            <span class="help-block text-center"></span>
                        </div>
                        <div class="form-group">
                            <div class="col-sm-12 text-right">
                                <br>
                                <a class="btn btn-danger md-close">{{ trans('shared/common.no') }}</a>
                                <button id="btn-cancel" type="submit" class="btn btn-success">{{ trans('shared/common.yes') }}</button>
                            </div>
                        </div>
                    </form>
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
    $('.cancel-action').on('click', function() {
        $("#id").val($(this).data('id'));
        $("#cancel-text").html(
            '{{ trans('shared/common.cancel-confirmation', ['variable' => trans('accountreceivables/menu.cek-giro')]) }} ' + $(this).data('label') + '?'
        );
        clearFormCancel()
    });

    $('#btn-cancel').on('click', function(event) {
        event.preventDefault();
        if ($('textarea[name="reason"]').val() == '') {
            $(this).parent().parent().parent().addClass('has-error');
            $(this).parent().parent().parent().find('span.help-block').html('Reason is required');
            return
        } else {
            clearFormCancel()
        }

        $('#form-cancel').trigger('submit');
    });
});

var clearFormCancel = function() {
    $('#form-cancel').removeClass('has-error');
    $('#form-cancel').find('span.help-block').html('');
};
</script>
@endsection
