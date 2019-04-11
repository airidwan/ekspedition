@extends('layouts.master')

@section('title', trans('purchasing/menu.master-type-po'))

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="widget">
            <div class="widget-header">
                <h2><i class="fa fa-truck"></i> <strong>{{ $title }}</strong> {{ trans('purchasing/menu.master-type-po') }}</h2>
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
                        <input type="hidden" name="id" value="{{ $model->type_id }}">
                        <div class="col-sm-12 portlets">
                            <div class="form-group {{ $errors->has('typeName') ? 'has-error' : '' }}">
                                <label for="typeName" class="col-sm-3 control-label">{{ trans('purchasing/fields.type-name') }} <span class="required">*</span></label>
                                <div class="col-sm-9">
                                    <input type="text" class="form-control" id="typeName" name="typeName" value="{{ count($errors) > 0 ? old('typeName') : $model->type_name }}">
                                    @if($errors->has('typeName'))
                                    <span class="help-block">{{ $errors->first('typeName') }}</span>
                                    @endif
                                </div>
                            </div>
                            <?php 
                                $coa     = $model->coa;
                                $coaCode = !empty($coa) ? $coa->coa_code : '';
                                $coaDesc = !empty($coa) ? $coa->description : '';
                            ?>
                            <div class="form-group {{ $errors->has('coa') ? 'has-error' : '' }}">
                                <label for="coa" class="col-sm-3 control-label">{{ trans('inventory/fields.coa') }} (C) <span class="required">*</span></label>
                                <div class="col-sm-9">
                                    <div class="input-group">
                                    <input type="hidden" class="form-control" id="coa" name="coa" value="{{ count($errors) > 0 ? old('coa') : $model->coa_id }}">
                                    <input type="text" class="form-control" id="coaCode" name="coaCode" value="{{ count($errors) > 0 ? old('coaCode') : $coaCode }}" readonly>
                                    <span class="btn input-group-addon" data-toggle="modal" data-target="#modal-lov-coa"><i class="fa fa-search"></i></span>
                                    </div>
                                    @if($errors->has('coa'))
                                    <span class="help-block">{{ $errors->first('coa') }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="form-group {{ $errors->has('description') ? 'has-error' : '' }}">
                                <label for="description" class="col-sm-3 control-label">{{ trans('shared/common.description') }} </label>
                                <div class="col-sm-9">
                                    <input type="text" class="form-control" id="description" name="description" value="{{ count($errors) > 0 ? old('description') : $coaDesc }}" readonly>
                                    @if($errors->has('description'))
                                    <span class="help-block">{{ $errors->first('description') }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="form-group {{ $errors->has('status') ? 'has-error' : '' }}">
                                <label class="col-sm-3 control-label">{{ trans('shared/common.status') }}</label>
                                <div class="col-sm-9">
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
<div id="modal-lov-coa" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title text-center">{{ trans('general-ledger/fields.coa-code') }}</h4>
            </div>
            <div class="modal-body">
                <table id="datatables-lov" class="table table-hover table-striped table-bordered" cellspacing="0" width="100%">
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
                                <input type="hidden" value="{{ $coa->coa_code }}" name="coaCode[]">
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
@parent
<script type="text/javascript">
    $(document).on('ready', function(){
        $('#combination').select2();

        $("#datatables-lov").dataTable({
            "pagelength" : 10,
            "lengthChange": false
        });

        $('#datatables-lov tbody').on('click', 'tr', function () {
            var coaId              = $(this).find('input[name="coaId[]"]').val();
            var coaCode         = $(this).find('input[name="coaCode[]"]').val();
            var description     = $(this).find('input[name="description[]"]').val();
        
            $('#coa').val(coaId);
            $('#coaCode').val(coaCode);
            $('#description').val(description);
            
            $('#modal-lov-coa').modal("hide");
        });
    });
</script>
@endsection
