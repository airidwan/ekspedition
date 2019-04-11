@extends('layouts.master')

@section('title', trans('inventory/menu.master-category'))

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="widget">
            <div class="widget-header">
                <h2><i class="fa fa-briefcase"></i> <strong>{{ $title }}</strong> {{ trans('inventory/menu.master-category') }}</h2>
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
                        <input type="hidden" name="id" value="{{ $model->category_id }}">
                        <div class="col-sm-8 portlets">
                            <div class="form-group {{ $errors->has('categoryCode') ? 'has-error' : '' }}">
                                <label for="categoryCode" class="col-sm-4 control-label">{{ trans('inventory/fields.category-code') }} <span class="required">*</span></label>
                                <div class="col-sm-8">
                                    <select class="form-control" id="categoryCode" name="categoryCode">
                                        <?php $stringType = count($errors) > 0 ? old('categoryCode') : $model->category_code; ?>
                                        <option value="">{{ trans('shared/common.please-select') }}</option>
                                        @foreach($optionCategory as $categoryCode)
                                        <option value="{{ $categoryCode }}" {{ $categoryCode == $stringType ? 'selected' : '' }}>{{ $categoryCode }}</option>
                                        @endforeach
                                    </select>
                                    @if($errors->has('categoryCode'))
                                    <span class="help-block">{{ $errors->first('categoryCode') }}</span>
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
                            <?php 
                                $coa     = $model->coa;
                                $coaCode = !empty($coa) ? $coa->coa_code : '';
                                $coaDesc = !empty($coa) ? $coa->description : '';
                            ?>
                            <div class="form-group {{ $errors->has('coa') ? 'has-error' : '' }}">
                                <label for="coa" class="col-sm-4 control-label">{{ trans('inventory/fields.coa') }} (D) <span class="required">*</span></label>
                                <div class="col-sm-8">
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
                            <div class="form-group {{ $errors->has('coaDesc') ? 'has-error' : '' }}">
                                <label for="coaDesc" class="col-sm-4 control-label">{{ trans('general-ledger/fields.coa-description') }} </label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="coaDesc" name="coaDesc" value="{{ count($errors) > 0 ? old('coaDesc') : $coaDesc }}" readonly>
                                    @if($errors->has('coaDesc'))
                                    <span class="help-block">{{ $errors->first('coaDesc') }}</span>
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
                                <input type="hidden" value="{{ $coa->coa_id }}" name="coa[]">
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
@parent()
<script type="text/javascript">
    $(document).on('ready', function(){
        $("#datatables-lov").dataTable({
            "pagelength" : 10,
            "lengthChange": false
        });

        $('#datatables-lov tbody').on('click', 'tr', function () {
            var coa              = $(this).find('input[name="coa[]"]').val();
            var coaCode         = $(this).find('input[name="coaCode[]"]').val();
            var description     = $(this).find('input[name="description[]"]').val();
        
            $('#coa').val(coa);
            $('#coaCode').val(coaCode);
            $('#coaDesc').val(description);
            
            $('#modal-lov-coa').modal("hide");
        });
    });
</script>
@endsection
