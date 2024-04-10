<?php

namespace App\Helpers;




class Helper
{

    public static function generate_uniq_code($id)
    {
        $length = 5;
        $randomletter = strtoupper(substr(str_shuffle("abcdefghijklmnopqrstuvwxyz"), 0, $length));
        return $id . $randomletter . rand(10, 90);
    }

    public static function generate_uniq_code_for_otp($id)
    {
        $length = 5;
        $randomletter = strtoupper(substr(str_shuffle("0123456789"), 0, $length));
        return $id . $randomletter . rand(10, 90);
    }

    public static function generate_uniq_code_for_request_code($id)
    {
        $table_id = $id;
        $length = strlen($id);

        if ($length == 1) {

            $len = 3;
            $randomletter = strtoupper(substr(str_shuffle("0000"), 0, $len));
            $a = $randomletter . $table_id;
            $request_code = 'MR' . 'A' . $a;

        } elseif ($length == 2) {

            $len = 2;
            $randomletter = strtoupper(substr(str_shuffle("0000"), 0, $len));
            $a = $randomletter . $table_id;
            $request_code = 'MR' . 'B' . $a;

        } elseif ($length == 3) {

            $len = 1;
            $randomletter = strtoupper(substr(str_shuffle("0000"), 0, $len));
            $a = $randomletter . $table_id;
            $request_code = 'MR' . 'C' . $a;

        } elseif ($length == 4) {

            $a = $table_id;
            $request_code = 'MR' . 'D' . $a;
        } elseif ($length >= 5) {

            $a = $table_id;
            $request_code = 'MR' . $a;
        }

        return $request_code;
    }

    public static function randomPassword()
    {
        $alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
        $pass = array(); //remember to declare $pass as an array
        $alphaLength = strlen($alphabet) - 1;
        for ($i = 0; $i < 10; $i++) {
            $n = rand(0, $alphaLength);
            $pass[] = $alphabet[$n];
        }
        return implode($pass); //turn the array into a string
    }

    
}
