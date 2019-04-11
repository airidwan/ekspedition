@extends('layouts.master')

@section('title', trans('payable/menu.master-ap-type'))

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="widget">
            <div class="widget-header">
                <h2><i class="fa fa-usd"></i> <strong>{{ $title }}</strong> {{ trans('payable/menu.master-ap-type') }}</h2>
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
                                <label for="typeName" class="col-sm-3 control-label">{{ trans('payable/fields.type-name') }} <span class="required">*</span></label>
                                <div class="col-sm-9">
                                    <input type="text" class="form-control" id="typeName" name="typeName" value="{{ count($errors) > 0 ? old('typeName') : $model->type_name }}">
                                    @if($errors->has('typeName'))
                                    <span class="help-block">{{ $errors->first('typeName') }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="form-group {{ $errors->has('coaD') ? 'has-error' : '' }}">
                                <label for="coaD" class="col-sm-3 control-label">{{ trans('payable/fields.coa-d') }}<span class="required">*</span></label>
                                <div class="col-sm-9">
                                    <div class="input-group">
                                    <input type="hidden" class="form-control" id="coaD" name="coaD" value="{{ count($errors) > 0 ? old('coaD') : $model->coa_id_d }}">
                                    <input type="text" class="form-control" id="coaDView" name="coaDView" value="{{ count($errors) > 0 ? old('coaDView') : $coaDDesc }}" readonly>
                                        <span class="btn input-group-addon" data-toggle="modal" data-target="#modal-lov-coa-d"><i class="fa fa-search"></i></span>
                                    </div>
                                    @if($errors->has('coaD'))
                                    <span class="help-block">{{ $errors->first('coaD') }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="form-group {{ $errors->has('coaC') ? 'has-error' : '' }}">
                                <label for="coaC" class="col-sm-3 control-label">{{ trans('payable/fields.coa-c') }}<span class="required">*</span></label>
                                <div class="col-sm-9">
                                    <div class="input-group">
                                    <input type="hidden" class="form-control" id="coaC" name="coaC" value="{{ count($errors) > 0 ? old('coaC') : $model->coa_id_c }}">
                                    <input type="text" class="form-control" id="coaCView" name="coaCView" value="{{ count($errors) > 0 ? old('coaCView') : $coaCDesc }}" readonly>
                                        <span class="btn input-group-addon" data-toggle="modal" data-target="#modal-lov-coa-c"><i class="fa fa-search"></i></span>
                                    </div>
                                    @if($errors->has('coaC'))
                                    <span class="help-block">{{ $errors->first('coaC') }}</span>
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
<div id="modal-lov-coa-c" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title text-center">{{ trans('general-ledger/fields.coa-code') }}</h4>
            </div>
            <div class="modal-body">
                <table id="datatables-lov-coa-c" class="table table-hover table-striped table-bordered" cellspacing="0" width="100%">
                    <thead>
                        <tr>
                            <th>{{ trans('general-ledger/fields.coa-code') }}</th>
                            <th>{{ trans('general-ledger/fields.description') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $accountCombinationId = count($errors) > 0 ? old('coa') : $model->coa_id ?>
                        @foreach ($optionsCoa as $coa)
                        <tr style="cursor: pointer;" class="tr-lov">
                            <td>{{ $coa->coa_code }}
                                <input type="hidden" value="{{ $coa->coa_id }}" name="coaC[]">
                                <input type="hidden" value="{{ $coa->description }}" name="descriptionC[]">
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
<div id="modal-lov-coa-d" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title text-center">{{ trans('general-ledger/fields.coa-code') }}</h4>
            </div>
            <div class="modal-body">
                <table id="datatables-lov-coa-d" class="table table-hover table-striped table-bordered" cellspacing="0" width="100%">
                    <thead>
                        <tr>
                            <th>{{ trans('general-ledger/fields.coa-code') }}</th>
                            <th>{{ trans('general-ledger/fields.description') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $accountCombinationId = count($errors) > 0 ? old('coa') : $model->coa_id ?>
                        @foreach ($optionsCoa as $coa)
                        <tr style="cursor: pointer;" class="tr-lov">
                            <td>{{ $coa->coa_code }}
                                <input type="hidden" value="{{ $coa->coa_id }}" name="coaD[]">
                                <input type="hidden" value="{{ $coa->description }}" name="descriptionD[]">
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

        $("#datatables-lov-coa-c").dataTable({
            "pagelength" : 10,
            "lengthChange": false
        });

        $("#datatables-lov-coa-d").dataTable({
            "pagelength" : 10,
            "lengthChange": false
        });

        $('#datatables-lov-coa-c tbody').on('click', 'tr', function () {
            var coaC  = $(this).find('input[name="coaC[]"]').val();
            var descriptionC         = $(this).find('input[name="descriptionC[]"]').val();
        
            $('#coaC').val(coaC);
            $('#coaCView').val(descriptionC);
            
            $('#modal-lov-coa-c').modal("hide");
        });

        $('#datatables-lov-coa-d tbody').on('click', 'tr', function () {
            var coaD  = $(this).find('input[name="coaD[]"]').val();
            var descriptionD         = $(this).find('input[name="descriptionD[]"]').val();
        
            $('#coaD').val(coaD);
            $('#coaDView').val(descriptionD);
            
            $('#modal-lov-coa-d').modal("hide");
        });
    });
</script>
@endsection
