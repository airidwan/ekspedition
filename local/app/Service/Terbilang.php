<?php

namespace App\Service;

class Terbilang
{
    public static function rupiah($number)
    {
          $angkaBilangan = array("", "satu", "dua", "tiga", "empat", "lima", "enam", "tujuh", "delapan", "sembilan", "sepuluh", "sebelas");
          if ($number < 0)
            $nominal = "min " . self::rupiah(abs($number));
          else if ($number < 12)
            $nominal = " " . $angkaBilangan[$number];
          elseif ($number < 20)
            $nominal = self::rupiah($number - 10) . "belas";
          elseif ($number < 100)
            $nominal = self::rupiah($number / 10) . " puluh" . self::rupiah($number % 10);
          elseif ($number < 200)
            $nominal = " seratus" . self::rupiah($number - 100);
          elseif ($number < 1000)
            $nominal = self::rupiah($number / 100) . " ratus" . self::rupiah($number % 100);
          elseif ($number < 2000)
            $nominal = " seribu" . self::rupiah($number - 1000);
          elseif ($number < 1000000)
            $nominal = self::rupiah($number / 1000) . " ribu" . self::rupiah($number % 1000);
          elseif ($number < 1000000000)
            $nominal = self::rupiah($number / 1000000) . " juta" . self::rupiah($number % 1000000);
          elseif ($number < 1000000000000)
            $nominal = self::rupiah($number / 1000000000) . " milyar" . self::rupiah($number % 1000000000);

        return $nominal;
    }
}
