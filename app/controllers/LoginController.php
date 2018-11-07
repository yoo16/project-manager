<?php

/**
 * LoginController 
 *
 * @author  Yohei Yoshikawa
 * @create  2018/10/03 
 */
require_once 'AppController.php';

class LoginController extends AppController
{

    public $layout = 'login';
    public $auth_controller = 'login';
    public $auth_model = 'Staff';
    public $auth_top_controller = 'project';

    function before_action($action)
    {
        parent::before_action($action);
    }

    /**
     * index
     *
     * @param
     * @return void
     */
    function action_index()
    {
    }

}