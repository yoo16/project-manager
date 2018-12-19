<?php
/**
 * Admin 
 * 
 * @create  2017-10-18 12:58:52 
 */

//namespace project_manager;

require_once 'PwPgsql.php';

class _Admin extends PwPgsql {

    public $id_column = 'id';
    public $name = 'admins';
    public $entity_name = 'admin';

    public $columns = array(
        'created_at' => array('type' => 'timestamp'),
        'email' => array('type' => 'varchar', 'length' => 256),
        'first_name' => array('type' => 'varchar', 'length' => 64),
        'last_name' => array('type' => 'varchar', 'length' => 64),
        'login_name' => array('type' => 'varchar', 'length' => 256),
        'memo' => array('type' => 'text'),
        'password' => array('type' => 'varchar', 'length' => 256),
        'sort_order' => array('type' => 'int2'),
        'tmp_password' => array('type' => 'varchar', 'length' => 256),
        'updated_at' => array('type' => 'timestamp'),
    );

    public $primary_key = 'admins_pkey';

    public $unique = array(
            'admins_email_key' => [
                        'email',
                        ],
            'admins_login_name_key' => [
                        'login_name',
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