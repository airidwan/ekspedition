<?php

namespace App\Modules\Generalledger\Model\Master;

use Illuminate\Database\Eloquent\Model;

class MasterCoa extends Model
{
    const COMPANY_CODE    = '01';
    const FUTURE_CODE     = '00000';

    const COMPANY         = 'Company';
    const COST_CENTER     = 'Cost Center';
    const ACCOUNT         = 'Account';
    const SUB_ACCOUNT     = 'Sub Account';
    const FUTURE          = 'Future 1';

    const NONAME_SUB_ACCOUNT = '00000';
    const DEFAULT_ACCOUNT    = '13110000';

    const PENDAPATAN     = [
                            '41100001', // PENDAPATAN UTAMA
                            '41200004', // PENDAPATAN DO
                            '41200005', // PENDAPATAN PICKUP
                            '41200002', // KEUNTUNGAN JUAL ASSET
                            '41200006', // PENDAPATAN TAMBAHAN
                            '41200010', // PENDAPATAN LAIN-LAIN
                            ];

    const PENDAPATAN_UTAMA= [
                            '41100001', // PENDAPATAN UTAMA
                            '41200004', // PENDAPATAN DO
                            '41200005', // PENDAPATAN PICKUP
                            '41200002', // KEUNTUNGAN JUAL ASSET
                            '41200006', // PENDAPATAN TAMBAHAN
                            ];

    const PENDAPATAN_LAIN= [
                            '41200010', // PENDAPATAN LAIN-LAIN
                            ];

    const POTONGAN      = [
                            '41300001', // DISKON PENJUALAN
                            '41300002', // PEMBULATAN
                            '41200011', // KERUGIAN JUAL ASSET
                            '41100002', // PENGEMBALIAN PENDAPATAN
                            ];

    const BEBAN_OPERASIONAL = [
                            '61000001', // BEBAN GAJI
                            '61000002', // BEBAN TUNJANGAN
                            '61000003', // BEBAN SEWA KENDARAAN
                            '61000004', // BEBAN OPERASIONAL KANTOR
                            '61000005', // BEBAN PELIMPAHAN
                            '61000006', // BEBAN PEMELIHARAAN KENDARAAN
                            '61000007', // BEBAN PENGINAPAN
                            '61000008', // BEBAN TRANSPORTASI
                            '61000009', // BEBAN BONGKAR MUAT
                            '61000010', // BEBAN UANG JALAN MANIFEST
                            '61000011', // BEBAN UANG JALAN DO
                            '61000012', // BEBAN UANG JALAN PICKUP
                            '61000013', // BEBAN KONSUMSI KANTOR
                            '61000014', // BEBAN SUMBANGAN
                            '61000015', // BEBAN PULSA KANTOR
                            '61000016', // BEBAN TIKET KAPAL
                            '61000017', // BEBAN TIKET PESAWAT
                            '61000018', // FEE MARKETING
                            '61000019', // BEBAN PERJALANAN DINAS
                            ];

    const BEBAN_ADMINISTRASI = [
                            '71000001', // BEBAN ADMINISTRASI UMUM
                            '71000002', // PERALATAN KANTOR
                            '71000003', // BEBAN TELEPON LISTRIK DAN AIR
                            '71000004', // BEBAN PEMELIHARAAN
                            '71000005', // BEBAN ALAT TULIS KANTOR / ATK
                            '71000006', // BEBAN SEWA
                            '71000008', // BEBAN BAHAN BAKAR MINYAK
                            '71000009', // BEBAN PARKIR & TOL
                            '71000010', // BEBAN KIR & STNK
                            '71000011', // BEBAN PERGANTIAN BARANG RUSAK / KEHILANGAN
                            '71000012', // BEBAN ADMINISTRASI BANK
                            '71000013', // BEBAN ANGSURAN/ CICILAN
                            '81000004', // BEBAN PAJAK KENDARAAN
                            '81000006', // BEBAN PENYUSUTAN
                            ];
                            
    const BEBAN_LAIN    = [
                            '81000007', // BEBAN LAIN-LAIN
                            '81000003', // BEBAN BUNGA PINJAMAN
                            '81000005', // BEBAN ASURANSI
                            '81000011', // BUNGA BANK
                            ];

