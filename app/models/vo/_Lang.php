<?php
/**
 * Lang 
 * 
 * @create  2017-10-03 03:28:17 
 */

//namespace project_manager;

require_once 'PgsqlEntity.php';

class _Lang extends PgsqlEntity {

    public $id_column = 'id';
    public $name = 'langs';
    public $entity_name = 'lang';

    public $columns = array(
        'created_at' => array('type' => 'timestamp'),
        'lang' => array('type' => 'varchar', 'length' => 8, 'is_required' => true),
        'name' => array('type' => 'varchar', 'length' => 256, 'is_required' => true),
        'sort_order' => array('type' => 'int4'),
        'updated_at' => array('type' => 'timestamp'),
    );

    public $primary_key = 'langs_pkey';

    public $unique = array(
            'langs_lang_key' => [
                        'lang',
                        ],
            'langs_name_key' => [
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