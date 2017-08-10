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
    var $entity_name = 'user_project_setting';

    var $columns = array(
        'created_at' => array('type' => 'timestamp', 'option' => 'NULL DEFAULT CURRENT_TIMESTAMP'),
        'updated_at' => array('type' => 'timestamp'),
        'sort_order' => array('type' => 'int4'),
        'user_id' => array('type' => 'int4'),
        'project_id' => array('type' => 'int4', 'is_required' => true),
        'project_path' => array('type' => 'varchar', 'length' => 256, 'is_required' => true),
        'group_name' => array('type' => 'varchar', 'length' => 256),
        'user_name' => array('type' => 'varchar', 'length' => 256),
    );

    function __construct($params = null) {
        parent::__construct();        
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