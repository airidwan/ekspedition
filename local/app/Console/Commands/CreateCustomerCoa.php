<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Requests;
use Illuminate\Http\Request;
use App\Modules\Operational\Model\Master\MasterCustomer;
use App\Modules\Generalledger\Model\Master\MasterCoa;
use App\Service\Penomoran;

class CreateCustomerCoa extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cron:create-customer-coa';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create customer coa';
    protected $now;
    const DESC     = 'CUSTOMER';


    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->now = new \DateTime();

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
        $models = MasterCustomer::whereNull('subaccount_code')->take(500)->get();
        foreach ($models as $model) {
            
            $this->info($model->customer_code);
            $this->info($model->customer_name);

            $count   = \DB::table('gl.mst_coa')->where('segment_name','=',MasterCoa::SUB_ACCOUNT)->count();
            $codeSub = Penomoran::getStringNomor($count+1, 5);
            $model->subaccount_code  = $codeSub;

            $modelCoa = new MasterCoa();

            $modelCoa->description  = $model->customer_name.' ('.self::DESC.')';

            $modelCoa->segment_name = MasterCoa::SUB_ACCOUNT;

            $modelCoa->coa_code     = $codeSub;
            $modelCoa->created_date = $this->now;
            $modelCoa->created_by   = $model->created_by;
            $modelCoa->active       = 'Y';

            $model->save();
            $modelCoa->save();
        }
    }
}