    const BEBAN_PAJAK    = [
                            '81000002', // BEBAN PAJAK PPH 21
                            '81000001', // BEBAN PAJAK FINAL
                            '81000009', // BEBAN PAJAK BUNGA BANK
                            '81000010', // BEBAN PAJAK BANGUNAN
                            ];

    const BIAYA         = [
                            '61000001', // BEBAN GAJI
                            '61000002', // BEBAN TUNJANGAN
                            '61000003', // BEBAN SEWA KENDARAAN
                            '61000004', // BEBAN OPERASIONAL KANTOR
                            '61000005', // BEBAN PELIMPAHAN
                            '61000006', // BEBAN PEMELIHARAAN KENDARAAN
                            '61000007', // BEBAN PENGINAPAN
                            '61000008', // BEBAN TRANSPORTASI
                            '61000009', // BEBAN BONGKAR MUAT
                            '61000010', // BEBAN UANG JALAN MANIFEST
                            '61000011', // BEBAN UANG JALAN DO
                            '61000012', // BEBAN UANG JALAN PICKUP
                            '61000013', // BEBAN KONSUMSI KANTOR
                            '61000014', // BEBAN SUMBANGAN
                            '61000015', // BEBAN PULSA KANTOR
                            '61000016', // BEBAN TIKET KAPAL
                            '61000017', // BEBAN TIKET PESAWAT
                            '61000018', // FEE MARKETING
                            '61000019', // BEBAN PERJALANAN DINAS
                            
                            '71000001', // BEBAN ADMINISTRASI UMUM
                            '71000002', // PERALATAN KANTOR
                            '71000003', // BEBAN TELEPON LISTRIK DAN AIR
                            '71000004', // BEBAN PEMELIHARAAN
                            '71000005', // BEBAN ALAT TULIS KANTOR / ATK
                            '71000006', // BEBAN SEWA
                            '71000008', // BEBAN BAHAN BAKAR MINYAK
                            '71000009', // BEBAN PARKIR & TOL
                            '71000010', // BEBAN KIR & STNK
                            '71000011', // BEBAN PERGANTIAN BARANG RUSAK / KEHILANGAN
                            '71000012', // BEBAN ADMINISTRASI BANK
                            '71000013', // BEBAN ANGSURAN/ CICILAN
                            '81000004', // BEBAN PAJAK KENDARAAN
                            '81000006', // BEBAN PENYUSUTAN

                            '81000007', // BEBAN LAIN-LAIN
                            '81000003', // BEBAN BUNGA PINJAMAN
                            '81000005', // BEBAN ASURANSI
                            '81000011', // BUNGA BANK
                           
                            '81000002', // BEBAN PAJAK PPH 21
                            '81000001', // BEBAN PAJAK FINAL
                            '81000009', // BEBAN PAJAK BUNGA BANK
                            '81000010', // BEBAN PAJAK BANGUNAN
                            ];

    const ACTIVA_KAS     = [
                            '11100003', // KAS MASUK
                            '11100001', // PETTY CASH
                            '11100004', // KAS GABUNGAN KANTOR DEPAN   Asset       
                            '11100005', // KAS GABUNGAN KANTOR BELAKANG
                            ];

