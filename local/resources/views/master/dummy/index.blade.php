@extends('layouts.master')

@section('title', trans('sys-admin/menu.dummy'))

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="widget">
            <div class="widget-header">
                <h2><i class="fa fa-laptop"></i> {{ trans('sys-admin/menu.dummy') }}</h2>
                <div class="additional-btn">
                    <a href="#" class="widget-maximize"><i class="icon-resize-full-1"></i></a>
                    <a href="#" class="widget-toggle"><i class="icon-down-open-2"></i></a>
                    <a href="#" class="widget-help"><i class="icon-help-2"></i></a>
                </div>
            </div>
            <div class="widget-content padding">
                <form  role="form" id="registerForm" class="form-horizontal" method="post" action="">
                    {{ csrf_field() }}
                    <div id="horizontal-form">
                        <div class="col-sm-6 portlets">
                            <div class="form-group">
                                <label for="username" class="col-sm-4 control-label">{{ trans('sys-admin/fields.kolom-string') }}</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="kolomString" name="kolomString" value="{{ !empty($filters['kolomString']) ? $filters['kolomString'] : '' }}">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="username" class="col-sm-4 control-label">{{ trans('sys-admin/fields.kolom-select') }}</label>
                                <div class="col-sm-8">
                                    <select class="form-control" name="kolomSelect" id="kolomSelect">
                                        <option value="" {{ !empty($filters['kolomSelect']) && $filters['kolomSelect'] == 'All' ? 'selected' : '' }}>All</option>
                                        <option value="Kolom Select 1" {{ !empty($filters['kolomSelect']) && $filters['kolomSelect'] == 'Kolom Select 1' ? 'selected' : '' }}>Kolom Select 1</option>
                                        <option value="Kolom Select 2" {{ !empty($filters['kolomSelect']) && $filters['kolomSelect'] == 'Kolom Select 2' ?  'selected' : '' }}>Kolom Select 2</option>
                                        <option value="Kolom Select 3" {{ !empty($filters['kolomSelect']) && $filters['kolomSelect'] == 'Kolom Select 3' ? 'selected' : '' }}>Kolom Select 3</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6 portlets">
                            <div class="form-group">
                                <label for="username" class="col-sm-2 control-label">{{ trans('shared/common.periode') }}</label>
                                <div class="col-sm-5">
                                    <div class="input-group">
                                          <input data-mask="99-99-9999" placeholder="dd-mm-yyyy" type="text" id="tanggalAwal" name="tanggalAwal" class="form-control datepicker-input" value="{{ !empty($filters['tanggalAwal']) ? $filters['tanggalAwal'] : '' }}">
                                        <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                    </div>
                                </div>
                                <div class="col-sm-5">
                                    <div class="input-group">
                                        <input data-mask="99-99-9999" placeholder="dd-mm-yyyy" type="text" id="tanggalAkhir" name="tanggalAkhir" class="form-control datepicker-input" value="{{ !empty($filters['tanggalAkhir']) ? $filters['tanggalAkhir'] : '' }}">
                                        <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="clearfix"></div>
                    </div>
                    <div class="data-table-toolbar">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="toolbar-btn-action">
                                    <button type="submit" class="btn btn-sm btn-info"><i class="fa fa-search"></i> {{ trans('shared/common.filter') }}</button>
                                    <a href="{{ URL('sys-admin/master/dummy/add') }}" class="btn btn-sm btn-primary"><i class="fa fa-plus-circle"></i> {{ trans('shared/common.add-new') }}</a>
                                    <a href="{{ URL('sys-admin/master/dummy/print') }}" class="btn btn-sm btn-warning"><i class="fa fa-print"></i> {{ trans('shared/common.print') }}</a>
                                    <a href="{{ URL('sys-admin/master/dummy/print-excell') }}" class="btn btn-sm btn-success"><i class="fa fa-bars"></i> {{ trans('shared/common.print-excell') }}</a>
                                    <a href="{{ URL('sys-admin/master/dummy/print-pdf') }}" class="btn btn-sm btn-danger"><i class="fa fa-book"></i> {{ trans('shared/common.print-pdf') }}</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th width="50px">No</th>
                                <th>{{ trans('sys-admin/fields.kolom-string') }}</th>
                                <th>{{ trans('sys-admin/fields.kolom-select') }}</th>
                                <th>{{ trans('sys-admin/fields.kolom-date') }}</th>
                                <th width="100px">{{ trans('shared/common.action') }}</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php $no = ($headers->currentPage() - 1) * $headers->perPage() + 1; ?>
                            @foreach ($headers as $header)
                            <?php $kolomDate = !empty($header->kolom_date) ? new \DateTime($header->kolom_date) : null; ?>
                            <tr>
                                <td style="text-align: center;">{{ $no++ }}</td>
                                <td>{{ $header->kolom_string }}</td>
                                <td>{{ $header->kolom_select }}</td>
                                <td>{{ !empty($kolomDate) ? $kolomDate->format('d-M-Y') : '' }}</td>
                                <td style="text-align: center;">
                                    <a href="{{ URL('sys-admin/master/dummy/edit/' . $header->id) }}" data-toggle="tooltip" class="btn btn-xs btn-warning" data-original-title="{{ trans('shared/common.edit') }}">
                                        <i class="fa fa-pencil"></i>
                                    </a>
                                    <a data-id="{{ $header->id }}" data-label="{{ $header->kolom_string }}" data-toggle="tooltip" class="btn btn-danger btn-xs md-trigger delete-action" data-original-title="{{ trans('shared/common.delete') }}" data-modal="modal-delete">
                                        <i class="fa fa-remove"></i>
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="data-table-toolbar">
                    {!! $headers->render() !!}
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
        <h3 style="padding-bottom: 0px;"><strong>{{ trans('shared/common.delete') }}</strong> {{ trans('sys-admin/menu.dummy') }}</h3>
        <div>
            <div class="row">
                <div class="col-sm-12">
                    <h4 id="delete-text">Are you sure want to delete ?</h4>
                    <form role="form" method="post" action="{{ URL('sys-admin/master/dummy/delete') }}" class="text-right">
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
        $('.delete-action').on('click', function() {
            $("#delete-id").val($(this).data('id'));
            $("#delete-text").html('{{ trans('shared/common.delete-confirmation', ['variable' => trans('sys-admin/menu.dummy')]) }} ' + $(this).data('label') + '?');
        });
    });
</script>
@endsection
