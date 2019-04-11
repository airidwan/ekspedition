@extends('layouts.master')

@section('title', trans('inventory/menu.master-item'))

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="widget">
            <div class="widget-header">
                <h2><i class="fa fa-briefcase"></i> <strong>{{ $title }}</strong> {{ trans('inventory/menu.master-item') }}</h2>
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
                        <input type="hidden" name="id" value="{{ $model->item_id }}">
                        <div class="col-sm-6 portlets">
                            <div class="form-group {{ $errors->has('itemCode') ? 'has-error' : '' }}">
                                <label for="itemCode" class="col-sm-4 control-label">{{ trans('inventory/fields.item-code') }} <span class="required">*</span></label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="itemCode" name="itemCode" value="{{ count($errors) > 0 ? old('itemCode') : $model->item_code }}">
                                    @if($errors->has('itemCode'))
                                    <span class="help-block">{{ $errors->first('itemCode') }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="form-group {{ $errors->has('description') ? 'has-error' : '' }}">
                                <label for="description" class="col-sm-4 control-label">{{ trans('shared/common.deskripsi') }} <span class="required">*</span></label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="description" name="description" value="{{ count($errors) > 0 ? old('description') : $model->description }}">
                                    @if($errors->has('description'))
                                    <span class="help-block">{{ $errors->first('description') }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="form-group {{ $errors->has('uom') ? 'has-error' : '' }}">
                                <label for="uom" class="col-sm-4 control-label">{{ trans('inventory/fields.uom') }}</label>
                                <div class="col-sm-8">
                                    <select class="form-control" id="uom" name="uom">
                                        <?php $uomId = count($errors) > 0 ? old('uom') : $model->uom_id ?>
                                        @foreach($optionsUom as $uom)
                                        <option value="{{ $uom->uom_id }}" {{ $uom->uom_id == $uomId ? 'selected' : '' }}>
                                            {{ $uom->uom_code }}
                                        </option>
                                        @endforeach
                                    </select>
                                    @if($errors->has('uom'))
                                    <span class="help-block">{{ $errors->first('uom') }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="form-group {{ $errors->has('category') ? 'has-error' : '' }}">
                                <label for="category" class="col-sm-4 control-label">{{ trans('shared/common.kategori') }}</label>
                                <div class="col-sm-8">
                                    <select class="form-control" id="category" name="category">
                                        <?php $categoryId = count($errors) > 0 ? old('category') : $model->category_id ?>
                                        @foreach($optionsCategory as $category)
                                        <option value="{{ $category->category_id }}" {{ $category->category_id == $categoryId ? 'selected' : '' }}>
                                            {{ $category->description }}
                                        </option>
                                        @endforeach
                                    </select>
                                    @if($errors->has('category'))
                                    <span class="help-block">{{ $errors->first('category') }}</span>
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
                        <div class="col-sm-12 data-table-toolbar text-right">
                            <div class="form-group">
                                <a href="{{ URL($url) }}" class="btn btn-sm btn-warning"><i class="fa fa-reply"></i> {{ trans('shared/common.cancel') }}</a>
                                <button type="submit" class="btn btn-sm btn-primary"><i class="fa fa-save"></i> {{ trans('shared/common.save') }}</button>
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

@section('script')
@parent
<script type="text/javascript">
    $(document).on('ready', function(){
    });
</script>
@endsection