    const ACTIVA_BANK     = [
                            // '11720001', // BCA 1020437797 (MUHAMMAD RIYADI, SE)
                            // '11720002', // BCA 3360001198 (M. JUNI ALIAKBAR)
                            // '11720004', // BCA 0511030489 (MUHAMMAD RIYADI, SE)
                            // '11720012', // BCA 1020454462 (MUHAMMAD RIYADI, SE)
                            // '11720007', // BCA 7820053871 (ADI RAHMAN)
                            // '11720003', // BCA 4564910122 (ARHAM LATIF)
                            // '11720011', // BCA 7990592866 (M ALWI)
                            // '11720008', // BCA 2560120951 (AJI ABDUL RAHIM)
                            // '11720005', // BCA 8685021028 (M. ASAD)
                            // '11710001', // BANK MANDIRI 1210004215806 (M. JUNI ALIAKBAR)
                            // '11710007', // BANK MANDIRI 310004896471 (ADI RAHMAN)
                            // '11710003', // BANK MANDIRI 1520007420033 (M. ALWI)
                            // '11740004', // BRI 012601039401502 (M. ASAD)

                            '11710002', // BANK MANDIRI 0310002234410 (MUHAMMAD RIYADI, SE)
                            '11710003', // BANK MANDIRI 1520007420033 (M. ALWI)
                            '11710004', // BANK MANDIRI 0310006939683 (M. ASAD)
                            '11710005', // BANK MANDIRI 1490005209327 (ABDUL KADIR MASSAING)
                            '11710006', // BANK MANDIRI 1022052221 (ADI RAHMAN)
                            '11720001', // BCA 1020437797 (MUHAMMAD RIYADI, SE)
                            '11720002', // BCA 3360001198 (M. JUNI ALIAKBAR)
                            '11720003', // BCA 4564910122 (ARHAM LATIF)
                            '11720005', // BCA 8685021028 (M. ASAD)
                            '11720006', // BCA 1911602852 (ABDUL KADIR MASSAING)
                            '11720007', // BCA 7820053871 (ADI RAHMAN)
                            '11720008', // BCA 2560120951 (AJI ABDUL RAHIM)
                            '11720009', // BCA 7820058849 (RAHMAWATI)
                            '11730001', // BNI 0362085245 (PT. AR KARYATI)
                            '11730002', // BNI 0274456493 (NURLELA)
                            '11730003', // BNI 0192274834 (MUHAMMAD YUNUS)
                            '11730004', // BNI SYARIAH 0337477606 (M. ASAD)
                            '11710007', // BANK MANDIRI 310004896471 (ADI RAHMAN)
                            '11730005', // BNI 0092377184 (AJI ABDUL RAHIM)
                            '11710001', // BANK MANDIRI 1210004215806 (M. JUNI ALIAKBAR)
                            '11730006', // BNI SYARIAH 0310003153056 (ISNAWATI)
                            '11740001', // BRI 022201012524500 (MUHAMMAD YUNUS)
                            '11740002', // BRI 8685021028 (M. ASAD)
                            '11740003', // BRI 000301001029568 (MUHAMMAD SUPIAN AKBARI)
                            '11750001', // BANK DANAMON 3596547673 (ADI RAHMAN)
                            '11720010', // BCA 3971251709 (HERMAN)
                            '11100006', // BANK KARYATI BANJARMASIN
                            '11740004', // BRI 012601039401502 (M. ASAD)
                            '11730008', // BANK BNI 1144445553 (HUSNIYATI)
                            '11720011', // BCA 7990592866 (M ALWI)
                            '11740005', // BRI 380601003233500 (M ALWI)
                            '11720012', // BCA 1020454462 (MUHAMMAD RIYADI, SE)
                            ];

    const ACTIVA_PERSEDIAAN = [
                            '13000001', // PERSEDIAAN SPAREPART
                            '13000002', // PERSEDIAAN CONSUMABLE
                            '13000003', // PERSEDIAAN CLEARING
                            '15000001', // INTRANSIT INVENTORY
                            ];

    const ACTIVA_SEWA    = [
                            ];

    const ACTIVA_PIUTANG = [
                            '11300001', // PIUTANG USAHA
                            '11300002', // PIUTANG KARYAWAN
                            '11300007', // PIUTANG AFILIASI
                            '11900001', // CEK/GIRO
                            '11300008', // PIUTANG AFILIASI AR
                            ];

    const ACTIVA_ASSET   = [
                            '12000002', // ASSET TANAH
                            '12000003', // ASSET BANGUNAN
                            '12000004', // ASSET KENDARAAN
                            '12000006', // ASSET ELEKTRONIK
                            '12000005', // ASSET RUMAH TANGGA
                            ];

