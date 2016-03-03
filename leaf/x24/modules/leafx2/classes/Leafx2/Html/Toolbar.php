<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Leaf Html Toolbar class
 *
 * @package    Html

 * @copyright  (c) 2010
 */
class Leafx2_Html_Toolbar {

    /**
     * @var array Buttons and icons
     */
    private $data = array();

    /**
     * @var String Toolbar title
     */
    public $title;

    /**
     * @var String Toolbar main icon
     */
    public $icon;

    /**
     * Add button to toolbar
     * @param $name Button caption
     * @param $url
     * @param null $icon
     * @param null $title
     * @param null $addon
     */
    public function add($name,$url,$icon = null,$title = null,$addon = null) {
        $this->data[] = array('url' => $url, 'name' => $name,'icon' => $icon, 'title' => $title, 'addon' => $addon);
    }
    
    public function get() {
        $this->icon = $this->icon == NULL? "fa-bars" : $this->icon;
        $this->title = "<h2>".($this->title == NULL? "title" : $this->title)."</h2>";

        $buffer = "";
        if (count($this->data) > 0 && Leafx2::$output == Leafx2::OUTPUT_HTML) {
            foreach (array_reverse($this->data) as $item) {
            $buffer .= '<div class="toolbar-button"><a href="'
                     .  $item['url']
                     .  '"><img src="'.Leafx2::$base
                     .  $item['icon']
                     .  '" border="0" title="'
                     .  $item['title']
                     .  '" '
                     .  $item['addon']
                     .  '/>'
                     .  $item['name']
                     .  '</a></div>'."\n";
            }
        }
        else {
            $buffer = "&nbsp;";
        }
        
        $buffer = "
        <div class='toolbar row'>
            <div class='col-md-6'>
                <div class='col-md-1'><i class='fa $this->icon fa-3x'></i></div>
                <div class='col-md-11'>$this->title</div>
            </div>
            <div class='col-md-6'>$buffer</div>
        </div>
        ";
        return $buffer;
    }
    
    public function __toString() {
        return $this->get();
    }
    
    public function display() {
        echo $this->get();
    }
    
} // End 
    