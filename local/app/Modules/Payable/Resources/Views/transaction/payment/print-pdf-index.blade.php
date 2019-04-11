@extends('layouts.print')

<?php
    use App\Modules\Payable\Model\Transaction\InvoiceHeader;
?>

@section('content')
<table id="filtes" width="100%" cellpadding="0" cellspacing="0">
    <tr>
        <td width="75%" cellpadding="0" cellspacing="0">
            <table>
                @if (!empty($filters['paymentNumber']))
                    <tr>
                        <td width="18%">{{ trans('payable/fields.payment-number') }}</td>
                        <td width="2%">:</td>
                        <td width="80%">{{ $filters['paymentNumber'] }}</td>
                    </tr>
                @endif
                @if (!empty($filters['invoiceNumber']))
                    <tr>
                        <td width="18%">{{ trans('payable/fields.invoice-number') }}</td>
                        <td width="2%">:</td>
                        <td width="80%">{{ $filters['invoiceNumber'] }}</td>
                    </tr>
                @endif
                @if (!empty($filters['vendorCode']))
                    <tr>
                        <td width="18%">{{ trans('payable/fields.trading-code') }}</td>
                        <td width="2%">:</td>
                        <td width="80%">{{ $filters['vendorCode'] }}</td>
                    </tr>
                @endif
                @if (!empty($filters['vendor']))
                    <tr>
                        <td width="18%">{{ trans('payable/fields.trading-name') }}</td>
                        <td width="2%">:</td>
                        <td width="80%">{{ $filters['vendor'] }}</td>
                    </tr>
                @endif
                @if (!empty($filters['createdBy']))
                    <tr>
                        <td width="18%">{{ trans('shared/common.created-by') }}</td>
                        <td width="2%">:</td>
                        <td width="80%">{{ $filters['createdBy'] }}</td>
                    </tr>
                @endif
                @if (!empty($filters['description']))
                    <tr>
                        <td width="18%">{{ trans('shared/common.description') }}</td>
                        <td width="2%">:</td>
                        <td width="80%">{{ $filters['description'] }}</td>
                    </tr>
                @endif
                @if (!empty($filters['type']))
                    <?php $type = \DB::table('ap.mst_ap_type')->select('type_name')->where('type_id', '=', $filters['type'])->first(); ?>
                    <tr>
                        <td width="18%">{{ trans('shared/common.type') }}</td>
                        <td width="2%">:</td>
                        <td width="80%">{{ $type->type_name }}</td>
                    </tr>
                @endif
                @if (!empty($filters['paymentMethod']))
                    <tr>
                        <td width="18%">{{ trans('payable/fields.payment-method') }}</td>
                        <td width="2%">:</td>
                        <td width="80%">{{ $filters['paymentMethod'] }}</td>
                    </tr>
                @endif
                @if (!empty($filters['status']))
                    <tr>
                        <td width="18%">{{ trans('shared/common.status') }}</td>
                        <td width="2%">:</td>
                        <td width="80%">{{ $filters['status'] }}</td>
                    </tr>
                @endif
                @if (!empty($filters['dateFrom']))
                    <tr>
                        <td width="18%">{{ trans('shared/common.date-from') }}</td>
                        <td width="2%">:</td>
                        <td width="80%">{{ $filters['dateFrom'] }}</td>
                    </tr>
                @endif
                @if (!empty($filters['dateTo']))
                    <tr>
                        <td width="18%">{{ trans('shared/common.date-to') }}</td>
                        <td width="2%">:</td>
                        <td width="80%">{{ $filters['dateTo'] }}</td>
                    </tr>
                @endif
            </table>
            <br/>
        </td>
        <td width="25%" cellpadding="0" cellspacing="0">
            <table>
                <?php $date = new \DateTime(); ?>
                <tr>
                    <td width="25%">{{ trans('shared/common.date') }}</td>
                    <td width="5%">:</td>
                    <td width="70%">{{ $date->format('d-M-Y') }}</td>
                </tr>
                <tr>
                    <td width="25%">{{ trans('shared/common.user') }}</td>
                    <td width="5%">:</td>
                    <td width="70%">{{ \Auth::user()->full_name }}</td>
                </tr>
                <tr>
                    <td width="25%">{{ trans('shared/common.branch') }}</td>
                    <td width="5%">:</td>
                    <td width="70%">{{ \Session::get('currentBranch')->branch_name }}</td>
                </tr>
            </table>
            <br/>
        </td>
    </tr>
