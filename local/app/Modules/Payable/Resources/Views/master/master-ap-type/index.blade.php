@extends('layouts.master')

@section('title', trans('payable/menu.master-ap-type'))

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="widget">
            <div class="widget-header">
                <h2><i class="fa fa-usd"></i> {{ trans('payable/menu.master-ap-type') }}</h2>
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
                        <div class="col-sm-6 portlets">
                            <div class="form-group">
                                <label for="typeName" class="col-sm-4 control-label">{{ trans('payable/fields.type-name') }}</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="typeName" name="typeName" value="{{ !empty($filters['typeName']) ? $filters['typeName'] : '' }}">
                                </div>
                            </div>
                            <div class="form-group {{ $errors->has('status') ? 'has-error' : '' }}">
                                <label class="col-sm-4 control-label">{{ trans('shared/common.status') }}</label>
                                <div class="col-sm-8">
                                    <label class="checkbox-inline icheckbox">
                                        <?php $status = !empty($filters['status']) || !Session::has('filters') ?>
                                        <input type="checkbox" id="status" name="status" value="Y" {{ $status == 'Y' ? 'checked' : '' }}> {{ trans('shared/common.active') }}
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-12 data-table-toolbar text-right">
                            <div class="form-group">
                                <button type="submit" class="btn btn-sm btn-info"><i class="fa fa-search"></i> {{ trans('shared/common.filter') }}</button>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="clearfix"></div>
                <hr>
                <div class="table-responsive">
                    <table class="table table-striped table-bordered" cellspacing="0" width="100%">
                        <thead>
                            <tr>
                                <th>{{ trans('payable/fields.type-name') }}</th>
                                <th>{{ trans('payable/fields.coa-d') }}</th>
                                <th>{{ trans('general-ledger/fields.coa-description') }}</th>
                                <th>{{ trans('payable/fields.coa-c') }}</th>
                                <th>{{ trans('general-ledger/fields.coa-description') }}</th>
                                <th>{{ trans('shared/common.active') }}</th>
                                <th width="60px">{{ trans('shared/common.action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($models as $model)
                            <tr>
                                <td>{{ $model->type_name }}</td>
                                <td>{{ $model->coa_code_d }}</td>
                                <td>{{ $model->coa_description_d }}</td>
                                <td>{{ $model->coa_code_c }}</td>
                                <td>{{ $model->coa_description_c }}</td>
                                <td class="text-center">
                                    <i class="fa {{ $model->active == 'Y' ? 'fa-check' : 'fa-remove' }}"></i>
                                </td>
                                <td class="text-center">
                                    @can('access', [$resource, 'update'])
                                    <a href="{{ URL($url . '/edit/' . $model->type_id) }}" data-toggle="tooltip" class="btn btn-xs btn-warning" data-original-title="{{ trans('shared/common.edit') }}">
                                        <i class="fa fa-pencil"></i>
                                    </a>
                                    @endcan
                                    @can('access', [$resource, 'delete'])
                                    <!-- <a data-id="{{ $model->type_id }}" data-label="{{ $model->type_name }}" data-toggle="tooltip" class="btn btn-danger btn-xs md-trigger delete-action" data-original-title="{{ trans('shared/common.delete') }}" data-modal="modal-delete">
                                        <i class="fa fa-remove"></i>
                                    </a> -->
                                    @endcan
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="data-table-toolbar">
                    {!! $models->render() !!}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('modal')
@parent
<div class="md-modal md-3d-flip-horizontal" id="modal-delete">
    <div class="md-content">
        <h3 style="padding-bottom: 0px;"><strong>{{ trans('shared/common.delete') }}</strong> {{ trans('payable/menu.master-ap-type') }}</h3>
        <div>
            <div class="row">
                <div class="col-sm-12">
                    <h4 id="delete-text">Are you sure want to delete ?</h4>
                    <form role="form" method="post" action="{{ URL($url . '/delete') }}" class="text-right">
                        {{ csrf_field() }}
                        <input type="hidden" id="delete-id" name="id" >
                        <a class="btn btn-danger md-close">{{ trans('shared/common.no') }}</a>
                        <button type="submit" class="btn btn-success">{{ trans('shared/common.yes') }}</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
@parent
<script type="text/javascript">
    $(document).on('ready', function(){
        $('#kota').select2();
        $('.delete-action').on('click', function() {
            $("#delete-id").val($(this).data('id'));
            $("#delete-text").html('{{ trans('shared/common.delete-confirmation', ['variable' => trans('payable/menu.master-ap-type')]) }} ' + $(this).data('label') + '?');
        });
    });
</script>
@endsection
