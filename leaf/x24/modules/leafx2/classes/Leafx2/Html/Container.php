<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Leaf Html Container class
 *
 * @package    Html

 * @copyright  (c) 2010
 */
class Leafx2_Html_Container {

    // the html body data
    protected $data;
    
    // Singleton static instance
	protected static $instance;

    /**
     * Returns a singleton instance of the class
     *
     * @return  Leafx2_Html_Container
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
        $this->data = array();
    }
    
    /**
     * enforcing singleton behaviour
     */
    private function __clone() {
    }

    /**
     * add to container
     * @param string $key name of container
     * @param string $content content to fill up
     * @access public
     * @return void
     */
    public function add($key,$content) {
    	if (isset($this->data[$key]))
    	    $this->data[$key] .= $content;
    	else
    	    $this->data[$key] = $content;
    }
    
    /**
     * get all data
     * @return array $data Array of container
     * @access public
     */
    public function get() {
        return $this->data;
    }
    
} // End 
