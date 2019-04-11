@extends('layouts.master')

@section('title', trans('payable/menu.approve-debt-employee-invoice'))

 <?php use App\Modules\Payable\Model\Transaction\InvoiceHeader; ?>

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="widget">
            <div class="widget-header">
                <h2><i class="fa fa-usd"></i> {{ trans('payable/menu.approve-debt-employee-invoice') }}</h2>
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
                        <input type="hidden" id="id" name="id" value="{{ count($errors) > 0 ? old('id') : $model->header_id }}">
                        <ul id="demo1" class="nav nav-tabs">
                            <li class="active">
                                <a href="#tabApprove" data-toggle="tab">{{ trans('shared/common.approve') }} <span class="label label-success"></span></a>
                            </li>
                            <li class="">
                                <a href="#tabContent" data-toggle="tab">{{ trans('shared/common.content') }} <span class="label label-success"></span></a>
                            </li>
                        </ul>
                        <div class="tab-content">
                            <div class="tab-pane fade active in" id="tabApprove">
                                <div class="col-sm-6 portlets">
                                    <div class="form-group {{ $errors->has('note') ? 'has-error' : '' }}">
                                        <label for="poNumber" class="col-sm-4 control-label">Note about Approve or Reject <span class="required">*</span></label>
                                        <div class="col-sm-8">
                                            <textarea type="text" class="form-control" id="note" name="note" rows="4">{{ count($errors) > 0 ? old('note') : '' }}</textarea>
                                            @if($errors->has('note'))
                                            <span class="help-block">{{ $errors->first('note') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="tabContent">
                                <div class="col-sm-6 portlets">
                                    <div class="form-group">
                                        <label for="invoiceNumber" class="col-sm-4 control-label">{{ trans('payable/fields.invoice-number') }}</label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="invoiceNumber" name="invoiceNumber"  value="{{ !empty($model->invoice_number) ? $model->invoice_number : '' }}" readonly>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="status" class="col-sm-4 control-label">{{ trans('shared/common.status') }}</label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="status" name="status"  value="{{ !empty($model->status) ? $model->status : '' }}" readonly>
                                        </div>
                                    </div>
                                    <div class="form-group {{ $errors->has('type') ? 'has-error' : '' }}">
                                        <label for="type" class="col-sm-4 control-label">{{ trans('shared/common.type') }} <span class="required">*</span></label>
                                        <div class="col-sm-8">
                                            <select class="form-control" id="type" name="type" {{ $model->status == InvoiceHeader::INCOMPLETE ? '' : 'disabled' }}>
                                                <option value="">{{ trans('shared/common.please-select') }} {{ trans('shared/common.type') }}</option>
                                                <?php $typeId = count($errors) > 0 ? old('type') : $model->type_id; ?>

                                                @foreach($optionType as $type)
                                                <option value="{{ $type->type_id }}" {{ $type->type_id == $typeId ? 'selected' : '' }}>{{ $type->type_name }}</option>
                                                @endforeach
                                            </select>
                                            @if($errors->has('type'))
                                            <span class="help-block">{{ $errors->first('type') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    <?php
                                    if ($model->type_id == InvoiceHeader::KAS_BON_EMPLOYEE) {
                                        $vendor = $model->vendor;
                                        $vendorCode = !empty($vendor) ? $vendor->vendor_code : '';
                                        $vendorName = !empty($vendor) ? $vendor->vendor_name : '';
                                        $address = !empty($vendor) ? $vendor->address : '';
                                    }else {
                                        $driver = $model->driver;
                                        $vendorCode = !empty($driver) ? $driver->driver_code : '';
                                        $vendorName = !empty($driver) ? $driver->driver_name : '';
                                        $address = !empty($driver) ? $driver->address : '';
                                    }
                                    ?>
                                    <div class="form-group {{ $errors->has('vendorCode') ? 'has-error' : '' }}">
                                        <label for="vendorCode" class="col-sm-4 control-label">{{ trans('payable/fields.trading') }} <span class="required">*</span></label>
                                        <div class="col-sm-8">
                                            <div class="input-group">
                                                <input type="text" class="form-control" id="vendorCode" name="vendorCode" value="{{ count($errors) > 0 ? old('vendorCode') : $vendorCode }}" readonly>
                                                <input type="hidden" id="vendorId" name="vendorId" value="{{ count($errors) > 0 ? old('vendorId') : $model->vendor_id }}">
                                                 <span id="modalVendor" class="btn input-group-addon {{ $model->status == InvoiceHeader::INCOMPLETE ? 'md-trigger' : '' }}" data-modal="modal-vendor"><i class="fa fa-search"></i></span>
                                                 <span id="modalDriver" class="btn input-group-addon {{ $model->status == InvoiceHeader::INCOMPLETE ? 'md-trigger' : '' }}" data-modal="modal-driver"><i class="fa fa-search"></i></span>
                                            </div>
                                                @if($errors->has('vendorCode'))
                                                <span class="help-block">{{ $errors->first('vendorCode') }}</span>
                                                @endif
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="vendorName" class="col-sm-4 control-label">{{ trans('shared/common.name') }}</label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control " id="vendorName" name="vendorName" value="{{ count($errors) > 0 ? old('vendorName') : $vendorName }}" readonly>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="address" class="col-sm-4 control-label">{{ trans('shared/common.address') }}</label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="address" name="address" value="{{ count($errors) > 0 ? old('address') : $address }}" readonly>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-5 portlets">
                                    <?php
                                        $line   = $model->lineOne;
                                        $amount = !empty($line) ? $line->amount : '';
                                    ?>
                                    <div class="form-group {{ $errors->has('totalAmount') ? 'has-error' : '' }}">
                                        <label for="totalAmount" class="col-sm-4 control-label">{{ trans('payable/fields.amount') }}  <span class="required">*</span></label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control currency text-right" id="totalAmount" name="totalAmount" value="{{ count($errors) > 0 ? str_replace(',', '', old('totalAmount')) : $amount }}" {{ $model->status == InvoiceHeader::INCOMPLETE ? '' : 'readonly' }}>
                                            @if($errors->has('poNumber'))
                                                <span class="help-block">{{ $errors->first('totalAmount') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    <?php
                                        $taxLine = !empty($line) ? $line->tax : '';
                                    ?>
                                    <div class="form-group {{ $errors->has('tax') ? 'has-error' : '' }}">
                                        <label for="tax" class="col-sm-4 control-label">{{ trans('payable/fields.tax') }} </label>
                                        <div class="col-sm-8">
                                            <select class="form-control" id="tax" name="tax" {{ $model->status == InvoiceHeader::INCOMPLETE ? '' : 'disabled' }}>
                                                <option value="">{{ trans('shared/common.please-select') }} {{ trans('payable/fields.tax') }}</option>
                                                <?php $tax = count($errors) > 0 ? old('tax') : $taxLine ;?>
                                                @foreach($optionTax as $row)
                                                <option value="{{ $row }}" {{ $row== $tax ? 'selected' : '' }}>{{ $row }}</option>
                                                @endforeach
                                            </select>
                                            @if($errors->has('tax'))
                                            <span class="help-block">{{ $errors->first('tax') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    <?php
                                        $fixAmount = !empty($line) ? $line->amount + ($line->tax / 100 * $line->amount) : '';
                                    ?>
                                    <div class="form-group {{ $errors->has('fixAmount') ? 'has-error' : '' }}">
                                        <label for="fixAmount" class="col-sm-4 control-label">{{ trans('payable/fields.total-invoice') }}  <span class="required">*</span></label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control currency text-right" id="fixAmount" name="fixAmount" value="{{ count($errors) > 0 ? str_replace(',', '', old('fixAmount')) : $fixAmount }}" readonly>
                                            @if($errors->has('poNumber'))
                                                <span class="help-block">{{ $errors->first('fixAmount') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="form-group {{ $errors->has('description') ? 'has-error' : '' }}">
                                        <label for="description" class="col-sm-4 control-label">{{ trans('shared/common.description') }}</label>
                                        <div class="col-sm-8">
                                            <textarea type="text" class="form-control" id="description" name="description" rows="3" {{ $model->status == InvoiceHeader::INCOMPLETE ? '' : 'readonly' }}>{{ count($errors) > 0 ? old('description') : $model->description }}</textarea>
                                            @if($errors->has('description'))
                                            <span class="help-block">{{ $errors->first('description') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="clearfix"></div>
                        <hr>
                        <div class="col-sm-12 data-table-toolbar text-right">
                            <div class="form-group">
                                <a href="{{ URL($url) }}" class="btn btn-sm btn-warning">
                                    <i class="fa fa-reply"></i> {{ trans('shared/common.cancel') }}
                                </a>
                                @if(Gate::check('access', [$resource, 'reject']) && $model->isInprocess())
                                <button type="submit" name="btn-reject" class="btn btn-sm btn-danger">
                                    <i class="fa fa-remove"></i> {{ trans('shared/common.reject') }}
                                </button>
                                @endif
                                @if(Gate::check('access', [$resource, 'approve']) && $model->isInprocess())
                                <button type="submit" name="btn-approve" class="btn btn-sm btn-info">
                                    <i class="fa fa-save"></i> {{ trans('shared/common.approve') }}
                                </button>
                                @endif
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('modal')
@parent
<div class="md-modal-lg md-fade-in-scale-up" id="modal-vendor">
    <div class="md-content">
        <div class="md-close-btn"><a class="md-close"><i class="fa fa-times"></i></a></div>
        <h3><strong>List of Employee non Driver/Assistant</strong></h3>
        <div>
            <div class="row">
                <div class="col-sm-12">
                    <table id="datatables-vendor" class="table table-hover table-striped table-bordered" cellspacing="0" width="100%">
                        <thead>
                            <tr>
                                <th>{{ trans('payable/fields.vendor-code') }}</th>
                                <th>{{ trans('payable/fields.vendor-name') }}</th>
                                <th>{{ trans('shared/common.address') }}</th>
                                <th>{{ trans('shared/common.description') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                           @foreach ($optionVendor as $vendor)
                        <tr style="cursor: pointer;" data-vendor="{{ json_encode($vendor) }}">
                            <td>{{ $vendor->vendor_code }}</td>
                            <td>{{ $vendor->vendor_name }}</td>
                            <td>{{ $vendor->address }}</td>
                            <td>{{ $vendor->description }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="md-modal-lg md-fade-in-scale-up" id="modal-driver">
    <div class="md-content">
        <div class="md-close-btn"><a class="md-close"><i class="fa fa-times"></i></a></div>
        <h3><strong>List of Driver/Assistant</strong></h3>
        <div>
            <div class="row">
                <div class="col-sm-12">
                    <table id="datatables-driver" class="table table-hover table-striped table-bordered" cellspacing="0" width="100%">
                        <thead>
                            <tr>
                                <th>{{ trans('operational/fields.driver-code') }}</th>
                                <th>{{ trans('operational/fields.no-ktp') }}</th>
                                <th>{{ trans('operational/fields.driver-name') }}</th>
                                <th>{{ trans('shared/common.address') }}</th>
                                <th>{{ trans('shared/common.description') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                           @foreach ($optionDriver as $driver)
                        <tr style="cursor: pointer;" data-driver="{{ json_encode($driver) }}">
                            <td>{{ $driver->driver_code }}</td>
                            <td>{{ $driver->identity_number }}</td>
                            <td>{{ $driver->driver_name }}</td>
                            <td>{{ $driver->address }}</td>
                            <td>{{ $driver->description }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    </table>
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
        enableModal();
        $("#type").on('change', function(){
            clearForm();
            enableModal();
        });

        $("#totalAmount").on('keyup', calculateFixAmount);
         $("#tax").on('change', calculateFixAmount);

        $("#datatables-vendor").dataTable({
            "pagelength" : 10,
            "lengthChange": false
        });

        $('#datatables-vendor tbody').on('click', 'tr', function () {
            var data = $(this).data('vendor');

            $('#vendorCode').val(data.vendor_code);
            $('#vendorId').val(data.vendor_id);
            $('#vendorName').val(data.vendor_name);
            $('#address').val(data.address);

            $('#modal-vendor').removeClass("md-show");
        });

        $("#datatables-driver").dataTable({
            "pagelength" : 10,
            "lengthChange": false
        });

        $('#datatables-driver tbody').on('click', 'tr', function () {
            var data = $(this).data('driver');

            $('#vendorCode').val(data.driver_code);
            $('#vendorId').val(data.driver_id);
            $('#vendorName').val(data.driver_name);
            $('#address').val(data.address);

            $('#modal-driver').removeClass("md-show");
        });
    });

    var clearForm = function(){
        $('#vendorId').val('');
        $('#vendorCode').val('');
        $('#vendorName').val('');
        $('#address').val('');
    };

    var enableModal = function(){
        $('#modalVendor').addClass('disabled');
        $('#modalDriver').addClass('disabled');
        $('#modalVendor').removeClass('hidden');
        $('#modalDriver').addClass('hidden');

        if ($('#type').val() == {{ InvoiceHeader::KAS_BON_EMPLOYEE }}) { // Kas Bon Employee
            $('#modalVendor').removeClass('disabled');
        }
        else if($('#type').val() == {{ InvoiceHeader::KAS_BON_DRIVER }}) { // Kas Bon Driver
            $('#modalVendor').addClass('hidden');
            $('#modalDriver').removeClass('disabled');
            $('#modalDriver').removeClass('hidden');
        }
    };

    var calculateFixAmount = function(){
        var amount    = currencyToInt($('#totalAmount').val());
        var tax       = currencyToInt($('#tax').val());
        var fixAmount = amount + (tax / 100 * amount);
        
        $("#fixAmount").val(fixAmount);
        $('#fixAmount').autoNumeric('update', {mDec: 0});
    };
</script>
@endsection
