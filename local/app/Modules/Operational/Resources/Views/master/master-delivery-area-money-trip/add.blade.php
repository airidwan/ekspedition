@extends('layouts.master')

@section('title', trans('operational/menu.delivery-area-money-trip'))

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="widget">
            <div class="widget-header">
                <h2><i class="fa fa-truck"></i> <strong>{{ $title }}</strong> {{ trans('operational/menu.delivery-area-money-trip') }}</h2>
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
                        <input type="hidden" id="id" name="id" value="{{ count($errors) > 0 ? old('id') : $model->delivery_area_money_trip_id }}">
                        <div class="col-sm-6 portlets">
                            <?php 
                            $deliveryAreaName = !empty($model->deliveryArea) ? $model->deliveryArea->delivery_area_name : '';
                            ?>
                            <div class="form-group {{ $errors->has('deliveryAreaId') ? 'has-error' : '' }}">
                                <label for="deliveryAreaName" class="col-sm-4 control-label">{{ trans('operational/fields.delivery-area') }} <span class="required">*</span></label>
                                <div class="col-sm-8">
                                    <div class="input-group">
                                        <input type="hidden" id="deliveryAreaId" name="deliveryAreaId" value="{{ count($errors) > 0 ? old('deliveryAreaId') : $model->delivery_area_id }}">
                                        <input type="text" class="form-control" id="deliveryAreaName" name="deliveryAreaName" value="{{ count($errors) > 0 ? old('deliveryAreaName') : $deliveryAreaName }}" readonly>
                                        <span class="btn input-group-addon" data-toggle="modal" data-target="#modal-delivery-area"><i class="fa fa-search"></i></span>
                                    </div>
                                    @if($errors->has('deliveryAreaId'))
                                    <span class="help-block">{{ $errors->first('deliveryAreaId') }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="form-group {{ $errors->has('type') ? 'has-error' : '' }}">
                                <label for="type" class="col-sm-4 control-label">{{ trans('operational/fields.vehicle-type') }} <span class="required">*</span></label>
                                <div class="col-sm-8">
                                    <select class="form-control" id="type" name="type">
                                        <?php $stringType = count($errors) > 0 ? old('type') : $model->vehicle_type; ?>
                                        <option value="">{{ trans('shared/common.please-select') }}</option>
                                        @foreach($optionType as $type)
                                        <option value="{{ $type->lookup_code }}" {{ $type->lookup_code == $stringType ? 'selected' : '' }}>{{ $type->meaning }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="form-group {{ $errors->has('moneyTrip') ? 'has-error' : '' }}">
                                <label for="moneyTrip" class="col-sm-4 control-label">{{ trans('operational/fields.money-trip') }} <span class="required">*</span></label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control currency text-right" id="moneyTrip" name="moneyTrip" value="{{ count($errors) > 0 ? str_replace(',', '', old('moneyTrip')) : $model->money_trip_rate }}">
                                    @if($errors->has('moneyTrip'))
                                    <span class="help-block">{{ $errors->first('moneyTrip') }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="form-group ">
                                <label for="description" class="col-sm-4 control-label">{{ trans('shared/common.description') }}</label>
                                <div class="col-sm-8">
                                    <textarea type="text" class="form-control" id="description" name="description" rows="4">{{ count($errors) > 0 ? old('description') : $model->description }}</textarea>
                                    @if($errors->has('description'))
                                    <span class="help-block">{{ $errors->first('description') }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="form-group {{ $errors->has('status') ? 'has-error' : '' }}">
                                <label for="status" class="col-sm-4 control-label">{{ trans('shared/common.status') }}</label>
                                <div class="col-sm-8">
                                    <label class="checkbox-inline icheckbox">
                                        <?php $status = count($errors) > 0 ? old('status') : $model->active; ?>
                                        <input type="checkbox" id="status" name="status" value="Y" {{ $status == 'Y' ? 'checked' : '' }}> {{ trans('shared/common.active') }}
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-12 data-table-toolbar text-right">
                            <div class="form-group">
                                <a href="{{ URL($url) }}" class="btn btn-sm btn-warning"><i class="fa fa-reply">
                                </i> {{ trans('shared/common.cancel') }}
                            </a>
                            <button type="submit" class="btn btn-sm btn-primary"><i class="fa fa-save"></i> {{ trans('shared/common.save') }}</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
</div>
@endsection

@section('modal')
@parent
<div id="modal-delivery-area" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title text-center">{{ trans('operational/fields.delivery-area') }}</h4>
            </div>
            <div class="modal-body">
                <table id="datatables-lov" class="table table-hover table-striped table-bordered" cellspacing="0" width="100%">
                    <thead>
                        <tr>
                            <th>{{ trans('operational/fields.delivery-area') }}</th>
                            <th>{{ trans('shared/common.description') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($optionDeliveryArea as $deliveryArea)

                        <tr style="cursor: pointer;" class="tr-lov" data-delivery="{{ json_encode($deliveryArea) }}">
                            <td>{{ $deliveryArea->delivery_area_name }}</td>
                            <td>{{ $deliveryArea->description }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-sm btn-warning" data-dismiss="modal">{{ trans('shared/common.close') }}</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
@parent
<script type="text/javascript">
    $(document).on('ready', function() {
        $("#datatables-lov").dataTable({
            "pagelength" : 10,
            "lengthChange": false
        });

        $('#datatables-lov tbody').on('click', 'tr', function () {
            var delivery = $(this).data('delivery');

            $('#deliveryAreaId').val(delivery.delivery_area_id);
            $('#deliveryAreaName').val(delivery.delivery_area_name);
            
            $('#modal-delivery-area').modal("hide");
        });    
    });
</script>
@endsection
