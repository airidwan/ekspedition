@extends('layouts.master')

@section('title', trans('payable/menu.debt-employee-invoice'))

 <?php use App\Modules\Payable\Model\Transaction\InvoiceHeader; ?>

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="widget">
            <div class="widget-header">
                <h2><i class="fa fa-usd"></i> {{ trans('payable/menu.debt-employee-invoice') }}</h2>
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
                                        <?php $typeId = count($errors) > 0 ? old('type') : $model->type_id ?>
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
                                        <span id="modalVendor" class="btn input-group-addon" data-toggle="{{ $model->status == InvoiceHeader::INCOMPLETE ? 'modal' : '' }}" data-target="#modal-vendor"><i class="fa fa-search"></i></span>
                                        <span id="modalDriver" class="btn input-group-addon" data-toggle="{{ $model->status == InvoiceHeader::INCOMPLETE ? 'modal' : '' }}" data-target="#modal-driver"><i class="fa fa-search"></i></span>

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
                                    <input type="text" class="form-control currency text-right" id="totalAmount" name="totalAmount" value="{{ count($errors) > 0 ? old('totalAmount') : $amount }}" {{ $model->status == InvoiceHeader::INCOMPLETE ? '' : 'disabled' }}>
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
                                    <input type="text" class="form-control currency text-right" id="fixAmount" name="fixAmount" value="{{ count($errors) > 0 ? old('fixAmount') : $fixAmount }}" readonly>
                                    @if($errors->has('poNumber'))
                                        <span class="help-block">{{ $errors->first('fixAmount') }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="form-group {{ $errors->has('description') ? 'has-error' : '' }}">
                                <label for="description" class="col-sm-4 control-label">{{ trans('shared/common.description') }} <span class="required">*</span></label>
                                <div class="col-sm-8">
                                    <textarea type="text" class="form-control" id="description" name="description" rows="3" {{ $model->status == InvoiceHeader::INCOMPLETE ? '' : 'disabled' }}>{{ count($errors) > 0 ? old('description') : $model->description }}</textarea>
                                    @if($errors->has('description'))
                                    <span class="help-block">{{ $errors->first('description') }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-4 control-label">{{ trans('payable/fields.is-invoice') }}</label>
                                <div class="col-sm-8">
                                    <label class="checkbox-inline icheckbox">
                                        <?php $isInvoice = count($errors) > 0 ? old('isInvoice') : $model->is_invoice; ?>
                                        <input type="checkbox" id="isInvoice" name="isInvoice" value="1" {{ $isInvoice ? 'checked' : '' }} {{ $model->status == InvoiceHeader::INCOMPLETE ? '' : 'disabled' }}>
                                    </label>
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
                                @if($model->status == InvoiceHeader::APPROVED || $model->status == InvoiceHeader::CLOSED)
                                <a href="{{ URL($url.'/print-pdf-detail/'.$model->header_id) }}" class="button btn btn-sm btn-success" target="_blank">
                                    <i class="fa fa-print"></i> {{ trans('shared/common.print') }}
                                </a>
                                @endif
                                @if(Gate::check('access', [$resource, 'insert']) && $model->status == InvoiceHeader::INCOMPLETE)
                                <button type="submit" class="btn btn-sm btn-primary">
                                    <i class="fa fa-save"></i> {{ trans('shared/common.save') }}
                                </button>
                                @endif
                                @if(Gate::check('access', [$resource, 'approveAdmin']) && $model->isIncomplete())
                                <button type="submit" name="btn-approve-admin" class="btn btn-sm btn-info">
                                    <i class="fa fa-save"></i> {{ trans('purchasing/fields.approve-admin') }}
                                </button>
                                @endif
                                @if(Gate::check('access', [$resource, 'approveKacab']) && $model->isIncomplete())
                                <button type="submit" name="btn-approve-kacab" class="btn btn-sm btn-info">
                                    <i class="fa fa-save"></i> {{ trans('purchasing/fields.approve-kacab') }}
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
<div id="modal-vendor" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title text-center">Employee List</h4>
            </div>
            <div class="modal-body">
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
            <div class="modal-footer">
                <button type="button" class="btn btn-sm btn-warning" data-dismiss="modal">{{ trans('shared/common.close') }}</button>
            </div>
        </div>
    </div>
</div>
<div id="modal-driver" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title text-center">Driver/Assistant List</h4>
            </div>
            <div class="modal-body">
                <table id="datatables-driver" class="table table-hover table-striped table-bordered" cellspacing="0" width="100%">
                    <thead>
                        <tr>
                            <th>{{ trans('operational/fields.driver-code') }}</th>
                            <th>{{ trans('operational/fields.no-ktp') }}</th>
                            <th>{{ trans('operational/fields.driver-name') }}</th>
                            <th>{{ trans('operational/fields.nickname') }}</th>
                            <th>{{ trans('shared/common.address') }}</th>
                            <th>{{ trans('shared/common.position') }}</th>
                            <th>{{ trans('shared/common.type') }}</th>
                            <th>{{ trans('shared/common.description') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                       @foreach ($optionDriver as $driver)
                        <tr style="cursor: pointer;" data-driver="{{ json_encode($driver) }}">
                            <td>{{ $driver->driver_code }}</td>
                            <td>{{ $driver->identity_number }}</td>
                            <td>{{ $driver->driver_name }}</td>
                            <td>{{ $driver->driver_nickname }}</td>
                            <td>{{ $driver->address }}</td>
                            <td>{{ $driver->position }}</td>
                            <td>{{ $driver->type }}</td>
                            <td>{{ $driver->description }}</td>
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

            $('#modal-vendor').modal("hide");
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

            $('#modal-driver').modal("hide");
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
