@extends('layouts.master')

@section('title', trans('operational/menu.truck'))

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
                <h2><i class="fa fa-truck"></i> <strong> {{ $title }} </strong> {{ trans('operational/menu.truck') }}</h2>
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
                        <input type="hidden" name="id" value="{{ $model->truck_id }}">
                        <ul id="demo1" class="nav nav-tabs">
                            <li class="active">
                                <a href="#trukMasterTab" data-toggle="tab">{{ trans('operational/menu.truck') }}</a>
                            </li>
                            <li >
                                <a href="#activationTab" data-toggle="tab">{{ trans('shared/common.activation') }}</a>
                            </li>
                            <li id="li-rentInfoTab" class="disabled">
                                <a href="#rentInfoTab" data-toggle="tab">{{ trans('operational/fields.info-sewa-bulanan') }}</a>
                            </li>
                        </ul>
                        <div class="tab-content">
                            <div class="tab-pane fade active in" id="trukMasterTab">
                                <div class="col-sm-4 portlets">
                                    <div class="form-group {{ $errors->has('code') ? 'has-error' : '' }}">
                                        <label for="code" class="col-sm-5 control-label">{{ trans('shared/common.code') }} <span class="required">*</span></label>
                                        <div class="col-sm-7">
                                            <input type="text" class="form-control" id="code" name="code"  value="{{ !empty($model->truck_code) ? $model->truck_code : '' }}" disabled>
                                            @if($errors->has('code'))
                                            <span class="help-block">{{ $errors->first('code') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="form-group {{ $errors->has('category') ? 'has-error' : '' }}">
                                        <label for="category" class="col-sm-5 control-label">{{ trans('shared/common.category') }} <span class="required">*</span></label>
                                        <div class="col-sm-7">
                                            <select class="form-control" id="category" name="category">
                                                <?php $stringCategory = count($errors) > 0 ? old('category') : $model->category; ?>
                                                <option value="">{{ trans('shared/common.please-select') }}</option>
                                                @foreach($optionCategory as $category)
                                                <option value="{{ $category->lookup_code }}" {{ $category->lookup_code == $stringCategory ? 'selected' : '' }}>{{ $category->meaning }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <?php 
                                        $asset = $model->asset;
                                        $assetNumber = !empty($asset) ? $asset->asset_number : '';
                                        $assetId = !empty($asset) ? $asset->asset_id : '';
                                    ?>
                                    <div id="formAssetNumber" class="hidden form-group {{ $errors->has('assetNumber') ? 'has-error' : '' }}">
                                        <label for="assetNumber" class="col-sm-5 control-label">{{ trans('asset/fields.asset-number') }} <span id="spanManifest" class="required">*</span></label>
                                        <div class="col-sm-7">
                                            <div class="input-group">
                                                <input type="hidden" name="assetId" id="assetId" value="{{ count($errors) > 0 ? old('assetId') : $assetId }}" >
                                                <input type="text" class="form-control disabled" id="assetNumber" name="assetNumber" value="{{ count($errors) > 0 ? old('assetNumber') : $assetNumber }}"  readonly>
                                                <span class="btn input-group-addon" id="modalManifest" data-toggle="modal" data-target="#modal-asset"><i class="fa fa-search"></i></span>
                                            </div>
                                            @if($errors->has('asset'))
                                            <span class="help-block">{{ $errors->first('asset') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="form-group {{ $errors->has('ownerName') ? 'has-error' : '' }}">
                                        <label for="ownerName" class="col-sm-5 control-label">{{ trans('operational/fields.nama-pemilik') }} <span class="required">*</span></label>
                                        <div class="col-sm-7">
                                            <input type="text" class="form-control" id="ownerName" name="ownerName" value="{{ count($errors) > 0 ? old('ownerName') : $model->owner_name }}">
                                            @if($errors->has('ownerName'))
                                            <span class="help-block">{{ $errors->first('ownerName') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="form-group {{ $errors->has('price') ? 'has-error' : '' }}">
                                        <label for="price" class="col-sm-5 control-label">{{ trans('operational/fields.price-per-unit') }} <span class="required">*</span></label>
                                        <div class="col-sm-7">
                                            <input type="text" class="currency form-control" id="price" name="price" value="{{ count($errors) > 0 ? str_replace(',','',old('price')) : $model->truck_price }}">
                                            @if($errors->has('price'))
                                            <span class="help-block">{{ $errors->first('price') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="form-group {{ $errors->has('policeNumber') ? 'has-error' : '' }}">
                                        <label for="policeNumber" class="col-sm-5 control-label">{{ trans('operational/fields.police-number') }} <span class="required">*</span></label>
                                        <div class="col-sm-7">
                                            <input type="text" class="form-control" style="text-transform: uppercase" id="policeNumber" name="policeNumber" value="{{ count($errors) > 0 ? old('policeNumber') : $model->police_number }}">
                                            @if($errors->has('policeNumber'))
                                            <span class="help-block">{{ $errors->first('policeNumber') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="form-group {{ $errors->has('brand') ? 'has-error' : '' }}">
                                        <label for="brand" class="col-sm-5 control-label">{{ trans('operational/fields.brand') }} <span class="required">*</span></label>
                                        <div class="col-sm-7">
                                            <select class="form-control" id="brand" name="brand">
                                                <?php $stringBrand = count($errors) > 0 ? old('brand') : $model->brand; ?>
                                                <option value="">{{ trans('shared/common.please-select') }}</option>
                                                @foreach($optionBrand as $brand)
                                                <option value="{{ $brand->lookup_code }}" {{ $brand->lookup_code == $stringBrand ? 'selected' : '' }}>{{ $brand->meaning }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group {{ $errors->has('type') ? 'has-error' : '' }}">
                                        <label for="type" class="col-sm-5 control-label">{{ trans('operational/fields.type') }} <span class="required">*</span></label>
                                        <div class="col-sm-7">
                                            <select class="form-control" id="type" name="type">
                                                <?php $stringType = count($errors) > 0 ? old('type') : $model->type; ?>
                                                <option value="">{{ trans('shared/common.please-select') }}</option>
                                                @foreach($optionType as $type)
                                                <option value="{{ $type->lookup_code }}" {{ $type->lookup_code == $stringType ? 'selected' : '' }}>{{ $type->meaning }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-4 portlets">
                                    <div class="form-group {{ $errors->has('productionYear') ? 'has-error' : '' }}">
                                        <label for="productionYear" class="col-sm-5 control-label">{{ trans('operational/fields.tahun-buat') }}</label>
                                        <div class="col-sm-7">
                                            <select class="form-control" id="productionYear" name="productionYear">
                                                <?php $productionYear = count($errors) > 0 ? old('productionYear') : $model->production_year; ?>
                                                @foreach($optionYears as $years)
                                                <option value="{{ $years }}" {{ $years == $productionYear ? 'selected' : '' }}>{{ $years }}</option>
                                                @endforeach
                                            </select>
                                            @if($errors->has('productionYear'))
                                            <span class="help-block">{{ $errors->first('productionYear') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="form-group {{ $errors->has('chassisNumber') ? 'has-error' : '' }}">
                                        <label for="chassisNumber" class="col-sm-5 control-label">{{ trans('operational/fields.nomor-rangka') }}</label>
                                        <div class="col-sm-7">
                                            <input type="text" class="form-control" id="chassisNumber" name="chassisNumber" value="{{ count($errors) > 0 ? old('chassisNumber') : $model->chassis_number }}">
                                            @if($errors->has('chassisNumber'))
                                            <span class="help-block">{{ $errors->first('chassisNumber') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="form-group {{ $errors->has('machineNumber') ? 'has-error' : '' }}">
                                        <label for="machineNumber" class="col-sm-5 control-label">{{ trans('operational/fields.nomor-mesin') }}</label>
                                        <div class="col-sm-7">
                                            <input type="text" class="form-control" id="machineNumber" name="machineNumber" value="{{ count($errors) > 0 ? old('machineNumber') : $model->machine_number }}">
                                            @if($errors->has('machineNumber'))
                                            <span class="help-block">{{ $errors->first('machineNumber') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="form-group {{ $errors->has('tubeDimension') ? 'has-error' : '' }}">
                                        <label for="tubeDimension" class="col-sm-5 control-label">{{ trans('operational/fields.dimensi-bak-plt') }} (m)</label>
                                        <div class="col-sm-2" style="padding:0px 0px 0px 15px;">
                                            <!--     <label for="longTube" class="text-center control-label">P</label> -->
                                            <input type="text" class="form-control decimal text-right" id="longTube" placeholder="P" name="longTube" value="{{ count($errors) > 0 ? str_replace(',','',old('longTube')) : $model->long_tube }}">
                                        </div>

                                        <div class="col-sm-2" style="padding:0px 0px 0px 15px;">
                                            <!-- <label for="widthTube" class="text-center control-label">L</label> -->
                                            <input type="text" class="form-control decimal text-right" id="widthTube" placeholder="L" name="widthTube" value="{{ count($errors) > 0 ? str_replace(',','',old('widthTube')) : $model->width_tube }}">
                                        </div>
                                        <div class="col-sm-2" style="padding:0px 0px 0px 15px;">
                                            <!-- <label for="heightTube" class="text-center control-label">T</label> -->
                                            <input type="text" class="form-control decimal text-right" id="heightTube" placeholder="T" name="heightTube" value="{{ count($errors) > 0 ? str_replace(',','',old('heightTube')) : $model->height_tube }}">
                                        </div>
                                    </div>
                                    <?php
                                    if (count($errors) > 0) {
                                        $stnkDate = !empty(old('stnkDate')) ? new \DateTime(old('stnkDate')) : null;
                                    } else {
                                        $stnkDate = !empty($model->stnk_date) ? new \DateTime($model->stnk_date) : null;
                                    }
                                    ?>
                                    <div class="form-group {{ $errors->has('stnkDate') ? 'has-error' : '' }}">
                                        <label for="stnkDate" class="col-sm-5 control-label">{{ trans('operational/fields.tanggal-stnk') }}</label>
                                        <div class="col-sm-7">
                                            <div class="input-group">
                                                <input type="text" id="stnkDate" name="stnkDate" class="form-control datepicker-input" data-mask="99-99-9999" placeholder="dd-mm-yyyy" value="{{ $stnkDate !== null ? $stnkDate->format('d-m-Y') : '' }}">
                                                <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                            </div>
                                            @if($errors->has('stnkDate'))
                                            <span class="help-block">{{ $errors->first('stnkDate') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    <?php
                                    if (count($errors) > 0) {
                                        $kirDate = !empty(old('kirDate')) ? new \DateTime(old('kirDate')) : null;
                                    } else {
                                        $kirDate = !empty($model->kir_date) ? new \DateTime($model->kir_date) : null;
                                    }
                                    ?>
                                    <div class="form-group {{ $errors->has('kirDate') ? 'has-error' : '' }}">
                                        <label for="kirDate" class="col-sm-5 control-label">{{ trans('operational/fields.tanggal-kir') }} </label>
                                        <div class="col-sm-7">
                                            <div class="input-group">
                                                <input type="text" id="kirDate" name="kirDate" class="form-control datepicker-input" data-mask="99-99-9999" placeholder="dd-mm-yyyy" value="{{ $kirDate !== null ? $kirDate->format('d-m-Y') : '' }}">
                                                <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                            </div>
                                            @if($errors->has('kirDate'))
                                            <span class="help-block">{{ $errors->first('kirDate') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="form-group {{ $errors->has('maxWeight') ? 'has-error' : '' }}">
                                        <label for="maxWeight" class="col-sm-5 control-label">{{ trans('operational/fields.berat-max') }}</label>
                                        <div class="col-sm-7">
                                            <input type="text" class="currency form-control text-right" id="maxWeight" name="maxWeight" value="{{ count($errors) > 0 ? str_replace(',', '', old('maxWeight')) : $model->weight_max }}">
                                            @if($errors->has('maxWeight'))
                                            <span class="help-block">{{ $errors->first('maxWeight') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="form-group {{ $errors->has('groundClearance') ? 'has-error' : '' }}">
                                        <label for="groundClearance" class="col-sm-5 control-label">{{ trans('operational/fields.ground-clearance') }} (m)</label>
                                        <div class="col-sm-7">
                                            <input type="text" class="decimal form-control text-right" id="groundClearance" name="groundClearance" value="{{ count($errors) > 0 ? str_replace(',', '', old('groundClearance')) : $model->ground_clearance }}">
                                            @if($errors->has('groundClearance'))
                                            <span class="help-block">{{ $errors->first('groundClearance') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-4 portlets">
                                    <div class="form-group">
                                        <label for="pic" class="col-sm-12 control-label">{{ trans('operational/fields.penanggung-jawab') }}</label>
                                    </div>
                                    <div class="form-group {{ $errors->has('pic') ? 'has-error' : '' }}">
                                        <div class="col-sm-12">
                                            <input type="text" class="form-control" id="pic" name="pic" value="{{ count($errors) > 0 ? old('pic') : $model->pic }}">
                                            @if($errors->has('pic'))
                                            <span class="help-block">{{ $errors->first('pic') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="form-group ">
                                        <label for="description" class="col-sm-12 control-label">{{ trans('shared/common.description') }}</label>
                                    </div>
                                    <div class="form-group {{ $errors->has('description') ? 'has-error' : '' }}">
                                        <div class="col-sm-12">
                                            <textarea type="text" class="form-control" id="description" name="description" rows="3">{{ count($errors) > 0 ? old('description') : $model->description }}</textarea>
                                            @if($errors->has('description'))
                                            <span class="help-block">{{ $errors->first('description') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    <input type="hidden" name="subAccountCode" value="{{ count($errors) > 0 ? old('subAccountCode') : $model->subaccount_code }}">
                                    <div class="form-group">
                                        <label for="subAccountCode" class="col-sm-12 control-label">{{ trans('operational/fields.sub-account') }}</label>
                                    </div>
                                    <div class="form-group {{ $errors->has('viewKodeSub') ? 'has-error' : '' }}">
                                        <div class="col-sm-12">
                                            <input type="text" class="form-control" name="viewSubAccountCode" value="{{ count($errors) > 0 ? old('subAccountCode') : $model->subaccount_code }}" disabled>
                                            @if($errors->has('viewKodeSub'))
                                            <span class="help-block">{{ $errors->first('viewKodeSub') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="form-group {{ $errors->has('status') ? 'has-error' : '' }}">
                                        <div class="col-sm-12">
                                            <label class="checkbox-inline icheckbox">
                                                <?php $status = count($errors) > 0 ? old('status') : $model->active; ?>
                                                <input type="checkbox" id="status" name="status" value="Y"{{ $status == 'Y' ? 'checked' : '' }}> {{ trans('shared/common.active') }}
                                            </label>
                                        </div>
                                    </div>
                                </div>

                            </div>
                            <div class="tab-pane fade" id="activationTab">
                                <div class="table-responsive">
                                    <div class="col-sm-12 portlets">
                                        <table class="table table-striped table-bordered" cellspacing="0" width="100%">
                                            <thead>
                                                <tr>
                                                    <th>{{ trans('shared/common.cabang') }}</th>
                                                    <th><input name="all-branch" id="all-branch" type="checkbox" ></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                $detailBranchId = [];
                                                if (count($errors) > 0) {
                                                    $detailBranchId = old('branchDetail',[]);
                                                }else{
                                                    $branchDetail   = DB::table('op.dt_truck_branch')->where('truck_id', '=', $model->truck_id)->get();
                                                    foreach ($branchDetail as $dtlBranch) {
                                                        $detailBranchId[] = $dtlBranch->branch_id;
                                                    }
                                                }
                                                ?>
                                                @foreach($optionBranch as $branch)
                                                <tr>
                                                    <td>{{ $branch->branch_name }}</td>
                                                    <td class="text-center">
                                                        <input name="branchDetail[]" value="{{ $branch->branch_id }}" type="checkbox" class="rows-check" {{ in_array($branch->branch_id, $detailBranchId) ? 'checked' : '' }}  >
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="rentInfoTab">
                                <div id="horizontal-form">
                                    <div class="col-sm-6 portlets">
                                        <div class="form-group {{ $errors->has('contractNumber') ? 'has-error' : '' }}">
                                            <label for="contractNumber" class="col-sm-5 control-label">{{ trans('operational/fields.no-kontrak') }} <span class="required">*</span></label>
                                            <div class="col-sm-7">
                                                <input type="text" class="form-control" id="contractNumber" name="contractNumber"  value="{{ count($errors) > 0 ? old('contractNumber') : $modelRent->contract_number }}">
                                                @if($errors->has('contractNumber'))
                                                <span class="help-block">{{ $errors->first('contractNumber') }}</span>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="form-group {{ $errors->has('contractLength') ? 'has-error' : '' }}">
                                            <label for="contractLength" class="col-sm-5 control-label">{{ trans('operational/fields.lama-kontrak') }} (month) <span class="required">*</span></label>
                                            <div class="col-sm-7">
                                                <input type="text" class="form-control currency text-right" id="contractLength" name="contractLength" value="{{ count($errors) > 0 ? str_replace(',','',old('contractLength')) : $modelRent->contract_length }}">
                                                @if($errors->has('contractLength'))
                                                <span class="help-block">{{ $errors->first('contractLength') }}</span>
                                                @endif
                                            </div>
                                        </div>
                                        <?php
                                        if (count($errors) > 0) {
                                            $dueDate = !empty(old('dueDate')) ? new \DateTime(old('dueDate')) : null;
                                        } else {
                                            $dueDate = !empty($modelRent->due_date) ? new \DateTime($modelRent->due_date) : null;
                                        }
                                        ?>
                                        <div class="form-group {{ $errors->has('dueDate') ? 'has-error' : '' }}">
                                            <label for="dueDate" class="col-sm-5 control-label">{{ trans('operational/fields.jatuh-tempo') }} <span class="required">*</span></label>
                                            <div class="col-sm-7">
                                                <div class="input-group">
                                                    <input type="text" id="dueDate" name="dueDate" class="form-control datepicker-input" data-mask="99-99-9999" placeholder="dd-mm-yyyy" value="{{ $dueDate !== null ? $dueDate->format('d-m-Y') : '' }}">
                                                    <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                                </div>
                                                @if($errors->has('dueDate'))
                                                <span class="help-block">{{ $errors->first('dueDate') }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 portlets">
                                        <div class="form-group {{ $errors->has('rateMonth') ? 'has-error' : '' }}">
                                            <label for="rateMonth" class="col-sm-5 control-label">{{ trans('operational/fields.biaya-sewa-per-bulan') }} <span class="required">*</span></label>
                                            <div class="col-sm-7">
                                                <input type="text" class="form-control currency text-right" id="rateMonth" name="rateMonth" value="{{ count($errors) > 0 ? str_replace(',','',old('rateMonth')) : $modelRent->rate_per_month }}">
                                                @if($errors->has('rateMonth'))
                                                <span class="help-block">{{ $errors->first('rateMonth') }}</span>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="form-group {{ $errors->has('discountMonth') ? 'has-error' : '' }}">
                                            <label for="discountMonth" class="col-sm-5 control-label">{{ trans('operational/fields.pot-sewa-per-bulan') }} <span class="required">*</span></label>
                                            <div class="col-sm-7">
                                                <input type="text" class="form-control currency text-right" id="discountMonth" name="discountMonth" value="{{ count($errors) > 0 ? str_replace(',','',old('discountMonth')) : $modelRent->rate_discount_per_month }}">
                                                @if($errors->has('discountMonth'))
                                                <span class="help-block">{{ $errors->first('discountMonth') }}</span>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="col-sm-12 control-label">?) {{ trans('operational/fields.jika-kendaraan-mangkir') }}</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="clearfix"></div>
                        <hr>
                        <div class="col-sm-12 data-table-toolbar text-right">
                            <div class="form-group">
                                <a href="{{ URL('operational/master/master-truck') }}" class="btn btn-sm btn-warning"><i class="fa fa-reply"></i> {{ trans('shared/common.cancel') }}</a>
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
<div id="modal-asset" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title text-center">LOV Asset</h4>
            </div>
            <div class="modal-body">
                <table id="datatables-asset" class="table table-hover table-striped table-bordered" cellspacing="0" width="100%">
                   <thead>
                        <tr>
                            <th>{{ trans('asset/fields.asset-number') }}</th>
                            <th>{{ trans('inventory/fields.item-code') }}</th>
                            <th>{{ trans('shared/common.description') }}</th>
                            <th>{{ trans('asset/fields.police-number') }}</th>
                            <th>{{ trans('asset/fields.po-cost') }}</th>
                            <th>{{ trans('asset/fields.employee') }}</th>
                            <th>{{ trans('shared/common.category') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                       @foreach ($optionAsset as $asset)
                        <tr style="cursor: pointer;" data-asset="{{ json_encode($asset) }}">
                            <td>{{ $asset->asset_number }}</td>
                            <td>{{ $asset->item_code }}</td>
                            <td>{{ $asset->item_description }}</td>
                            <td>{{ $asset->police_number }}</td>
                            <td class="text-right">{{ number_format($asset->po_cost) }}</td>
                            <td>{{ $asset->employee_name }}</td>
                            <td>{{ $asset->category_name }}</td>
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
        $("#currency").maskMoney({thousands:'.', decimal:',', precision:0});
        if ($('#category').val() == 'ASSET') {
                $('#formAssetNumber').removeClass('hidden');
            }
        $('#subAccountCode').select2();

        $("#datatables-asset").dataTable({
            "pageLength" : 10,
            "lengthChange": false
        });

        $('#datatables-asset tbody').on('click', 'tr', function () {
            var asset = $(this).data('asset');

            $('#assetNumber').val(asset.asset_number);
            $('#assetId').val(asset.asset_id);
            $('#ownerName').val(asset.employee_name);
            $('#pic').val(asset.employee_name);
            $('#description').val(asset.item_description);
            $('#policeNumber').val(asset.police_number);
            $('#price').val(asset.po_cost);

            $('#price').autoNumeric('update', {mDec: 0});

            $('#modal-asset').modal('hide');
        });

        $('#all-branch').on('ifChanged', function(){
            var $inputs = $(this).parent().parent().parent().parent().parent().find('input[type="checkbox"]');
            if (!$(this).parent('[class*="icheckbox"]').hasClass("checked")) {
                $inputs.iCheck('check');
            } else {
                $inputs.iCheck('uncheck');
            }
        });

        $('#li-rentInfoTab').on('click', function(){
            if ($(this).hasClass('disabled')) {
                return false;
            }
        });

        var disableRentInfoTab = function() {
            if ($('#category').val() == 'SEWA_BULANAN') {
                $('#li-rentInfoTab').removeClass('disabled');
            } else {
                $('#li-rentInfoTab').addClass('disabled');
            }
        };

        $('#category').on('change', function(){
            if ($('#category').val() == 'ASSET') {
                $('#formAssetNumber').removeClass('hidden');
            }else{
                $('#formAssetNumber').addClass('hidden');
            }
            disableRentInfoTab();
        });

        disableRentInfoTab();
    });
</script>
@endsection
