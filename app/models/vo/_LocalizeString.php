<?php
/**
 * LocalizeString 
 * 
 * @create  2017-10-03 00:56:54 
 */

//namespace project_manager;

require_once 'PwPgsql.php';

class _LocalizeString extends PwPgsql {

    public $id_column = 'id';
    public $name = 'localize_strings';
    public $entity_name = 'localize_string';

    public $columns = array(
        'created_at' => array('type' => 'timestamp'),
        'label' => array('type' => 'text'),
        'name' => array('type' => 'varchar', 'length' => 256, 'is_required' => true),
        'project_id' => array('type' => 'int4', 'is_required' => true),
        'sort_order' => array('type' => 'int4'),
        'updated_at' => array('type' => 'timestamp'),
    );

    public $primary_key = 'localize_strings_pkey';
    public $foreign = array(
            'localize_strings_project_id_id_fkey' => [
                                  'column' => 'project_id',
                                  'class_name' => 'Project',
                                  'foreign_table' => 'projects',
                                  'foreign_column' => 'id',
                                  'cascade_update_type' => 'a',
                                  'cascade_delete_type' => 'a',
                                  ],
    );

    public $unique = array(
            'localize_strings_name_project_id_key' => [
                        'project_id',
                        'name',
                        ],
    );



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