<?php

namespace App\Modules\Inventory\Model\Master;

use Illuminate\Database\Eloquent\Model;
use App\Modules\Generalledger\Model\Master\MasterCoa;

class MasterCategory extends Model
{
    protected $connection = 'inventory';
    protected $table      = 'mst_category';
    protected $primaryKey = 'category_id';

    public $timestamps = false;

    const SP   = 'SP';
    const AST  = 'AST';
    const CSM  = 'CSM';
    const JS   = 'JS';

    const JSK  = 'JSK';
    const JTK  = 'JTK';
    const JOK  = 'JOK';
    const JPK  = 'JPK';
    const JPN  = 'JPN';
    const JTS  = 'JTS';
    const JBM  = 'JBM';
    const JKK  = 'JKK';
    const JP   = 'JP';
    const JBB  = 'JBB';
    const JAS  = 'JAS';
    const JAB  = 'JAB';
    const JTP  = 'JTP';

    const STOCK = [self::SP, self::CSM];
    

    const ASSET_ID = 11;

    public function coa(){
        return $this->belongsTo(MasterCoa::class, 'coa_id');
    }

}
