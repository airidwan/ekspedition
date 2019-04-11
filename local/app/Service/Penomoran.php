<?php

namespace App\Service;

class Penomoran
{
    public static function getStringNomor($number, $digit)
    {
        $string = '';
        for ($i=0; $i < $digit - strlen($number); $i++) {
            $string .= '0';
        }

        return $string.$number;
    }
}
