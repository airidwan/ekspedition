<?php use App\Modules\Asset\Model\Transaction\RetirementAsset; ?>
<?php use App\Modules\Asset\Model\Transaction\AdditionAsset; ?>

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
                        <input type="hidden" name="id" value="{{ $model->asset_id }}">
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
                                    <div class="form-group">
                                        <label for="assetNumber" class="col-sm-4 control-label">{{ trans('asset/fields.asset-number') }}</label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="assetNumber" name="assetNumber"  value="{{ !empty($model->asset_number) ? $model->asset_number : '' }}" disabled>
                                        </div>
                                    </div>
                                    <?php $typeString = $model->type; ?>
                                    <div class="form-group">
                                        <label for="type" class="col-sm-4 control-label">{{ trans('shared/common.type') }}</label>
                                        <div class="col-sm-8">
                                            <select class="form-control" id="type" name="type" disabled>
                                                <option value="">{{ trans('shared/common.please-select') }}</option>
                                                @foreach($optionType as $type)
                                                    <option value="{{ $type }}" {{ $type == $typeString ? 'selected' : '' }}>{{ $type  }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <?php 
                                    $itemId = !empty($item) ? $item->item_id : '' ; 
                                    $itemCode = !empty($item) ? $item->item_code : '' ; 
                                    ?>
                                    <div class="form-group">
                                        <label for="itemCode" class="col-sm-4 control-label">{{ trans('inventory/fields.item-code') }}</label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="itemCode" name="itemCode" value="{{ $itemCode }}" disabled>
                                        </div>
                                    </div>
                                    <?php $itemDesc = !empty($item) ? $item->description : '' ; ?>
                                    <div class="form-group">
                                        <label for="itemDescription" class="col-sm-4 control-label">{{ trans('inventory/fields.item') }}</label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="itemDescription" name="itemDescription" value="{{ $itemDesc }}" disabled>
                                        </div>
                                    </div>
                                    <?php $poNumber = !empty($poHeader) ? $poHeader->po_number : '' ; ?>
                                    <div id="formPo" class="form-group">
                                        <label for="poNumber" class="col-sm-4 control-label">{{ trans('purchasing/fields.po-number') }}</label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="poNumber" name="poNumber" value="{{ $poNumber }}" disabled>
                                        </div>
                                    </div>
                                    <?php $receiptLineId = !empty($receiptLine) ? $receiptLine->receipt_line_id : '' ; ?>
                                    <?php $receiptId     = !empty($receipt) ? $receipt->receipt_id : '' ; ?>
                                    <?php $receiptNumber = !empty($receipt) ? $receipt->receipt_number : '' ; ?>
                                    <div id="formReceipt" class="form-group">
                                        <label for="receiptNumber" class="col-sm-4 control-label">{{ trans('inventory/fields.receipt-number') }}</label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="receiptNumber" name="receiptNumber" value="{{ $receiptNumber }}" disabled>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="poCost" class="col-sm-4 control-label">{{ trans('asset/fields.cost') }}</label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control currency text-right" id="poCost" name="poCost" value="{{ $model->po_cost }}" disabled>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-6 portlets">
                                    <?php $categoryId = !empty($model->asset_category_id) ? $model->asset_category_id : ''; ?>
                                    <?php $categoryId =  $categoryId; ?>
                                    <div class="form-group">
                                        <label for="category" class="col-sm-4 control-label">{{ trans('shared/common.category') }}</label>
                                        <div class="col-sm-8">
                                            <select class="form-control" id="category" name="category" disabled>
                                                <option value="">{{ trans('shared/common.please-select') }}</option>
                                                @foreach($optionCategory as $category)
                                                    <option value="{{ $category->asset_category_id }}" {{ $category->asset_category_id == $categoryId ? 'selected' : '' }}>{{ $category->category_name  }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="serialNumber" class="col-sm-4 control-label">{{ trans('asset/fields.serial-number') }} </label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="serialNumber" name="serialNumber" value="{{ $model->serial_number }}" disabled>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="leaseNumber" class="col-sm-4 control-label">{{ trans('asset/fields.lease-number') }} </label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="leaseNumber" name="leaseNumber" value="{{ $model->lease_number }}" disabled>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="policeNumber" class="col-sm-4 control-label">{{ trans('asset/fields.police-number') }} </label>
                                        <div class="col-sm-8">
                                            <input type="text" style="text-transform: uppercase" class="form-control" id="policeNumber" name="policeNumber" value="{{ $model->police_number }}" disabled>
                                        </div>
                                    </div>
                                    <?php $assetDate = new \DateTime($model->asset_date); ?>
                                    <div class="form-group">
                                        <label for="assetDate" class="col-sm-4 control-label">{{ trans('shared/common.tanggal') }}</label>
                                        <div class="col-sm-8">
                                            <input type="text" id="assetDate" name="assetDate" class="form-control datepicker-input" data-mask="99-99-9999" placeholder="dd-mm-yyyy" value="{{ $assetDate->format('d-m-Y') }}" disabled>
                                        </div>
                                    </div>
                                    <?php
                                    $statusId = !empty($model->status_id) ? $model->status_id : '';
                                    $stringStatus = AdditionAsset::DEFAULT_STATUS;
                                    foreach($optionStatus as $status){
                                        if ($status->asset_status_id == $statusId ) {
                                            $stringStatus = $status->status;
                                        }
                                    }
                                    ?>
                                    <div class="form-group">
                                        <label for="status" class="col-sm-4 control-label">{{ trans('shared/common.status') }} </label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="status" name="status" value="{{ $stringStatus }}" disabled>
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
                                        <div class="form-group">
                                            <label for="retirementDate" class="col-sm-4 control-label">{{ trans('asset/fields.retirement-date') }}</label>
                                            <div class="col-sm-8">
                                                <input type="text" id="retirementDate" name="retirementDate" class="form-control datepicker-input" data-mask="99-99-9999" placeholder="dd-mm-yyyy" value="{{ $retirementDate !== null ? $retirementDate->format('d-m-Y') : '' }}" disabled>
                                            </div>
                                        </div>
                                        <?php $retirementType = !empty($retirement) ? $retirement->retirement_type : '' ?>
                                        <?php $retirementType = $retirementType ?>
                                        <div class="form-group">
                                            <label for="retirementType" class="col-sm-4 control-label">{{ trans('asset/fields.retirement-type') }}</label>
                                            <div class="col-sm-8">
                                                <select class="form-control" id="retirementType" name="retirementType" disabled>
                                                    <option value="" {{ $retirementType == '' ? 'selected' : '' }}> {{ trans('asset/fields.please-select') }}</option>
                                                    <option value="{{ RetirementAsset::BROKEN }}" {{ $retirementType == RetirementAsset::BROKEN ? 'selected' : '' }}>Broken</option>
                                                    <option value="{{ RetirementAsset::SALE }}" {{ $retirementType == RetirementAsset::SALE ? 'selected' : '' }}>Sale</option>
                                                </select>
                                            </div>
                                        </div>
                                        <?php $currentCost = !empty($retirement) ? $retirement->current_cost : '' ?>
                                        <div class="form-group">
                                            <label for="currentCost" class="col-sm-4 control-label">{{ trans('asset/fields.current-cost') }} </label>
                                            <div class="col-sm-8">
                                                <input type="text" class="form-control currency" id="currentCost" name="currentCost" value="{{ $currentCost }}" disabled>
                                            </div>
                                        </div>
                                        <?php $retirementCost = !empty($retirement) ? $retirement->retirement_cost : '' ?>
                                        <div class="form-group">
                                            <label for="retirementCost" class="col-sm-4 control-label">{{ trans('asset/fields.retirement-cost') }} </label>
                                            <div class="col-sm-8">
                                                <input type="text" class="form-control currency" id="retirementCost" name="retirementCost" value="{{ $retirementCost }}" disabled>
                                            </div>
                                        </div>
                                        <?php $retirementDescription = !empty($retirement) ? $retirement->description : '' ?>
                                        <div class="form-group">
                                            <label for="retirementDescription" class="col-sm-4 control-label">{{ trans('shared/common.description') }} </label>
                                            <div class="col-sm-8">
                                                <input type="text" class="form-control" id="retirementDescription" name="retirementDescription" value="{{ $retirementDescription }}" disabled>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="depreciationTab">
                                <div id="horizontal-form">
                                    <div class="col-sm-6 portlets">
                                        <?php $depreciationLifeYear = !empty($depreciation) ? $depreciation->life_year : '' ?>
                                        <div class="form-group">
                                            <label for="depreciationLifeYear" class="col-sm-4 control-label">{{ trans('asset/fields.life-year') }} </label> 
                                            <div class="col-sm-8">
                                                <input type="text" class="form-control currency" id="depreciationLifeYear" name="depreciationLifeYear" value="{{ $depreciationLifeYear }}" disabled>
                                            </div>
                                        </div>
                                        <?php $depreciationCostYear = !empty($depreciation) ? $depreciation->cost_year : '' ?>
                                        <div class="form-group">
                                            <label for="depreciationCostYear" class="col-sm-4 control-label">{{ trans('asset/fields.cost-year') }} </label>
                                            <div class="col-sm-8">
                                                <input type="text" class="form-control currency text-right" id="depreciationCostYear" name="depreciationCostYear" value="{{ count($errors) > 0 ? str_replace(',', '', old('depreciationCostYear')) : $depreciationCostYear }}" disabled>
                                            </div>
                                        </div>
                                        <?php $depreciationCostMonth = !empty($depreciation) ? $depreciation->cost_month : '' ?>
                                        <div class="form-group">
                                            <label for="depreciationCostMonth" class="col-sm-4 control-label">{{ trans('asset/fields.cost-month') }} </label>
                                            <div class="col-sm-8">
                                                <input type="text" class="form-control currency text-right" id="depreciationCostMonth" name="depreciationCostMonth" value="{{ count($errors) > 0 ? str_replace(',', '', old('depreciationCostMonth')) : $depreciationCostMonth }}" disabled>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="assigmentTab">
                                <div id="horizontal-form">
                                    <div class="col-sm-6 portlets">
                                        <?php $assigmentEmployee = !empty($assigment) ? $assigment->employee_name : '' ?>
                                        <div class="form-group">
                                            <label for="assigmentEmployee" class="col-sm-4 control-label">{{ trans('asset/fields.employee') }}</label>
                                            <div class="col-sm-8">
                                                <input type="text" class="form-control" id="assigmentEmployee" name="assigmentEmployee" value="{{ $assigmentEmployee }}" disabled>
                                            </div>
                                        </div>
                                        <?php $assigmentLocation = !empty($assigment) ? $assigment->location : '' ?>
                                        <div class="form-group">
                                            <label for="assigmentLocation" class="col-sm-4 control-label">{{ trans('asset/fields.location') }}</label>
                                            <div class="col-sm-8">
                                                <input type="text" class="form-control" id="assigmentLocation" name="assigmentLocation" value="{{ $assigmentLocation }}" disabled>
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
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