    const ACTIVA         = [
                            '13000001', // PERSEDIAAN SPAREPART
                            '13000002', // PERSEDIAAN CONSUMABLE
                            '11100001', // PETTY CASH
                            '11300001', // PIUTANG USAHA
                            '11300002', // PIUTANG KARYAWAN
                            '11300003', // KAS BON GAJI
                            '11300004', // KAS BON JALDIN
                            '11300005', // KAS BON LAIN-LAIN
                            '15000001', // INTRANSIT INVENTORY
                            '21000005', // HUTANG GAJI KARYAWAN
                            '11720004', // BCA 0511030489 (MUHAMMAD RIYADI, SE)
                            '11100002', // KAS OWNER
                            '11710002', // BANK MANDIRI 0310002234410 (MUHAMMAD RIYADI, SE)
                            '11710003', // BANK MANDIRI 1520007420033 (M. ALWI)
                            '11710004', // BANK MANDIRI 0310006939683 (M. ASAD)
                            '11710005', // BANK MANDIRI 1490005209327 (ABDUL KADIR MASSAING)
                            '11710006', // BANK MANDIRI 1022052221 (ADI RAHMAN)
                            '11720001', // BCA 1020437797 (MUHAMMAD RIYADI, SE)
                            '11720002', // BCA 3360001198 (M. JUNI ALIAKBAR)
                            '11720003', // BCA 4564910122 (ARHAM LATIF)
                            '11720005', // BCA 8685021028 (M. ASAD)
                            '11720006', // BCA 1911602852 (ABDUL KADIR MASSAING)
                            '11720007', // BCA 7820053871 (ADI RAHMAN)
                            '11720008', // BCA 2560120951 (AJI ABDUL RAHIM)
                            '11720009', // BCA 7820058849 (RAHMAWATI)
                            '11730001', // BNI 0362085245 (PT. AR KARYATI)
                            '11730002', // BNI 0274456493 (NURLELA)
                            '11730003', // BNI 0192274834 (MUHAMMAD YUNUS)
                            '11730004', // BNI SYARIAH 0337477606 (M. ASAD)
                            '11710007', // BANK MANDIRI 310004896471 (ADI RAHMAN)
                            '11730005', // BNI 0092377184 (AJI ABDUL RAHIM)
                            '11710001', // BANK MANDIRI 1210004215806 (M. JUNI ALIAKBAR)
                            '11730006', // BNI SYARIAH 0310003153056 (ISNAWATI)
                            '11740001', // BRI 022201012524500 (MUHAMMAD YUNUS)
                            '11740002', // BRI 8685021028 (M. ASAD)
                            '11740003', // BRI 000301001029568 (MUHAMMAD SUPIAN AKBARI)
                            '11750001', // BANK DANAMON 3596547673 (ADI RAHMAN)
                            '11100003', // KAS MASUK
                            '11100004', // KAS GABUNGAN KANTOR DEPAN
                            '11100005', // KAS GABUNGAN KANTOR BELAKANG
                            '11720010', // BCA 3971251709 (HERMAN)
                            '11100006', // BANK KARYATI BANJARMASIN
                            '11740004', // BRI 012601039401502 (M. ASAD)
                            '11730008', // BANK BNI 1144445553 (HUSNIYATI)
                            '11720011', // BCA 7990592866 (M ALWI)
                            '11740005', // BRI 380601003233500 (M ALWI)
                            '11720012', // BCA 1020454462 (MUHAMMAD RIYADI, SE)
                            '11300007', // PIUTANG AFILIASI
                            ];

    const SUSUT         = [
                            '14100001', // AKUMULASI PENYUSUTAN BANGUNAN
                            '14100002', // AKUMULASI PENYUSUTAN KENDARAAN
                            '14100003', // AKUMULASI PENYUSUTAN ELEKTRONIK
                            '14100004', // AKUMULASI PENYUSUTAN RUMAH TANGGA
                            '14100005', // AKUMULASI PENYUSUTAN TANAH
                            ];

    const PASIVA        = [
                            '21000001', //HUTANG USAHA
                            '21000008', //HUTANG SUSPENSE
                            '21000009', //HUTANG AFILIASI
                            '31000001', //SALDO AWAL OPENING BALANCE
                            ];

    const PASIVA_HUTANG = [
                            '21000001', //HUTANG USAHA
                            '21000008', //HUTANG SUSPENSE
                            '21000009', //HUTANG AFILIASI
                            '21000010', //HUTANG AFILIASI AR
                            '31000001', //SALDO AWAL OPENING BALANCE
                            ];
    const PASIVA_EQUITAS = [
                            ];

    const ASSET         = 1;
    const LIABILITY     = 2;
    const EKUITAS       = 3;
    const REVENUE       = 4;
    const EXPENSE       = 5;

    protected $connection = 'gl';
    protected $table      = 'mst_coa';
    protected $primaryKey = 'coa_id';

    public $timestamps    = false;
}
