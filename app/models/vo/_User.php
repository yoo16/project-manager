<?php
/**
 * User 
 * 
 * @create  2017-10-18 12:58:58 
 */

//namespace project_manager;

require_once 'PgsqlEntity.php';

class _User extends PgsqlEntity {

    public $id_column = 'id';
    public $name = 'users';
    public $entity_name = 'user';

    public $columns = array(
        'birthday_at' => array('type' => 'timestamp'),
        'created_at' => array('type' => 'timestamp'),
        'email' => array('type' => 'varchar', 'length' => 256),
        'first_name' => array('type' => 'varchar', 'length' => 64),
        'first_name_kana' => array('type' => 'varchar', 'length' => 64),
        'last_name' => array('type' => 'varchar', 'length' => 64),
        'last_name_kana' => array('type' => 'varchar', 'length' => 64),
        'login_name' => array('type' => 'varchar', 'length' => 64),
        'memo' => array('type' => 'text'),
        'password' => array('type' => 'varchar', 'length' => 256),
        'sort_order' => array('type' => 'int2'),
        'tmp_password' => array('type' => 'varchar', 'length' => 256),
        'updated_at' => array('type' => 'timestamp'),
    );

    public $primary_key = 'users_pkey';

    public $unique = array(
            'users_email_key' => [
                        'email',
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