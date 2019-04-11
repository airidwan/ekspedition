@extends('layouts.print')

<?php
use App\Modules\Operational\Model\Master\MasterOrganization;
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
.web { font-size: 10px; font-weight: bold; }
.ujpt { font-size: 10px; }
.branch { font-size: 6px; }
.branch-title { font-weight: bold; }
.surat-jalan { font-size: 15px; font-weight: bold; }
.valign-surat-jalan { font-size: 12px; }
.valign-amount { font-size: 7px; }
.valign-payment { font-size: 5px; }
.payment { font-size: 10px; font-weight: bold; }
.insurance { font-size: 8px; }
.resi-number, .resi-date, { font-size: 15px; font-weight: bold; }
.sender, .receiver, { font-weight: bold; }
.customer-code, .route-city, { font-size: 10px; font-weight: bold; }
.translate { font-weight: normal; font-size: 6px; font-style: italic; }
.column-title { font-weight: bold; }
.syarat-ketentuan-pengiriman { font-size: 7px; }
.keterangan-ketentuan-pengiriman { color: red; }
.pengaduan-pelanggan { color: red; font-weight: bold; font-size: 13px; }
.amounts { font-weight: bold; }
.total-amount { font-size: 9px; }
</style>
@endsection

@section('content')
<table width="100%" cellspacing="0" cellpadding="0" border="1">
    <tr>
        <td width="50%" rowspan="2">
            <table width="100%" cellspacing="0" cellpadding="2">
                <tr>
                    <td width="40%" align="center">
                        <img src="images/ar-karyati.png" height="23"/>
                    </td>
                    <td align="center" border="1" width="60%"><span class="ujpt">UJPT Nomor: P2T/09/06.37/II/2015</span><br/><span class="web">www.karyati.co.id</span></td>
                </tr>
                <tr>
                    <td colspan="2" class="branch"><span class="branch-title">Head Office:</span> {{ $org->address }} {{ $org->city->city_name }}. Telp {{ $org->phone_number }}.
                        <br/><span class="branch-title">Branch Office:</span> {{ $branch->address }} {{ $branch->city->city_name }}. Telp {{ $branch->phone_number }}.
                    </td>
                </tr>
            </table>
        </td>
        <td width="20%" align="center" class="surat-jalan" rowspan="2"><span class="valign-surat-jalan">&nbsp;<br/></span>SURAT JALAN</td>
        <td width="30%" class="resi-date" height="23px"> Tanggal: {{ $resiDate->format('d M Y') }}</td>
    </tr>
    <tr>
        <td width="30%" class="resi-number" > No Resi: {{ $model->getShortNumber() }}</td>
    </tr>
    <tr>
        <td width="50%" >
            <table width="100%" class="sender" cellspacing="0" cellpadding="2">
                <tr>
                    <td width="15%">Pengirim<br/><span class="translate">Sender</span></td>
                    <td width="3%">:</td>
                    <td width="47%">{{ !empty($model->customer) ? $model->customer->customer_name : $model->sender_name }}</td>
                    <td width="35%" class="customer-code" align="center" border="1">{{ !empty($model->customer) ? $model->customer->customer_code : '' }}</td>
                </tr>
                <tr>
                    <td width="15%">Alamat<br/><span class="translate">Address</span></td>
                    <td width="3%">:</td>
                    <td width="82%" height="40px" colspan="2">{{ $model->sender_address }}</td>
                </tr>
                <tr>
                    <td width="15%">Telepon<br/><span class="translate">Phone</span></td>
                    <td width="3%">:</td>
                    <td width="47%">{{ $model->sender_phone }}</td>
                    <td width="35%" class="route-city" border="1" align="center">{{ !empty($model->route->cityStart) ? strtoupper($model->route->cityStart->city_name) : '' }}</td>
                </tr>
            </table>
        </td>
        <td width="50%" >
            <table width="100%" class="receiver" cellspacing="0" cellpadding="2">
                <tr>
                    <td width="15%">Penerima<br/><span class="translate">Receiver</span></td>
                    <td width="3%">:</td>
                    <td width="47%">{{ !empty($model->customerReceiver) ? $model->customerReceiver->customer_name : $model->receiver_name }}</td>
                    <td width="35%" class="customer-code" align="center" border="1">{{ !empty($model->customerReceiver) ? $model->customerReceiver->customer_code : '' }}</td>
                </tr>
                <tr>
                    <td width="15%">Alamat<br/><span class="translate">Address</span></td>
                    <td width="3%">:</td>
                    <td width="82%" height="40px" colspan="2">{{ $model->receiver_address }}</td>
                </tr>
                <tr>
                    <td width="15%">Telepon<br/><span class="translate">Phone</span></td>
                    <td width="3%">:</td>
                    <td width="47%">{{ $model->receiver_phone }}</td>
                    <td width="35%" class="route-city" border="1" align="center">{{ !empty($model->route->cityStart) ? strtoupper($model->route->cityEnd->city_name) : '' }}</td>
                </tr>
            </table>
        </td>
    </tr>
    <tr>
        <td align="center" width="15%" class="column-title">Jumlah Coly<br/><span class="translate">Total Colly</span></td>
        <td align="center" width="20%" class="column-title">Rincian Paket<br/><span class="translate">Detail Colly</span></td>
        <td align="center" width="25%" class="column-title">Isi Menurut Pengakuan<br/><span class="translate">Item Name</span></td>
        <td align="center" width="15%" class="column-title">Biaya<br/><span class="translate">Amount</span></td>
        <td align="center" width="25%" class="column-title">Keterangan<br/><span class="translate">Description</span></td>
    </tr>
    <tr>
        <td align="center" height="77px" rowspan="2" width="15%">{{ $model->totalColy() }} Koli</td>
        <td align="center" height="77px" rowspan="2" width="20%">
            @foreach($model->lineDetail as $detail)
                {{ $detail->coly }} Koli - {{ $detail->isMenangWeight() ? number_format($detail->weight, 2).' kg' : number_format($detail->totalVolume(), 6).' m3' }}<br/>
            @endforeach
            @foreach($model->lineUnit as $unit)
                {{ $unit->coly }} Koli - {{ $unit->item_name }}<br/>
            @endforeach
        </td>
        <td align="center" height="77px" rowspan="2" width="25%"><b>{{ $model->item_name }}</b></td>
        <td align="center" height="30px" width="15%"><span class="valign-amount">&nbsp;<br/></span><b>Rp {{ !$tanpaBiaya ? number_format($model->totalAmount()) : 0 }}</b></td>
        <td align="center" height="77px" rowspan="2" width="25%">
            {{ $model->description }}<br/>
            {{ $model->pickupRequest !== null ? $model->pickupRequest->note_add.'<br/>' : '' }}
            @foreach($model->invoiceExtraCosts as $invoiceExtraCost)
                {{ $invoiceExtraCost->description }}<br/>
            @endforeach
        </td>
    </tr>
    <tr>
        <td align="center" height="30px">
            <span class="valign-payment">&nbsp;<br/></span>
            <span class="payment">{{ strtoupper($model->payment) }}</span><br/>
            <span class="insurance">{{ $model->insurance ? '('.trans('operational/fields.insurance').')' : '' }}</span>
        </td>
    </tr>
    <tr>
        <td width="35%">
            <table width="100%" cellspacing="0" cellpadding="2">
                <tr>
                    <td colspan="2" class="syarat-ketentuan-pengiriman">Dengan Menyerahkan <b>TITIPAN</b> pada <b>AR KARYATI</b>, Selaku <b>PENGIRIM</b> kami menyatakan bahwa keterangan yang ditulis/dicetak pada lembar ini adalah benar dan kami setuju serta tunduk pada pedoman dan ketentuan pengiriman <b>AR KARYATI</b>.<br/>
                        <span class="keterangan-ketentuan-pengiriman">Syarat dan Ketentuan Pengiriman di Belakang Surat Jalan.</span>
                        <br/>
                    </td>
                </tr>
                <tr>
                    <td align="center"><b>Pengirim</b><br/><span class="translate">Sender</span></td>
                    <td align="center"><b>Penerima</b><br/><span class="translate">Receiver</span></td>
                </tr>
                <tr>
                    <td align="center" colspan="2" height="40px"></td>
                </tr>
                <tr>
                    <td align="center">(.................................)</td>
                    <td align="center">(.................................)</td>
                </tr>
            </table>
        </td>
        <td width="40%">
            <table width="100%" cellspacing="0" cellpadding="2">
                <tr>
                    <td colspan="3" height="10px"><b>Pengaduan Pelanggan :</b></td>
                </tr>
                <tr>
                    <td colspan="3" height="37px" align="right" class="pengaduan-pelanggan">
                        SMS/WA : 087885184447<br/>
                        Email : complaint@karyati.co.id
                    </td>
                </tr>
                <tr>
                    <td colspan="3" height="5px"><span class="translate">Created by: {{ $model->createdBy->full_name }}</span></td>
                </tr>
                <tr>
                    <td align="center" width="30%"><b>Hormat Kami</b><br/><span class="translate">Best Regards</span></td>
                    <td align="center" width="30%"><b>Admin/Kasir</b><br/><span class="translate">Admin/Cashier</span></td>
                    <td align="center" width="40%"><b>Kepala Gudang / Kurir</b><br/><span class="translate">Head Warehouse/Courier</span></td>
                </tr>
                <tr>
                    <td colspan="3" height="40px"></td>
                </tr>
                <tr>
                    <td align="center" width="30%">({{ \Auth::user()->full_name }})</td>
                    <td align="center" width="30%">(.......................)</td>
                    <td align="center" width="40%">(.................................)</td>
                </tr>
            </table>
        </td>
        <td width="25%">
            <table width="100%" class="amounts" cellspacing="0" cellpadding="2">
                <tr>
                    <td width="40%">Jumlah<br/><span class="translate">Amount</span></td>
                    <td width="5%">:</td>
                    <td width="55%" align="right">Rp {{ !$tanpaBiaya ? number_format($model->totalAmount()) : 0 }}</td>
                </tr>
                <tr>
                    <td>Diskon<br/><span class="translate">Discount</span></td>
                    <td>:</td>
                    <td align="right">Rp {{ !$tanpaBiaya ? number_format($model->discount) : 0 }}</td>
                </tr>
                <tr>
                    <td>Biaya Tambahan<br/><span class="translate">Extra Cost</span></td>
                    <td>:</td>
                    <td align="right">Rp {{ !$tanpaBiaya ? number_format($extraCost) : 0 }}</td>
                </tr>
                <tr>
                    <td>Uang Muka<br/><span class="translate">Down Payment</span></td>
                    <td>:</td>
                    <td align="right">Rp {{ !$tanpaBiaya ? number_format($model->getDownPayment()) : 0 }}</td>
                </tr>
                <tr>
                    <td class="total-amount">TOTAL</td>
                    <td>:</td>
                    <td class="total-amount" align="right">Rp {{ !$tanpaBiaya ? number_format($total) : 0 }}</td>
                </tr>
            </table>
        </td>
    </tr>
</table>
@endsection
