<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AccessControl extends Model
{
    protected $connection = 'adm';
    protected $fillable   = array('resource', 'privilege', 'access');

    /**
     * Get the role of the access.
     */
    public function role()
    {
        return $this->belongsTo('App\Role');
    }
}
