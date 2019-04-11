@extends('layouts.master')

@section('title', trans('asset/menu.asset-category'))

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="widget">
            <div class="widget-header">
                <h2><i class="fa fa-folder"></i> <strong>{{ $title }}</strong> {{ trans('asset/menu.asset-category') }}</h2>
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
                        <input type="hidden" name="id" value="{{ $model->asset_category_id }}">
                        <div class="col-sm-8 portlets">
                            <div class="form-group {{ $errors->has('category') ? 'has-error' : '' }}">
                                <label for="category" class="col-sm-4 control-label">{{ trans('shared/common.category') }} <span class="required">*</span></label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="category" name="category" value="{{ count($errors) > 0 ? old('category') : $model->category_name }}">
                                    @if($errors->has('category'))
                                    <span class="help-block">{{ $errors->first('category') }}</span>
                                    @endif
                                </div>
                            </div>
                            <?php 
                            $clearing     = $model->clearing;
                            $clearingDesc = !empty($clearing) ? $clearing->description : ''; 
                            ?>
                            <div class="form-group {{ $errors->has('clearing') ? 'has-error' : '' }}">
                                <label for="clearing" class="col-sm-4 control-label">{{ trans('asset/fields.clearing') }} <span class="required">*</span></label>
                                <div class="col-sm-8">
                                    <div class="input-group">
                                    <input type="hidden" class="form-control" id="clearing" name="clearing" value="{{ count($errors) > 0 ? old('clearing') : $model->clearing_coa_id }}">
                                    <input type="text" class="form-control" id="clearingView" name="clearingView" value="{{ count($errors) > 0 ? old('clearingView') : $clearingDesc }}" disabled>
                                    <span class="btn input-group-addon" data-toggle="modal" data-target="#modal-lov-clearing"><i class="fa fa-search"></i></span>
                                    </div>
                                    @if($errors->has('clearing'))
                                    <span class="help-block">{{ $errors->first('clearing') }}</span>
                                    @endif
                                </div>
                            </div>
                            <?php 
                            $depreciation     = $model->depreciation;
                            $depreciationDesc = !empty($depreciation) ? $depreciation->description : ''; 
                            ?>
                            <div class="form-group {{ $errors->has('depreciation') ? 'has-error' : '' }}">
                                <label for="depreciation" class="col-sm-4 control-label">{{ trans('asset/fields.depreciation') }} <span class="required">*</span></label>
                                <div class="col-sm-8">
                                    <div class="input-group">
                                    <input type="hidden" class="form-control" id="depreciation" name="depreciation" value="{{ count($errors) > 0 ? old('depreciation') : $model->depreciation_coa_id }}">
                                    <input type="text" class="form-control" id="depreciationView" name="depreciationView" value="{{ count($errors) > 0 ? old('depreciationView') : $depreciationDesc }}" disabled>
                                    <span class="btn input-group-addon" data-toggle="modal" data-target="#modal-lov-depreciation"><i class="fa fa-search"></i></span>
                                    </div>
                                    @if($errors->has('depreciation'))
                                    <span class="help-block">{{ $errors->first('depreciation') }}</span>
                                    @endif
                                </div>
                            </div>
                            <?php 
                            $acumulated     = $model->acumulated;
                            $acumulatedDesc = !empty($acumulated) ? $acumulated->description : ''; 
                            ?>
                            <div class="form-group {{ $errors->has('acumulated') ? 'has-error' : '' }}">
                                <label for="acumulated" class="col-sm-4 control-label">{{ trans('asset/fields.acumulated') }} <span class="required">*</span></label>
                                <div class="col-sm-8">
                                    <div class="input-group">
                                    <input type="hidden" class="form-control" id="acumulated" name="acumulated" value="{{ count($errors) > 0 ? old('acumulated') : $model->acumulated_coa_id }}">
                                    <input type="text" class="form-control" id="acumulatedView" name="acumulatedView" value="{{ count($errors) > 0 ? old('acumulatedView') : $acumulatedDesc }}" disabled>
                                    <span class="btn input-group-addon" data-toggle="modal" data-target="#modal-lov-acumulated"><i class="fa fa-search"></i></span>
                                    </div>
                                    @if($errors->has('acumulated'))
                                    <span class="help-block">{{ $errors->first('acumulated') }}</span>
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

