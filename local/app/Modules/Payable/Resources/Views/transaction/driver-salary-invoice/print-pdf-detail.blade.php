@extends('layouts.print')

@section('header')
@parent
<style type="text/css">
.number, .amount{ font-weight: bold; font-size: 12px; }
</style>
@endsection

<?php 
    use App\Service\Terbilang;
    use App\Modules\Payable\Model\Transaction\InvoiceLine;
    use App\Modules\Operational\Model\Master\MasterCity;
    $driver = $model->driver;
    $lines  = $model->lines;
    $date = !empty($model->created_date) ? new \DateTime($model->created_date) : new \DateTime();
?>

@section('content')
<table id="filtes" width="100%" cellpadding="0" cellspacing="0">
    <tr>
        <td width="50%" cellpadding="0" cellspacing="0">
            <table>
                <tr>
                    <td width="38%">{{ trans('operational/fields.driver') }}</td>
                    <td width="2%">:</td>
                    <td width="60%">{{ !empty($driver) ? $driver->driver_name : '' }}</td>
                </tr>
                <tr>
                    <td width="38%">{{ trans('shared/common.address') }}</td>
                    <td width="2%">:</td>
                    <td width="60%">{{ !empty($driver) ? $driver->address : '' }}</td>
                </tr>
                <tr>
                    <td width="38%">{{ trans('shared/common.phone') }}</td>
                    <td width="2%">:</td>
                    <td width="60%">{{ !empty($driver) ? $driver->phone_number : '' }}</td>
                </tr>
            </table>
            <br/>
        </td>
        <td width="50%" cellpadding="0" cellspacing="0">
            <table>
                <tr>
                    <td width="38%">{{ trans('payable/fields.invoice-number') }}</td>
                    <td width="2%">:</td>
                    <td width="60%">{{ $model->invoice_number }}</td>
                </tr>
                <tr>
                    <td width="38%">{{ trans('shared/common.description') }}</td>
                    <td width="2%">:</td>
                    <td width="60%">{{ $model->description }}</td>
                </tr>
                <tr>
                    <td width="38%">{{ trans('shared/common.date') }}</td>
                    <td width="2%">:</td>
                    <td width="60%">{{ $date->format('d-m-Y') }}</td>
                </tr>
            </table>
            <br/>
        </td>
    </tr>
