@extends('layouts.print')

<?php 
    use App\Modules\Operational\Model\Master\MasterCity;
    use App\User;
    $vendor = $model->vendor;
    $lines  = $model->purchaseOrderLines;
    $date = !empty($model->created_date) ? new \DateTime($model->created_date) : new \DateTime();
?>

@section('content')
<table id="filtes" width="100%" cellpadding="0" cellspacing="0">
    <tr>
        <td width="100%" cellpadding="0" cellspacing="0">
            Kepada Yth,
        </td>
    </tr>
    <tr>
        <td width="50%" cellpadding="0" cellspacing="0">
            <table>
                <tr>
                    <td width="38%">{{ trans('purchasing/fields.supplier') }}</td>
                    <td width="2%">:</td>
                    <td width="60%">{{ !empty($vendor) ? $vendor->vendor_name : '' }}</td>
                </tr>
                <tr>
                    <td width="38%">{{ trans('shared/common.address') }}</td>
                    <td width="2%">:</td>
                    <td width="60%">{{ !empty($vendor) ? $vendor->address : '' }}</td>
                </tr>
                <tr>
                    <td width="38%">{{ trans('shared/common.phone') }}</td>
                    <td width="2%">:</td>
                    <td width="60%">{{ !empty($vendor) ? $vendor->phone_number : '' }}</td>
                </tr>
            </table>
            <br/>
        </td>
        <td width="50%" cellpadding="0" cellspacing="0">
            <table>
                <tr>
                    <td width="38%">{{ trans('purchasing/fields.po-number') }}</td>
                    <td width="2%">:</td>
                    <td width="60%">{{ $model->po_number }}</td>
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
<table class="table" cellspacing="0" cellpadding="2" border="1">
    <thead>
        <tr>
            <th width="5%">{{ trans('shared/common.num') }}</th>
            <th width="15%">{{ trans('inventory/fields.item-code') }}</th>
            <th width="30%">{{ trans('inventory/fields.item-name') }}</th>
            <th width="10%">{{ trans('purchasing/fields.qty') }}</th>
            <th width="10%">{{ trans('inventory/fields.uom') }}</th>
            <th width="15%">{{ trans('purchasing/fields.unit-price') }}</th>
            <th width="15%">{{ trans('purchasing/fields.amount') }}</th>
        </tr>
    </thead>
    <tbody>
         <?php $no = 1; ?>
         @foreach($lines as $line)
         <?php  
            $item     = $line->item;
            $uom      = !empty($item) ? $item->uom : null;
        ?>
        <tr>
            <td width="5%" align="center">{{ $no++ }}</td>
            <td width="15%">{{ !empty($item) ? $item->item_code : '' }}</td>
            <td width="30%">{{ !empty($item) ? $item->description : '' }}</td>
            <td width="10%" align="right">{{ number_format($line->quantity_need) }}</td>
            <td width="10%">{{ !empty($uom) ? $uom->uom_code : '' }}</td>
            <td width="15%" align="right">{{ number_format($line->unit_price) }}</td>
            <td width="15%" align="right">{{ number_format($line->total_price) }}</td>
        </tr>
        @endforeach
        <tr>
            <td width="85%" align="center"><strong>{{ trans('shared/common.total-amount') }} </strong></td>
            <td width="15%" align="right"><strong>{{ number_format($model->getTotalPrice()) }}</strong></td>
        </tr>
    </tbody>
</table>

<?php
$city = MasterCity::find(\Session::get('currentBranch')->city_id);
$createdDate  = new \DateTime($model->created_date);
$approvedDate = new \DateTime($model->approved_date);
$userCreated  = User::find($model->created_by);
$userApproved = User::find($model->approved_by);
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
            <td width="25%" align="center">( {{ strtoupper($userCreated->full_name) }} )</td>
            <td width="25%" align="center">( {{ strtoupper($userApproved->full_name) }} )</td>
        </tr>
    </tbody>
</table>
@endsection

