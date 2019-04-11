@extends('layouts.master')

@section('title', trans('operational/menu.delivery-area'))

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="widget">
            <div class="widget-header">
                <h2><i class="fa fa-truck"></i> <strong>{{ $title }}</strong> {{ trans('operational/menu.delivery-area') }}</h2>
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
                        <input type="hidden" name="id" value="{{ $model->delivery_area_id }}">
                        <ul id="demo1" class="nav nav-tabs">
                            <li class="active">
                                <a href="#deliveryAreaTab" data-toggle="tab">{{ trans('operational/menu.delivery-area') }} <span class="label label-success"></span></a>
                            </li>
                            <li class="">
                                <a href="#activationTab" data-toggle="tab">{{ trans('shared/common.activation') }} <span class="badge badge-primary"></span></a>
                            </li>
                        </ul>
                        <div class="tab-content">
                            <div class="tab-pane fade active in" id="deliveryAreaTab">
                                <div class="col-sm-6 portlets">
                                    <div class="form-group {{ $errors->has('name') ? 'has-error' : '' }}">
                                        <label for="name" class="col-sm-4 control-label">{{ trans('operational/fields.area-name') }} <span class="required">*</span></label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="name" name="name" value="{{ count($errors) > 0 ? old('name') : $model->delivery_area_name }}">
                                            @if($errors->has('name'))
                                            <span class="help-block">{{ $errors->first('name') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="form-group {{ $errors->has('description') ? 'has-error' : '' }}">
                                        <label for="description" class="col-sm-4 control-label">{{ trans('shared/common.description') }} <span class="required">*</span></label>
                                        <div class="col-sm-8">
                                            <textarea type="text" class="form-control" id="description" name="description" rows="4">{{ count($errors) > 0 ? old('description') : $model->description }}</textarea>
                                            @if($errors->has('description'))
                                            <span class="help-block">{{ $errors->first('description') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="form-group {{ $errors->has('status') ? 'has-error' : '' }}">
                                        <label class="col-sm-4 control-label">{{ trans('shared/common.status') }}</label>
                                        <div class="col-sm-8">
                                            <label class="checkbox-inline icheckbox">
                                                <?php $status = count($errors) > 0 ? old('status') : $model->active; ?>
                                                <input type="checkbox" id="status" name="status" value="Y" {{ $status == 'Y' ? 'checked' : '' }}> {{ trans('shared/common.active') }}
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
                                                    <th>{{ trans('shared/common.branch') }}</th>
                                                    <th><input name="all-branch" id="all-branch" type="checkbox" ></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                $detailBranchId = [];
                                                if (count($errors) > 0) {
                                                    $detailBranchId = old('branchDetail',[]);
                                                }else{
                                                    $branchDetail   = $model->deliveryAreaBranch()->get();
                                                    foreach ($branchDetail as $dtlBranch) {
                                                        $detailBranchId[] = $dtlBranch->branch_id;
                                                    }
                                                }
                                                ?>
                                                @foreach($optionBranch as $branch)
                                                <tr>
                                                    <td>{{ $branch->branch_name }} </td>
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
                        </div>
                        <div class="col-sm-12 data-table-toolbar text-right">
                            <div class="form-group">
                                <a href="{{ URL($url) }}" class="btn btn-sm btn-warning"><i class="fa fa-reply"></i> {{ trans('shared/common.cancel') }}</a>
                                <button type="submit" class="btn btn-sm btn-primary"><i class="fa fa-save"></i> {{ trans('shared/common.save') }}</button>
                            </div>
                        </div>
                    </form>
                </div>
                <br>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
@parent()
<script type="text/javascript">
    $(document).on('ready', function(){
        $('#all-branch').on('ifChanged', function(){
            var $inputs = $(this).parent().parent().parent().parent().parent().find('input[type="checkbox"]');
            if (!$(this).parent('[class*="icheckbox"]').hasClass("checked")) {
                $inputs.iCheck('check');
            } else {
                $inputs.iCheck('uncheck');
            }
        });
    });
</script>
@endsection
