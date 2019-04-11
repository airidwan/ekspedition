@extends('layouts.master')

@section('title', trans('shared/menu.notification'))

@section('header')
@parent
<style type="text/css">
    tr.unread td { font-weight: bold; background-color: #eee; }
    #notifications tbody tr { cursor: pointer; }
</style>
@endsection

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="widget">
            <div class="widget-header">
                <h2><i class="fa fa-envelope"></i> {{ trans('shared/menu.notification') }}</h2>
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
                                <label for="category" class="col-sm-4 control-label">{{ trans('shared/common.category') }}</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="category" name="category" value="{{ !empty($filters['category']) ? $filters['category'] : '' }}">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="message" class="col-sm-4 control-label">{{ trans('shared/common.message') }}</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="message" name="message" value="{{ !empty($filters['message']) ? $filters['message'] : '' }}">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="status" class="col-sm-4 control-label">{{ trans('shared/common.status') }}</label>
                                <div class="col-sm-8">
                                    <select name="status" class="form-control">
                                        <option value="">ALL</option>
                                        <option value="UN READ" {{ !empty($filters['status']) && $filters['status'] == 'UN READ' ? 'selected' : '' }}>UN READ</option>
                                        <option value="READ" {{ !empty($filters['status']) && $filters['status'] == 'READ' ? 'selected' : '' }}>READ</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6 portlets">
                            <div class="form-group">
                                <label for="role" class="col-sm-4 control-label">{{ trans('shared/common.role') }}</label>
                                <div class="col-sm-8">
                                    <select name="role" class="form-control">
                                        <option value="">ALL</option>
                                        @foreach($optionRole as $role)
                                        <option value="{{ $role->id }}" {{ !empty($filters['role']) && $filters['role'] == $role->id ? 'selected' : '' }}>{{ $role->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="branch" class="col-sm-4 control-label">{{ trans('shared/common.branch') }}</label>
                                <div class="col-sm-8">
                                    <select name="branch" class="form-control">
                                        <option value="">ALL</option>
                                        @foreach($optionBranch as $branch)
                                        <option value="{{ $branch->branch_id }}" {{ !empty($filters['branch']) && $filters['branch'] == $branch->branch_id ? 'selected' : '' }}>{{ $branch->branch_name }}</option>
                                        @endforeach
                                    </select>
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
                    <table id="notifications" class="table table-bordered" cellspacing="0" width="100%">
                        <thead>
                            <tr>
                                <th width="50px">{{ trans('shared/common.num') }}</th>
                                <th>{{ trans('shared/common.category') }}</th>
                                <th>{{ trans('shared/common.message') }}</th>
                                <th>{{ trans('shared/common.date') }}</th>
                                <th>{{ trans('shared/common.role') }}</th>
                                <th>{{ trans('shared/common.branch') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $no = ($models->currentPage() - 1) * $models->perPage() + 1; ?>
                            @foreach($models as $model)
                            <?php
                            $notification = App\Notification::find($model->notification_id);
                            $role = $notification->role()->first();
                            $branch = $notification->branch()->first();
                            $date = \App\Service\TimezoneDateConverter::getClientDateTime($model->created_at);
                            ?>
                            <tr class="{{ empty($notification->read_at) ? 'unread' : '' }}" data-id="{{ $notification->notification_id }}">
                                <td class="text-center">{{ $no++ }}</td>
                                <td>{{ $notification->category }}</td>
                                <td>{{ $notification->message }}</td>
                                <td class="text-center">{{ $date->format('d-m-Y H:i:s') }}</td>
                                <td>{{ $role !== null ? $role->name : '' }}</td>
                                <td>{{ $branch !== null ? $branch->branch_name : '' }}</td>
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
$(document).on('ready', function() {
    $('#notifications tbody tr').on('click', function() {
        var id = $(this).data('id');
        window.location.href = '{{ URL('read-notification') }}/' + id;
    });
});
</script>
@endsection