<?php

class Alt_Test extends PHPUnit_Framework_TestCase {
    public $url = "";
    public $route = "";
    public $api;

    public function _construct(){
        $this->api = new Alt_Api($this->url, $this->route);
    }

    public function connect($url, $data = array()){
        return $this->api->connect($url, $data);
    }
}