@extends('layouts.master')

@section('title', trans('asset/menu.addition-asset'))

@section('header')
@parent
<style type="text/css">
    ::-webkit-input-placeholder {
        text-align: center;
    }
</style>
@endsection
<?php use App\Modules\Asset\Model\Transaction\RetirementAsset; ?>
<?php use App\Modules\Asset\Model\Transaction\AdditionAsset; ?>
@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="widget">
            <div class="widget-header">
                <h2><i class="fa fa-folder"></i> <strong> {{ $title }} </strong> {{ trans('asset/menu.addition-asset') }}</h2>
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
                        <input type="hidden" name="id" value="{{ count($errors) > 0 ? old('id') : $model->asset_id }}">
                        <ul id="demo1" class="nav nav-tabs">
                            <li class="active">
                                <a href="#additionalAssetTab" data-toggle="tab">{{ trans('asset/menu.addition-asset') }}</a>
                            </li>
                            <li >
                                <a href="#assigmentTab" data-toggle="tab">{{ trans('asset/fields.assigment') }}</a>
                            </li>
                            <li >
                                <a href="#depreciationTab" data-toggle="tab">{{ trans('asset/fields.depreciation') }}</a>
                            </li>
                            <li >
                                <a href="#retirementTab" data-toggle="tab">{{ trans('asset/fields.retirement') }}</a>
                            </li>
                        </ul>
                        <div class="tab-content">
                            <div class="tab-pane fade active in" id="additionalAssetTab">
                                <div class="col-sm-6 portlets">
                                    <div class="form-group {{ $errors->has('assetNumber') ? 'has-error' : '' }}">
                                        <label for="assetNumber" class="col-sm-4 control-label">{{ trans('asset/fields.asset-number') }} <span class="required">*</span></label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="assetNumber" name="assetNumber"  value="{{ !empty($model->asset_number) ? $model->asset_number : '' }}" disabled>
                                            @if($errors->has('assetNumber'))
                                            <span class="help-block">{{ $errors->first('assetNumber') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="form-group {{ $errors->has('type') ? 'has-error' : '' }}">
                                        <label for="type" class="col-sm-4 control-label">{{ trans('shared/common.type') }} <span class="required">*</span></label>
                                        <div class="col-sm-8">
                                            <select class="form-control" id="type" name="type" {{ $model->isRetirement() || $model->isSold() || !empty($model->asset_id ) ? 'disabled' : '' }}>
                                                <?php $typeString =  count($errors) > 0 ? old('type') : $model->type; ?>
                                                <option value="">{{ trans('shared/common.please-select') }}</option>
                                                @foreach($optionType as $type)
                                                <option value="{{ $type }}" {{ $type == $typeString ? 'selected' : '' }}>{{ $type  }}</option>
                                                @endforeach
                                            </select>
                                            @if($errors->has('type'))
                                            <span class="help-block">{{ $errors->first('type') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    <?php 
                                    $itemId = !empty($item) ? $item->item_id : '' ; 
                                    $itemCode = !empty($item) ? $item->item_code : '' ; 
                                    ?>
                                    <div class="form-group {{ $errors->has('itemCode') ? 'has-error' : '' }}">
                                        <label for="itemCode" class="col-sm-4 control-label">{{ trans('inventory/fields.item-code') }} <span class="required">*</span></label>
                                        <div class="col-sm-8">
                                            <div class="input-group">
                                                <input type="hidden" class="form-control" id="itemId" name="itemId" value="{{ count($errors) > 0 ? old('itemId') : $itemId }}">
                                                <input type="hidden" class="form-control" id="whId" name="whId" value="{{ count($errors) > 0 ? old('itemId') : $itemId }}">
                                                <input type="text" class="form-control" id="itemCode" name="itemCode" value="{{ count($errors) > 0 ? old('itemCode') : $itemCode }}" readonly>
                                                <span id="modalItem" class="btn input-group-addon" data-toggle="{{ empty($model->asset_id) ? 'modal' : '' }}" data-target="#modal-lov-item"><i class="fa fa-search"></i></span>
                                                <span id="modalManual" class="btn input-group-addon" data-toggle="{{ empty($model->asset_id) ? 'modal' : '' }}" data-target="#modal-lov-manual"><i class="fa fa-search"></i></span>
                                            </div>
                                                @if($errors->has('itemCode'))
                                                <span class="help-block">{{ $errors->first('itemCode') }}</span>
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
                                    <?php $poNumber = !empty($poHeader) ? $poHeader->po_number : '' ; ?>
                                    <div id="formPo" class="form-group {{ $errors->has('poNumber') ? 'has-error' : '' }}">
                                        <label for="poNumber" class="col-sm-4 control-label">{{ trans('purchasing/fields.po-number') }}</label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="poNumber" name="poNumber" value="{{ count($errors) > 0 ? old('poNumber') : $poNumber }}" readonly>
                                            @if($errors->has('poNumber'))
                                            <span class="help-block">{{ $errors->first('poNumber') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    <?php $receiptLineId = !empty($receiptLine) ? $receiptLine->receipt_line_id : '' ; ?>
                                    <?php $receiptId     = !empty($receipt) ? $receipt->receipt_id : '' ; ?>
                                    <?php $receiptNumber = !empty($receipt) ? $receipt->receipt_number : '' ; ?>
                                    <div id="formReceipt" class="form-group {{ $errors->has('receiptNumber') ? 'has-error' : '' }}">
                                        <label for="receiptNumber" class="col-sm-4 control-label">{{ trans('inventory/fields.receipt-number') }}</label>
                                        <div class="col-sm-8">
                                            <input type="hidden" class="form-control" id="receiptLineId" name="receiptLineId" value="{{ count($errors) > 0 ? old('receiptLineId') : $receiptLineId }}">
                                            <input type="hidden" class="form-control" id="receiptId" name="receiptId" value="{{ count($errors) > 0 ? old('receiptId') : $receiptId }}">
                                            <input type="text" class="form-control" id="receiptNumber" name="receiptNumber" value="{{ count($errors) > 0 ? old('receiptNumber') : $receiptNumber }}" readonly>
                                            @if($errors->has('receiptNumber'))
                                            <span class="help-block">{{ $errors->first('receiptNumber') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="form-group {{ $errors->has('poCost') ? 'has-error' : '' }}">
                                        <label for="poCost" class="col-sm-4 control-label">{{ trans('asset/fields.cost') }} <span class="required">*</span></label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control currency text-right" id="poCost" name="poCost" value="{{ count($errors) > 0 ? str_replace(',', '', old('poCost')) : $model->po_cost }}" {{ !empty($model->asset_id) ? 'readonly' : '' }}>
                                            @if($errors->has('poCost'))
                                            <span class="help-block">{{ $errors->first('poCost') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-6 portlets">
                                    <div class="form-group {{ $errors->has('category') ? 'has-error' : '' }}">
                                        <label for="category" class="col-sm-4 control-label">{{ trans('shared/common.category') }} <span class="required">*</span></label>
                                        <div class="col-sm-8">
                                            <select class="form-control" id="category" name="category" {{ !empty($model->asset_id) ? 'disabled' : '' }}>
                                                <?php $categoryId = !empty($model->asset_category_id) ? $model->asset_category_id : ''; ?>
                                                <?php $categoryId =  count($errors) > 0 ? old('category') : $categoryId; ?>
                                                <option value="">{{ trans('shared/common.please-select') }}</option>
                                                @foreach($optionCategory as $category)
                                                <option value="{{ $category->asset_category_id }}" {{ $category->asset_category_id == $categoryId ? 'selected' : '' }}>{{ $category->category_name  }}</option>
                                                @endforeach
                                            </select>
                                            @if($errors->has('category'))
                                            <span class="help-block">{{ $errors->first('category') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="form-group {{ $errors->has('serialNumber') ? 'has-error' : '' }}">
                                        <label for="serialNumber" class="col-sm-4 control-label">{{ trans('asset/fields.serial-number') }} </label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="serialNumber" name="serialNumber" value="{{ count($errors) > 0 ? old('serialNumber') : $model->serial_number }}" {{ !empty($model->asset_id) ? 'readonly' : '' }}>
                                            @if($errors->has('serialNumber'))
                                            <span class="help-block">{{ $errors->first('serialNumber') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="form-group {{ $errors->has('leaseNumber') ? 'has-error' : '' }}">
                                        <label for="leaseNumber" class="col-sm-4 control-label">{{ trans('asset/fields.lease-number') }} </label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="leaseNumber" name="leaseNumber" value="{{ count($errors) > 0 ? old('leaseNumber') : $model->lease_number }}" {{ !empty($model->asset_id) ? 'readonly' : '' }}>
                                            @if($errors->has('leaseNumber'))
                                            <span class="help-block">{{ $errors->first('leaseNumber') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="form-group {{ $errors->has('policeNumber') ? 'has-error' : '' }}">
                                        <label for="policeNumber" class="col-sm-4 control-label">{{ trans('asset/fields.police-number') }} </label>
                                        <div class="col-sm-8">
                                            <input type="text" style="text-transform: uppercase" class="form-control" id="policeNumber" name="policeNumber" value="{{ count($errors) > 0 ? old('policeNumber') : $model->police_number }}" {{ $model->isRetirement() || $model->isSold() ? 'readonly' : '' }}>
                                            @if($errors->has('policeNumber'))
                                            <span class="help-block">{{ $errors->first('policeNumber') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="assetDate" class="col-sm-4 control-label">{{ trans('shared/common.tanggal') }}</label>
                                        <div class="col-sm-8">
                                            <div class="input-group">
                                                <?php $assetDate = new \DateTime($model->asset_date); ?>
                                                <input type="text" id="assetDate" name="assetDate" class="form-control datepicker-input" data-mask="99-99-9999" placeholder="dd-mm-yyyy" value="{{ $assetDate->format('d-m-Y') }}" {{ !empty($model->asset_id) ? 'disabled' : '' }}>
                                                <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                            </div>
                                        </div>
                                        @if($errors->has('assetDate'))
                                        <span class="help-block">{{ $errors->first('assetDate') }}</span>
                                        @endif
                                    </div>
                                    <?php $statusId = !empty($model->status_id) ? $model->status_id : ''; 
                                    $stringStatus = AdditionAsset::DEFAULT_STATUS; 
                                    foreach($optionStatus as $status){
                                        if ($status->asset_status_id == $statusId ) {
                                            $stringStatus = $status->status; 
                                        }
                                    }
                                    ?>
                                    <div class="form-group {{ $errors->has('status') ? 'has-error' : '' }}">
                                        <label for="status" class="col-sm-4 control-label">{{ trans('shared/common.status') }} </label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="status" name="status" value="{{ count($errors) > 0 ? old('status') : $stringStatus }}" readonly>
                                            @if($errors->has('status'))
                                            <span class="help-block">{{ $errors->first('status') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    
                                </div>
                            </div>
                            <div class="tab-pane fade" id="retirementTab">
                                <div id="horizontal-form">
                                    <div class="col-sm-6 portlets">
                                        <?php
                                        if (count($errors) > 0) {
                                            $retirementDate = !empty(old('retirementDate')) ? new \DateTime(old('retirementDate')) : null;
                                        } else {
                                            $retirementDate = $model->isRetirement() || $model->isSold() ? new \DateTime($retirement->retirement_date) : null;
                                        }
                                        ?>
                                        <div class="form-group {{ $errors->has('retirementDate') ? 'has-error' : '' }}">
                                            <label for="retirementDate" class="col-sm-4 control-label">{{ trans('asset/fields.retirement-date') }}</label>
                                            <div class="col-sm-8">
                                                <div class="input-group">
                                                    <input type="text" id="retirementDate" name="retirementDate" class="form-control datepicker-input" data-mask="99-99-9999" placeholder="dd-mm-yyyy" value="{{ $retirementDate !== null ? $retirementDate->format('d-m-Y') : '' }}" {{ $model->isRetirement() || $model->isSold() ? 'readonly' : '' }}>
                                                    <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                                </div>
                                                @if($errors->has('retirementDate'))
                                                <span class="help-block">{{ $errors->first('retirementDate') }}</span>
                                                @endif
                                            </div>
                                        </div>
                                        <?php $retirementType = !empty($retirement) ? $retirement->retirement_type : '' ?>
                                        <?php $retirementType = count($errors) > 0 ? old('retirementType') : $retirementType ?>
                                        <div class="form-group {{ $errors->has('retirementType') ? 'has-error' : '' }}">
                                            <label for="retirementType" class="col-sm-4 control-label">{{ trans('asset/fields.retirement-type') }}</label>
                                            <div class="col-sm-8">
                                                <select class="form-control" id="retirementType" name="retirementType" {{ $model->isRetirement() || $model->isSold() ? 'readonly' : '' }}>
                                                    <option value="" {{ $retirementType == '' ? 'selected' : '' }}> {{ trans('asset/fields.please-select') }}</option>
                                                    <option value="{{ RetirementAsset::BROKEN }}" {{ $retirementType == RetirementAsset::BROKEN ? 'selected' : '' }}>Broken</option>
                                                    <option value="{{ RetirementAsset::SALE }}" {{ $retirementType == RetirementAsset::SALE ? 'selected' : '' }}>Sale</option>
                                                </select>
                                                @if($errors->has('retirementType'))
                                                <span class="help-block">{{ $errors->first('retirementType') }}</span>
                                                @endif
                                            </div>
                                        </div>
                                        <?php $currentCost = !empty($retirement) ? $retirement->current_cost : '' ?>
                                        <div class="form-group {{ $errors->has('currentCost') ? 'has-error' : '' }}">
                                            <label for="currentCost" class="col-sm-4 control-label">{{ trans('asset/fields.current-cost') }} </label>
                                            <div class="col-sm-8">
                                                <input type="text" class="form-control currency" id="currentCost" name="currentCost" value="{{ count($errors) > 0 ? old('currentCost') : $currentCost }}" {{ $model->isRetirement() || $model->isSold() ? 'readonly' : '' }}>
                                                @if($errors->has('currentCost'))
                                                <span class="help-block">{{ $errors->first('currentCost') }}</span>
                                                @endif
                                            </div>
                                        </div>
                                        <?php $retirementCost = !empty($retirement) ? $retirement->retirement_cost : '' ?>
                                        <div class="form-group {{ $errors->has('retirementCost') ? 'has-error' : '' }}">
                                            <label for="retirementCost" class="col-sm-4 control-label">{{ trans('asset/fields.retirement-cost') }} </label>
                                            <div class="col-sm-8">
                                                <input type="text" class="form-control currency" id="retirementCost" name="retirementCost" value="{{ count($errors) > 0 ? old('retirementCost') : $retirementCost }}" {{ $model->isRetirement() || $model->isSold() ? 'readonly' : '' }}>
                                                @if($errors->has('retirementCost'))
                                                <span class="help-block">{{ $errors->first('retirementCost') }}</span>
                                                @endif
                                            </div>
                                        </div>
                                        <?php $retirementDescription = !empty($retirement) ? $retirement->description : '' ?>
                                        <div class="form-group {{ $errors->has('retirementDescription') ? 'has-error' : '' }}">
                                            <label for="retirementDescription" class="col-sm-4 control-label">{{ trans('shared/common.description') }} </label>
                                            <div class="col-sm-8">
                                                <input type="text" class="form-control" id="retirementDescription" name="retirementDescription" value="{{ count($errors) > 0 ? old('retirementDescription') : $retirementDescription }}" {{ $model->isRetirement() || $model->isSold() ? 'readonly' : '' }}>
                                                @if($errors->has('retirementDescription'))
                                                <span class="help-block">{{ $errors->first('retirementDescription') }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="depreciationTab">
                                <div id="horizontal-form">
                                    <div class="col-sm-6 portlets">
                                        <?php $depreciationLifeYear = !empty($depreciation) ? $depreciation->life_year : '' ?>
                                        <div class="form-group {{ $errors->has('depreciationLifeYear') ? 'has-error' : '' }}">
                                            <label for="depreciationLifeYear" class="col-sm-4 control-label">{{ trans('asset/fields.life-year') }} </label> 
                                            <div class="col-sm-8">
                                                <input type="text" class="form-control currency" id="depreciationLifeYear" name="depreciationLifeYear" value="{{ count($errors) > 0 ? old('depreciationLifeYear') : $depreciationLifeYear }}" {{ $model->isRetirement() || $model->isSold() ? 'readonly' : '' }}>
                                                @if($errors->has('depreciationLifeYear'))
                                                <span class="help-block">{{ $errors->first('depreciationLifeYear') }}</span>
                                                @endif
                                            </div>
                                        </div>
                                        <?php $depreciationCostYear = !empty($depreciation) ? $depreciation->cost_year : '' ?>
                                        <div class="form-group {{ $errors->has('depreciationCostYear') ? 'has-error' : '' }}">
                                            <label for="depreciationCostYear" class="col-sm-4 control-label">{{ trans('asset/fields.cost-year') }} </label>
                                            <div class="col-sm-8">
                                                <input type="text" class="form-control currency text-right" id="depreciationCostYear" name="depreciationCostYear" value="{{ count($errors) > 0 ? str_replace(',', '', old('depreciationCostYear')) : $depreciationCostYear }}" readonly>
                                                @if($errors->has('depreciationCostYear'))
                                                <span class="help-block">{{ $errors->first('depreciationCostYear') }}</span>
                                                @endif
                                            </div>
                                        </div>
                                        <?php $depreciationCostMonth = !empty($depreciation) ? $depreciation->cost_month : '' ?>
                                        <div class="form-group {{ $errors->has('depreciationCostMonth') ? 'has-error' : '' }}">
                                            <label for="depreciationCostMonth" class="col-sm-4 control-label">{{ trans('asset/fields.cost-month') }} </label>
                                            <div class="col-sm-8">
                                                <input type="text" class="form-control currency text-right" id="depreciationCostMonth" name="depreciationCostMonth" value="{{ count($errors) > 0 ? str_replace(',', '', old('depreciationCostMonth')) : $depreciationCostMonth }}" readonly>
                                                @if($errors->has('depreciationCostMonth'))
                                                <span class="help-block">{{ $errors->first('depreciationCostMonth') }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="assigmentTab">
                                <div id="horizontal-form">
                                    <div class="col-sm-6 portlets">
                                        <?php $assigmentEmployee = !empty($assigment) ? $assigment->employee_name : '' ?>
                                        <div class="form-group {{ $errors->has('assigmentEmployee') ? 'has-error' : '' }}">
                                            <label for="assigmentEmployee" class="col-sm-4 control-label">{{ trans('asset/fields.employee') }} 
                                            @if(empty($model->asset_id))
                                                <span class="required">*</span>
                                            @endif
                                            </label>
                                            <div class="col-sm-8">
                                                <input type="text" class="form-control" id="assigmentEmployee" name="assigmentEmployee" value="{{ count($errors) > 0 ? old('assigmentEmployee') : $assigmentEmployee }}" {{ $model->isRetirement() || $model->isSold() ? 'readonly' : '' }}>
                                                @if($errors->has('assigmentEmployee'))
                                                <span class="help-block">{{ $errors->first('assigmentEmployee') }}</span>
                                                @endif
                                            </div>
                                        </div>
                                        <?php $assigmentLocation = !empty($assigment) ? $assigment->location : '' ?>
                                        <div class="form-group {{ $errors->has('assigmentLocation') ? 'has-error' : '' }}">
                                            <label for="assigmentLocation" class="col-sm-4 control-label">{{ trans('asset/fields.location') }} 
                                                @if(empty($model->asset_id))
                                                <span class="required">*</span>
                                                @endif
                                            </label>
                                            <div class="col-sm-8">
                                                <input type="text" class="form-control" id="assigmentLocation" name="assigmentLocation" value="{{ count($errors) > 0 ? old('assigmentLocation') : $assigmentLocation }}" {{ $model->isRetirement() || $model->isSold() ? 'readonly' : '' }}>
                                                @if($errors->has('assigmentLocation'))
                                                <span class="help-block">{{ $errors->first('assigmentLocation') }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="clearfix"></div>
                        <hr>
                        <div class="col-sm-12 data-table-toolbar text-right">
                            <div class="form-group">
                                <a href="{{ URL('asset/transaction/addition-asset') }}" class="btn btn-sm btn-warning"><i class="fa fa-reply"></i> {{ trans('shared/common.cancel') }}</a>
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
<div id="modal-lov-item" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title text-center">{{ trans('inventory/fields.item-code') }}</h4>
            </div>
            <div class="modal-body">
                <table id="datatables-lov" class="table table-hover table-striped table-bordered" cellspacing="0" width="100%">
                    <thead>
                        <tr>
                            <th>{{ trans('inventory/fields.item-code') }}</th>
                            <th>{{ trans('shared/common.description') }}</th>
                            <th>{{ trans('purchasing/fields.po-number') }}</th>
                            <th>{{ trans('inventory/fields.receipt-number') }}</th>
                            <th>{{ trans('inventory/fields.wh') }}</th>
                            <th>{{ trans('asset/fields.po-cost') }}</th>
                            <th>{{ trans('asset/fields.total') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($optionItem as $item)
                        <tr style="cursor: pointer;" data-item="{{ json_encode($item) }}">
                            <td>{{ $item->item_code }}</td>
                            <td>{{ $item->item_description }}</td>
                            <td>{{ $item->po_number }}</td>
                            <td>{{ $item->receipt_number }}</td>
                            <td>{{ $item->warehouse_description }}</td>
                            <td class="text-right">{{ number_format($item->po_cost) }}</td>
                            <td class="text-right">{{ number_format($item->jumlah) }}</td>
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

<div id="modal-lov-manual" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title text-center">{{ trans('inventory/fields.item-code') }}</h4>
            </div>
            <div class="modal-body">
                <table id="datatables-lov-manual" class="table table-hover table-striped table-bordered" cellspacing="0" width="100%">
                    <thead>
                        <tr>
                            <th>{{ trans('inventory/fields.item-code') }}</th>
                            <th>{{ trans('shared/common.description') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($optionMasterItem as $item)
                        <tr style="cursor: pointer;" data-manual="{{ json_encode($item) }}">
                            <td>{{ $item->item_code }}</td>
                            <td>{{ $item->description }}</td>
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
        $('#depreciationLifeYear').on('keyup', calculateCost);
        $('#poCost').on('keyup', calculateCost);

        disableForm();
        $
        $('#type').on('change', function(){
            clearForm();
            disableForm();
        });

        $("#datatables-lov-manual").dataTable({
            "pageLength" : 10,
            "lengthChange": false
        });

        $('#datatables-lov tbody').on('click', 'tr', function () {
            var item = $(this).data('item');

            $('#itemId').val(item.item_id);
            $('#whId').val(item.wh_id);
            $('#itemCode').val(item.item_code);
            $('#itemDescription').val(item.item_description);
            $('#poNumber').val(item.po_number);
            $('#receiptNumber').val(item.receipt_number);
            $('#receiptId').val(item.receipt_id);
            $('#receiptLineId').val(item.receipt_line_id);
            $('#poCost').val(item.po_cost);
            $('#poCost').autoNumeric('update', {mDec: 0});
            
            $('#modal-lov-item').modal("hide");
        });

        $("#datatables-lov").dataTable({
            "pageLength" : 10,
            "lengthChange": false
        });

        $('#datatables-lov-manual tbody').on('click', 'tr', function () {
            var item = $(this).data('manual');

            $('#itemId').val(item.item_id);
            $('#itemCode').val(item.item_code);
            $('#itemDescription').val(item.description);
            
            $('#modal-lov-manual').modal("hide");
        });
    });

    var clearForm = function(){
        $('#itemId').val('');
        $('#itemCode').val('');
        $('#itemDescription').val('');
        $('#poNumber').val('');
        $('#receiptNumber').val('');
        $('#poCost').val('');
    }
    var disableForm = function(){
        $('#formPo').addClass('hidden');
        $('#formReceipt').addClass('hidden');
        $('#modalItem').removeClass('hidden');
        $('#modalManual').addClass('hidden');
        $('#modalItem').addClass('disabled');
        $('#modalManual').addClass('disabled');

        if ($('#type').val() == '{{AdditionAsset::EXIST}}') { // Tagihan PO Standart
            $('#modalItem').addClass('hidden');
            $('#modalManual').removeClass('hidden');
            $('#modalManual').removeClass('disabled');
        }

        if ($('#type').val() == '{{AdditionAsset::PO}}') { // Tagihan PO Standart
            $('#formPo').removeClass('hidden');
            $('#formReceipt').removeClass('hidden');
            $('#modalItem').removeClass('disabled');
            $('#modalItem').removeClass('hidden');
            $('#modalManual').addClass('hidden');
            $('#amount').removeAttr('readonly','readonly');
        }

    };

    var calculateCost = function() {
        if ($('#itemId').val() == '') {
            $('#modal-alert').find('.alert-message').html('{{ trans('asset/fields.choose-item-alert') }}');
            $('#modal-alert').modal('show');
            return;
        }

        var lifeYear  = $('#depreciationLifeYear').val().split(',').join('');
        var poCost    = $('#poCost').val().split(',').join('');
        var costYear  = 0;
        var costMonth = 0;

        if (lifeYear != 0) {
            costYear = poCost / lifeYear;
            costMonth = poCost / lifeYear / 12;
        }

        $('#depreciationCostYear').val(costYear);
        $('#depreciationCostMonth').val(costMonth);

        $('#depreciationCostYear').autoNumeric('update', {mDec: 0});
        $('#depreciationCostMonth').autoNumeric('update', {mDec: 0});
    };
</script>
@endsection