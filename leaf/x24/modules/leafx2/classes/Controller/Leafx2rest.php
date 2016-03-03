<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Abstract leafx2 controller class as default template
 *
 * @package    Controller

 * @copyright  (c) 2014
 */
abstract class Controller_Leafx2rest extends Controller {

    /**
     * @var bool continue execution
     */
    protected $runaction = TRUE;
    /**
     * @var array blank return
     */
    protected $ret = array(
        "v"   => 100,
        "s"   => 0,
        "m"   => "",
        "t"   => "",
        "u"   => "",
        "d"   => null
    );
    /**
     * @var int time start
     */
    protected $timestart;
    /**
     * @var int time stop
     */
    protected $timestop;

    /**
     * Do the preAction
     *
     * @return  void
     */
    public function before() {
        // start timer
        list($usec, $sec) = explode(" ", microtime());
        $this->timestart = ((float)$usec + (float)$sec);

        // add on plugins
        $plugins = Kohana::$config->load("leafx2.pre_service");
        if (is_array($plugins)) foreach ($plugins as $plugin) {
            $filename = "plugins/$plugin.php";
            if (file_exists($filename))
                include_once("plugins/$plugin.php");
        }

        // last, turn on output buffering
        ob_start();
        parent::before();
    }

    /**
     * Executes the given action and calls the [Controller::before] and [Controller::after] methods.
     *
     * Can also be used to catch exceptions from actions in a single place.
     *
     * 1. Before the controller action is called, the [Controller::before] method
     * will be called.
     * 2. Next the controller action will be called.
     * 3. After the controller action is called, the [Controller::after] method
     * will be called.
     *
     * @throws  HTTP_Exception_404
     * @return  Response
     */
    public function execute()
    {
        // Execute the "before action" method
        $this->before();

        // continue to run action?
        if ($this->runaction) {
            // Determine the action to use
            $action = 'action_'.$this->request->action();

            // If the action doesn't exist, it's a 404
            if ( ! method_exists($this, $action))
            {
                throw HTTP_Exception::factory(404,
                    'The requested URL :uri was not found on this server.',
                    array(':uri' => $this->request->uri())
                )->request($this->request);
            }

            // Execute the action itself
            $this->{$action}();
        }

        // Execute the "after action" method
        $this->after();

        // Return the response
        return $this->response;
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

        // sending headers
        $this->response->headers("Content-Type","application/json");
        $this->response->headers("Access-Control-Allow-Origin","*");
        $this->response->headers("Access-Control-Allow-Methods","*");
        $this->response->headers("Access-Control-Allow-Headers","*");

        // select output type based on application need
        $this->response->body(json_encode($this->ret));
        // parent task
        parent::after();
    }

    protected function send() {
        if (Kohana::$environment == Kohana::DEVELOPMENT) {
            list($usec, $sec) = explode(" ", microtime());
            $this->timestop = ((float)$usec + (float)$sec);
            $this->ret["t"] = round($this->timestop - $this->timestart,4);
            $this->ret["u"] = memory_get_peak_usage(true) / 1000;
        }
    }

    protected function send_response($data) {
        $this->ret["d"] = $data;
        $this->send();
    }

    protected function send_error($code,$message) {
        $this->ret["s"] = $code;
        $this->ret["m"] = $message;
        $this->send();
    }


} // End
