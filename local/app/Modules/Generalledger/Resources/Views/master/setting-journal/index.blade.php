@extends('layouts.master')

@section('title', trans('general-ledger/menu.setting-journal'))

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="widget">
            <div class="widget-header">
                <h2><i class="fa fa-truck"></i> {{ trans('general-ledger/menu.setting-journal') }}</h2>
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
                                <label for="settingName" class="col-sm-4 control-label">{{ trans('general-ledger/fields.setting-name') }}</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="settingName" name="settingName" value="{{ !empty($filters['settingName']) ? $filters['settingName'] : '' }}">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="segmentName" class="col-sm-4 control-label">{{ trans('general-ledger/fields.segment-name') }}</label>
                                <div class="col-sm-8">
                                    <select class="form-control" id="segmentName" name="segmentName">
                                        <option value="" >ALL</option>
                                        @foreach($optionSegmentName as $option)
                                            <option value="{{ $option }}" {{ !empty($filters['segmentName']) && $filters['segmentName'] == $option ? 'selected' : '' }}>
                                                {{ $option }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6 portlets">
                            <div class="form-group">
                                <label for="coa" class="col-sm-4 control-label">{{ trans('general-ledger/fields.coa') }}</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="coa" name="coa" value="{{ !empty($filters['coa']) ? $filters['coa'] : '' }}">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="description" class="col-sm-4 control-label">{{ trans('shared/common.description') }}</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="description" name="description" value="{{ !empty($filters['description']) ? $filters['description'] : '' }}">
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
                                <th width="50px">{{ trans('shared/common.num') }}</th>    
                                <th>{{ trans('general-ledger/fields.setting-name') }}</th>
                                <th>{{ trans('general-ledger/fields.segment-name') }}</th>
                                <th>{{ trans('general-ledger/fields.coa') }}</th>
                                <th>{{ trans('shared/common.description') }}</th>
                                <th width="100px">{{ trans('shared/common.action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $no = ($models->currentPage() - 1) * $models->perPage() + 1;?>
                            @foreach($models as $model)
                            <tr>
                                <td class="text-center">{{ $no++ }}</td>
                                <td>{{ $model->setting_name }}</td>
                                <td>{{ $model->segment_name }}</td>
                                <td>{{ $model->coa_code.' - '.$model->coa_description }}</td>
                                <td>{{ $model->description }}</td>
                                <td class="text-center">
                                    @can('access', [$resource, 'update'])
                                    <a href="{{ URL($url . '/edit/' . $model->setting_journal_id) }}" data-toggle="tooltip" class="btn btn-xs btn-warning" data-original-title="{{ trans('shared/common.edit') }}">
                                        <i class="fa fa-pencil"></i>
                                    </a>
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
