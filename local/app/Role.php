<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $connection   = 'adm';
    protected $fillable     = ['name'];

    const ADMINISTRATOR     = 1;
    const BRANCH_MANAGER    = 2;
    const OPERATIONAL_ADMIN = 5;
    const WAREHOUSE_ADMIN   = 4;
    const PURCHASING_ADMIN  = 13;
    const FINANCE_ADMIN     = 6;
    const WAREHOUSE_MANAGER = 3;
    const CASHIER           = 7;
    const OPERATOR          = 15;

    /**
     * Get the accessControls for the blog post.
     */
    public function accessControls()
    {
        return $this->hasMany('App\AccessControl');
    }

    public function canAccess($resource, $privilege)
    {
        foreach ($this->accessControls as $access) {
            if ($access->resource == $resource && $access->privilege == $privilege && $access->access) {
                return true;
            }
        }

        return false;
    }
}
