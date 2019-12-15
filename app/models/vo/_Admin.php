<?php
/**
 * Admin 
 * 
 * @create  2019/08/29 12:24:05 
 */

require_once 'PwPgsql.php';

class _Admin extends PwPgsql {

    public $id_column = 'id';
    public $name = 'admins';
    public $entity_name = 'admin';

    public $columns = [
        'created_at' => ['type' => 'timestamp'],
        'email' => ['type' => 'varchar', 'length' => 256],
        'first_name' => ['type' => 'varchar', 'length' => 64],
        'last_name' => ['type' => 'varchar', 'length' => 64],
        'login_name' => ['type' => 'varchar', 'length' => 256],
        'memo' => ['type' => 'text'],
        'password' => ['type' => 'varchar', 'length' => 256],
        'sort_order' => ['type' => 'int2'],
        'tmp_password' => ['type' => 'varchar', 'length' => 256],
        'updated_at' => ['type' => 'timestamp'],
    ];

    public $primary_key = 'admins_pkey';

    public $unique = [
            'admins_email_key' => [
                        'email',
                        ],
            'admins_login_name_key' => [
                        'login_name',
                        ],
    ];
    public $index_keys = [
    'admins_pkey' => 'CREATE UNIQUE INDEX admins_pkey ON public.admins USING btree (id)',
    'admins_email_key' => 'CREATE UNIQUE INDEX admins_email_key ON public.admins USING btree (email)',
    'admins_login_name_key' => 'CREATE UNIQUE INDEX admins_login_name_key ON public.admins USING btree (login_name)',
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