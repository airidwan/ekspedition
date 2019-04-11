@extends('layouts.master')

@section('title', trans('asset/menu.service-asset'))

<?php 
use App\Modules\Asset\Model\Transaction\AdditionAsset;
?>

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="widget">
            <div class="widget-header">
                <h2><i class="fa fa-folder"></i> <strong>{{ $title }}</strong> {{ trans('asset/menu.service-asset') }}</h2>
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
                            $assetId = !empty($addAsset) ? $addAsset->asset_id : '' ; 
                            $assetNumber = !empty($addAsset) ? $addAsset->asset_number : '' ; 
                            $assetStatus = !empty($addAsset) ? $addAsset->status_id : '' ; 
                            ?>
                            <div class="form-group {{ $errors->has('asset') ? 'has-error' : '' }}">
                                <label for="asset" class="col-sm-4 control-label">{{ trans('asset/fields.asset-number') }} <span class="required">*</span></label>
                                <div class="col-sm-8">
                                    <div class="input-group">
                                    <input type="hidden" class="form-control" id="assetId" name="assetId" value="{{ count($errors) > 0 ? old('assetId') : $assetId }}">
                                    <input type="text" class="form-control" id="assetNumber" name="assetNumber" value="{{ count($errors) > 0 ? old('assetNumber') : $assetNumber }}" readonly>
                                    <span class="btn input-group-addon" data-toggle="{{ empty($model->service_asset_id) || empty($model->finish_date)  ? 'modal' : '' }}" data-target="#modal-lov-asset"><i class="fa fa-search"></i></span>
                                    @if($errors->has('asset'))
                                    <span class="help-block">{{ $errors->first('asset') }}</span>
                                    @endif
                                    </div>
                                </div>
                            </div>
                            <?php 
                            $itemId = !empty($item) ? $item->item_id : '' ; 
                            $itemCode = !empty($item) ? $item->item_code : '' ; 
                            ?>
                            <div class="form-group {{ $errors->has('item') ? 'has-error' : '' }}">
                                <label for="item" class="col-sm-4 control-label">{{ trans('inventory/fields.item-code') }} <span class="required">*</span></label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="itemCode" name="itemCode" value="{{ count($errors) > 0 ? old('itemCode') : $itemCode }}" readonly>
                                    @if($errors->has('item'))
                                    <span class="help-block">{{ $errors->first('item') }}</span>
                                    @endif
                                </div>
                            </div>
                            <?php $itemDesc = !empty($item) ? $item->description : '' ; ?>
                            <div class="form-group {{ $errors->has('itemDescription') ? 'has-error' : '' }}">
                                <label for="itemDescription" class="col-sm-4 control-label">{{ trans('inventory/fields.item') }}</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="itemDescription" name="itemDescription" value="{{ count($errors) > 0 ? old('itemDescription') : $itemDesc }}" readonly>
                                    @if($errors->has('itemDescription'))
                                    <span class="help-block">{{ $errors->first('itemDescription') }}</span>
                                    @endif
                                </div>
                            </div>
                            <?php $category = !empty($category) ? $category->category_name : '' ; ?>
                            <div class="form-group {{ $errors->has('category') ? 'has-error' : '' }}">
                                <label for="category" class="col-sm-4 control-label">{{ trans('shared/common.category') }}</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="category" name="category" value="{{ count($errors) > 0 ? old('category') : $category }}" readonly>
                                    @if($errors->has('category'))
                                    <span class="help-block">{{ $errors->first('category') }}</span>
                                    @endif
                                </div>
                            </div>
                            <?php $poNumber = !empty($poHeader) ? $poHeader->po_number : '' ; ?>
                            <div class="form-group {{ $errors->has('poNumber') ? 'has-error' : '' }}">
                                <label for="poNumber" class="col-sm-4 control-label">{{ trans('purchasing/fields.po-number') }}</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="poNumber" name="poNumber" value="{{ count($errors) > 0 ? old('poNumber') : $poNumber }}" readonly>
                                    @if($errors->has('poNumber'))
                                    <span class="help-block">{{ $errors->first('poNumber') }}</span>
                                    @endif
                                </div>
                            </div>
                            <?php $employee = !empty($assigment) ? $assigment->employee_name : '' ; ?>
                            <div class="form-group {{ $errors->has('employee') ? 'has-error' : '' }}">
                                <label for="employee" class="col-sm-4 control-label">{{ trans('asset/fields.employee') }}</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="employee" name="employee" value="{{ count($errors) > 0 ? old('employee') : $employee }}" readonly>
                                    @if($errors->has('employee'))
                                    <span class="help-block">{{ $errors->first('employee') }}</span>
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
<div id="modal-lov-asset" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title text-center">{{ trans('asset/fields.asset-number') }}</h4>
            </div>
            <div class="modal-body">
                <table id="datatables-lov-asset" class="table table-hover table-striped table-bordered" cellspacing="0" width="100%">
                    <thead>
                        <tr>
                            <th>{{ trans('asset/fields.asset-number') }}</th>
                            <th>{{ trans('purchasing/fields.po-number') }}</th>
                            <th>{{ trans('shared/common.category') }}</th>
                            <th>{{ trans('inventory/fields.item-code') }}</th>
                            <th>{{ trans('inventory/fields.item') }}</th>
                            <th>{{ trans('asset/fields.employee') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($optionAsset as $asset)
                        <tr style="cursor: pointer;" data-asset="{{ json_encode($asset) }}">
                            <td>{{ $asset->asset_number }}</td>
                            <td>{{ $asset->po_number }}</td>
                            <td>{{ $asset->category_name }}</td>
                            <td>{{ $asset->item_code }}</td>
                            <td>{{ $asset->item_description }}</td>
                            <td>{{ $asset->employee_name }}</td>
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
        $("#datatables-lov-asset").dataTable({
            "pagelength" : 10,
            "lengthChange": false
        });

        $('#datatables-lov-asset tbody').on('click', 'tr', function () {
            var asset = $(this).data('asset');

            $('#assetId').val(asset.asset_id);
            $('#assetNumber').val(asset.asset_number);
            $('#itemCode').val(asset.item_code);
            $('#itemDescription').val(asset.item_description);
            $('#category').val(asset.category_name);
            $('#poNumber').val(asset.po_number);
            $('#employee').val(asset.employee_name);
            
            $('#modal-lov-asset').modal("hide");
        });
    });
</script>
@endsection
