<?php
/**
 * Class Leafx2 Util
 *
 * class for many utilities
 *
 * @version  1.0
 * @author   Agung Prihanggoro <agung@.co.id>
 */
class Leafx2_Util {

    /**
     * @return string
     */
    public static function generateid() {
        list($usec, $sec) = explode(' ', microtime());
        return md5($sec . $usec . uniqid("",true));
    }
}
?>