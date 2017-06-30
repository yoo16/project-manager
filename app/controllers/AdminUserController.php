<?php
/**
 * AdminUserController 
 *
 * @copyright 2017 copyright Yohei Yoshikawa (http://yoo-s.com)
 */
require_once 'AdminController.php';

class AdminUserController extends AdminController {

    var $name = 'admin_user';
    var $layout = null;

    function before_action($action) {
        parent::before_action($action);
    }

   /**
    * トップページ
    *
    * @param
    * @return void
    */ 
    function index() {
        $this->layout = 'admin';
    }

   /**
    * 
    *
    * @param
    * @return void
    */ 
    function action_list() {
        $limit = 10;
        $offset = 0;
        $user = new User();
        //$user->id_index = true;
        $this->users = $user//->order('created_at', true)
                            ->order('email')
                            //->offset($offset)
                            //->limit($limit)
                            ->select();

        $this->count = $user->count();
    }

   /**
    * 
    *
    * @param
    * @return void
    */ 
    function action_new() {
        $user = new User();
        $this->user = $user->value;
    }

   /**
    * 
    *
    * @param
    * @return void
    */ 
    function action_edit() {
        $user = new User();
        $user->fetch($_REQUEST['id']);
        $this->user = $user->value;

        //$user = new User();
        //$this->users = $user->select();
        //$this->forms['question']['name'] = 'authority';
    }

   /**
    * 
    *
    * @param
    * @return void
    */ 
    function action_update() {
        $this->isRequestPost();

        $posts = json_decode($_REQUEST['user'], true);
        if ($posts['password']) $posts['password'] = hash('sha256', $posts['password'], false);

        $user = new User();
        $user->fetch($posts['id']);
        $user->takeValues($posts);
        $is_success = $user->save();

        if ($user->errors) {
            $values['errors'] = $user->errors;
        }
        $values['user'] = $user->value;
        $json = json_encode($values);
        echo($json);
        exit;
    }

   /**
    * 
    *
    * @param
    * @return void
    */ 
    function action_delete() {
        $this->isRequestPost();

        $posts = json_decode($_REQUEST['user'], true);

        $user = new User();
        $user->fetch($posts['id']);
        if ($user->value) $user->delete();
        
        $values['errors'] = $user->errors;
        $json = json_encode($values);
        echo($json);
        exit;
    }

}

?>
