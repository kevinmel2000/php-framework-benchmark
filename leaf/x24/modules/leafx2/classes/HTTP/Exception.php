<?php
/**
 * Created by JetBrains PhpStorm.

 * Date: 10/16/13
 * Time: 3:47 PM
 * To change this template use File | Settings | File Templates.
 */
class HTTP_Exception extends Kohana_HTTP_Exception {

    /**
     * Generate a Response for all Exceptions without a more specific override
     *
     * The user should see a nice error page, however, if we are in development
     * mode we should show the normal Kohana error page.
     *
     * @return Response
     */
    public function get_response()
    {
        // Lets log the Exception, Just in case it's important!
        Kohana_Exception::log($this);

        if (Kohana::$environment >= Kohana::DEVELOPMENT)
        {
            // Show the normal Kohana error page.
            return parent::get_response();
        }
        else
        {
            // Generate a nicer looking "Oops" page.
            $view = View::factory('Errors/default');
            $view->error_code = 888;
            $view->error_rant = "something's not right here";
            $view->error_message = "and I don't know why :(";

            $response = Response::factory()
                ->status($this->getCode())
                ->body($view->render());

            return $response;
        }
    }
}