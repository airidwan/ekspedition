@extends('layouts.master')

@section('title', trans('asset/menu.service-truck-monthly'))

<?php 
use App\Modules\Asset\Model\Transaction\AdditionAsset;
?>

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="widget">
            <div class="widget-header">
                <h2><i class="fa fa-folder"></i> <strong>{{ $title }}</strong> {{ trans('asset/menu.service-truck-monthly') }}</h2>
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
                        <input type="hidden" name="id" value="{{ count($errors) > 0 ? old('id') : $model->service_asset_id }}">
                        <div class="col-sm-6 portlets">
                            <div class="form-group {{ $errors->has('serviceNumber') ? 'has-error' : '' }}">
                                <label for="serviceNumber" class="col-sm-4 control-label">{{ trans('asset/fields.service-number') }} <span class="required">*</span></label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="serviceNumber" name="serviceNumber"  value="{{ !empty($model->service_number) ? $model->service_number : '' }}" disabled>
                                    @if($errors->has('serviceNumber'))
                                    <span class="help-block">{{ $errors->first('serviceNumber') }}</span>
                                    @endif
                                </div>
                            </div>
                            <?php 
                            $policeNumber  = !empty($model->truck) ? $model->truck->police_number : '' ; 
                            $ownerName     = !empty($model->truck) ? $model->truck->owner_name : '' ; 
                            $truckCategory = !empty($model->truck) ? $model->truck->getCategory() : '' ; 
                            $truckBrand    = !empty($model->truck) ? $model->truck->getBrand() : '' ; 
                            $truckType     = !empty($model->truck) ? $model->truck->getType() : '' ; 
                            ?>
                            <div class="form-group {{ $errors->has('truckId') ? 'has-error' : '' }}">
                                <label for="truck" class="col-sm-4 control-label">{{ trans('operational/fields.police-number') }} <span class="required">*</span></label>
                                <div class="col-sm-8">
                                    <div class="input-group">
                                    <input type="hidden" class="form-control" id="truckId" name="truckId" value="{{ count($errors) > 0 ? old('truckId') : $model->truck_id }}">
                                    <input type="text" class="form-control" id="policeNumber" name="policeNumber" value="{{ count($errors) > 0 ? old('policeNumber') : $policeNumber }}" readonly>
                                    <span class="btn input-group-addon" data-toggle="{{ empty($model->service_asset_id) || empty($model->finish_date)  ? 'modal' : '' }}" data-target="#modal-lov-truck"><i class="fa fa-search"></i></span>
                                    @if($errors->has('truckId'))
                                    <span class="help-block">{{ $errors->first('truckId') }}</span>
                                    @endif
                                    </div>
                                </div>
                            </div>
                            <div class="form-group {{ $errors->has('ownerName') ? 'has-error' : '' }}">
                                <label for="ownerName" class="col-sm-4 control-label">{{ trans('operational/fields.owner-name') }}</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="ownerName" name="ownerName" value="{{ count($errors) > 0 ? old('ownerName') : $ownerName }}" readonly>
                                    @if($errors->has('ownerName'))
                                    <span class="help-block">{{ $errors->first('ownerName') }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="form-group {{ $errors->has('truckCategory') ? 'has-error' : '' }}">
                                <label for="truckCategory" class="col-sm-4 control-label">{{ trans('operational/fields.truck-category') }}</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="truckCategory" name="truckCategory" value="{{ count($errors) > 0 ? old('truckCategory') : $truckCategory }}" readonly>
                                    @if($errors->has('truckCategory'))
                                    <span class="help-block">{{ $errors->first('truckCategory') }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="form-group {{ $errors->has('truckBrand') ? 'has-error' : '' }}">
                                <label for="truckBrand" class="col-sm-4 control-label">{{ trans('operational/fields.brand') }}</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="truckBrand" name="truckBrand" value="{{ count($errors) > 0 ? old('truckBrand') : $truckBrand }}" readonly>
                                    @if($errors->has('truckBrand'))
                                    <span class="help-block">{{ $errors->first('truckBrand') }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="form-group {{ $errors->has('truckType') ? 'has-error' : '' }}">
                                <label for="truckType" class="col-sm-4 control-label">{{ trans('operational/fields.truck-type') }}</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="truckType" name="truckType" value="{{ count($errors) > 0 ? old('truckType') : $truckType }}" readonly>
                                    @if($errors->has('truckType'))
                                    <span class="help-block">{{ $errors->first('truckType') }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6 portlets">
                            <?php
                            if (count($errors) > 0) {
                                $serviceDate = !empty(old('serviceDate')) ? new \DateTime(old('serviceDate')) : new \DateTime();
                            } else {
                                $serviceDate = !empty($model->service_date) ? new \DateTime($model->service_date) : new \DateTime();
                            }
                            ?>
                            <div class="form-group {{ $errors->has('serviceDate') ? 'has-error' : '' }}">
                                <label for="serviceDate" class="col-sm-4 control-label">{{ trans('asset/fields.service-date') }}</label>
                                <div class="col-sm-8">
                                    <div class="input-group">
                                        <input type="text" id="serviceDate" name="serviceDate" class="form-control datepicker-input" data-mask="99-99-9999" placeholder="dd-mm-yyyy" value="{{ $serviceDate !== null ? $serviceDate->format('d-m-Y') : '' }}" {{ !empty($model->finish_date) ? 'disabled' : '' }}>
                                        <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                    </div>
                                    @if($errors->has('serviceDate'))
                                    <span class="help-block">{{ $errors->first('serviceDate') }}</span>
                                    @endif
                                </div>
                            </div>
                            <?php
                            if (count($errors) > 0) {
                                $finishDate = !empty(old('finishDate')) ? new \DateTime(old('finishDate')) : null;
                            } else {
                                $finishDate = !empty($model->finish_date) ? new \DateTime($model->finish_date) : null;
                            }
                            ?>
                            <div class="form-group {{ $errors->has('finishDate') ? 'has-error' : '' }}">
                                <label for="finishDate" class="col-sm-4 control-label">{{ trans('asset/fields.finish-date') }} </label>
                                <div class="col-sm-8">
                                    <div class="input-group">
                                        <input type="text" id="finishDate" name="finishDate" class="form-control datepicker-input" data-mask="99-99-9999" placeholder="dd-mm-yyyy" value="{{ $finishDate !== null ? $finishDate->format('d-m-Y') : '' }}" {{ !empty($model->finish_date) ? 'disabled' : '' }}>
                                        <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                    </div>
                                    @if($errors->has('finishDate'))
                                    <span class="help-block">{{ $errors->first('finishDate') }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="form-group {{ $errors->has('note') ? 'has-error' : '' }}">
                                <label for="note" class="col-sm-4 control-label">{{ trans('shared/common.note') }} </label>
                                <div class="col-sm-8">
                                    <textarea type="text" class="form-control" id="note" name="note" rows="3" {{ !empty($model->finish_date) ? 'disabled' : '' }}>{{ count($errors) > 0 ? old('note') : $model->note }}</textarea>
                                    @if($errors->has('note'))
                                    <span class="help-block">{{ $errors->first('note') }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-12 data-table-toolbar text-right">
                            <div class="form-group">
                                <a href="{{ URL($url) }}" class="btn btn-sm btn-warning"><i class="fa fa-reply"></i> {{ trans('shared/common.cancel') }}</a>
                                @if(empty($model->finish_date))
                                <button type="submit" class="btn btn-sm btn-primary"><i class="fa fa-save"></i> {{ trans('shared/common.save') }}</button>
                                @endif
                                @if($title == trans('shared/common.edit') && empty($model->finish_date))
                                <button type="submit" name="btn-finish" class="btn btn-sm btn-success"><i class="fa fa-save"></i> {{ trans('shared/common.finish') }}</button>
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
<div id="modal-lov-truck" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title text-center">{{ trans('operational/fields.truck') }}</h4>
            </div>
            <div class="modal-body">
                <table id="datatables-lov-truck" class="table table-hover table-striped table-bordered" cellspacing="0" width="100%">
                    <thead>
                        <tr>
                            <th>{{ trans('operational/fields.police-number') }}</th>
                            <th>{{ trans('operational/fields.owner-name') }}</th>
                            <th>{{ trans('operational/fields.truck-category') }}</th>
                            <th>{{ trans('operational/fields.brand') }}</th>
                            <th>{{ trans('operational/fields.type') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($optionTruck as $truck)
                        <tr style="cursor: pointer;" data-truck="{{ json_encode($truck) }}">
                            <td>{{ $truck->police_number }}</td>
                            <td>{{ $truck->owner_name }}</td>
                            <td>{{ $truck->vehicle_category }}</td>
                            <td>{{ $truck->vehicle_merk }}</td>
                            <td>{{ $truck->vehicle_type }}</td>
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
@parent()
<script type="text/javascript">
    $(document).on('ready', function(){
        $("#datatables-lov-truck").dataTable({
            "pagelength" : 10,
            "lengthChange": false
        });

        $('#datatables-lov-truck tbody').on('click', 'tr', function () {
            var truck = $(this).data('truck');

            $('#truckId').val(truck.truck_id);
            $('#policeNumber').val(truck.police_number);
            $('#ownerName').val(truck.owner_name);
            $('#truckCategory').val(truck.vehicle_category);
            $('#truckBrand').val(truck.vehicle_merk);
            $('#truckType').val(truck.vehicle_type);
            
            $('#modal-lov-truck').modal("hide");
        });
    });
</script>
@endsection
