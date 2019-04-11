@extends('layouts.master')

@section('title', trans('marketing/menu.pickup-request'))
<?php use App\Modules\Marketing\Model\Transaction\PickupRequest; ?>
@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="widget">
            <div class="widget-header">
                <h2><i class="fa fa-users"></i> {{ trans('marketing/menu.pickup-request') }}</h2>
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
                                <label for="pickupRequestNumber" class="col-sm-4 control-label">{{ trans('marketing/fields.pickup-request-number') }}</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="pickupRequestNumber" name="pickupRequestNumber" value="{{ !empty($filters['pickupRequestNumber']) ? $filters['pickupRequestNumber'] : '' }}">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="customerName" class="col-sm-4 control-label">{{ trans('operational/fields.customer') }}</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="customerName" name="customerName" value="{{ !empty($filters['customerName']) ? $filters['customerName'] : '' }}">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="branch" class="col-sm-4 control-label">{{ trans('shared/common.status') }}</label>
                                <div class="col-sm-8">
                                    <select class="form-control" id="status" name="status">
                                        <option value="" >ALL</option>
                                        @foreach($optionStatus as $status)
                                        <option value="{{ $status }}" {{ !empty($filters['status']) && $filters['status'] == $status ? 'selected' : '' }}>{{ $status }}</option>
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
                        </div>
                        <div class="col-sm-12 data-table-toolbar text-right">
                            <div class="form-group">
                                <button type="submit" class="btn btn-sm btn-info"><i class="fa fa-search"></i> {{ trans('shared/common.filter') }}</button>
                                <a href="{{ URL($url.'/print-excel') }}" class="button btn btn-sm btn-success" target="_blank">
                                    <i class="fa fa-file-excel-o"></i> {{ trans('shared/common.print-excel') }}
                                </a>
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
                                <th>{{ trans('shared/common.num') }}</th>
                                <th>{{ trans('marketing/fields.pickup-request-number') }}</th>
                                <th>{{ trans('operational/fields.customer') }}<hr/>
                                    {{ trans('operational/fields.sender') }}</th>
                                <th>{{ trans('operational/fields.item-name') }}<hr/>
                                    {{ trans('operational/fields.total-coly') }} (kg)</th>
                                <th>{{ trans('operational/fields.weight') }} (kg)<hr/>
                                    {{ trans('operational/fields.dimension') }} (m<sup>3</sup>)</th>
                                <th>{{ trans('operational/fields.pickup-cost') }}</th>
                                <th>{{ trans('shared/common.date') }}<hr/>
                                    {{ trans('shared/common.time') }}</th>
                                <th>{{ trans('shared/common.status') }}</th>
                                <th>{{ trans('shared/common.note') }}</th>
                                <th>{{ trans('operational/fields.approved-note') }}</th>
                                <th width="100px">{{ trans('shared/common.action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $no = ($models->currentPage() - 1) * $models->perPage() + 1; ?>
                            @foreach($models as $model)
                            <?php
                                 $date = !empty($model->pickup_request_time) ? \App\Service\TimezoneDateConverter::getClientDateTime($model->pickup_request_time) : null;
                             ?>
                            <tr>
                                <td class="text-center">{{ $no++ }}</td>
                                <td>{{ $model->pickup_request_number }}</td>
                                <td>{{ $model->mst_customer_name }}<hr/>
                                    {{ $model->customer_name }}</td>
                                <td>{{ $model->item_name }}<hr/>
                                {{ $model->total_coly }}</td>
                                <td class="text-right">
                                    {{ number_format($model->weight, 2) }}
                                <hr/>
                                    {{ number_format($model->dimension, 6) }}
                                </td>
                                <td class="text-right">{{ number_format($model->pickup_cost) }}</td>
                                <td>{{ !empty($date) ? $date->format('d-M-Y') : '' }}<hr/>
                                    {{ !empty($date) ? $date->format('H:i') : '' }}</td>
                                <td>{{ $model->status }}</td>
                                <td >{{ substr($model->note, 0, 50)}}@if(strlen($model->note) > 50)......@endif</td>
                                <td >{{ substr($model->note_add, 0, 50)}}@if(strlen($model->note_add) > 50)......@endif</td>
                                <td class="text-center">
                                    <a href="{{ URL($url . '/edit/' . $model->pickup_request_id) }}" data-toggle="tooltip" class="btn btn-xs btn-warning" data-original-title="{{ trans('shared/common.edit') }}">
                                        <i class="fa fa-pencil"></i>
                                    </a>
                                    @if(Gate::check('access', [$resource, 'approve']) && $model->status == PickupRequest::OPEN)
                                    <a href="{{ URL($url . '/approve/' . $model->pickup_request_id) }}" data-toggle="tooltip" class="btn btn-xs btn-info" data-original-title="{{ trans('shared/common.approve') }}">
                                        <i class="fa fa-gavel"></i>
                                    </a>
                                    @endif
                                    @if($model->status == PickupRequest::APPROVED || $model->status == PickupRequest::CLOSED)
                                    <a href="{{ URL($url . '/print-pdf-detail/' . $model->pickup_request_id) }}" target="_blank" data-toggle="tooltip" class="btn btn-xs btn-success" data-original-title="{{ trans('shared/common.print') }}">
                                        <i class="fa fa-print"></i>
                                    </a>
                                    @endif
                                    @if(Gate::check('access', [$resource, 'cancel']) && $model->status != PickupRequest::CLOSED && $model->status != PickupRequest::CANCELED)
                                    <a data-id="{{ $model->pickup_request_id }}" data-label="{{ $model->pickup_request_number }}" data-toggle="tooltip" class="btn btn-danger btn-xs md-trigger cancel-action" data-original-title="{{ trans('shared/common.cancel') }} PR" data-modal="modal-cancel">
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
        <h3 style="padding-bottom: 0px;"><strong>{{ trans('shared/common.cancel') }}</strong> {{ trans('marketing/menu.pickup-request') }}</h3>
        <div>
            <div class="row">
                <div class="col-sm-12">
                    <h4 id="cancel-text">Are you sure want to cancel ?</h4>
                    <form id="form-cancel" role="form" method="post" action="{{ URL($url . '/cancel-pr') }}">
                        {{ csrf_field() }}
                        <input type="hidden" id="cancel-id" name="id" >
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
                                <button id="btn-cancel-pr" type="submit" class="btn btn-success">{{ trans('shared/common.yes') }}</button>
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
            $("#cancel-id").val($(this).data('id'));
            $("#cancel-text").html('{{ trans('purchasing/fields.cancel-confirmation', ['variable' => trans('marketing/menu.pickup-request')]) }} ' + $(this).data('label') + '?');
            clearFormCancel()
        });

        $('#btn-cancel-pr').on('click', function(event) {
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