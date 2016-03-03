<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Leaf Html Header class
 *
 * @package    Html

 * @copyright  (c) 2010
 */
class Leafx2_Html_Header {

    // the html head data (css, meta and script)
    protected $data;
    
    // Singleton static instance
	protected static $instance;

    /**
     * Returns a singleton instance of class
     *
     * @return  Leafx2_Html_Header
     */
    public static function instance() {
        if (self::$instance === NULL) {
            // Create a new instance
            self::$instance = new self;
        }
        return self::$instance;
    }

    /**
     * Cronstructing class
     * enforcing singleton behaviour
     * @return void
     */
    private function __construct() {
        $this->data['css']['fetch']     = array();
        $this->data['css']['path']      = array();
        $this->data['script']['fetch']  = array();
        $this->data['script']['path']   = array();
    }
    
    /**
     * enforcing singleton behaviour
     */
    private function __clone() {
    }

    /**
     * add css header
     * @param string $path Path to add
     */
    public function add_css($path) {
        if (!in_array($path,$this->data['css']['path'])) {
            $this->data['css']['path'][] = $path;
            $this->data['css']['fetch'][] = "<link rel='stylesheet' href='".Leafx2::$base."$path' type='text/css' />";
        }
    }
    
    /**
     * add script header
     * @param string $path Path to add
     */
    public function add_script($path) {
        if (!in_array($path,$this->data['script']['path'])) {
            $this->data['script']['path'][] = $path;
            $this->data['script']['fetch'][] = "<script src='".Leafx2::$base."$path' language='JavaScript' type='text/javascript'></script>";
        }
    }

    /**
     * get css/script header as data
     * @return array data
     */
    public function get($type) {
        return $this->data[$type];
    }
    
    /**
     * get css/script header as string
     * @return string header
     */
    public function getAsString($type) {
        $str = "";
        if (is_array($this->data[$type]['fetch'])) 
            foreach ($this->data[$type]['fetch'] as $item) {
                $str .= $item."\n";
        }
        return $str;
    }
    
} // End 
