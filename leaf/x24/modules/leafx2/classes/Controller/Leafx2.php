<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Abstract leafx2 controller class as default template
 *
 * @package    Controller

 * @copyright  (c) 2010
 */
abstract class Controller_Leafx2 extends Controller {

    /**
     * @var  boolean  auto render template
     **/
    protected $auto_render = TRUE;
    
    protected $benchmark = array();
    
    protected $db;
    
    protected $html_header;
    
    protected $html_container;
    
    public $tpl_public = "default.public.htm";
    
    public $tpl_internal = "default.internal.htm";
    
    protected $view; // view object
    
    /**
     * Do the preAction
     *
     * @return  void
     */
    public function before() {
        // starting up the database
        $this->db = Database::instance();

        // set static requested controller / action
        Leafx2::$action     = $this->request->action();
        Leafx2::$base       = URL::base();
        Leafx2::$baseaction = URL::base() .($this->request->directory()==""? "" : $this->request->directory()."/").$this->request->controller() ."/". $this->request->action();
        Leafx2::$basecontroller = ($this->request->directory() == '') // check directory
            ? URL::base() . $this->request->controller()
            : URL::base() . $this->request->directory() ."/". $this->request->controller();
        Leafx2::$controller = $this->request->controller();
        Leafx2::$directory = $this->request->directory();
        Leafx2::$error_page = "public/error";
        Leafx2::$theme = Kohana::$config->load("leafx2.theme");

        // unset weird QSA-GET
        unset($_GET[Leafx2::$controller."/".Leafx2::$action]);

        // load init set for application
        if (file_exists('app.initialize.php'))
            include_once('app.initialize.php');

        // add on plugins
        $plugins = Kohana::$config->load("leafx2.pre_execution");
        if (is_array($plugins)) foreach ($plugins as $plugin) {
            $filename = "plugins/$plugin.php";
            if (file_exists($filename))
                include_once("plugins/$plugin.php");
        }

        // get the default view for this action
        $view_file = ($this->request->directory() == '') // check directory
                ? $this->request->controller() .DIRECTORY_SEPARATOR. "dsp.".$this->request->action()
                : $this->request->directory() .DIRECTORY_SEPARATOR. $this->request->controller().DIRECTORY_SEPARATOR."dsp.".$this->request->action();
        if (Kohana::find_file('views',$view_file) !== FALSE)
            $this->view = View::factory($view_file);
        
        if ($this->auto_render === TRUE) {
            // html header
            $this->html_header = Leafx2_Html_Header::instance();
            
            // html container
            $this->html_container = Leafx2_Html_Container::instance();
            $this->html_container->add('APP_TITLE',Kohana::$config->load('leafx2.window_title'));
            $this->html_container->add('HTML_TITLE',Kohana::$config->load('leafx2.html_title'));
        }
        // last, turn on output buffering
        ob_start();
        parent::before();
    }

    /**
     * Do the postAction
     *
     * @return  void
     */
    public function after() {
        // first of all, get buffer content from controller
        // note, if controller do "output"-ing something, its likely a debug message, or AJAX response
        $controller_output = ob_get_contents();
        ob_end_clean();
        
        // 2. check the response->body if they had content
        $response_output = $this->response->body();

        // 3. render the view -- only if auto_render = TRUE
        $view_output = '';
        if ($this->view != null && $this->auto_render) {
            $view_output = $this->view->render();
        } 

        // 4. injecting html -- only if auto_render = TRUE
        if ($this->auto_render) {
            $this->html_container->add('HTML_CONTENT',$view_output);
            $this->html_container->add('APP_BASE',Leafx2::$base);
        }

        // 5. load post plugins
        $plugins = Kohana::$config->load("leafx2.post_execution");
        if (is_array($plugins)) foreach ($plugins as $plugin) {
            $filename = "plugins/$plugin.php";
            if (file_exists($filename))
                include_once("plugins/$plugin.php");
        }

        // 6. add css and scripts -- only if auto_render = TRUE
        if ($this->auto_render) {
            $this->html_container->add('HTML_CSS',$this->html_header->getAsString('css'));
            $this->html_container->add('HTML_SCRIPT',$this->html_header->getAsString('script'));
        }
        
        // 7th, select output type based on application need
        switch (Leafx2::$output) {
            // output as html, format and get theme
            case Leafx2::OUTPUT_HTML :
                // output from action
                $this->html_container->add('HTML_DEBUG',$controller_output);
                $this->html_container->add('HTML_RESPONSE',$response_output);

                // load final set for application
                if (file_exists('app.finalize.php'))
                    include_once('app.finalize.php');
                
                // now preparing for injecting contents
                $content = $this->html_container->get();
                
                // load theme file, then inject contents
                $tfile = Leafx2_Auth::instance()->is_login() && file_exists('themes/'.Leafx2::$theme.'/'.$this->tpl_internal)? $this->tpl_internal : $this->tpl_public;
                $tfile = $_GET["x2print"]? "default.print.htm" : $tfile;
                $tpl = Leafx2_Filesystem::get('themes/'.Leafx2::$theme.'/'.$tfile);
                preg_match_all("/{([A-Za-z\_]*)}/",$tpl,$syn);
                foreach ($syn[1] as $key) {
                    $tpl = isset($content[$key]) ? str_replace("{".$key."}",$content[$key],$tpl) : str_replace("{".$key."}","",$tpl);
                }
                
                // last, return the html result
                $this->response->headers("Content-Encoding","gzip");
                $tpl = gzencode($tpl);

                $this->response->body($tpl);
                break;

            // output controller / ajax, display result as-is
            case Leafx2::OUTPUT_CONTROLLER :
            case Leafx2::OUTPUT_AJAX :
                $this->response->body($controller_output);
                break;

            // output by response, display result as-is
            case Leafx2::OUTPUT_RESPONSE :
                $this->response->body($response_output);
                break;
            
            // output none, display result as-is
            case Leafx2::OUTPUT_NONE :
                $this->response->body($view_output);
                break;
        }
        parent::after();
    }


} // End
