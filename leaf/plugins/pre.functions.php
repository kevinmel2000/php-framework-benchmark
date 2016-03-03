<?php defined('SYSPATH') or die('No direct script access.');
    /**
     * define custom functions
     *
     */


    function title2sef ($str) {
        $res = "";
        $lastChar = "";
        for ($i = 0; $i < strlen($str); $i++) {
            $idx = ord($str{$i});
            if (($idx >= 48 && $idx <= 57)
                || ($idx >= 65 && $idx <= 90)
                || ($idx >= 97 && $idx <= 122)
                //|| $idx == 32
                ) {
                $res .= $str{$i};
                $lastChar = $str{$i};
            }
            else {
                $res .= $lastChar == "_"? "" : "_";
                $lastChar = "_";
            }
        }
        return $res;
    }

    function numformat($num) {
        if ($_GET['mode'] == "excel")
            return $num;
        else
            return number_format($num,0,',','.');
    }
    