<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Leaf Html Tabcontainer class
 *
 * @package    Html

 * @copyright  (c) 2010
 */
class Leafx2_Html_Tabcontainer {
	/**
     * contents
	 * @var string
     */
	private $contents = array();
	

	/**
	 * constructor
	 * @return void
	 **/
	public function __construct (){
	}
	
	/**
	 * destructor
	 * @return void
	 **/
	public function __destruct(){
	}
	
	public function add($caption, $content){
		$this->contents[$caption] = $content;
	}
	
	/**
	 * @return string
	 **/
	public function get(){
		$buffer = '<ul class="nav nav-tabs" role="tablist">';
        $tablist = $tabpane = "";
        $first = true;
        if (count($this->contents) > 0) foreach ($this->contents as $caption => $content) {
            $key = preg_replace('/[^a-zA-Z0-9]+/','',$caption);
            $tablist .= "<li".($first? " class='active'" : "")."><a href='#tab_$key' role='tab' data-toggle='tab'><b>$caption</b></a></li>";
            $tabpane .= "<div class='tab-pane".($first? " active" : "")."' id='tab_$key'>$content</div>";
            $first = false;
        }

		return '<div class="container"><ul class="nav nav-tabs" role="tablist">'.$tablist.'</ul><div class="tab-content">'.$tabpane.'</div></div>';
	}
	
	/**
	 * TabContainer::display()
	 * display tabContainer with it's tab pane(s)
	 * @return void
	 **/
	public function display(){
		echo $this->get();
	}
    
} // End 

