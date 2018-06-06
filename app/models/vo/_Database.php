<?php
/**
 * Database 
 * 
 * @create  2017-08-21 13:46:26 
 */

//namespace project_manager;

require_once 'PgsqlEntity.php';

class _Database extends PgsqlEntity {

    public $id_column = 'id';
    public $name = 'databases';
    public $entity_name = 'database';

    public $columns = array(
        'created_at' => array('type' => 'timestamp'),
        'current_version' => array('type' => 'int4'),
        'hostname' => array('type' => 'varchar', 'is_required' => true),
        'is_lock' => array('type' => 'bool'),
        'name' => array('type' => 'varchar', 'is_required' => true),
        'port' => array('type' => 'int4', 'is_required' => true),
        'type' => array('type' => 'varchar'),
        'updated_at' => array('type' => 'timestamp'),
        'user_name' => array('type' => 'varchar', 'is_required' => true),
    );

    public $primary_key = 'databases_pkey';

    public $unique = array(
            'databases_name_hostname_key' => [
                        'name',
                        'hostname',
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