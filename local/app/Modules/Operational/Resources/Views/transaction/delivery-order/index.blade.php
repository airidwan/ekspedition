@extends('layouts.master')

@section('title', trans('operational/menu.delivery-order'))
<?php use App\Modules\Operational\Model\Transaction\TransactionResiHeader; ?>
<?php use App\Modules\Operational\Model\Transaction\DeliveryOrderHeader; ?>
@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="widget">
            <div class="widget-header">
                <h2><i class="fa fa-truck"></i> {{ trans('operational/menu.delivery-order') }}</h2>
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
                                <label for="deliveryOrderNumber" class="col-sm-4 control-label">{{ trans('operational/fields.do-number') }}</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="deliveryOrderNumber" name="deliveryOrderNumber" value="{{ !empty($filters['deliveryOrderNumber']) ? $filters['deliveryOrderNumber'] : '' }}">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="driver" class="col-sm-4 control-label">{{ trans('operational/fields.driver') }}</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="driver" name="driver" value="{{ !empty($filters['driver']) ? $filters['driver'] : '' }}">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="policeNumber" class="col-sm-4 control-label">{{ trans('operational/fields.police-number') }}</label>
                                <div class="col-sm-8">
                                    <input type="text" style="text-transform: uppercase" class="form-control" id="policeNumber" name="policeNumber" value="{{ !empty($filters['policeNumber']) ? $filters['policeNumber'] : '' }}">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="resiNumber" class="col-sm-4 control-label">{{ trans('operational/fields.resi-number') }}</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="resiNumber" name="resiNumber" value="{{ !empty($filters['resiNumber']) ? $filters['resiNumber'] : '' }}">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="receiverName" class="col-sm-4 control-label">{{ trans('operational/fields.receiver-name') }}</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="receiverName" name="receiverName" value="{{ !empty($filters['receiverName']) ? $filters['receiverName'] : '' }}">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="branch" class="col-sm-4 control-label"></label>
                                <div class="col-sm-8">
                                    <?php $jenis = !empty($filters['jenis']) ? $filters['jenis'] : 'headers' ?>
                                    <label class="radio-inline iradio">
                                        <input type="radio" name="jenis" id="radio1" value="headers" {{ $jenis == 'headers' ? 'checked' : '' }}> Headers
                                    </label>
                                    <label class="radio-inline iradio">
                                        <input type="radio" name="jenis" id="radio2" value="lines" {{ $jenis == 'lines' ? 'checked' : '' }}> Lines
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6 portlets">
                            <div class="form-group">
                                <label for="partnerName" class="col-sm-4 control-label">{{ trans('operational/fields.partner-name') }}</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="partnerName" name="partnerName" value="{{ !empty($filters['partnerName']) ? $filters['partnerName'] : '' }}">
                                </div>
                            </div>
                            <div class="form-group {{ $errors->has('type') ? 'has-error' : '' }}">
                                <label for="type" class="col-sm-4 control-label">{{ trans('shared/common.type') }}</label>
                                <div class="col-sm-8">
                                    <select class="form-control" id="type" name="type">
                                        <?php $stringType = !empty($filters['type']) ? $filters['type'] : ''; ?>
                                        <option value="">ALL</option>
                                        @foreach($optionType as $type)
                                        <option value="{{ $type }}" {{ $type == $stringType ? 'selected' : '' }}>{{ $type }}</option>
                                        @endforeach
                                    </select>
                                    @if($errors->has('type'))
                                    <span class="help-block">{{ $errors->first('type') }}</span>
                                    @endif
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
                @if (empty($filters['jenis']) || $filters['jenis'] == 'headers')
                    <table class="table table-striped table-bordered" cellspacing="0" width="100%">
                        <thead>
                            <tr>
                                <th>{{ trans('shared/common.num') }}</th>
                                <th>{{ trans('operational/fields.do-number') }}</th>
                                <th>{{ trans('operational/fields.driver') }}</th>
                                <th>{{ trans('operational/fields.driver-assistant') }}</th>
                                <th>{{ trans('operational/fields.police-number') }}</th>
                                <th>{{ trans('shared/common.type') }}</th>
                                <th>{{ trans('operational/fields.partner-name') }}</th>
                                <th>{{ trans('operational/fields.start-date') }}</th>
                                <th>{{ trans('operational/fields.end-date') }}</th>
                                <th>{{ trans('operational/fields.draft-do') }}</th>
                                <th>{{ trans('shared/common.status') }}</th>
                                <th width="80px">{{ trans('shared/common.action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $no = ($models->currentPage() - 1) * $models->perPage() + 1; ?>
                            @foreach($models as $model)
                            <?php
                                 $startTime      = !empty($model->delivery_start_time) ? \App\Service\TimezoneDateConverter::getClientDateTime($model->delivery_start_time) : null;
                                 $endTime      = !empty($model->delivery_end_time) ? \App\Service\TimezoneDateConverter::getClientDateTime($model->delivery_end_time) : null;
                             ?>
                            <tr>
                                <td class="text-center">{{ $no++ }}</td>
                                <td>{{ $model->delivery_order_number }}</td>
                                <td>{{ $model->driver_name }}</td>
                                <td>{{ $model->assistant_name }}</td>
                                <td>{{ $model->police_number }}</td>
                                <td>{{ $model->type }}</td>
                                <td>{{ $model->vendor_name }}</td>
                                <td>{{ !empty($startTime) ? $startTime->format('d-M-Y H:i') : '' }}</td>
                                <td>{{ !empty($endTime) ? $endTime->format('d-M-Y H:i') : '' }}</td>
                                <td>{{ $model->draft_delivery_order_number }}</td>
                                <td>{{ $model->status }}</td>
                                <td class="text-center">
                                    <a href="{{ URL($url . '/edit/' . $model->delivery_order_header_id) }}" data-toggle="tooltip" class="btn btn-xs btn-warning" data-original-title="{{ trans('shared/common.edit') }}">
                                        <i class="fa fa-pencil"></i>
                                    </a>
                                    @if(in_array($model->status, DeliveryOrderHeader::canPrint()))
                                    <a href="{{ URL($url . '/print-pdf-detail/' . $model->delivery_order_header_id) }}" target="_blank" data-toggle="tooltip" class="btn btn-xs btn-success" data-original-title="{{ trans('shared/common.print') }}">
                                        <i class="fa fa-print"></i>
                                    </a>
                                    @endif
                                    @if(Gate::check('access', [$resource, 'cancel']) && $model->status != DeliveryOrderHeader::CLOSED && $model->status != DeliveryOrderHeader::CANCELED && $model->status != DeliveryOrderHeader::ON_THE_ROAD)
                                    <a data-id="{{ $model->delivery_order_header_id }}" data-label="{{ $model->delivery_order_number }}" data-toggle="tooltip" class="btn btn-danger btn-xs md-trigger cancel-action" data-original-title="{{ trans('shared/common.cancel') }} PO" data-modal="modal-cancel">
                                        <i class="fa fa-remove"></i>
                                    </a>
                                    @endcan
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <table class="table table-striped table-bordered" cellspacing="0" width="100%">
                        <thead>
                            <tr>
                                <th>{{ trans('shared/common.num') }}</th>
                                <th>{{ trans('operational/fields.resi-number') }}</th>
                                <th>{{ trans('operational/fields.do-number') }}</th>
                                <th>{{ trans('operational/fields.item-name') }}</th>    
                                <th>{{ trans('operational/fields.receiver') }}</th>
                                <th>{{ trans('shared/common.address') }}</th>
                                <th>{{ trans('shared/common.telepon') }}</th>
                                <th>{{ trans('operational/fields.total-send') }}</th>
                                <th style="min-width:60px;">{{ trans('shared/common.action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $no = ($models->currentPage() - 1) * $models->perPage() + 1; ?>
                            @foreach($models as $model)
                            <tr>
                                <td class="text-center">{{ $no++ }}</td>
                                <td>{{ $model->delivery_order_number }}</td>
                                <td>{{ $model->resi_number }}</td>
                                <td>{{ $model->item_name }}</td>
                                <td>{{ $model->receiver_name }}</td>
                                <td>{{ $model->receiver_address }}</td>
                                <td>{{ $model->receiver_phone }}</td>
                                <td class="text-center">{{ number_format($model->coly_send) }}</td>
                                <td class="text-center">
                                    @can('access', [$resource, 'update'])
                                    <a href="{{ URL($url . '/edit/' . $model->delivery_order_header_id) }}" data-toggle="tooltip" class="btn btn-xs btn-warning" data-original-title="{{ trans('shared/common.edit') }}">
                                        <i class="fa fa-pencil"></i>
                                    </a>
                                    @endcan
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
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
        <h3 style="padding-bottom: 0px;"><strong>{{ trans('shared/common.cancel') }}</strong> {{ trans('operational/menu.delivery-order') }}</h3>
        <div>
            <div class="row">
                <div class="col-sm-12">
                    <h4 id="cancel-text">Are you sure want to cancel ?</h4>
                    <form id="form-cancel" role="form" method="post" action="{{ URL($url . '/cancel-do') }}">
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
                                <button id="btn-cancel-do" type="submit" class="btn btn-success">{{ trans('shared/common.yes') }}</button>
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
            $("#cancel-text").html('{{ trans('purchasing/fields.cancel-confirmation', ['variable' => trans('operational/menu.delivery-order')]) }} ' + $(this).data('label') + '?');
            clearFormCancel()
        });

        $('#btn-cancel-do').on('click', function(event) {
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
