<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Modules\Asset\Model\Transaction\AdditionAsset;
use App\Modules\Generalledger\Model\Transaction\JournalHeader;
use App\Modules\Generalledger\Model\Transaction\JournalLine;
use App\Modules\Generalledger\Service\Master\AccountCombinationService;
use App\Modules\Generalledger\Service\Master\JournalService;
use App\Modules\Generalledger\Model\Master\SettingJournal;

class JurnalPenyusutan extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cron:jurnal-penyusutan';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Membuat jurnal penyusutan otomatis saat umur asset bertambah 1 bulan';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $now = new \DateTime();
        $this->info('==========================');
        $this->info('Start: '.$now->format('d-m-Y H:i:s').PHP_EOL);

        $this->proses();

        $now = new \DateTime();
        $this->info(PHP_EOL.'End: '.$now->format('d-m-Y H:i:s').PHP_EOL);
    }

    protected function proses()
    {
        $now    = new \DateTime();
        $period = new \DateTime($now->format('Y-m-1'));

        foreach($this->getActiveAssets() as $asset) {
            $this->info('------------------------------');
            $this->info('Asset no '.$asset->asset_number);

            $asset                     = AdditionAsset::find($asset->asset_id);
            $tanggalAsset              = new \DateTime($asset->created_date);
            $penyusutan                = $asset->depreciation !== null ? $asset->depreciation->cost_month : 0;
            $lifeYear                  = $asset->depreciation !== null ? $asset->depreciation->life_year : 0;
            $jumlahHariSebulan         = 30;
            $jumlahBulanSetahun        = 12;
            $jumlahHariHabisPenyusutan = $lifeYear * $jumlahHariSebulan * $jumlahBulanSetahun;
            $tanggalHabisPenyusutan    = clone $tanggalAsset;
            $tanggalHabisPenyusutan->add(new \DateInterval('P'.$jumlahHariHabisPenyusutan.'D'));

            if ($now > $tanggalHabisPenyusutan) {
                $this->info('Masa Penyusutan sudah habis');
                continue;
            }

            $dateInterval          = $now->diff($tanggalAsset);
            $umurHari              = intval($dateInterval->format("%a"));
            $umurHari              = $umurHari > 0 ? $umurHari : 0;
            $jumlahHariTengahBulan = 15;
            $hariKeDalamSebulan    = $umurHari % $jumlahHariSebulan;

            // pengecekan bahwa asset sudah lewat satu bulan
            // dengan asumsi cron dijalankan sehari sekali
            if ($hariKeDalamSebulan != $jumlahHariTengahBulan + 1) {
                $this->info('Belum waktunya penyusutan');
                continue;
            }

            $journalHeader                 = new JournalHeader();
            $journalHeader->category       = JournalHeader::DEPRECIATION;
            $journalHeader->status         = JournalHeader::OPEN;
            $journalHeader->period         = $period;
            $journalHeader->description    = $asset->asset_number;
            $journalHeader->branch_id      = $asset->branch_id;
            $journalHeader->journal_date   = $now;
            $journalHeader->created_date   = $now;
            $journalHeader->journal_number = JournalService::getJournalNumber($journalHeader);
            $journalHeader->save();

            // insert journal line debit
            $combination    = AccountCombinationService::getCombination($asset->category->depreciation->coa_code, null, $asset->branch_id);

            $journalLine                         = new JournalLine();
            $journalLine->journal_header_id      = $journalHeader->journal_header_id;
            $journalLine->account_combination_id = $combination->account_combination_id;
            $journalLine->debet                  = $penyusutan;
            $journalLine->credit                 = 0;
            $journalLine->description            = 'Beban Penyusutan';
            $journalLine->created_date           = $now;
            $journalLine->save();

            // insert journal line credit
            $journalLine    = new JournalLine();
            $combination    = AccountCombinationService::getCombination($asset->category->acumulated->coa_code, null, $asset->branch_id);

            $journalLine->journal_header_id      = $journalHeader->journal_header_id;
            $journalLine->account_combination_id = $combination->account_combination_id;
            $journalLine->debet                  = 0;
            $journalLine->credit                 = $penyusutan;
            $journalLine->description            = 'Akumulasi Penyusutan';
            $journalLine->created_date           = $now;
            $journalLine->save();

            $this->info('Nominal: '.number_format($penyusutan).'. Journal Number: '.$journalHeader->journal_number);
        }
    }

    protected function getActiveAssets()
    {
        $query = \DB::table('ast.addition_asset')
                        ->where(function($query) {
                            $query->where('status_id', '=', AdditionAsset::ACTIVE)
                                    ->orWhere('status_id', '=', AdditionAsset::ONSERVICE);
                        })
                        ->orderBy('asset_id', 'asc');

        return $query->get();
    }
}
