<?php
/**
 * User 
 * 
 * @create  2019/08/29 12:24:09 
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
    'users_pkey' => 'CREATE UNIQUE INDEX users_pkey ON public.users USING btree (id)',
    'users_email_key' => 'CREATE UNIQUE INDEX users_email_key ON public.users USING btree (email)',
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