<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DummyHeader extends Model
{
    public function lines()
    {
        return $this->hasMany(DummyLine::class);
    }
}
