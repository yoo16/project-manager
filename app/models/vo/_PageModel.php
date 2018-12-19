<?php
/**
 * PageModel 
 * 
 * @create  2017-10-03 18:45:29 
 */

//namespace project_manager;

require_once 'PwPgsql.php';

class _PageModel extends PwPgsql {

    public $id_column = 'id';
    public $name = 'page_models';
    public $entity_name = 'page_model';

    public $columns = array(
        'created_at' => array('type' => 'timestamp'),
        'is_fetch_list_values' => array('type' => 'bool'),
        'is_request_session' => array('type' => 'bool'),
        'model_id' => array('type' => 'int4', 'is_required' => true),
        'page_id' => array('type' => 'int4', 'is_required' => true),
        'sort_order' => array('type' => 'int4'),
        'updated_at' => array('type' => 'timestamp'),
        'where_model_id' => array('type' => 'int4'),
    );

    public $primary_key = 'page_models_pkey';
    public $foreign = array(
            'page_models_model_id_fkey' => [
                                  'column' => 'model_id',
                                  'class_name' => 'Model',
                                  'foreign_table' => 'models',
                                  'foreign_column' => 'id',
                                  'cascade_update_type' => 'a',
                                  'cascade_delete_type' => 'a',
                                  ],
            'page_models_page_id_fkey' => [
                                  'column' => 'page_id',
                                  'class_name' => 'Page',
                                  'foreign_table' => 'pages',
                                  'foreign_column' => 'id',
                                  'cascade_update_type' => 'a',
                                  'cascade_delete_type' => 'a',
                                  ],
    );

    public $unique = array(
            'page_models_model_id_page_id_key' => [
                        'model_id',
                        'page_id',
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