</table>
<table class="table" cellspacing="0" cellpadding="2" border="0">
    <thead>
        <tr>
            <th width="5%" style="border: 1px solid black">{{ trans('shared/common.num') }}</th>
            <th width="13%" style="border: 1px solid black">{{ trans('operational/fields.manifest-number') }}</th>
            <th width="13%" style="border: 1px solid black">{{ trans('operational/fields.pickup-number') }}</th>
            <th width="13%" style="border: 1px solid black">{{ trans('operational/fields.do-number') }}</th>
            <th width="13%" style="border: 1px solid black">{{ trans('operational/fields.route-or-area') }}</th>
            <th width="15%" style="border: 1px solid black">{{ trans('shared/common.description') }}</th>
            <th width="10%" style="border: 1px solid black">{{ trans('payable/fields.amount') }}</th>
            <th width="8%" style="border: 1px solid black">{{ trans('payable/fields.tax') }}</th>
            <th width="10%" style="border: 1px solid black">{{ trans('payable/fields.total-amount') }}</th>
        </tr>
    </thead>
    <tbody>
         <?php $no = 1; ?>
         @foreach($lines as $line)
         <?php  
            $modelLine = InvoiceLine::find($line->line_id);
            if($line->type == InvoiceLine::DELIVERY_ORDER_SALARY){
                $routeArea = !empty($line->deliveryOrder->deliveryArea) ? $line->deliveryOrder->deliveryArea->delivery_area_name : '';
            }elseif($line->type == InvoiceLine::PICKUP_SALARY){
                $routeArea = !empty($line->pickup->deliveryArea) ? $line->pickup->deliveryArea->delivery_area_name : '';
            }else{
                $routeArea = !empty($line->manifest->route) ? $line->manifest->route->route_code : '';
            }
        ?>
        <tr>
            <td width="5%" align="center" style="border: 1px solid black">{{ $no++ }}</td>
            <td width="13%" style="border: 1px solid black">{{ !empty($modelLine->manifest) ? $modelLine->manifest->manifest_number : '' }}</td>
            <td width="13%" style="border: 1px solid black">{{ !empty($modelLine->pickup) ? $modelLine->pickup->pickup_form_number : '' }}</td>
            <td width="13%" style="border: 1px solid black">{{ !empty($modelLine->deliveryOrder) ? $modelLine->deliveryOrder->delivery_order_number : '' }}</td>
            <td width="13%" style="border: 1px solid black">{{ $routeArea }}</td>
            <td width="15%" style="border: 1px solid black">{{ $line->description }}</td>
            <td width="10%" align="right" style="border: 1px solid black">{{ number_format($line->amount) }}</td>
            <td width="8%" align="right" style="border: 1px solid black">{{ number_format($modelLine->totalTax()) }}</td>
            <td width="10%" align="right" style="border: 1px solid black">{{ number_format($modelLine->totalAmount()) }}</td>
        </tr>
        @endforeach
        <tr>
            <td width="72%" align="right"> </td>
            <td width="18%" align="right" style="border: 1px solid black">{{ trans('shared/common.total-invoice') }} </td>
            <td width="10%" align="right" style="border: 1px solid black">{{ number_format($model->getTotalInvoice()) }}</td>
        </tr>
        <tr>
            <td width="72%" align="right"> </td>
            <td width="18%" align="right" style="border: 1px solid black">{{ trans('shared/common.total-payment') }} </td>
            <td width="10%" align="right" style="border: 1px solid black">{{ number_format($model->getTotalPayment()) }}</td>
        </tr>
        <tr>
            <td width="72%" align="right"> </td>
            <td width="18%" align="right" style="border: 1px solid black"><strong>{{ trans('shared/common.total-remain') }} </strong></td>
            <td width="10%" align="right" style="border: 1px solid black"><strong>{{ number_format($model->getTotalRemain()) }}</strong></td>
        </tr>
    </tbody>
</table>

<br/>
<table class="amount" id="amount" cellspacing="0" cellpadding="2">
    <tbody>
        <tr>
            <!-- <td width="15%"></td> -->
            <td width="100%"><u>Terbilang : {{ trim(ucwords(Terbilang::rupiah($model->getTotalRemain()))) . ' Rupiah' }}</u></td>
        </tr>
    </tbody>
</table>

<?php
$city = MasterCity::find(\Session::get('currentBranch')->city_id);
$createdDate  = new \DateTime($model->created_date);
$approvedDate = new \DateTime($model->approved_date);
$userCreated  = App\User::find($model->created_by);
$userApproved = App\User::find($model->approved_by);
?>
<br>
<br>
<table class="table" cellspacing="0" cellpadding="2">
    <tbody>
        <tr>
            <td width="50%"></td>
            <td width="25%" align="center">{{ $city->city_name }}, {{ $createdDate->format('d-m-Y') }}</td>
            <td width="25%" align="center">{{ $city->city_name }}, {{ $approvedDate->format('d-m-Y') }}</td>
        </tr>
        <tr>
            <td width="50%"></td>
            <td width="25%" align="center">{{ trans('shared/common.created-by') }}</td>
            <td width="25%" align="center">{{ trans('shared/common.approved-by') }}</td>
        </tr>
        <tr>
            <td width="50%"></td>
            <td width="25%" height="40px" align="center"></td>
            <td width="25%" height="40px" align="center"></td>
        </tr>
        <tr>
            <td width="50%"></td>
            <td width="25%" align="center">( {{ !empty($userCreated) ? strtoupper($userCreated->full_name) : '' }} )</td>
            <td width="25%" align="center">( {{ !empty($userApproved) ? strtoupper($userApproved->full_name) : '.......................' }} )</td>
        </tr>
    </tbody>
</table>
@endsection