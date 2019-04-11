<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DummyLine extends Model
{
    protected $fillable = array('kolom_string', 'kolom_select', 'kolom_currency', 'kolom_date');

    public function user()
    {
       return $this->belongsTo(DummyHeader::class);
    }
}
