<?php
/**
 * Created by JetBrains PhpStorm.

 * Date: 10/16/13
 * Time: 3:46 PM
 * To change this template use File | Settings | File Templates.
 */

class HTTP_Exception_404 extends Kohana_HTTP_Exception_404 {

    /**
     * Generate a Response for the 404 Exception.
     *
     * The user should be shown a nice 404 page.
     *
     * @return Response
     */
    public function get_response() {
        $view = View::factory('Errors/default');

        // Remembering that `$this` is an instance of HTTP_Exception_404
        $view->message = $this->getMessage();

        $view->error_code = 404;
        $view->error_rant = "it's not my fault";
        $view->error_message = "This is not the droid, uh,.. err, not the page you looking for";
        if (Kohana::$environment == KOHANA::DEVELOPMENT)
            $view->error_message .= "<br><pre style='text-align:center'>".$this->getMessage()."</pre>";

        $response = Response::factory()
            ->status(404)
            ->body($view->render());

        return $response;
    }
}