<?php
/**
 * RootController 
 *
 * @author  Yohei Yoshikawa
 * @create  2012/10/03 
 */
require_once 'AppController.php';

class RootController extends AppController {

    function before_action($action) {
        parent::before_action();
    }

    function index() {
        $this->redirect_to('project/');
    }

}