<?php
defined('SYSPATH') or die('No direct script access.');

class Controller_Public extends Controller_Leafx2 {
    
    public function action_index() {
        Leafx2::$output = Leafx2::OUTPUT_AJAX;

        echo "Hello World!";

        require $_SERVER['DOCUMENT_ROOT'].'/php-framework-benchmark/libs/output_data.php';
    }
}