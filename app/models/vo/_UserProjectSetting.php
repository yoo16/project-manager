<?php
/**
 * UserProjectSetting 
 * 
 * @create  2017-08-21 13:46:26 
 */

//namespace project_manager;

require_once 'PwPgsql.php';

class _UserProjectSetting extends PwPgsql {

    public $id_column = 'id';
    public $name = 'user_project_settings';
    public $entity_name = 'user_project_setting';

    public $columns = array(
        'created_at' => array('type' => 'timestamp'),
        'group_name' => array('type' => 'varchar', 'length' => 256),
        'project_id' => array('type' => 'int4', 'is_required' => true),
        'project_path' => array('type' => 'varchar', 'length' => 256, 'is_required' => true),
        'sort_order' => array('type' => 'int4'),
        'updated_at' => array('type' => 'timestamp'),
        'user_id' => array('type' => 'int4'),
        'user_name' => array('type' => 'varchar', 'length' => 256),
    );

    public $primary_key = 'user_project_settings_pkey';




    function __construct($params = null) {
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