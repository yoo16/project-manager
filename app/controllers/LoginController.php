<?php

/**
 * LoginController 
 *
 * @author  Yohei Yoshikawa
 * @create  2012/10/03 
 */
require_once 'AppController.php';

class LoginController extends AppController
{

    public $layout = 'login';
    public $name = 'login';
    public $auth_top_controller = 'project';

    function before_action($action)
    {
        parent::before_action($action);
    }

    /**
     * login index
     *
     * @return void
     */
    function index() {
        $this->pwLogin();
    }

    /**
     * auth
     *
     * @param  
     * @return void
     **/
    function auth()
    {
        $this->redirectAuthTop($action);
        exit;
    }

}