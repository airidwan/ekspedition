@extends('layouts.print')

<?php
use App\Modules\Operational\Model\Master\MasterOrganization;
use App\Modules\Operational\Model\Transaction\TransactionResiHeader;
use App\Modules\Operational\Model\Master\MasterBranch;

$org = MasterOrganization::first();
$branch = MasterBranch::find(\Session::get('currentBranch')->branch_id);
$resiDate = new \DateTime($model->created_date);
$invoicePickup = $model->getInvoicePickup();
$extraCost = $invoicePickup !== null ? $invoicePickup->amount : 0;
foreach($model->invoiceExtraCosts as $invoiceExtraCost) {
    $extraCost += $invoiceExtraCost->amount;
}

$total = $model->total() + $extraCost - $model->getDownPayment();
?>

@section('header')
@parent
<style type="text/css">
    table { font-size: 9px }
    .web { font-size: 10px; color: white; }
    .ujpt { font-size: 10px; color: white; }
    .branch { font-size: 6px; }
    .branch-title { color: white; }
    .surat-jalan { font-size: 15px; color: white; }
    .valign-surat-jalan { font-size: 12px; }
    .valign-amount { font-size: 7px; }
    .valign-payment { font-size: 5px; }
    .payment-indo { font-size: 10px; }
    .payment-eng { font-size: 8px; }
    .insurance { font-size: 8px; }
    .resi-date, { font-size: 14px; }
    .resi-number, { font-size: 22px; }
    .sender, .receiver, { font-size: 10px; }
    .customer-code { font-size: 8px; }
    .route-city { font-size: 10px; }
    .translate { font-weight: normal; font-size: 6px; font-style: italic; color: white; }
    .column-title { color: white; }
    .syarat-ketentuan-pengiriman { font-size: 7px; color: white; }
    .keterangan-ketentuan-pengiriman { color: red; color: white; }
    .pengaduan-pelanggan { color: red; font-size: 8px; color: white; }
    .amounts { font-size: 11px; font-weight: bold;}
    .total-amount { font-size: 11px; }
    table tr td { border: none; }
</style>
@endsection