</table>
<table class="table" cellspacing="0" cellpadding="2" border="1">
    <thead>
        <tr>
            <th width="3%" rowspan="2">{{ trans('shared/common.num') }}</th>
            <th width="10%">{{ trans('payable/fields.payment-number') }}</th>
            <th width="10%">{{ trans('shared/common.type') }}</th>
            <th width="15%">{{ trans('payable/fields.trading-code') }}</th>
            <th width="18%" rowspan="2">{{ trans('shared/common.address') }}</th>
            <th width="18%" rowspan="2">{{ trans('shared/common.description') }}</th>
            <th width="10%">{{ trans('payable/fields.payment-method') }}</th>
            <th width="8%">{{ trans('shared/common.status') }}</th>
            <th width="8%" rowspan="2">{{ trans('payable/fields.total-payment') }}</th>
        </tr>
        <tr>
            <th >{{ trans('payable/fields.invoice-number') }}</th>
            <th >{{ trans('shared/common.date') }}</th>
            <th >{{ trans('payable/fields.trading-name') }}</th>
            <th >{{ trans('accountreceivables/fields.cash-or-bank') }}</th>
            <th >{{ trans('shared/common.created-by') }}</th>
        </tr>
    </thead>
    <tbody>
        <?php 
        $no = 1;
        $totalAmount   = 0; 
        $totalInterest = 0; 
        ?>
        @foreach($models as $model)
        <?php
            $date       = !empty($model->created_date) ? new \DateTime($model->created_date) : null;
        ?>
        <tr>
            <td width="3%" rowspan="2" align="center">{{ $no++ }}</td>
            <td width="10%" >{{ $model->payment_number }}</td>
            <td width="10%" >{{ $model->type_name }}</td>
            @if( in_array($model->type_id, InvoiceHeader::VENDOR_TYPE))
            <td width="15%" >{{ $model->vendor_code }}</td>
            <td width="18%" rowspan="2">{{ $model->vendor_address }}</td>
            @elseif(in_array($model->type_id, InvoiceHeader::DRIVER_TYPE))
            <td width="15%" >{{ $model->driver_code }}</td>
            <td width="18%" rowspan="2">{{ $model->driver_address }}</td>
            @endif
            <td width="18%" rowspan="2">{{ $model->note }}</td>
            <td width="10%" >{{ $model->payment_method }}</td>
            <td width="8%" >{{ $model->status }}</td>
            <td width="8%" rowspan="2" align="right">{{ number_format($model->total_amount + $model->total_interest) }}</td>
        </tr>
        <tr>
            <td>{{ $model->invoice_number }}</td>
            <td>{{ $date->format('d-m-Y') }}</td>
            @if( in_array($model->type_id, InvoiceHeader::VENDOR_TYPE))
            <td >{{ $model->vendor_name }}</td>
            @elseif(in_array($model->type_id, InvoiceHeader::DRIVER_TYPE))
            <td >{{ $model->driver_name }}</td>
            @endif
            <td >{{ $model->bank_name }}</td>
            <td >{{ $model->full_name }}</td>
        </tr>
        <?php 
            $totalAmount   += $model->total_amount;
            $totalInterest += $model->total_interest;
         ?>
        @endforeach
        <tr>
            <td width="92%" align="center"><strong>{{ trans('shared/common.total') }}</strong></td>
            <td width="8%" align="right"><strong>{{ number_format($totalAmount + $totalInterest) }}</strong></td>
        </tr>
    </tbody>
</table>

<?php
use App\Modules\Operational\Model\Master\MasterCity;
$city = MasterCity::find(\Session::get('currentBranch')->city_id);
$date = new \DateTime;

$branchManager = DB::table('adm.user_role_branch')
                    ->select('users.full_name')
                    ->join('adm.user_role', 'user_role.user_role_id', '=', 'user_role_branch.user_role_id')
                    ->join('adm.users', 'users.id', '=', 'user_role.user_id')
                    ->where('user_role_branch.branch_id', \Session::get('currentBranch')->branch_id)
                    ->where('user_role.role_id', App\Role::BRANCH_MANAGER)
                    ->first();
?>
<br>
<br>
<table class="table" cellspacing="0" cellpadding="2">
    <tbody>
        <tr>
            <td width="50%" align="center"></td>
            <td width="50%" align="center">{{ $city->city_name }}, {{ $date->format('d M Y') }}</td>
        </tr>
        <tr>
            <td width="25%" align="center">{{ trans('shared/common.cashier') }}</td>
            <td width="25%" align="center">{{ trans('shared/common.finance-admin') }}</td>
            <td width="25%" align="center">{{ trans('shared/common.finance') }}</td>
            <td width="25%" align="center">{{ trans('shared/common.branch-manager') }}</td>
        </tr>
        <tr>
            <td width="100%" height="40px" align="center"></td>
        </tr>
        <tr>
            <td width="25%" align="center">(.............................................)</td>
            <td width="25%" align="center">(.............................................)</td>
            <td width="25%" align="center">(.............................................)</td>
            <td width="25%" align="center">(.............................................)</td>
        </tr>
    </tbody>
</table>
@endsection