@section('modal')
@parent
<div id="modal-lov-clearing" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title text-center">{{ trans('general-ledger/fields.coa-code') }}</h4>
            </div>
            <div class="modal-body">
                <table id="datatables-lov-clearing" class="table table-hover table-striped table-bordered" cellspacing="0" width="100%">
                    <thead>
                        <tr>
                            <th>{{ trans('general-ledger/fields.coa-code') }}</th>
                            <th>{{ trans('general-ledger/fields.description') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($optionsCoa as $coa)
                        <tr style="cursor: pointer;" class="tr-lov">
                            <td>{{ $coa->coa_code }}
                                <input type="hidden" value="{{ $coa->coa_id }}" name="coaId[]">
                                <input type="hidden" value="{{ $coa->description }}" name="description[]">
                            </td>
                            <td>{{ $coa->description }}</td>
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

<div id="modal-lov-depreciation" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title text-center">{{ trans('general-ledger/fields.coa-code') }}</h4>
            </div>
            <div class="modal-body">
                <table id="datatables-lov-depreciation" class="table table-hover table-striped table-bordered" cellspacing="0" width="100%">
                    <thead>
                        <tr>
                            <th>{{ trans('general-ledger/fields.coa-code') }}</th>
                            <th>{{ trans('general-ledger/fields.description') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $coaIdId = count($errors) > 0 ? old('coa') : $model->coa_id ?>
                        @foreach ($optionsCoa as $coa)
                        <tr style="cursor: pointer;" class="tr-lov">
                            <td>{{ $coa->coa_code }}
                                <input type="hidden" value="{{ $coa->coa_id }}" name="coaId[]">
                                <input type="hidden" value="{{ $coa->description }}" name="description[]">
                            </td>
                            <td>{{ $coa->description }}</td>
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
<div id="modal-lov-acumulated" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title text-center">{{ trans('general-ledger/fields.coa-code') }}</h4>
            </div>
            <div class="modal-body">
                <table id="datatables-lov-acumulated" class="table table-hover table-striped table-bordered" cellspacing="0" width="100%">
                    <thead>
                        <tr>
                            <th>{{ trans('general-ledger/fields.coa-code') }}</th>
                            <th>{{ trans('general-ledger/fields.description') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $coaIdId = count($errors) > 0 ? old('coa') : $model->coa_id ?>
                        @foreach ($optionsCoa as $coa)
                        <tr style="cursor: pointer;" class="tr-lov">
                            <td>{{ $coa->coa_code }}
                                <input type="hidden" value="{{ $coa->coa_id }}" name="coaId[]">
                                <input type="hidden" value="{{ $coa->description }}" name="description[]">
                            </td>
                            <td>{{ $coa->description }}</td>
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
        $("#datatables-lov-clearing").dataTable({
            "pagelength" : 10,
            "lengthChange": false
        });

        $('#datatables-lov-clearing tbody').on('click', 'tr', function () {
            var coaId  = $(this).find('input[name="coaId[]"]').val();
            var description         = $(this).find('input[name="description[]"]').val();
        
            $('#clearing').val(coaId);
            $('#clearingView').val(description);
            
            $('#modal-lov-clearing').modal("hide");
        });

        $("#datatables-lov-depreciation").dataTable({
            "pagelength" : 10,
            "lengthChange": false
        });

        $('#datatables-lov-depreciation tbody').on('click', 'tr', function () {
            var coaId  = $(this).find('input[name="coaId[]"]').val();
            var description         = $(this).find('input[name="description[]"]').val();
        
            $('#depreciation').val(coaId);
            $('#depreciationView').val(description);
            
            $('#modal-lov-depreciation').modal("hide");
        });

        $("#datatables-lov-acumulated").dataTable({
            "pagelength" : 10,
            "lengthChange": false
        });

        $('#datatables-lov-acumulated tbody').on('click', 'tr', function () {
            var coaId  = $(this).find('input[name="coaId[]"]').val();
            var description         = $(this).find('input[name="description[]"]').val();
        
            $('#acumulated').val(coaId);
            $('#acumulatedView').val(description);
            
            $('#modal-lov-acumulated').modal("hide");
        });
    });
</script>
@endsection