@section('content')
<table width="100%" cellspacing="0" cellpadding="0" border="0">
    <tr>
        <td width="50%" rowspan="2">
            <table width="100%" cellspacing="0" cellpadding="2">
                <tr>
                    <td width="40%" align="center">
                        <!--img src="images/ar-karyati.png" height="23"/-->
                    </td>
                    <td align="center" border="0" width="60%"><span class="ujpt">UJPT Nomor: P2T/09/06.37/II/2015</span><br/><span class="web">www.karyati.co.id</span></td>
                </tr>
                <tr>
                    <td colspan="2" class="branch"><span class="branch-title">Head Office:</span>
                        <br/><span class="branch-title">Branch Office:</span> 
                    </td>
                </tr>
            </table>
        </td>
        <td width="20%" align="center" class="surat-jalan" rowspan="2"><span class="valign-surat-jalan">&nbsp;<br/></span>SURAT JALAN</td>
        <td width="30%" class="resi-date" align="right" height="33px"> {{ $resiDate->format('d M Y') }}</td>
    </tr>
    <tr>
        <td width="30%" class="resi-number" align="right" height="33px"> {{ $model->getShortNumber() }}</td>
    </tr>
    <tr>
        <td width="50%" >
            <table width="100%" class="sender" cellspacing="0" cellpadding="2">
                <tr>
                    <td width="17%">&nbsp;<br/><span class="translate">Sender</span></td>
                    <td width="3%">&nbsp;</td>
                    <td width="45%" >{{ !empty($model->customerReceiver) ? $model->customerReceiver->customer_name : $model->receiver_name }}</td>
                    <td width="35%" class="customer-code" align="right" border="0">{{ !empty($model->customerReceiver) ? $model->customerReceiver->customer_code : '' }}</td>
                </tr>
                <tr>
                    <td width="17%">&nbsp;<br/><span class="translate">Address</span></td>
                    <td width="3%">&nbsp;</td>
                    <td width="80%" height="50px" colspan="2">{{ $model->receiver_address }}</td>
                </tr>
                <tr>
                    <td width="17%">&nbsp;<br/><span class="translate">Phone</span></td>
                    <td width="3%">&nbsp;</td>
                    <td width="45%" style="font-size:14px;">{{ $model->receiver_phone }}</td>
                    <td width="35%" class="route-city" border="0" align="right">{{ !empty($model->route->cityStart) ? strtoupper($model->route->cityEnd->city_name) : '' }}</td>
                </tr>
            </table>
        </td>
        <td width="50%" >
            <table width="100%" class="receiver" cellspacing="0" cellpadding="2">
                <tr>
                    <td width="17%">&nbsp;<br/><span class="translate">Receiver</span></td>
                    <td width="8%">&nbsp;</td>
                    <td width="40%">{{ !empty($model->customer) ? $model->customer->customer_name : $model->sender_name }}</td>
                    <td width="35%" class="customer-code" align="right" border="0">{{ !empty($model->customer) ? $model->customer->customer_code : '' }}</td>
                </tr>
                <tr>
                    <td width="17%">&nbsp;<br/><span class="translate">Address</span></td>
                    <td width="8%">&nbsp;</td>
                    <td width="75%" height="50px" colspan="2">{{ $model->sender_address }}</td>
                </tr>
                <tr>
                    <td width="17%">&nbsp;<br/><span class="translate">Phone</span></td>
                    <td width="8%">&nbsp;</td>
                    <td width="40%" style="font-size:14px;">{{ $model->sender_phone }}</td>
                    <td width="35%" class="route-city" border="0" align="right">{{ !empty($model->route->cityStart) ? strtoupper($model->route->cityStart->city_name) : '' }}</td>
                </tr>
            </table>
        </td>
    </tr>
    <tr>
        <td align="center" width="12%" class="column-title" height="34px">Jumlah Coly<br/><span class="translate">Total Colly</span></td>
        <td align="center" width="25%" class="column-title">Rincian Paket<br/><span class="translate">Detail Colly</span></td>
        <td align="center" width="20%" class="column-title">Isi Menurut Pengakuan<br/><span class="translate">Item Name</span></td>
        <td align="center" width="18%" class="column-title">Biaya<br/><span class="translate">Amount</span></td>
        <td align="center" width="25%" class="column-title">Keterangan<br/><span class="translate">Description</span></td>
    </tr>
    <tr>
        <td align="center" height="105px" rowspan="2" style="font-size:12px;">{{ $model->totalColy() }} Koli</td>
        <td align="center" height="105px" rowspan="2" style="font-size:12px;">
            <?php 
            $totalColyWeight   = 0;
            $totalMenangWeight = 0;
            $totalColyVolume   = 0;
            $totalMenangVolume = 0;
            ?>
            @foreach($model->lineDetail as $detail)
            <?php 
            if($detail->isMenangWeight()){
                $totalColyWeight   += $detail->coly;
                $totalMenangWeight += $detail->weight;
            }else{
                $totalColyVolume   += $detail->coly;
                $totalMenangVolume += $detail->totalVolume();
            }
            ?>
            <!-- {{ $detail->coly }} Koli - {{ $detail->isMenangWeight() ? number_format($detail->weight, 2).' kg' : number_format($detail->totalVolume(), 6).' m3' }} <br/> -->

            @endforeach

            {!! !empty($totalColyWeight) ? $totalColyWeight.' Koli - '.number_format($totalMenangWeight, 2).' kg <br>' : '' !!}
            {!! !empty($totalColyVolume) ? $totalColyVolume.' Koli - '.number_format($totalMenangVolume, 6).' m3 <br>' : ''  !!}

            @foreach($model->lineUnit as $unit)
            {{ $unit->coly }} Koli - {{ $unit->item_name }}<br/>
            @endforeach
        </td>
        <td align="center" height="105px" rowspan="2" style="font-size:12px;">{{ $model->item_name }}</td>
        <td align="center" height="50px" style="font-size:12px;"><span class="valign-amount">&nbsp;<br/></span>Rp {{ !$tanpaBiaya ? number_format($model->totalAmount()) : 0 }}</td>
        <td align="center" height="105px" rowspan="2" style="font-size:12px;">
            {{ $model->description }}<br/>
            {!! $model->pickupRequest !== null ? $model->pickupRequest->note_add.'<br/>' : '' !!}
            @foreach($model->invoiceExtraCosts as $invoiceExtraCost)
            {{ $invoiceExtraCost->description }}<br/>
            @endforeach
        </td>
    </tr>
    <?php
    $paymentIndo = ''; 
    if($model->payment == TransactionResiHeader::CASH){
        $paymentIndo = 'LUNAS';
    }elseif($model->payment == TransactionResiHeader::BILL_TO_SENDER){
        $paymentIndo = 'TAGIH PENGIRIM';
    }elseif($model->payment == TransactionResiHeader::BILL_TO_RECIEVER){
        $paymentIndo = 'TAGIH PENERIMA';
    }
        ?>
    <tr>
        <td align="center" height="59px">
            <span class="valign-payment">&nbsp;<br/></span><br>
            <span class="payment-indo">{{ strtoupper($paymentIndo) }}</span><br/>
            <span class="payment-eng">{{ strtoupper($model->payment) }}</span><br/>
            <span class="insurance">{{ $model->insurance ? '('.trans('operational/fields.insurance').')' : '' }}</span>
        </td>
    </tr>
    <tr>
        <td width="37%">
            <table width="100%" cellspacing="0" cellpadding="2">
                <tr>
                    <td colspan="2" class="syarat-ketentuan-pengiriman"><!--Dengan Menyerahkan <b>TITIPAN</b> pada <b>AR KARYATI</b>, Selaku <b>PENGIRIM</b> kami menyatakan bahwa keterangan yang ditulis/dicetak pada lembar ini adalah benar dan kami setuju serta tunduk pada pedoman dan ketentuan pengiriman <b>AR KARYATI</b>.<br/>
                        <span class="keterangan-ketentuan-pengiriman">Syarat dan Ketentuan Pengiriman di Belakang Surat Jalan.</span>
                        <br/> -->
                    </td>
                </tr>
                <tr>
                    <td align="center"><b>&nbsp;</b><br/><span class="translate">Sender</span></td>
                    <td align="center"><b>&nbsp;</b><br/><span class="translate">Receiver</span></td>
                </tr>
                <tr>
                    <td align="center" colspan="2" height="20px"></td>
                </tr>
                <tr>
                    <td align="center">&nbsp;</td>
                    <td align="center">&nbsp;</td>
                </tr>
            </table>
        </td>
        <td width="38%">
            <table width="100%" cellspacing="0" cellpadding="2" border="0">
                <!--tr>
                    <td colspan="3" height="10px"><b>&nbsp;</b></td>
                </tr-->
                <tr>
                    <td colspan="3" height="31px" align="right" class="pengaduan-pelanggan">
                        <!-- SMS/WA : 087885184447<br/>
                        Email : complaint@karyati.co.id -->
                    </td>
                </tr>
                <tr>
                    <td colspan="3" height="5px"><span>Created by: {{ $model->createdBy->full_name }}</span></td>
                </tr>
                <tr>
                    <td align="center" width="30%"><b>&nbsp;</b><br/><span class="translate">Best Regards</span></td>
                    <td align="center" width="30%"><b>&nbsp;</b><br/><span class="translate">Admin/Cashier</span></td>
                    <td align="center" width="40%"><b>&nbsp;</b><br/><span class="translate">Head Warehouse/Courier</span></td>
                </tr>
                <tr>
                    <td colspan="3" height="18px"></td>
                </tr>
                <tr>
                    <td align="left" width="60%" colspan="2" ="">{{ \Auth::user()->full_name }}</td>
                    <td align="center" width="40%">&nbsp;</td>
                </tr>
            </table>
        </td>
        <td width="25%">
            <table width="100%" class="amounts" cellspacing="0" cellpadding="2" border="0">
                <tr>
                    <td height="22px" width="10%">&nbsp;<!--br/><span class="translate">Amount</span--></td>
                    <td width="5%">&nbsp;</td>
                    <td width="85%" align="right">{{ !$tanpaBiaya ? number_format($model->totalAmount()) : 0 }}</td>
                </tr>
                <tr>
                    <td height="22px">&nbsp;<!--br/><span class="translate">Discount</span--></td>
                    <td>&nbsp;</td>
                    <td align="right">{{ !$tanpaBiaya ? number_format($model->discount) : 0 }}</td>
                </tr>
                <tr>
                    <td height="22px">&nbsp;<!--br/><span class="translate">Extra Cost</span--></td>
                    <td>&nbsp;</td>
                    <td align="right">{{ !$tanpaBiaya ? number_format($extraCost) : 0 }}</td>
                </tr>
                <tr>
                    <td height="22px">&nbsp;<!--br/><span class="translate">Down Payment</span--></td>
                    <td>&nbsp;</td>
                    <td align="right">{{ !$tanpaBiaya ? number_format($model->getDownPayment()) : 0 }}</td>
                </tr>
                <tr>
                    <td height="5px" class="total-amount">&nbsp;</td>
                    <td>&nbsp;</td>
                    <td class="total-amount" align="right">{{ !$tanpaBiaya ? number_format($total) : 0 }}</td>
                </tr>
            </table>
        </td>
    </tr>
</table>
@endsection
