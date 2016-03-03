<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Abstract leafx2 controller class as default template
 *
 * @package    Controller

 * @copyright  (c) 2010
 */
    ini_set('include_path', APPPATH.'vendor/'.PATH_SEPARATOR.ini_get('include_path'));
    include Kohana::find_file('vendor','Zend/Soap/AutoDiscover');
    include Kohana::find_file('vendor','Zend/Soap/Server');

abstract class Controller_Leafx2ws extends Controller {

    /**
     * @var  boolean  auto render template
     **/
    protected $auto_render = TRUE;
    
    protected $benchmark = array();
    
    protected $db;
    
    protected $html_header;
    
    protected $html_container;

    protected $service_type;

    protected $service_return;

    protected $service_group;

    protected $output_buffer;
    

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
        Leafx2::$base       = Url::base();
        Leafx2::$baseaction = Url::base() .($this->request->directory()==""? "" : $this->request->directory()."/").$this->request->controller() ."/". $this->request->action();
        Leafx2::$basecontroller = ($this->request->directory() == '') // check directory
            ? Url::base() . $this->request->controller()
            : Url::base() . $this->request->directory() ."/". $this->request->controller();
        Leafx2::$controller = $this->request->controller();
        Leafx2::$error_page = "public/error";
        Leafx2::$theme = Kohana::$config->load("leafx2.theme");

        // load init set for application
        if (file_exists('app.initialize.php'))
            include_once('app.initialize.php');

        if ($this->auto_render === TRUE) {
            // turn on output buffering
            ob_start();
        }
        // default service type
        $this->service_type = strtoupper($this->request->param("service_type",Leafx2::SERVICE_REST));
        $this->service_group = strtolower($this->request->param("service_group",""));
        // setting related to service type
        switch ($this->service_type) {
            case Leafx2::SERVICE_SOAP :
                include Kohana::find_file('classes','service/'.Leafx2::$controller);
                // disable wsdl cache
                ini_set('soap.wsdl_cache_enabled', '0');
                if (Leafx2::$action == "wsdl") {
                    $this->response->headers('Content-Type', 'text/xml');
                    //$wsdl = new Zend_Soap_AutoDiscover("Zend_Soap_Wsdl_Strategy_ArrayOfTypeComplex");
                    $wsdl = new Zend_Soap_AutoDiscover();
                    $wsdl->setOperationBodyStyle(array('use' => 'literal','namespace' => 'http://framework.zend.com'));
                    $wsdl->setUri('http://' . $_SERVER['HTTP_HOST'] . Leafx2::$base .'soap/'.Leafx2::$controller);
                    $wsdl->setClass('Service_'.Leafx2::$controller);
                    $wsdl->handle();
                }
                else {
                    $wsdl = 'http://' . $_SERVER['HTTP_HOST'] . Leafx2::$base . 'soap/'. Leafx2::$controller. '/wsdl';
                    $server = new SoapServer($wsdl);
                    $server->setClass('Service_'.Leafx2::$controller);
                    $server->handle();
                }
                break;
            case Leafx2::SERVICE_REST :
            default :
                break;
        }
        // This way, SOAP output won't confused with REST
        $this->output_buffer = ob_get_contents();
        ob_end_clean();

        if ($this->auto_render === TRUE) {
            // turn on output buffering
            ob_start();
        }
        parent::before();
    }

    /**
     * Do the postAction
     *
     * @return  void
     */
    public function after() {
        // first of all, get buffer content from controller
        // note, if controller do "output"-ing something, its likely a debug message
        $action_output = ob_get_contents();
        ob_end_clean();
        
        // select output by service type
        switch ($this->service_type) {
            case Leafx2::SERVICE_SOAP :
                $this->response->body($this->output_buffer);
                break;
            // default REST
            default :
            case Leafx2::SERVICE_REST :
                $this->response->body(json_encode($this->service_return));
                break;
        }
        parent::after();
    }


} // End
