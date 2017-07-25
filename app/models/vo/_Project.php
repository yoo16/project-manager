<?php
/**
 * Controller 
 *
 * Copyright (c) 2013 Yohei Yoshikawa (http://yoo-s.com/)
 */

require_once 'SqlEntity.php';

class _Project extends SqlEntity {

    var $id_column = 'id';
    var $name = 'projects';
    var $entity_name = 'project';

    var $columns = array(
        'created_at' => array('type' => 't'),
        'updated_at' => array('type' => 't'),
        'sort_order' => array('type' => 'i'),
        'database_id' => array('type' => 'i'),
        'name' => array('type' => 's', 'required' => true),
        'user_id' => array('type' => 'i'),
        'url' => array('type' => 's'),
        'dev_url' => array('type' => 's'),
        'entity_name' => array('type' => 's'),
        'is_public' => array('type' => 'b'),
        'external_project_id' => array('type' => 'i'),
        'is_export_external_model' => array('type' => 'b'),
        'is_autoload_model' => array('type' => 'b'),
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