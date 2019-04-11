<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as BaseVerifier;

class VerifyCsrfToken extends BaseVerifier
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array
     */
    protected $except = [
        'tracking-resi',
        'price-lists',
        'calculate-price',
        'get-tracking-resi',
        'get-city',
        'get-calculate-price',
    ];
}
