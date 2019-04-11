@extends('layouts.master')

@section('title', trans('asset/menu.addition-asset'))

<?php 
use App\Modules\Operational\Model\Master\MasterBranch;
     ?>
@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="widget">
            <div class="widget-header">
                <h2><i class="fa fa-folder"></i> {{ trans('asset/menu.addition-asset') }}</h2>
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
                        <div class="col-sm-4 portlets">
                            <div class="form-group">
                                <label for="assetNumber" class="col-sm-4 control-label">{{ trans('asset/fields.asset-number') }}</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="assetNumber" name="assetNumber" value="{{ !empty($filters['assetNumber']) ? $filters['assetNumber'] : '' }}">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="itemCode" class="col-sm-4 control-label">{{ trans('inventory/fields.item-code') }}</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="itemCode" name="itemCode" value="{{ !empty($filters['itemCode']) ? $filters['itemCode'] : '' }}">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="itemDescription" class="col-sm-4 control-label">{{ trans('inventory/fields.item-description') }}</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="itemDescription" name="itemDescription" value="{{ !empty($filters['itemDescription']) ? $filters['itemDescription'] : '' }}">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="policeNumber" class="col-sm-4 control-label">{{ trans('operational/fields.police-number') }}</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="policeNumber" name="policeNumber" value="{{ !empty($filters['policeNumber']) ? $filters['policeNumber'] : '' }}">
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-4 portlets">
                            <div class="form-group">
                                <label for="employee" class="col-sm-4 control-label">{{ trans('asset/fields.employee') }}</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="employee" name="employee" value="{{ !empty($filters['employee']) ? $filters['employee'] : '' }}">
                                </div>
                            </div>
                            <div class="form-group {{ $errors->has('category') ? 'has-error' : '' }}">
                                <label for="category" class="col-sm-4 control-label">{{ trans('shared/common.category') }}</label>
                                <div class="col-sm-8">
                                    <select class="form-control" id="category" name="category">
                                        <?php $stringCategory = !empty($filters['category']) ? $filters['category'] : ''; ?>
                                        <option value="">ALL</option>
                                        @foreach($optionCategory as $category)
                                        <option value="{{ $category->asset_category_id }}" {{ $category->asset_category_id == $stringCategory ? 'selected' : '' }}>{{ $category->category_name  }}</option>
                                        @endforeach
                                    </select>
                                    @if($errors->has('category'))
                                    <span class="help-block">{{ $errors->first('category') }}</span>
                                    @endif
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
                            @if(\Session::get('currentBranch')->branch_code_numeric == MasterBranch::KODE_NUMERIC_HO)
                            <div class="form-group {{ $errors->has('branch') ? 'has-error' : '' }}">
                                <label for="branch" class="col-sm-4 control-label">{{ trans('operational/fields.branch') }}</label>
                                <div class="col-sm-8">
                                    <select class="form-control" id="branch" name="branch">
                                        <?php $branchId = !empty($filters['branch']) ? $filters['branch'] : ''; ?>
                                        <option value="">ALL</option>
                                        @foreach($optionBranch as $branch)
                                        <option value="{{ $branch->branch_id }}" {{ $branch->branch_id == $branchId ? 'selected' : '' }}>{{ $branch->branch_name }}</option>
                                        @endforeach
                                    </select>
                                    @if($errors->has('branch'))
                                    <span class="help-block">{{ $errors->first('branch') }}</span>
                                    @endif
                                </div>
                            </div>
                            @endif
                        </div>
                        <div class="col-sm-4 portlets">
                            <div class="form-group {{ $errors->has('status') ? 'has-error' : '' }}">
                                <label for="status" class="col-sm-4 control-label">{{ trans('shared/common.status') }}</label>
                                <div class="col-sm-8">
                                    <select class="form-control" id="status" name="status">
                                        <?php $statusId = !empty($filters['status']) ? $filters['status'] : ''; ?>
                                        <option value="">ALL</option>
                                        @foreach($optionStatus as $status)
                                        <option value="{{ $status->asset_status_id }}" {{ $status->asset_status_id == $statusId ? 'selected' : '' }}>{{ $status->status  }}</option>
                                        @endforeach
                                    </select>
                                    @if($errors->has('status'))
                                    <span class="help-block">{{ $errors->first('status') }}</span>
                                    @endif
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
                                <a href="{{ URL($url.'/print-excel-index') }}" class="button btn btn-sm btn-success" target="_blank">
                                    <i class="fa fa-file-excel-o"></i> {{ trans('shared/common.print-excel') }}
                                </a>
                                <button type="submit" class="btn btn-sm btn-info">
                                    <i class="fa fa-search"></i> {{ trans('shared/common.filter') }}
                                </button>
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
                    <form class='form-horizontal' role='form' id="table-line">
                        <table class="table table-striped table-bordered" cellspacing="0" width="100%">
                            <thead>
                                <tr>
                                    <th width="50px">{{ trans('shared/common.num') }}</th>
                                    <th>{{ trans('asset/fields.asset-number') }}</th>
                                    <th>{{ trans('shared/common.type') }}</th>
                                    <th>{{ trans('shared/common.category') }}</th>
                                    <th>{{ trans('inventory/fields.item-code') }}</th>
                                    <th>{{ trans('inventory/fields.item-description') }}</th>
                                    <th>{{ trans('operational/fields.police-number') }}</th>
                                    <th>{{ trans('asset/fields.employee') }}</th>
                                    <th>{{ trans('shared/common.date') }}</th>
                                    <th>{{ trans('shared/common.status') }}</th>
                                    <th width="60px" >{{ trans('shared/common.action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $no = ($models->currentPage() - 1) * $models->perPage() + 1;?>
                                @foreach($models as $model)
                                 <?php
                                     $assetDate = !empty($model->asset_date) ? new \DateTime($model->asset_date) : null;
                                 ?>
                                <tr>
                                    <td class="text-center">{{ $no++ }}</td>
                                    <td>{{ $model->asset_number }}</td>
                                    <td>{{ $model->type }}</td>
                                    <td>{{ $model->category_name }}</td>
                                    <td>{{ $model->item_code }}</td>
                                    <td>{{ $model->item_description }}</td>
                                    <td>{{ $model->police_number }}</td>
                                    <td>{{ $model->employee_name }}</td>
                                    <td>{{ !empty($assetDate) ? $assetDate->format('d-M-Y') : '' }}</td>
                                    <td>{{ $model->status }}</td>
                                    <td class="text-center">
                                        <a href="{{ URL($url . '/edit/' . $model->asset_id) }}" data-toggle="tooltip" class="btn btn-xs btn-warning" data-original-title="{{ trans('shared/common.edit') }}">
                                            <i class="fa fa-pencil"></i>
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </form>
                </div>
                <div class="data-table-toolbar">
                    {!! $models->render() !!}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
