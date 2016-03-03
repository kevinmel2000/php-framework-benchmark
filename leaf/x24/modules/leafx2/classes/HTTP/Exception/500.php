<?php
/**
 * Created by JetBrains PhpStorm.

 * Date: 10/16/13
 * Time: 3:46 PM
 * To change this template use File | Settings | File Templates.
 */

class HTTP_Exception_500 extends Kohana_HTTP_Exception_500 {

    /**
     * Generate a Response for the 500 Exception.
     *
     * The user should be shown a nice 500 page.
     *
     * @return Response
     */
    public function get_response() {
        $view = View::factory('Errors/default');

        // Remembering that `$this` is an instance of HTTP_Exception_404
        $view->message = $this->getMessage();

        $view->error_code = 500;
        $view->error_rant = "something's wrong with our server";
        $view->error_message = "probably another rat ate our server cable. Please wait patiently :)";

        $response = Response::factory()
            ->status(500)
            ->body($view->render());

        return $response;
    }
}