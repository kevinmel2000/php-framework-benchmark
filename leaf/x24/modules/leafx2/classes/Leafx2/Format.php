<?php
/**
 * Class Leafx2 Format
 *
 * class for many utilities
 *
 * @version  1.0
 * @author   Agung Prihanggoro <agung@.co.id>
 */
class Leafx2_Format {
    
    public static function number_format($num) {
        return number_format($num,0,',','.');
    }
}
?>