<?php
/**
 * Admin
 *
 * @package 
 * @author  Yohei Yoshikawa
 * @create  2013-04-15 16:33:13
 */
require_once 'vo/_Admin.php';

class Admin extends _Admin {

    function __construct($params=null) {
        parent::__construct($params);        
    }

   /**
    * validate
    *
    * @param 
    * @return void
    */ 
    function validate() {
        parent::validate();
    }

}