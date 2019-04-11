@extends('layouts.master')

@section('title', trans('accountreceivables/menu.receivables-type'))

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="widget">
            <div class="widget-header">
                <h2><i class="fa fa-credit-card"></i> {{ trans('accountreceivables/menu.receivables-type') }}</h2>
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
                                <label for="kode" class="col-sm-4 control-label">{{ trans('accountreceivables/fields.nama-piutang') }}</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="namaPiutang" name="namaPiutang" value="{{ !empty($filters['namaPiutang']) ? $filters['namaPiutang'] : '' }}">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="akun" class="col-sm-4 control-label">{{ trans('accountreceivables/fields.akun') }}</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="akun" name="akun" value="{{ !empty($filters['akun']) ? $filters['akun'] : '' }}">
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6 portlets">
                        </div>
                        <div class="col-sm-12 data-table-toolbar text-right">
                            <div class="form-group">
                                <button type="submit" class="btn btn-sm btn-info"><i class="fa fa-search"></i> {{ trans('shared/common.filter') }}</button>
                                @can('access', [$resource, 'insert'])
                                <a href="{{ URL('accountreceivables/master/master-receivables-type/add') }}" class="btn btn-sm btn-primary">
                                    <i class="fa fa-plus-circle"></i> {{ trans('shared/common.add-new') }}
                                </a>
                                @endcan
                            </div>
                        </div>
                    </form>
                </div>
                <div class="clearfix"></div>
                <hr>
                <div class="table-responsive">
                    <form class='form-horizontal' role='form' id="table-line">
                        <table class="table table-striped table-bordered" cellspacing="0" width="100%">
                            <thead>
                                <tr>
                                    <th>{{ trans('accountreceivables/fields.nama-piutang') }}</th>
                                    <th>{{ trans('accountreceivables/fields.akun') }}</th>
                                    <th>{{ trans('shared/common.active') }}</th>
                                    <th>{{ trans('shared/common.action') }}</th>
                                </tr>
                            </thead>

                            <tbody>
                                <tr>
                                    <td>Piutang Faktur</td>
                                    <td>Piutang usaha</td>
                                    <td class="text-center">
                                        <i class="fa fa-check"></i>
                                    </td>
                                    <td class="text-center">
                                    @can('access', [$resource, 'update'])
                                        <a href="{{ URL($url . '/edit/' ) }}" data-toggle="tooltip" class="btn btn-xs btn-warning" data-original-title="{{ trans('shared/common.edit') }}">
                                        <i class="fa fa-pencil"></i>
                                        </a>
                                    @endcan
                                    @can('access', [$resource, 'insert'])
                                    <a data-id="{{ '' }}" data-label="{{ ''  }}" data-toggle="tooltip" class="btn btn-danger btn-xs md-trigger delete-action" data-original-title="{{ trans('shared/common.delete') }}" data-modal="modal-delete">
                                        <i class="fa fa-remove"></i>
                                    </a>
                                    @endcan
                                    </td>
                                </tr>
                                <tr>
                                    <td>Piutang lainnya</td>
                                    <td>Piutang karyawan</td>
                                    <td class="text-center">
                                        <i class="fa fa-check"></i>
                                    </td>
                                    <td class="text-center">
                                    @can('access', [$resource, 'update'])
                                        <a href="{{ URL($url . '/edit/' ) }}" data-toggle="tooltip" class="btn btn-xs btn-warning" data-original-title="{{ trans('shared/common.edit') }}">
                                        <i class="fa fa-pencil"></i>
                                        </a>
                                    @endcan
                                    @can('access', [$resource, 'insert'])
                                    <a data-id="{{ '' }}" data-label="{{ ''  }}" data-toggle="tooltip" class="btn btn-danger btn-xs md-trigger delete-action" data-original-title="{{ trans('shared/common.delete') }}" data-modal="modal-delete">
                                        <i class="fa fa-remove"></i>
                                    </a>
                                    @endcan
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
