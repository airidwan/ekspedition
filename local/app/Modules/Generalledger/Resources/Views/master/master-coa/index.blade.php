@extends('layouts.master')

@section('title', trans('general-ledger/menu.master-coa'))

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="widget">
            <div class="widget-header">
                <h2><i class="fa fa-money"></i> {{ trans('general-ledger/menu.master-coa') }}</h2>
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
                            <div class="form-group {{ $errors->has('segmentName') ? 'has-error' : '' }}">
                                <label for="segmentName" class="col-sm-4 control-label">{{ trans('general-ledger/fields.segment-name') }}</label>
                                <div class="col-sm-8">
                                    <?php $segmentName = !empty($filters['segmentName']) ? $filters['segmentName'] : '' ;
                                    ?>
                                    <select class="form-control" id="segmentName" name="segmentName">
                                        <option value="">All</option>
                                        <option value="Company" {{ $segmentName == 'Company' ? 'selected' : '' }}>Company</option>
                                        <option value="Cost Center" {{ $segmentName == 'Cost Center' ? 'selected' : '' }}>Cost Center</option>
                                        <option value="Account" {{ $segmentName == 'Account' ? 'selected' : '' }}>Account</option>
                                        <option value="Sub Account" {{ $segmentName == 'Sub Account' ? 'selected' : '' }}>Sub Account</option>
                                        <option value="Future 1" {{ $segmentName == 'Future 1' ? 'selected' : '' }}>Future 1</option>
                                    </select>
                                    @if($errors->has('segmentName'))
                                    <span class="help-block">{{ $errors->first('segmentName') }}</span>
                                    @endif
                                </div>
                            </div>    
                            <div class="form-group">
                                <label for="coaCode" class="col-sm-4 control-label">{{ trans('general-ledger/fields.coa-code') }}</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="coaCode" name="coaCode" value="{{ !empty($filters['coaCode']) ? $filters['coaCode'] : '' }}">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="description" class="col-sm-4 control-label">{{ trans('general-ledger/fields.description') }}</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="description" name="description" value="{{ !empty($filters['description']) ? $filters['description'] : '' }}">
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6 portlets">
                            <div class="form-group {{ $errors->has('identifier') ? 'has-error' : '' }}">
                                <label for="identifier" class="col-sm-4 control-label">{{ trans('general-ledger/fields.identifier') }}</label>
                                <div class="col-sm-8">
                                    <?php $identifier = !empty($filters['identifier']) ? $filters['identifier'] : '' ;
                                    ?>
                                    <select class="form-control" id="identifier" name="identifier" class="disabled">
                                        <option value="">All</option>
                                        <option value="1" {{ $identifier == '1' ? 'selected' : '' }}>Asset</option>
                                        <option value="2" {{ $identifier == '2' ? 'selected' : '' }}>Liability</option>
                                        <option value="3" {{ $identifier == '3' ? 'selected' : '' }}>Ekuitas</option>
                                        <option value="4" {{ $identifier == '4' ? 'selected' : '' }}>Revenue</option>
                                        <option value="5" {{ $identifier == '5' ? 'selected' : '' }}>Ekspense</option>
                                    </select>
                                    @if($errors->has('identifier'))
                                    <span class="help-block">{{ $errors->first('identifier') }}</span>
                                    @endif
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
                                <a href="{{ URL($url.'/print-excel') }}" class="button btn btn-sm btn-success" target="_blank">
                                    <i class="fa fa-file-excel-o"></i> {{ trans('shared/common.print-excel') }}
                                </a>
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
                    <table class="table table-striped table-bordered" cellspacing="0" width="100%">
                        <thead>
                            <tr>
                                <th width="50px">{{ trans('shared/common.num') }}</th>
                                <th>{{ trans('general-ledger/fields.segment-name') }}</th>
                                <th>{{ trans('general-ledger/fields.coa-code') }}</th>
                                <th>{{ trans('shared/common.deskripsi') }}</th>
                                <th>{{ trans('general-ledger/fields.identifier') }}</th>
                                <th>{{ trans('shared/common.active') }}</th>
                                <th width="60px">{{ trans('shared/common.action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $no = ($models->currentPage() - 1) * $models->perPage() + 1;?>
                            @foreach($models as $model)
                            <tr>
                                <td class="text-center">{{ $no++ }}</td>
                                <td>{{ $model->segment_name }}</td>
                                <td>{{ $model->coa_code }}</td>
                                <td>{{ $model->description }}</td>
                                <?php 
                                    $identifier = '';
                                    if ($model->identifier == '1') {
                                        $identifier = 'Asset';
                                    } else if ($model->identifier == '2'){
                                        $identifier = 'Liability';
                                    } else if ($model->identifier == '3'){
                                        $identifier = 'Equitas';
                                    } else if ($model->identifier == '4'){
                                        $identifier = 'Revenue';
                                    } else if ($model->identifier == '5'){
                                        $identifier = 'Ekspense';
                                    }
                                ?>
                                <td>{{ $identifier }}</td>
                                <td class="text-center">
                                    @if($model->active == 'Y')
                                    <i class="fa fa-check"></i>
                                    @else
                                    <i class="fa fa-remove"></i>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($model->segment_name == 'Account' || $model->segment_name == 'Future 1' )
                                    @can('access', [$resource, 'update'])
                                    <a href="{{ URL($url . '/edit/' . $model->coa_id) }}" data-toggle="tooltip" class="btn btn-xs btn-warning" data-original-title="{{ trans('shared/common.edit') }}">
                                        <i class="fa fa-pencil"></i>
                                    </a>
                                    @endcan
                                    @endif
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

@section('script')
@parent
<script type="text/javascript">
    $(document).on('ready', function(){
        var segmentName = function () {
            if ($('#segmentName').val() == 'Account')  {
                $('#identifier').prop('disabled', false);
            }
            else {
                $('#identifier').prop('disabled', 'disabled');
            }
        };
        $(segmentName);
        $("#segmentName").change(segmentName);
    });
</script>
@endsection
