<?php
/**
 * Controller 
 *
 * Copyright (c) 2013 Yohei Yoshikawa (http://yoo-s.com/)
 */

require_once 'PgsqlEntity.php';

class _UserProjectSetting extends PgsqlEntity {

    var $id_column = 'id';
    var $name = 'user_project_settings';
    static $entity_name = 'user_project_setting';

    var $columns = array(
        'created_at' => array('type' => 't'),
        'updated_at' => array('type' => 't'),
        'sort_order' => array('type' => 'i'),
        'project_id' => array('type' => 'i', 'required' => true),
        'project_path' => array('type' => 's', 'required' => true),
        'user_id' => array('type' => 'i'),
        'group_name' => array('type' => 's'),
        'user_name' => array('type' => 's'),
    );

    function __construct($params=null) {
        parent::__construct();        
        if ($params['pg_info']) $this->pg_info = $params['pg_info'];
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