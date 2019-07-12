<?php
/**
 * User 
 * 
 * @create  2017/10/18 12:58:58 
 */

require_once 'PwPgsql.php';

class _User extends PwPgsql {

    public $id_column = 'id';
    public $name = 'users';
    public $entity_name = 'user';

    public $columns = [
        'birthday_at' => ['type' => 'timestamp'],
        'created_at' => ['type' => 'timestamp'],
        'email' => ['type' => 'varchar', 'length' => 256],
        'first_name' => ['type' => 'varchar', 'length' => 64],
        'first_name_kana' => ['type' => 'varchar', 'length' => 64],
        'last_name' => ['type' => 'varchar', 'length' => 64],
        'last_name_kana' => ['type' => 'varchar', 'length' => 64],
        'login_name' => ['type' => 'varchar', 'length' => 64],
        'memo' => ['type' => 'text'],
        'password' => ['type' => 'varchar', 'length' => 256],
        'sort_order' => ['type' => 'int2'],
        'tmp_password' => ['type' => 'varchar', 'length' => 256],
        'updated_at' => ['type' => 'timestamp'],
    ];

    public $primary_key = 'users_pkey';

    public $unique = [
            'users_email_key' => [
                        'email',
                        ],
    ];
    public $index_keys = [
    'users_email_key' => 'CREATE UNIQUE INDEX users_email_key ON users USING btree (email)',
    'users_pkey' => 'CREATE UNIQUE INDEX users_pkey ON users USING btree (id)',
    ];


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