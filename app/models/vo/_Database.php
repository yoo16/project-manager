<?php
/**
 * Database 
 * 
 * @create  2017/08/21 13:46:26 
 */

require_once 'PwPgsql.php';

class _Database extends PwPgsql {

    public $id_column = 'id';
    public $name = 'databases';
    public $entity_name = 'database';

    public $columns = [
        'created_at' => ['type' => 'timestamp'],
        'current_version' => ['type' => 'int4'],
        'hostname' => ['type' => 'varchar', 'is_required' => true],
        'is_lock' => ['type' => 'bool'],
        'name' => ['type' => 'varchar', 'is_required' => true],
        'port' => ['type' => 'int4', 'is_required' => true],
        'password' => ['type' => 'varchar', 'length' => 256],
        'type' => ['type' => 'varchar'],
        'updated_at' => ['type' => 'timestamp'],
        'user_name' => ['type' => 'varchar', 'is_required' => true],
    ];

    public $primary_key = 'databases_pkey';

    public $unique = [
            'databases_name_hostname_key' => [
                        'name',
                        'hostname',
                        ],
    ];
    public $index_keys = [
    'databases_name_hostname_key' => 'CREATE UNIQUE INDEX databases_name_hostname_key ON databases USING btree (name, hostname)',
    'databases_pkey' => 'CREATE UNIQUE INDEX databases_pkey ON databases USING btree (id)',
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