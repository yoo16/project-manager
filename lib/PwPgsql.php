<?php
/**
 * PwPgsql 
 *
 * @copyright  Copyright (c) 2017 Yohei Yoshikawa (https://github.com/yoo16/)
 */

//namespace Libs;

require_once 'PwEntity.php';

//TODO pg_escape_identifier
//TODO pg_escape_literal
//TODO pg_field_num, pg_field_name

class PwPgsql extends PwEntity
{
    public $db_info = null;
    public $pg_info = null;
    public $pg_info_array = null;
    public $dbname = null;
    public $host = 'localhost';
    public $user = 'postgres';
    public $port = 5432;
    public $password = null;
    public $is_pconnect = false;
    public $is_connect_forece_new = false;
    public $table_name = null;
    public $is_bulk_select = false;
    public $is_old_table = false;
    public $is_sort_order = true;
    public $is_sort_order_column = true;
    public $is_excute_sql = true;
    public $is_value_object = false;

    public static $pg_info_columns = ['dbname', 'user', 'host', 'port', 'password'];
    public static $constraint_keys = [
        'p' => 'Primary Key',
        'u' => 'Unique',
        'f' => 'Foreign Key',
        'c' => 'Check'
    ];
    public static $constraint_actions = [
        'a' => 'NO ACTION',
        'c' => 'CASCADE',
        'r' => 'RESTRICT',
        'n' => 'SET NULL',
        'd' => 'SET DEFAULT'
    ];

    public static $number_types = ['int2', 'int4', 'int8', 'float', 'float8', 'double', 'real'];

    /**
     * constructor
     * 
     * @param array $params
     * @return array
     */
    function __construct($params = null)
    {
        parent::__construct($params);
        if (!$params) {
            $this->defaultDBInfo();
        } else {
            $this->setDBInfo($params);
        }
        $this->table_name = $this->name;
        if (!$this->dbname) exit("Not Found: dbname");
    }

    /**
     * PostgreSQL version information
     * 
     * @return array
     */
    function pgVersion()
    {
        $connection = $this->connection();
        $values = pg_version($connection);
        return $values;
    }

    /**
     * initDb
     * 
     * @return array
     */
    static function initDb()
    {
        $path = BASE_DIR . "script/init_db";
        $cmd = "php {$path} 2>&1";
        exec($cmd, $output, $return);

        $results['cmd'] = $cmd;
        $results['output'] = $output;
        $results['return'] = $return;
        return $results;
    }

    /**
     * createDatabase
     * 
     * @return array
     */
    function createDatabase()
    {
        if (!$this->dbname) return;
        $cmd = "createdb -U {$this->user} -E UTF8 --host {$this->host} --port {$this->port} {$this->dbname} 2>&1";
        exec($cmd, $output, $return);

        $results['cmd'] = $cmd;
        $results['output'] = $output;
        $results['return'] = $return;
        return $results;
    }

    /**
     * sequenceName
     * 
     * @param string $table_name
     * @param string $id_column
     * @return string
     */
    function sequenceName($table_name, $id_column = 'id')
    {
        if (!$table_name) return;
        $sequence_name = "{$table_name}_{$id_column}_seq";
        return $sequence_name;
    }


    /**
     * index list
     * 
     * @param string $table_name
     * @param array $conditions
     * @param string $schama_name
     * @return string
     */
    function pgIndexesByTableName($table_name, $conditions = null, $schama_name = 'public')
    {
        $conditions[] = "tablename = '{$table_name}'";
        return $this->pgIndexes($conditions, $schama_name);
    }

    /**
     * index list
     * 
     * @param string $schama_name
     * @param array $conditions
     * @return string
     */
    function pgIndexes($conditions = null, $schama_name = 'public')
    {
        $sql = self::indexSql($conditions, $schama_name);
        return $this->fetchRows($sql);
    }
    
    /**
     * create sequence
     * 
     * @param string $table_name
     * @param string $id_column
     * @return string
     */
    function createSequence($table_name, $id_column = 'id')
    {
        $sequence_name = self::sequenceName($table_name, $id_column);
        $sql = "CREATE SEQUENCE {$sequence_name};";
        return $this->query($sql);
    }

    /**
     * create index
     * 
     * @param string $table_name
     * @param any $column_name
     * @return string
     */
    function createPgIndex($table_name, $column_name)
    {
        $sql = self::createPgIndexSql($table_name, $column_name);
        return $this->query($sql);
    }

    /**
     * Create INDEX SQL
     *
     * @param  string $table_name
     * @param  mixed $column_name
     * @return string
     */
    static function createPgIndexSql($table_name, $column_name) {
        if (!$table_name) return;
        if (!$column_name) return;
        if (is_array($column_name)) {
            $name = implode('_', $column_name);
            $index_name = "{$table_name}_{$name}";
            $column_name = $name = implode(',', $column_name);
        } else {
            $index_name = "{$table_name}_{$column_name}";
        }
        $sql = "CREATE INDEX {$index_name} ON {$table_name} ({$column_name});".PHP_EOL;
        return $sql;
    }

    /**
     * SQL index
     *
     * @param  string $table_name
     * @return string
     */
    static function indexSql($conditions = null, $schema_name = 'public')
    {
        $conditions[] = "schemaname = '{$schema_name}'";
        $condition = implode(' AND ', $conditions);
        $sql = "SELECT * FROM pg_indexes WHERE {$condition};".PHP_EOL;
        return $sql;
    }

    /**
     * sequenceName
     * 
     * @param string $table_name
     * @param string $id_column
     * @return string
     */
    function dropSequence($table_name, $id_column = 'id')
    {
        $sequence_name = self::sequenceName($table_name, $id_column);
        $sql = "DROP SEQUENCE {$sequence_name};";
        return $this->query($sql);
    }

    /**
     * reset sequence
     * 
     * @param string $table_name
     * @param string $id_column
     * @return string
     */
    function resetSequence($table_name, $id_column = 'id')
    {
        $sequence_name = self::sequenceName($table_name, $id_column);
        $sql = "SELECT SETVAL ('{$sequence_name}', '1', false);";
        return $this->query($sql);
    }

    /**
     * dropDatabase
     * 
     * @return array
     * @return PwPgsql
     */
    function dropDatabase()
    {
        if (!$this->dbname) return;
        $cmd = "dropdb -U {$this->user} --host {$this->host} --port {$this->port} {$this->dbname} 2>&1";
        exec($cmd, $output, $return);

        $results['cmd'] = $cmd;
        $results['output'] = $output;
        $results['return'] = $return;
        return $results;
    }

    /**
     * default pg info
     * 
     * @param $pg_info
     * @return PwPgsql
     */
    function defaultDBInfo()
    {
        if (!defined('DB_NAME') || !DB_NAME) exit('not found DB_NAME');
        if (defined('DB_NAME')) $this->dbname = DB_NAME;
        if (defined('DB_HOST')) $this->host = DB_HOST;
        if (defined('DB_PORT')) $this->port = DB_PORT;
        if (defined('DB_USER')) $this->user = DB_USER;
        if (defined('DB_PASS')) $this->password = DB_PASS;
        $this->loadDBInfo();
        return $this;
    }

    /**
     * database name
     * 
     * @param array $name
     * @return PwPgsql
     */
    function setDBName($name)
    {
        if ($name) {
            $this->dbname = $name;
            $this->loadDBInfo();
        }
        return $this;
    }

    /**
     * database host
     * 
     * @param array $host
     * @return PwPgsql
     */
    function setDBHost($host)
    {
        if ($host) {
            $this->host = $host;
            $this->loadDBInfo();
        }
        return $this;
    }

    /**
     * database user
     * 
     * @param array $user
     * @return PwPgsql
     */
    function setDBUser($user)
    {
        if ($user) {
            $this->user = $user;
            $this->loadDBInfo();
        }
        return $this;
    }

    /**
     * database port
     * 
     * @param array $user
     * @return PwPgsql
     */
    function setDBPort($port)
    {
        if ($port) {
            $this->port = $port;
            $this->loadDBInfo();
        }
        return $this;
    }

    /**
     * pgInfo
     * 
     * @param array $params
     * @return PwPgsql
     */
    function setDBInfo($params)
    {
        $this->db_info = $params;
        if (!$params) return $this;
        foreach ($params as $key => $value) {
            if (in_array($key, self::$pg_info_columns)) {
                $this->$key = $value;
            }
        }
        $this->loadDBInfo();
        return $this;
    }

    /**
     * load database information
     * 
     * @param array $params
     * @return PwPgsql
     */
    function loadDBInfo()
    {
        foreach (self::$pg_info_columns as $column) {
            if (isset($this->$column)) {
                $pg_infos[] = "{$column}={$this->$column}";
                $this->pg_info_array[$column] = $this->$column;
            }
        }
        if ($pg_infos) $this->pg_info = implode(' ', $pg_infos);
        return $this;
    }

    /**
     * set database information
     * 
     * @param $pg_info
     * @return void
     */
    function setDBInfoForString($pg_info_string)
    {
        if (!is_string($pg_info_string)) return;
        $pg_infos = explode(' ', $pg_info_string);
        if (!$pg_infos) return;
        foreach ($pg_infos as $pg_info) {
            $key_values = explode('=', $pg_info);
            $key = $key_values[0];
            $value = $key_values[1];
            if (in_array($key, self::$pg_info_columns)) {
                $this->$key = $value;
            }
        }
    }

    /**
     * SELECT use old column
     * 
     * @param array $values
     * @return string
     */
    function useOldTable()
    {
        $this->is_old_table = true;
        if ($this->old_name) {
            $this->selectOldColumns();
            $this->from($this->old_name);
        }
        return $this;
    }

    /**
     * column type SQL
     * 
     * @param array $values
     * @return string
     */
    static function columnTypeSql($values)
    {
        if ($values['length']) {
            $type = "{$values['type']}({$values['length']})";
        } else {
            $type = $values['type'];
        }
        $type = strtoupper($type);
        return $type;
    }

    /**
     * column option SQL
     * 
     * @param array $values
     * @return string
     */
    static function columnOptionSql($values)
    {
        $option = '';
        if ($values['is_required']) $option .= "NOT NULL";
        if ($values['is_default_null']) $option .= "DEFAULT NULL";
        return $option;
    }

    /**
     * create table SQL
     * 
     * @param array $models
     * @return string
     */
    function createTablesSQLFromModels($models)
    {
        if (!$models) return;
        $sql = '';
        foreach ($models as $model) {
            $sql .= $this->createTableSql($model);
            $sql .= PHP_EOL;
        }
        return $sql;
    }

    /**
     * create table SQL
     * 
     * @param PwPgsql $model
     * @return string
     */
    public function createTableSql($model)
    {
        if (!$model) return;

        $column_sqls[] = "{$model->id_column} SERIAL PRIMARY KEY NOT NULL";
        foreach ($model->columns as $column_name => $column) {
            if ($column['type']) {
                $type = self::columnTypeSql($column);
                $option = self::columnOptionSql($column);

                $column_sql = "{$column_name} {$type}";
                if ($option) $column_sql .= " {$option}";
                $column_sqls[] = $column_sql;
            }
        }
        $column_sql = implode("\n, ", $column_sqls);
        $sql = "CREATE TABLE IF NOT EXISTS \"{$model->name}\" (\n{$column_sql}\n);" . PHP_EOL;
        $sql .= PHP_EOL;

        return $sql;
    }

    /**
     * table exists
     * 
     * @param  stirng $table_name
     * @return resource
     */
    public function tableExists($table_name)
    {
        $sql = "SELECT relname FROM pg_class WHERE relkind = 'r' AND relname = '{$table_name}'";
        return $this->fetchRow($sql);
    }

    /**
     * create constraint SQL
     * 
     * @param PwPgsql $model
     * @param array $cascades
     * @return string
     */
    public function constraintSql($model)
    {
        if (!$model) return;
        $sql = '';
        if ($model->foreign) {
            foreach ($model->foreign as $conname => $foreign) {
                $sql.= "ALTER TABLE {$model->name}" . PHP_EOL;
                $sql.= "      ADD CONSTRAINT {$conname} FOREIGN KEY ({$foreign['column']})" . PHP_EOL;
                $sql.= "      REFERENCES {$foreign['foreign_table']}({$foreign['foreign_column']})" . PHP_EOL;

                $update_type = PwPgsql::$constraint_actions[$foreign['cascade_update_type']];
                $delete_type = PwPgsql::$constraint_actions[$foreign['cascade_delete_type']];

                if ($update_type) $sql.= "      ON UPDATE {$update_type}" . PHP_EOL;
                if ($delete_type) $sql.= "      ON DELETE {$delete_type}" . PHP_EOL;
                $sql.= ';';
                $sql .= PHP_EOL;
            }
        }
        if ($model->unique) {
            foreach ($model->unique as $conname => $uniques) {
                $unique_column = implode(', ', $uniques);
                $sql .= "ALTER TABLE {$model->name}" . PHP_EOL;
                $sql .= "      ADD CONSTRAINT {$conname}" . PHP_EOL;
                $sql .= "      UNIQUE ({$unique_column});" . PHP_EOL;
                $sql .= PHP_EOL;
            }
        }
        return $sql;
    }

    /**
     * create table SQL
     * 
     * @param string $table_name
     * @param array $columns
     * @return string
     */
    public function createTableSqlByName($table_name, $columns)
    {
        if (!$table_name) return;
        if (!$columns) return;
        foreach ($columns as $column_name => $column) {
            if ($column['type']) {
                $type = self::columnTypeSql($column);
                $option = $column['option'];

                $column_sqls[] = "{$column_name} {$type} {$option}";
            }
        }
        $column_sql = implode(",\n", $column_sqls);
        $sql = "CREATE TABLE IF NOT EXISTS \"{$table_name}\" (\n{$column_sql}\n);\n";

        return $sql;
    }

    /**
     * create tables for project
     * 
     * @return $sql
     */
    function createTablesSQLForProject()
    {
        $vo_path = BASE_DIR . "app/models/vo/";
        $sql = $this->createTablesSQLForPath($vo_path, 'php');
        return $sql;
    }

    /**
     * create table SQL
     * 
     * @param string $vo_path
     * @param string $ext
     * @return string
     */
    function createTablesSQLForPath($vo_path, $ext = 'php')
    {
        if (!file_exists($vo_path)) {
            $message = "Not exists : {$vo_path}";
            echo ($message);
            exit;
        }
        $vo_files_path = "{$vo_path}*.{$ext}";

        foreach (glob($vo_files_path) as $file_path) {
            if (is_file($file_path)) {
                $file = pathinfo($file_path);
                $class_name = $file['filename'];
                require_once $file_path;
                $vo = new $class_name();
                $sql .= $this->createTableSql($vo);
            }
        }

        //constraint
        $sql .= PHP_EOL;
        foreach (glob($vo_files_path) as $file_path) {
            if (is_file($file_path)) {
                $file = pathinfo($file_path);
                $class_name = $file['filename'];
                require_once $file_path;
                $vo = new $class_name();

                $sql .= $this->constraintSql($vo);
            }
        }
        return $sql;
    }

    /**
     * create tables
     * 
     * @return resource
     */
    function createTable($table_name, $columns)
    {
        $sql = $this->createTableSqlByName($table_name, $columns);
        return $this->query($sql);
    }

    /**
     * create tables for project
     * 
     * @return resource
     */
    function createTablesForProject()
    {
        $sql = $this->createTablesSQLForProject();
        return $this->query($sql);
    }

    /**
     * rename database
     * 
     * @param string $db_name
     * @param string $new_db_name
     * @return resource
     */
    public function renameDatabase($db_name, $new_db_name)
    {
        if (!$db_name) return;
        if ($db_name == $new_db_name) return;

        $sql = "ALTER DATABASE \"{$db_name}\" RENAME TO \"{$new_db_name}\";";
        return $this->query($sql);
    }

    /**
     * renameTable
     * 
     * @param string $table_name
     * @param string $new_table_name
     * @return resource
     */
    public function renameTable($table_name, $new_table_name)
    {
        if (!$table_name) return;
        if ($table_name == $new_table_name) return;

        $sql = "ALTER TABLE \"{$table_name}\" RENAME TO \"{$new_table_name}\";";
        return $this->query($sql);
    }

    /**
     * renameColumn
     * 
     * @param string $table_name
     * @param string $column
     * @param string $new_column
     * @return resource
     */
    public function renameColumn($table_name, $column, $new_column)
    {
        if (!$table_name) return;
        if (!$column) return;
        if ($column == $new_column) return;

        $sql = "ALTER TABLE \"{$table_name}\" RENAME COLUMN \"{$column}\" TO \"{$new_column}\";";
        return $this->query($sql);
    }

    /**
     * renameColumn
     * 
     * @param string $table_name
     * @param string $column
     * @param array $options
     * @return resource
     */
    public function changeColumnType($table_name, $column, $options)
    {
        $this->sql = $this->alterColumnTypeSQL($table_name, $column, $options);
        if ($this->is_excute_sql) {
            return $this->query($this->sql);
        } else {
            echo ("** Mode: Do not execute SQL") . PHP_EOL;
        }
    }

    /**
     * ALTER column type SQL
     * 
     * @param string $table_name
     * @param string $column
     * @param array $options
     * @return string
     */
    public function alterColumnTypeSQL($table_name, $column, $options)
    {
        if (!$table_name) return;
        if (!$column) return;
        if (!$options) return;

        $using = '';
        //TODO float double
        $type = $this->sqlColumnType($options['type'], $options['length']);
        if (strstr($type, 'int')) {
            $sql = "ALTER TABLE \"{$table_name}\" ALTER COLUMN \"{$column}\" TYPE {$type}";
            $using = " USING {$column}::int";
        }
        $sql = "ALTER TABLE \"{$table_name}\" ALTER COLUMN \"{$column}\" TYPE {$type}{$using};";
        return $sql;
    }

    /**
     * dropTable
     * 
     * @param string $table_name
     * @return resource
     */
    public function dropTable($table_name)
    {
        if (!$table_name) return;
        $this->sql = "DROP TABLE \"{$table_name}\";";
        return $this->query($this->sql);
    }

    /**
     * dropColumn
     * 
     * @param string $table_name
     * @param string $column
     * @param string $column
     * @return resource
     */
    public function dropColumn($table_name, $column)
    {
        if (!$table_name) return;
        if (!$column) return;
        $sql = "ALTER TABLE \"{$table_name}\" DROP COLUMN \"{$column}\";";
        return $this->query($sql);
    }

    /**
     * addColumn
     * 
     * @param string $table_name
     * @param string $column
     * @param array $options
     * @return resource
     */
    public function addColumn($table_name, $column, $options)
    {
        if (!$table_name) return;
        if (!$column) return;
        if (!$options) return;

        $this->sql = $this->addColumnSql($table_name, $column, $options);
        if ($this->is_excute_sql) {
            return $this->query($this->sql);
        }
    }

    /**
     * add column SQL
     * 
     * @param string $table_name
     * @param string $column
     * @param array $options
     * @return string $sql
     */
    public function addColumnSql($table_name, $column, $options)
    {
        if (!$table_name) return;
        if (!$column) return;
        if (!$options) return;

        $type = $this->sqlColumnType($options['type'], $options['length']);
        $option = $option = self::columnOptionSql($options);

        $sql = "ALTER TABLE \"{$table_name}\" ADD COLUMN \"{$column}\" {$type} {$option};";
        return $sql;
    }

    /**
     * connection
     * 
     * @return resource
     */
    function connection()
    {
        if (!$this->is_pconnect) {
            return pg_connect($this->pg_info);
        } else {
            if ($this->is_connect_forece_new) {
                return pg_pconnect($this->pg_info, PGSQL_CONNECT_FORCE_NEW);
            } else {
                return pg_pconnect($this->pg_info);
            }
        }
    }

    /**
     * check connection
     * 
     * @return resource
     */
    function checkConnection()
    {
        $connection = $this->connection();
        if (!$connection) $this->is_connection_error = true;
        return $connection;
    }

    /**
     * connection
     *
     * @param string $sql 
     * @return resource
     */
    function query($sql)
    {
        if (!$this->checkSQL($sql)) return false;
        $this->sql_error = null;
        $this->sql = $sql;
        if (defined('SQL_LOG') && SQL_LOG) error_log("SQL: {$sql}");
        if ($pg = $this->connection()) {
            if ($is_busy = pg_connection_busy($pg)) exit('DB connection is busy.');
            $results = pg_query($pg, $sql);
            $this->pg_result_status = pg_result_status($results);
            $this->sql_error = pg_last_error($pg);
        }
        if ($pg) pg_close($pg);

        $this->finaly();
        return $results;
    }

    /**
     * fetchObjctRows
     * 
     * @param string $sql
     * @return array
     */
    function fetchObjctRows($sql)
    {
        if ($rs = $this->query($sql)) {
            while ($row = pg_fetch_object($rs)) {
                if ($this->id_index) {
                    $rows[$row[$this->id_column]] = $this->castRow($row);
                } else {
                    $rows[] = $this->castRow($row);
                }
            }
            return $rows;
        } else {
            return;
        }
    }


    /**
     * fetchRows
     * 
     * @param string $sql
     * @return array
     */
    function fetchRows($sql)
    {
        if ($rs = $this->query($sql)) {
            $rows = pg_fetch_all($rs, PGSQL_ASSOC);
            if ($this->is_cast && $this->columns) $rows = $this->castRows($rows);
            return $rows;
        } else {
            return;
        }
    }

    /**
     * fetchRow
     * 
     * @param string $sql
     * @return array
     */
    function fetchRow($sql)
    {
        if ($rs = $this->query($sql)) {
            if ($this->is_value_object) {
                $row = pg_fetch_object($rs);
            } else {
                $row = pg_fetch_assoc($rs);
            }
            $this->value = $this->castRow($row);
            $this->initLimit();
            return $this->value;
        } else {
            return;
        }
    }

    //TODO  pg_fetch_result(): Unable to jump to row 0 on PostgreSQL result index
    /**
     * fetch result
     * 
     * @param string $sql
     * @return string
     */
    function fetchResult($sql)
    {
        if ($rs = $this->query($sql)) {
            $result = pg_fetch_result($rs, 0, 0);
            return (isset($result)) ? $result : null;
        } else {
            return null;
        }
    }

    /**
     * table partition
     * 
     * @param string $key
     * @param string $separater
     * @return void
     */
    public function partition($key, $separater = '_')
    {
        if (!$key) return;
        $this->table_name = "{$this->name}{$separater}{$key}";
    }

    /**
     * chunk
     *
     * @param integer $limit
     * @return void
     */
    public function chunk($limit)
    {
        $count = $this->count();
        $offset = 0;
        $amount = ceil($count / $limit);

        if ($amount == 0) return;
        if ($amount == 1) return $this->all();

        $values = [];
        $this->limit = $limit;
        for ($i = 1; $i <= $amount; $i++) {
            $this->offset = $i * $limit;
            if ($this->offset <= $count) {
                $_values = $this->all();
                if ($values) {
                    $values = array($values, $_values);
                } else {
                    $values = $_values;
                }
            }
        }
    }

    /**
     * bind object by fetch
     * 
     * @param  string $model_name
     * @param  string $foreign_key
     * @return PwPgsql
     */
    public function bindFetch($model_name, $foreign_key = null)
    {
        if (!is_string($model_name)) return $this;
        $relation = DB::model($model_name);
        $column_name = $relation->entity_name;
        if (!$foreign_key) $foreign_key = "{$column_name}_id";
        if ($id = $this->value[$foreign_key]) $relation = $relation->fetch($id);
        $this->$column_name = $relation;
        return $this;
    }

    /**
     * relation by model
     * 
     * @param  string $model_name
     * @param  string $foreign_key
     * @param  string $value_key
     * @return PwPgsql
     */
    public function bindOne($model_name, $foreign_key = null, $value_key = null)
    {
        if (!is_string($model_name)) return $this;
        $relation = DB::model($model_name);

        $column_name = $relation->entity_name;
        $relation = $this->hasOne(get_class($relation), $foreign_key, $value_key);
        $this->$column_name = $relation;
        return $this;
    }

    /**
     * relation by model
     * 
     * @param  string $model_name
     * @param  string $foreign_key
     * @param  string $value_key
     * @return PwPgsql
     */
    public function bindMany($model_name, $foreign_key = null, $value_key = null)
    {
        if (!is_string($model_name)) exit('bindMany: $model_name is not string');
        $relation = DB::model($model_name);

        $column_name = $relation->entity_name;
        $relation = $this->relationMany(get_class($relation), $foreign_key, $value_key)->all();
        $this->$column_name = $relation;
        return $this;
    }

    /**
     * relation by model
     * 
     * @param  string $model_name
     * @param  string $foreign_key
     * @param  string $value_key
     * @return PwPgsql
     */
    public function bindBelongsTo($model_name, $foreign_key = null, $value_key = null)
    {
        if (!is_string($model_name)) exit('bindBelongsTo: $model_name is not string');
        $relation = DB::model($model_name);

        $column_name = $relation->entity_name;
        $relation = $this->belongsTo(get_class($relation), $foreign_key, $value_key);
        $this->$column_name = $relation;
        return $this;
    }

    /**
     * relations by model
     * 
     * @param  string $model_name
     * @param  string $foreign_key
     * @param  string $value_key
     * @return PwPgsql
     */
    public function hasOne($model_name, $foreign_key = null, $value_key = null)
    {
        if (is_null($this->value)) return $this;

        if (!is_string($model_name)) exit('hasOne: $model_name is not string');
        $relation = DB::model($model_name);

        if (!$foreign_key) $foreign_key = "{$this->entity_name}_id";
        if (!$value_key) $value_key = $this->id_column;

        $value = $this->value[$value_key];
        if (is_null($value)) return $this;
        return $relation->where($foreign_key, $value)->one();
    }

    //TODO delete function
    /**
     * relations SQL select by model
     * 
     * @param  string $class_name
     * @param  string $foreign_key
     * @param  string $value_key
     * @return PwPgsql
     */
    public function hasMany($class_name, $foreign_key = null, $value_key = null)
    {
        return $this->relationMany($class_name, $foreign_key, $value_key)->all();
    }

    /**
     * relation one to one by model
     * 
     * @param  string $class_name
     * @param  string $foreign_key
     * @param  string $value_key
     * @return PwPgsql
     */
    public function relation($class_name, $foreign_key = null, $value_key = null)
    {
        if (!class_exists($class_name)) exit('relation class_name: not found '.$class_name);
        if (!$foreign_key) $foreign_key = "{$this->entity_name}_id";
        if (!$value_key) $value_key = $this->id_column;
        $relation = DB::model($class_name);

        if (is_null($this->value)) return $relation;
        $value = $this->value[$value_key];
        if (is_null($value)) return $relation;
        return $relation->where($foreign_key, $value);
    }

    /**
     * relations by model
     * 
     * @param  string $class_name
     * @param  string $foreign_key
     * @param  string $value_key
     * @return PwPgsql
     */
    public function relationMany($class_name, $foreign_key = null, $value_key = null)
    {
        if (!class_exists($class_name)) exit('relation class_name: not found '.$class_name);
        if (!$foreign_key) $foreign_key = "{$this->entity_name}_id";
        if (!$value_key) $value_key = $this->id_column;
        $relation = DB::model($class_name);

        if (is_null($this->value)) return $relation;
        $value = $this->value[$value_key];
        if (is_null($value)) return $relation;
        return $relation->where($foreign_key, $value);
    }

    /**
     * relation one to one by model
     * 
     * @param  string $class_name
     * @param  string $foreign_key
     * @param  string $value_key
     * @return PwPgsql
     */
    public function relationOne($class_name, $foreign_key = null, $value_key = null)
    {
        $relation = $this->relation($class_name, $foreign_key, $value_key);
        return $relation->one();
    }

    /**
     * through relations by model
     * 
     * @param  string $class_name
     * @param  array $conditions
     * @param  string $foreign_key
     * @param  string $value_key
     * @return PwPgsql
     */
    public function hasManyThrough($class_name, $through_class_name, $foreign_key = null, $value_key = null)
    {
        if (!class_exists($class_name)) exit('relation class_name: not found '.$class_name);
        $relation = DB::model($class_name);

        if (!class_exists($through_class_name)) exit('hasMany: not found '.$through_class_name);
        $through = DB::model($through_class_name);
        $through_left_column = "{$this->entity_name}_id";
        $through_right_column = "{$relation->entity_name}_id";

        $this->join($through_class_name, $through_left_column, 'id');
        $this->join($through_class_name, $through_right_column, 'id', $class_name);

        return $relation->all();
    }

    /**
     * relation by model
     * 
     * @param  string $class_name
     * @param  string $foreign_key
     * @param  string $value_key
     * @return PwPgsql
     */
    public function belongsTo($class_name, $foreign_key = null, $value_key = null)
    {
        if (!class_exists($class_name)) exit('relation class_name: not found '.$class_name);
        $relation = DB::model($class_name);

        if (!$foreign_key) $foreign_key = $relation->id_column;
        if (!$value_key) $value_key = "{$relation->entity_name}_id";
        if (is_null($this->value)) return $relation;

        $value = $this->value[$value_key];
        if (is_null($value)) return $relation;

        $condition = "{$relation->id_column} = {$value}";
        return $relation->where($condition)->one();
    }

    /**
     * relation by values
     * 
     * @param  string $class_name
     * @param  string $foreign_key
     * @return PwPgsql
     */
    public function relationByValues($class_name, $foreign_key, $is_id_index = false)
    {
        if (!class_exists($class_name)) exit('relation class_name: not found '.$class_name);
        $relation = DB::model($class_name);

        if (!$this->values) return $relation;
        if ($ids = array_column($this->values, $foreign_key)) {
            $relation->whereIn($relation->id_column, $ids);
            if ($is_id_index) $relation->idIndex();
            $relation->all();
        }
        return $relation;
    }


    /**
     * has record value
     * 
     * @return Boolean
     */
    function hasData()
    {
        $this->select(['id'])->limit(1)->one();
        return (boolean) $this->value;
    }

    /**
     * fetch
     * 
     * @param  int $id
     * @param  array $params
     * @return array
     */
    public function fetchValue($id, $params = null)
    {
        return $this->fetch($id, $params)->value;
    }

    /**
     * select
     * 
     * @param  array $columns
     * @param  array $as_columns
     * @return PwPgsql
     */
    public function select($columns = null, $as_columns = null)
    {
        $this->select_columns = [];
        if (is_array($columns)) $this->select_columns = $columns;

        $this->select_as_columns = [];
        if (is_array($as_columns)) $this->select_as_columns = $as_columns;
        unset($this->id);
        return $this;
    }

    /**
     * one
     * 
     * @return array
     */
    public function one($use_limit = true)
    {
        if ($use_limit && $this->conditions) $this->limit(1);

        $sql = $this->selectSql();

        $this->fetchRow($sql);

        $this->id = $this->value[$this->id_column];
        $this->before_value = $this->value;
        $this->values = null;
        return $this;
    }

    /**
     * fetch
     * 
     * @param  int $id
     * @return PwPgsql
     */
    public function fetch($id)
    {
        $this->orders = null;
        $this->values = null;
        $this->group_by_columns = null;
        if (!$id) return $this;

        $this->where($this->id_column, $id);

        $sql = $this->selectSql();

        $this->fetchRow($sql);

        $this->id = $this->value[$this->id_column];
        $this->before_value = $this->value;
        $this->values = null;
        return $this;
    }

    /**
     * fetch
     * 
     * @param  int $id
     * @param  array $columns
     * @return PwPgsql
     */
    public function fetchByTrue($id, $columns)
    {
        $this->conditions = null;
        $this->orders = null;
        $this->values = null;
        $this->group_by_columns = null;
        if (!$id) return $this;

        $this->where($this->id_column, $id);
        foreach ($columns as $column) {
            $this->where($column, true);
        }
        $this->one();
        return $this;
    }

    /**
     * count
     * 
     * TODO GROUP BY
     * 
     * @return integer
     **/
    public function count($column = null)
    {
        $sql = $this->selectCountSql($column);
        $count = $this->fetchResult($sql);
        if (is_null($count)) $count = 0;
        return $count;
    }

    /**
     * find
     * 
     * @param  int $id
     * @return PwPgsql
     */
    public function find($id)
    {
        return $this->fetch($id);
    }

    /**
     * first
     * 
     * @param  int $id
     * @return Object
     */
    public function first()
    {
        $this->limit(1);
        return $this->one();
    }

    /**
     * select all
     * 
     * @return PwPgsql
     */
    public function bulkSelect()
    {
        $this->is_bulk_select = true;
        return $this;
    }

    /**
     * select all
     * 
     * @return PwPgsql
     */
    public function all($is_id_index = false)
    {
        if ($is_id_index) $this->idIndex();
        $this->values = null;
        if ($this->is_bulk_select) {
            return $this->bulkAll($this->limit);
        } else {
            if (!$this->is_old_table) {
                if ($this->is_sort_order_column) {
                    if (!$this->orders && $this->columns['sort_order']) {
                        $this->order('sort_order');
                        if ($this->id_column) $this->order($this->id_column);
                    }
                }
            }
            $sql = $this->selectSql();
            $this->values = $this->fetchRows($sql);
            return $this;
        }
    }


    /**
     * select all
     * 
     * @return PwPgsql
     */
    public function byAll($column)
    {
        $columns = array_keys($this->columns);
        
        if (!in_array($column, $columns)) {
            exit("Not found column: {$column}");
        }
        $this->values_index_column = $column;
        $this->all();
        return $this;
    }

    /**
     * selectAll test method
     * 
     * TODO under construction
     *
     * @return void
     */
    public function selectAll()
    {
        if ($pg = $this->connection()) {
            if (pg_connection_busy($pg)) {
                exit('DB connection is busy.');
            }
            $columns = [];
            $columns['id'] = '';
            $this->values = pg_select($pg, $this->table_name, $columns, PGSQL_DML_EXEC);
            return $this;
        }
    }

    /**
     * select all
     * 
     * @param  array $params
     * @return PwPgsql
     */
    public function bulkAll($limit)
    {
        $this->sqls = null;
        $has_data = true;
        $offset = 0;
        $i = 0;

        if (!$limit) $limit = 100;
        $this->limit = $limit;
        $values = [];
        while ($has_data) {
            $offset = $limit * $i;
            $this->offset = $offset;

            $sql = $this->selectSql();
            $rows = $this->fetchRows($sql);

            if (!$rows) break;

            $this->sqls[] = $sql;
            if ($rows) {
                if ($values) {
                    $values = array_merge($values, $rows);
                } else {
                    $values = $rows;
                }
            }
            $i++;
        }
        $this->values = $values;
        return $this;
    }

    /**
     * select all (get)
     * 
     * @param  array $params
     * @return array
     */
    public function get()
    {
        return $this->all();
    }

    /**
     * select all values
     * 
     * @return array
     */
    public function allValues()
    {
        return $this->all()->values;
    }

    /**
     * select one value
     * 
     * @return array
     */
    public function oneValue()
    {
        return $this->one()->value;
    }

    /**
     * refresh
     * 
     * @return PwPgsql
     */
    public function refresh()
    {
        if ($this->value['id']) $this->fetch($this->value['id']);
        return $this;
    }

    /**
     * insert
     * 
     * @param  array $posts
     * @return PwPgsql
     */
    public function insert($posts = null)
    {
        $this->id = null;
        $this->value[$this->id_column] = null;
        $this->value['created_at'] = null;
        $this->value['updated_at'] = null;
        $this->values = null;

        if ($this->value['id']) unset($this->value['id']);
        if ($posts) $this->takeValues($posts);

        $this->validate();
        if ($this->errors) {
            return $this;
        }

        $sql = $this->insertSql();
        if ($this->id = $this->fetchResult($sql)) {
            $this->value[$this->id_column] = $this->id;
        } else {
            $this->addError('save', 'error');
        }
        if (!$this->errors) PwSession::clear('pw_posts');
        return $this;
    }

    /**
     * update
     * 
     * TODO update
     * mixed pg_update ( resource $connection , string $table_name , array $data , array $condition [, int $options = PGSQL_DML_EXEC ] )
     * 
     * @param  array $posts
     * @param  integer $id
     * @return PwPgsql
     */
    public function update($posts = null, $id = null)
    {
        if (!$id) $id = $this->value[$this->id_column];
        if (!$id) $id = $this->id;
        if ($id > 0) $this->fetch($id);
        if (!$this->id) return $this;
        if ($posts[$this->id_column]) unset($posts[$this->id_column]);
        if ($posts) $this->takeValues($posts);

        $this->validate();
        //TODO function
        if ($this->errors) return $this;
        $sql = $this->updateSql();
        $result = $this->query($sql);
        if ($result === false) $this->addError('save', 'error');
        if (!$this->errors) PwSession::clear('pw_posts');

        return $this;
    }

    /**
     * update all
     * 
     * @param  array $posts
     * @return PwPgsql
     */
    public function updateAll($posts)
    {
        $sql = $this->updateAllSql($posts);
        $result = $this->query($sql);
        if ($result === false) $this->addError('save', 'error');
        if (!$this->errors) PwSession::clear('pw_posts');
        return $this;
    }

    /**
     * update by where
     * 
     * @return PwPgsql
     */
    public function updateByWhere($posts)
    {
        $sql = $this->updateByWhereSql($posts);
        $result = $this->query($sql);
        if ($result === false) $this->addError('save', 'error');
        return $this;
    }

    /**
     * update by id
     * 
     * TODO update
     * mixed pg_update ( resource $connection , string $table_name , array $data , array $condition [, int $options = PGSQL_DML_EXEC ] )
     * 
     * @param  array $posts
     * @param  int $id
     * @return PwPgsql
     */
    public function updateById($id, $posts)
    {
        $sql = $this->updateSqlById($id, $posts);
        $result = $this->query($sql);
        if ($result === false) $this->addError('save', 'error');
        return $this;
    }

    /**
     * upsert 
     * 
     * PostgreSQL 9.5 >
     * 
     * @return array $posts
     */
    public function upsert($posts)
    {
        if (!$this->table_name) return $this;
        $this->takeValues($posts);
        $sql = $this->upsertSql();
        $result = $this->query($sql);
        if ($result === false) $this->addError('save', 'error');
        return $this;
    }

    /**
     * updates
     * 
     * @param  array $posts
     * @return PwPgsql
     */
    public function updates($posts)
    {
        if (!$posts) return;
        $sql = $this->updatesSql($posts);
        $result = $this->query($sql);
        if ($result === false) $this->addError('save', 'error');
        return $this;
    }

    /**
     * renameColumn
     * 
     * @param int $id
     * @param string $column
     * @return resource
     */
    public function reverseBool($id, $column)
    {
        $this->fetch($id);
        if ($this->value) {
            $posts[$column] = !$this->value[$column];
            $this->update($posts);
        }
        return $this;
    }

    /**
     * inserts
     * 
     * @param  array $rows
     * @return PwPgsql
     */
    public function copyFrom($rows)
    {
        $model_columns = array_keys($this->columns);
        pg_copy_from($this->connection(), $this->table_name, $rows);
    }

    /**
     * move
     *
     * @param  string $model_name
     * @return PwPgsql
     */
    public function move($model_name) {
        if (!$this->values) return $this;
        $model = DB::model($model_name)->inserts($this->values);
        if (!$model->sql_error) {
            $ids = array_column($this->values, $this->id_column);
            if ($ids) $this->initWhere()->whereIn('id', $ids)->deletes();
        }
        return $this;
    }

    /**
     * inserts
     *
     * @param  array $rows
     * @return PwPgsql
     */
    function inserts($rows)
    {
        if (!$rows) return $this;
        $model_columns = array_keys($this->columns);
        if (!$model_columns) return $this;

        foreach ($rows as $row) {
            $sql_values = [];
            foreach ($model_columns as $column_name) {
                $value = null;
                if ($column_name == 'created_at') {
                    $sql_values[] = 'current_timestamp';
                } else {
                    if (isset($row[$column_name])) $value = $row[$column_name];
                    $sql_values[] = self::sqlValue($value);
                }
            }
            $value = implode(', ', $sql_values);
            $values[] = "\n({$value})";
        }
        $column = implode(', ', $model_columns);
        $value = implode(', ', $values);

        $sql = "INSERT INTO {$this->table_name} ({$column}) VALUES {$value}";
        $this->query($sql);
        return $this;
    }

    /**
     * pg insert
     * 
     * TODO under construction
     * 
     * @param  array $posts
     * @return PwPgsql
     */
    public function pgInsert($posts)
    {
        $model_columns = array_keys($this->columns);
        pg_insert($this->connection(), $this->table_name, $posts);
    }

    /**
     * update sort_order
     *
     * @param array $sort_orders
     * @return PwPgsql
     */
    function updateSortOrder($sort_orders)
    {
        if (!is_array($sort_orders)) return $this;
        $this->idIndex()->select([$this->id_column, 'sort_order'])->all();
        $class_name = get_class($this);
        foreach ($sort_orders as $sort_order) {
            $id = $sort_order['id'];
            $order = $sort_order['order'];
            if (is_numeric($id) && is_numeric($order)) {
                $current_value = $this->values[$id];
                if ($current_value['sort_order'] != $order) {
                    $posts['sort_order'] = (int) $order;
                    $class = DB::model($class_name)->update($posts, $id);
                }
            }
        }
        return $this;
    }

    /**
     * values from old table
     * 
     * @param  PwPgsql $old_pgsql
     * @return array
     */
    public function valuesFromOldTable($old_pgsql)
    {
        if (!$old_pgsql) exit('Not found old_pgsql');

        $sql = $this->selectSqlFromOldTable();
        $values = $old_pgsql->fetchRows($sql);

        return $values;
    }

    /**
     * copy from old table
     * 
     * @param  PwPgsql $old_pgsql
     * @return bool
     */
    public function copyFromOldTable($old_pgsql)
    {
        if (!$old_pgsql) exit('Not found old_pgsql');

        $values = $this->valuesFromOldTable($old_pgsql);
        $results = $this->copyFrom($values);
        return $results;
    }

    /**
     * insertsFromOldTable
     * TODO inserts()
     * 
     * @param  PwPgsql $old_pgsql
     * @return PwPgsql
     */
    public function insertsFromOldTable($old_pgsql)
    {
        if (!$old_pgsql) exit('Not found old_pgsql');

        $values = $this->valuesFromOldTable($old_pgsql);
        if (!$values) {
            $this->addError('save', 'error');
        } else {
            $this->_value = $this->value;
        }
        return $this;
    }

    /**
     * delete
     *
     * @param  int $id
     * @return PwPgsql
     */
    public function delete($id = null)
    {
        if (is_numeric($id)) $this->id = (int)$id;
        if (is_numeric($this->id)) $this->initWhere()->where($this->id_column, $this->id);
        if (!is_numeric($this->id)) return $this;

        $sql = $this->deleteSql();
        $result = $this->query($sql);

        if ($result === false) {
            $this->addError($this->name, 'delete');
        } else {
            unset($this->id);
            $this->value = null;
        }
        return $this;
    }

    /**
     * delete
     * 
     * @param  int $id
     * @return PwPgsql
     */
    public function deletes()
    {
        $sql = $this->deletesSql();
        $result = $this->query($sql);
        if ($result === false) $this->addError($this->name, 'delete');
        return $this;
    }

    /**
     * truncate
     * 
     * $option
     * RESTART IDENTITY
     * CONTINUE IDENTITY
     * 
     * CASCADE
     * RESTRICT
     * 
     * @return PwPgsql
     */
    public function truncate($option = null)
    {
        $sql = $this->truncateSql($option);
        $result = $this->query($sql);

        if ($result === false) $this->addError($this->name, 'truncate');
        return $this;
    }

    /**
     * where
     * 
     * @param  string $condition
     * @return PwPgsql
     */
    public function from($name)
    {
        if ($name) $this->table_name = $name;
        return $this;
    }

    /**
     * where
     * 
     * @param  mixed $condition
     * @param  string $value
     * @param  string $eq
     * @return PwPgsql
     */
    public function where($condition, $value = null, $eq = '=')
    {
        if (!$condition) return $this;
        if (isset($value)) {
            if (is_bool($value)) $value = ($value === true) ? 'TRUE' : 'FALSE';
            $this->conditions[] = "{$condition} {$eq} '{$value}'";
        } else {
            if (is_array($condition)) {
                $column = $condition[0];
                $value = $condition[1];
                $eq = (isset($condition[2])) ? $condition[2] : '=';
                if (is_bool($value)) $value = ($value === true) ? 'TRUE' : 'FALSE';
                $this->conditions[] = "{$column} {$eq} '{$value}'";
            } else {
                $this->conditions[] = $condition;
            }
        }
        $this->conditions = array_unique($this->conditions);
        $this->or_wheres = [];
        return $this;
    }

    /**
     * where
     * 
     * @param  mixed $condition
     * @param  string $value
     * @param  string $eq
     * @return PwPgsql
     */
    public function orWhere($condition, $value = null, $eq = '=')
    {
        if (!$condition) return $this;
        if (isset($value)) {
            if (is_bool($value)) $value = ($value === true) ? 'TRUE' : 'FALSE';
            $this->or_wheres[] = "{$condition} {$eq} '{$value}'";
        } else {
            if (is_array($condition)) {
                $column = $condition[0];
                $value = $condition[1];
                $eq = (isset($condition[2])) ? $condition[2] : '=';
                if (is_bool($value)) $value = ($value === true) ? 'TRUE' : 'FALSE';
                $this->or_wheres[] = "{$column} {$eq} '{$value}'";
            } else {
                $this->or_wheres[] = $condition;
            }
        }
        $this->or_wheres = array_unique($this->or_wheres);
        return $this;
    }

    /**
     * load where OR
     * 
     * @return array
     */
    public function initOR()
    {
        $this->or_wheres = [];
    }

    /**
     * where true
     * 
     * @param  string $column
     * @return PwPgsql
     */
    public function whereNot($column, $value)
    {
        if (!$column) return $this;
        $this->where("{$column} != '{$value}'");
        return $this;
    }

    /**
     * where true
     * 
     * @param  string $column
     * @return PwPgsql
     */
    public function whereTrue($column)
    {
        if (!$column) return $this;
        $this->where($column, true);
        return $this;
    }

    /**
     * where true
     * 
     * @param  string $column
     * @return PwPgsql
     */
    public function whereFalse($column)
    {
        if (!$column) return $this;
        $this->where($column, false);
        return $this;
    }

    /**
     * where true
     * 
     * @param  string $column
     * @return PwPgsql
     */
    public function whereNotTrue($column)
    {
        if (!$column) return $this;
        $this->where("{$column} IS NOT TRUE");
        return $this;
    }

    /**
     * where null
     * 
     * @param  string $column
     * @return PwPgsql
     */
    public function whereNull($column)
    {
        if (!$column) return $this;
        $this->where("{$column} IS NULL");
        return $this;
    }

    /**
     * where LIKE
     * 
     * @param  string $column
     * @param  string $value
     * @param  array $params
     * @return PwPgsql
     */
    public function whereLike($column, $value, $params = null)
    {
        if (!$column) return $this;
        $sql = $this->whereLikeSql($column, $value, $params);
        $this->where($sql);
        return $this;
    }

    /**
     * where LIKE
     * 
     * @param  array $key_values
     * @param  string $value
     * @param  array $params
     * @return PwPgsql
     */
    public function wheresLike($columns, $params = null)
    {
        if (!$columns) return $this;
        foreach ($columns as $column => $value) {
            $conditions[] = $this->whereLikeSql($column, $value, $params);
        }
        $connection = ($params['connection']) ? " {$params['connection']} " : ' AND ';
        $sql = implode($connection, $conditions);
        $this->where($sql);
        return $this;
    }

    /**
     * where LIKE
     * 
     * @param  string $column
     * @param  string $value
     * @param  array $params
     * @return string
     */
    public function whereLikeSql($column, $value, $params = null)
    {
        if (!$column) return;
        if ($params['before']) $before = $params['before'];
        if ($params['after']) $after = $params['after'];
        if (!$before && !$after) {
            $before = '%';
            $after = '%';
        }
        $condition =  "{$column} LIKE '{$before}{$value}{$after}'";
        return $condition;
    }


    /**
     * where in
     * 
     * @param  string $column
     * @param  array $values
     * @return PwPgsql
     */
    public function whereNotNull($column)
    {
        if (!$column) return $this;
        $this->where("{$column} IS NOT NULL");
        return $this;
    }

    /**
     * where between
     * 
     * @param  string $column
     * @param  array $values
     * @return PwPgsql
     */
    public function whereBetween($column, $values, $options = [])
    {
        if (!$column) return $this;
        if (!$values[0]) return $this;
        if (!$values[1]) return $this;
        if (!$options[0]) $options[0] = '>=';
        if (!$options[1]) $options[1] = '<=';

        $this->where($column, $values[0], $options[0]);
        $this->where($column, $values[1], $options[1]);
        return $this;
    }

    /**
     * where in
     * 
     * @param  string $column
     * @param  array $values
     * @return PwPgsql
     */
    public function whereIn($column, $values)
    {
        if (!$column) return $this;
        if (!is_array($values)) return $this;
        $condition = PwPgsql::whereInConditionSQL($column, $values);
        $this->where($condition);
        return $this;
    }

    /**
     * where in
     * 
     * @param  string $column
     * @param  array $values
     * @return PwPgsql
     */
    public function whereNotIn($column, $values)
    {
        if (!$column) return $this;
        foreach ($values as $value) $_values[] = self::sqlValue($value);
        $condition = PwPgsql::whereNotInConditionSQL($column, $_values);
        $this->where($condition);
        return $this;
    }

    /**
     * where in condition
     *
     * @param string $column
     * @return void
     */
    function whereInCondition($column) {
        $ids = $this->valuesByColumn($column);
        $condition = PwPgsql::whereInConditionSQL($column, $ids);
        return $condition;
    }

    /**
     * where in conditon
     * 
     * @param  string $column
     * @param  array $values
     * @return string
     */
    static function whereInConditionSQL($column, $values)
    {
        if (!is_array($values)) return;
        foreach ($values as $value) {
            $_values[] = self::sqlValue($value);
        }
        $value = implode(', ', $_values);
        $condition = "{$column} IN ({$value})";
        return $condition;
    }

    /**
     * where not in conditon
     * 
     * @param  string $column
     * @param  array $values
     * @return string
     */
    static function whereNotInConditionSQL($column, $values)
    {
        if (!is_array($values)) return;
        $value = implode(', ', $values);
        $condition = "{$column} NOT IN ({$value})";
        return $condition;
    }

    /**
     * wheres
     * 
     * @param  array $conditions
     * @return PwPgsql
     */
    public function wheres($conditions)
    {
        if (!$conditions) return $this;
        foreach ($conditions as $condition) $this->where($condition);
        return $this;
    }

    /**
     * filter
     * 
     * @param  string $condition
     * @param  string $value
     * @param  string $eq
     * @return PwPgsql
     */
    public function filter($filters)
    {
        if (!is_array($filters)) return $this;
        foreach ($filters as $column => $filter) {
            if (!$filter['eq']) $filter['eq'] = '=';
            $this->conditions[] = "{$column} {$filter['eq']} '{$filter['value']}'";
        }
        $this->conditions = array_unique($this->conditions);
        return $this;
    }

    /**
     * count
     * 
     * @return int
     **/
    public function groupBy($column)
    {
        $this->group_by_columns[] = $column;
        $this->group_by_columns = array_unique($this->group_by_columns);
        return $this;
    }

    /**
    * days condition
    * 
    * @param array $hours
    * @param string $column
    * @return PwPgsql
    */
    function daysCondition($days, $column) {
        if (!$days) return $this;
        foreach ($days as $day) {
            $conditions[] = $this->dayCondition($day, $column);
        }
        $condition = $this->sqlOR($conditions);
        $this->where($condition);
        return $this;
    }

    /**
    * day condition
    * 
    * @param int $day
    * @param string $column
    * @return string
    */
    function dayCondition($day, $column) {
        $condition = "date_part('day', $column) = {$day}";
        return $condition;
    }

    /**
    * hour
    * 
    * @param array $hours
    * @param string $column
    * @return PwPgsql
    */
    function hoursCondition($hours, $column) {
        if (!$hours) return $this;
        foreach ($hours as $hour) {
            $conditions[] = $this->hourCondition($hour, $column);
        }
        $condition = $this->sqlOR($conditions);
        $this->where($condition);
        return $this;
    }

    /**
    * hour
    * 
    * @param int $hour
    * @param string $column
    * @return string
    */
    function hourCondition($hour, $column) {
        $condition = "date_part('hour', $column) = {$hour}";
        return $condition;
    }

    /**
     * initWhere
     * 
     * @return PwPgsql
     */
    public function initWhere()
    {
        $this->conditions = null;
        return $this;
    }

    /**
     * initWhere
     * 
     * @return PwPgsql
     */
    public function initLimit()
    {
        $this->limit = null;
        return $this;
    }

    /**
     * order
     * 
     * @param  string $column
     * @param  string $optionn
     * @param  string $column_type
     * @return PwPgsql
     */
    public function order($column, $option = null, $column_type = null)
    {
        $value['column'] = $column;
        $value['option'] = $option;
        $value['column_type'] = $column_type;
        $this->orders[$column] = $value;
        return $this;
    }

    /**
     * orders
     * 
     * @param  array $sort_orders
     * @return PwPgsql
     */
    public function orders($sort_orders)
    {
        $this->orders = [];
        foreach ($sort_orders as $sort_order) {
            if (is_array($sort_order)) {
                if ($sort_order[1]) {
                    $this->order($sort_order[0], $sort_order[1]);
                } else {
                    $this->order($sort_order[0]);
                }
            } else {
                $this->order($sort_order);
            }
        }
        return $this;
    }

    /**
     * initOrder
     * 
     * @return PwPgsql
     */
    public function initOrder()
    {
        $this->orders = null;
        return $this;
    }

    /**
     * limit
     * 
     * @param  int $limit
     * @return PwPgsql
     */
    public function limit($limit)
    {
        $this->limit = $limit;
        return $this;
    }

    /**
     * offset
     * 
     * @param  string $condition
     * @return PwPgsql
     */
    public function offset($offset)
    {
        $this->offset = (int) $offset;
        return $this;
    }

    //TODO under construction
    /**
     * select column for join
     * 
     * @param  string $model_name
     * @param  array $conditions
     * @return PwPgsql
     */
    public function selectColumn($join_class_name, $column, $join_column, $eq = '=', $type = 'LEFT')
    {
        if (!$join_class_name) return $this;
        if (!$column) return $this;
        if (!$join_column) return $this;

        $join_class = DB::model($join_class_name);

        //$join['join_class_name'] = $join_class_name;
        $join['column'] = $column;
        $join['join_column'] = $join_column;
        $join['join_class'] = $join_class;
        $join['join_name'] = $join_class->name;
        $join['join_entity_name'] = $join_class->entity_name;
        $join['type'] = $type;
        $this->joins[] = $join;
        return $this;
    }

    /**
     * join
     * 
     * @param  string $join_class_name
     * @param  string $join_column
     * @param  string $column
     * @param  string $class_name
     * @param  array $params
     * @return PwPgsql
     */
    public function join($join_class_name, $join_column = null, $column = null, $class_name = null, $params = null)
    {
        //TODO Bug: join custom column value can't change cast.
        //TODO join conditions
        if (!$join_class_name) return $this;
        $join_class = DB::model($join_class_name);
        if (!$join_class) return $this;

        if (!$join_column) {
            $join_column = $join_class->id_column;
            if (!$join_column) return $this;
            return $this;
        }
        if (!$column) {
            $column = $this->id_column;
            if (!$column) return $this;
        }
        if (!$class_name) $class_name = get_class($this);
        $origin_class = DB::model($class_name);

        $join['eq'] = ($params['eq']) ? $params['eq'] : '='; 
        $join['type'] = ($params['type']) ? $params['type'] : 'LEFT'; 

        $join['origin_class_name'] = $class_name;
        $join['origin_table_name'] = $origin_class->table_name;
        $join['origin_column'] = $column;

        $join['join_class_name'] = $join_class_name;
        $join['join_table_name'] = $join_class->table_name;
        $join['join_column'] = $join_column;

        $join['join_name'] = $join_class->name;
        $join['join_as_name'] = $params['join_as_name'];
        $join['join_entity_name'] = $join_class->entity_name;
       
        $this->join_columns[$join_class->name] = $join_class->columns;
        $this->joins[] = $join;
        return $this;
    }

    /**
     * init Join
     * 
     * @param  string $model_name
     * @return PwPgsql
     */
    public function initJoin($class_name, $type = 'LEFT')
    {
        $this->joins = array();
        $this->join($class_name, $type);
        return $this;
    }

    /**
     * sqlValue
     * 
     * @param  Object $value
     * @return string
     */
    static function sqlValue($value, $type = null)
    {
        if (is_null($value)) {
            return "NULL";
        } else if (is_bool($value)) {
            return ($value) ? 'TRUE' : 'FALSE';
        } else if (is_array($value)) {
            return "'" . pg_escape_string(json_encode($value)) . "'";
        } else {
            return "'" . pg_escape_string($value) . "'";
        }
    }

    /**
     * where Sql
     * 
     * @return string
     */
    private function whereSql()
    {
        $sql = '';
        if ($condition = $this->sqlConditions($this->conditions)) $sql = " WHERE {$condition}";
        return $sql;
    }

    /**
     * join Sql
     * 
     * @return string
     */
    private function joinSql()
    {
        $sql = '';
        if (is_array($this->joins)) {
            foreach ($this->joins as $join) {
                $eq = $join['eq'];

                $origin_table_name = $join['origin_table_name'];
                $origin_column = $join['origin_column'];

                $join_table_name = $join['join_table_name'];
                $join_column = $join['join_column'];

                if ($join['join_as_name']) {
                    $join_name = "{$join_table_name} AS {$join['join_as_name']}";
                    $condition = "{$origin_table_name}.{$origin_column} {$eq} {$join['join_as_name']}.{$join_column}";
                } else {
                    $join_name = $join_table_name;
                    $condition = "{$origin_table_name}.{$origin_column} {$eq} {$join_table_name}.{$join_column}";
                }

                $joins[] = PHP_EOL . " {$join['type']} JOIN {$join_name} ON {$condition}";
            }
            $sql = implode(' ', $joins) . PHP_EOL;
        }
        return $sql;
    }

    /**
     * where Sql
     * 
     * @return string
     */
    private function groupBySql()
    {
        $sql = '';
        if ($group_by = $this->sqlGroupBy($this->group_by_columns)) $sql = " GROUP BY {$group_by}";
        return $sql;
    }

    /**
     * orderBySql
     * 
     * @return string
     */
    private function orderBySql()
    {
        $sql = '';
        if (!$this->orders) return;
        if ($order = $this->sqlOrders($this->orders)) $sql = " ORDER BY {$order}";
        return $sql;
    }

    /**
     * limitSql
     * 
     * @return string
     */
    private function limitSql()
    {
        $sql = '';
        if (!isset($this->limit)) return;
        if (!is_int($this->limit)) return;
        $sql = " LIMIT {$this->limit}";
        return $sql;
    }

    /**
     * offsetSql
     * 
     * @return string
     */
    private function offsetSql()
    {
        $sql = '';
        if (!isset($this->offset)) return;
        if (!is_int($this->offset)) return;
        $sql = " OFFSET {$this->offset}";
        return $sql;
    }

    /**
     * select column array
     * 
     * @return string
     */
    private function selectColumnArray()
    {
        $columns = array_keys($this->columns);
        if ($this->id_column) $columns[] = $this->id_column;
        foreach ($columns as $key => $column) {
            $columns[$key] = "{$this->table_name}.{$column}";
        }
        return $columns;
    }

    /**
     * select join column array
     * 
     * @return string
     */
    private function selectJoinColumnArray($columns)
    {
        if ($this->joins) {
            foreach ($this->joins as $join) {
                $join_class = $join['join_class'];
                foreach ($join_class->columns as $join_column_name => $join_column) {
                    $columns[] = "{$join['join_name']}.{$join_column_name} AS {$join['join_entity_name']}_{$join_column_name}";
                }
            }
        }
        return $columns;
    }

    /**
     * select column string
     * 
     * @param $columns
     * @return string
     */
    private function selectColumnString($columns)
    {
        $column = '';
        if (is_array($columns)) $column = implode(", ", $columns);
        if (!$column) $column = '*';
        return $column;
    }

    //TODO column name
    /**
     * selectSql
     * 
     * @return string
     */
    public function selectSql()
    {
        if (isset($this->select_columns) && is_array($this->select_columns)) {
            $columns = $this->select_columns;
        } else if ($this->is_use_select_column) {
            $columns = $this->selectColumnArray();
            $columns = $this->selectJoinColumnArray($columns);
        } else {
            $columns[] = '*';
        }
        //for join
        if (isset($this->select_as_columns) && is_array($this->select_as_columns)) {
            foreach ($this->select_as_columns as $class_name => $as_column) {
                if (class_exists($class_name)) {
                    $column_name = $as_column['column_name'];
                    $clazz = new $class_name();
                    $as_name = $as_column['as_name'];
                    $column = "{$clazz->name}.{$column_name}";
                    if ($as_name) $column.= " AS {$as_name}";
                    $columns[] = $column;
                    if ($clazz->columns) {
                        $cast_column = ($as_name) ? $as_name : $column_name;
                        $this->extra_casts[$class_name][$cast_column] = $clazz->columns[$column_name]['type'];
                    }
                }
            }
        }
        $select_column = $this->selectColumnString($columns);
        $sql = "SELECT {$select_column} FROM {$this->table_name}";

        if ($this->joins) $sql .= $this->joinSql();

        $sql.= $this->whereSql();
        $sql.= $this->groupBySql();
        if (!$this->group_by_columns) $sql.= $this->orderBySql();
        $sql.= $this->limitSql();
        $sql.= $this->offsetSql();
        $sql.= ";";
        return $sql;
    }

    /**
     * selectSql
     * 
     * @return string
     */
    private function selectOneSql()
    {
        if (is_array($this->select_columns)) {
            $columns = $this->select_columns;
        } else {
            $columns = $this->selectColumnArray();
            $columns = $this->selectJoinColumnArray($columns);
        }

        $column = $this->selectColumnString($columns);

        $sql = "SELECT {$column} FROM {$this->table_name}";

        if ($this->joins) $sql .= $this->joinSql();

        $sql .= $this->whereSql();
        if ($this->group_by_columns) $sql .= $this->groupBySql();
        $sql .= ";";
        return $sql;
    }

    /**
     * select sql for old table
     * 
     * @return string
     */
    public function selectSqlFromOldTable()
    {
        if (!$this->old_name) exit('Not found old_name');

        if ($this->select_columns) {
            $column = implode(", ", $this->select_columns);
        } else {
            $column = $this->oldColumnForSelect();
        }

        $sql = "SELECT {$column} FROM {$this->old_name}";

        $sql .= $this->whereSql();
        if ($this->old_id_column) " ORDER BY {$this->old_id_column}";

        $sql .= $this->groupBySql();
        $sql .= $this->limitSql();
        $sql .= $this->offsetSql();
        $sql .= ";";
        return $sql;
    }

    /**
     * old column for SQL select
     * 
     * @return PwPgsql
     */
    public function selectOldColumns()
    {
        $this->select_columns = null;
        if ($this->old_id_column) $select_columns[] = $this->old_id_column;
        if ($this->columns) {
            foreach ($this->columns as $column_name => $column) {
                if ($column['old_name']) {
                    $this->select_columns[] = $column['old_name'];
                }
            }
        }
        return $this;
    }

    /**
     * old column for SQL select
     * 
     * @param  string $column
     * @return string
     */
    public function oldColumnForSelect()
    {
        if ($this->columns) {
            foreach ($this->columns as $column_name => $column) {
                if ($column['old_name']) {
                    if ($column['old_name'] == $column_name) {
                        $select_columns[] = $column_name;
                    } else {
                        $select_columns[] = "{$column['old_name']} AS {$column_name}";
                    }
                }
            }
            $column = implode(", ", $select_columns) . PHP_EOL;
        }
        return $column;
    }

    /**
     * selectCountSql
     * 
     * @param  string $column
     * @return string
     */
    private function selectCountSql($column = null)
    {
        // TODO GROUP BY
        if (!$column) $column = $this->table_name;
        $sql = "SELECT count({$column}) FROM {$this->table_name}";
        $sql .= $this->whereSql();
        $sql .= ";";
        return $sql;
    }

    /**
     * selectMax
     * 
     * @param  string $column
     * @return string
     */
    public function selectMax($column)
    {
        $sql = $this->selectMaxSql($column);
        $values = $this->fetchResult($sql);
        return $values;
    }

    /**
     * selectMax
     * 
     * @param  string $column
     * @return string
     */
    public function selectMin($column)
    {
        $sql = $this->selectMinSql($column);
        $values = $this->fetchResult($sql);
        return $values;
    }

    /**
     * selectMaxMin
     * 
     * @param  string $column
     * @return string
     */
    public function selectMaxMin($column = null)
    {
        $sql = $this->selectMaxMinSql($column);
        $values = $this->fetchRow($sql);
        return $values;
    }

    /**
     * select max value
     * 
     * @param  string $column
     * @return string
     */
    public function selectMaxValue($column)
    {
        $sql = $this->selectMaxValueSql($column);
        $values = $this->fetchResult($sql);
        return $values;
    }

    /**
     * select min value
     * 
     * @param  string $column
     * @return string
     */
    public function selectMinValue($column)
    {
        $sql = $this->selectMinValueSql($column);
        $values = $this->fetchResult($sql);
        return $values;
    }

    /**
     * selectMaxMinValue
     * 
     * @param  string $column
     * @return string
     */
    public function selectMaxMinValue($column = null)
    {
        $values['max'] = $this->selectMaxValue($column);
        $values['min'] = $this->selectMinValue($column);
        return $values;
    }

    /**
     * selectMaxSql
     * 
     * @param  string $column
     * @return string
     */
    private function selectMaxSql($column)
    {
        $sql = "SELECT max({$column}) FROM {$this->table_name}";
        $sql .= $this->whereSql();
        $sql .= ";";
        return $sql;
    }

    /**
     * selectMinSql
     * 
     * @param  string $column
     * @return string
     */
    private function selectMinSql($column)
    {
        $sql = "SELECT min({$column}) FROM {$this->table_name}";
        $sql .= $this->whereSql();
        $sql .= ";";
        return $sql;
    }

    /**
     * selectMaxValueSql
     * 
     * @param  string $column
     * @return string
     */
    private function selectMaxValueSql($column)
    {
        $this->conditions[] = "{$column} IS NOT NULL";
        $condition = $this->sqlConditions($this->conditions);
        $sql = "SELECT {$column} FROM {$this->table_name} WHERE {$condition} ORDER BY {$column} DESC LIMIT 1;";
        return $sql;
    }

    /**
     * selectMinValueSql
     * 
     * @param  string $column
     * @return string
     */
    private function selectMinValueSql($column)
    {
        $this->conditions[] = "{$column} IS NOT NULL";
        $condition = $this->sqlConditions($this->conditions);
        $sql = "SELECT {$column} FROM {$this->table_name} WHERE {$condition} ORDER BY {$column} ASC LIMIT 1;";
        return $sql;
    }

    /**
     * selectMaxMinSql
     * 
     * @param  string $column
     * @return string
     */
    private function selectMaxMinSql($column)
    {
        $sql = "SELECT max({$column}), min({$column}) FROM {$this->table_name}";
        $sql .= $this->whereSql();
        $sql .= ";";
        return $sql;
    }

    /**
     * selectSumSql
     * 
     * @param  string $column
     * @return string
     */
    private function selectSumSql($column = null)
    {
        // TODO GROUP BY
        if (!$column) $column = $this->table_name;
        $sql = "SELECT sum({$column}) FROM {$this->table_name}";
        $sql .= $this->whereSql();
        $sql .= ";";
        return $sql;
    }

    /**
     * selectAvgSql
     * 
     * @param  string $column
     * @return string
     */
    private function selectAvgSql($column = null)
    {
        // TODO GROUP BY
        if (!$column) $column = $this->table_name;
        $sql = "SELECT avg({$column}) FROM {$this->table_name}";
        $sql .= $this->whereSql();
        $sql .= ";";
        return $sql;
    }

    /**
     * insertSql
     *
     * TODO : pg_prepare, pg_execute
     * 
     * @return string
     */
    private function insertSql()
    {
        if (!$this->value) return;
        if (!$this->columns) return;
        foreach ($this->columns as $column_name => $type) {
            $value = self::sqlValue($this->value[$column_name]);
            if ($column_name == 'created_at') $value = 'current_timestamp';
            $columns[] = $column_name;
            $values[] = $value;
        }
        $column = implode(',', $columns);
        $value = implode(',', $values);

        $sql = "INSERT INTO {$this->table_name} ({$column}) VALUES ({$value}) RETURNING {$this->id_column};";
        return $sql;
    }

    /**
     * updateSql
     * 
     * @return string
     */
    private function updateSql()
    {
        $sql = '';
        $changes = $this->changes();
        if (!$changes) return;

        foreach ($changes as $key => $org_value) {
            $value = self::sqlValue($this->value[$key]);
            $set_values[] = "{$key} = {$value}";
        }
        if (isset($this->columns['updated_at'])) $set_values[] = "updated_at = current_timestamp";
        if ($set_values) $set_value = implode(',', $set_values);
        if ($set_value) {
            if (!$this->conditions) $this->conditions[] = "{$this->id_column} = {$this->id}";
            $condition = $this->sqlConditions($this->conditions);
            $sql = "UPDATE {$this->table_name} SET {$set_value} WHERE {$condition};";
        }
        return $sql;
    }


    /**
     * updateSql
     * 
     * @return string
     */
    private function updateAllSql($posts)
    {
        $sql = '';
        foreach ($posts as $key => $value) {
            if (isset($this->columns[$key])) {
                $value = self::sqlValue($value);
                $set_values[] = "{$key} = {$value}";
            }
        }
        if (isset($this->columns['updated_at'])) $set_values[] = "updated_at = current_timestamp";
        if ($set_values) $set_value = implode(',', $set_values);
        if ($set_value) {
            $sql = "UPDATE {$this->table_name} SET {$set_value}";
            if ($condition = $this->sqlConditions($this->conditions)) $sql.= "WHERE {$condition}";
            $sql.= ";";
        }
        return $sql;
    }

    /**
     * updateSql
     * 
     * @return string
     */
    private function updateByWhereSql($posts)
    {
        if (!$posts) return;
        foreach ($posts as $key => $value) {
            $value = self::sqlValue($value);
            $set_values[] = "{$key} = {$value}";
        }
        if (isset($this->columns['updated_at'])) $set_values[] = "updated_at = current_timestamp";
        if ($set_values) $set_value = implode(',', $set_values);
        if ($set_value) {
            if (!$this->conditions) $this->conditions[] = "{$this->id_column} = {$this->id}";
            $condition = $this->sqlConditions($this->conditions);
            $sql = "UPDATE {$this->table_name} SET {$set_value} WHERE {$condition};";
        }
        return $sql;
    }

    /**
     * updateSql by Id
     * 
     * @param integer $id
     * @param array $posts
     * @return string
     */
    private function updateSqlById($id, $posts)
    {
        $sql = '';
        foreach ($posts as $column_name => $value) {
            if (isset($this->columns[$column_name])) {
                $value = self::sqlValue($value);
                $set_values[] = "{$column_name} = {$value}";
            }
        }
        if (isset($this->columns['updated_at'])) $set_values[] = "updated_at = current_timestamp";
        if ($set_values) $set_value = implode(',', $set_values);

        if ($set_value) {
            $sql = "UPDATE {$this->table_name} SET {$set_value} WHERE {$this->id_column} = {$id};";
        }
        return $sql;
    }

    /**
     * reload primary key
     * 
     * @return PwPgsql
     */
    public function reloadPrimaryKey()
    {
        $this->primary_key = "{$this->table_name}_pkey";
        return $this;
    }

    /**
     * set upsert constraint
     * 
     * @param string $upsert_constraint
     * @return PwPgsql
     */
    public function setUpsertConstraint($upsert_constraint)
    {
        $this->upsert_constraint = $upsert_constraint;
        return $this;
    }

    /**
     * upsertSql
     * 
     * @return string
     */
    private function upsertSql()
    {
        if (!$this->columns) return;
        if (!$this->upsert_constraint) {
            $msg = 'Not found upsert constraint key!';
            dump($msg);
            return;
        }

        //insert
        foreach ($this->columns as $key => $type) {
            $value = self::sqlValue($this->value[$key]);
            if ($key == 'created_at') $value = 'current_timestamp';
            $columns[] = $key;
            $values[] = $value;
        }
        $column = implode(',', $columns);
        $value = implode(',', $values);
        $insert_sql = "INSERT INTO {$this->table_name} ({$column}) VALUES ({$value})";

        //update
        foreach ($this->value as $key => $value) {
            if (isset($this->columns[$key])) {
                $value = self::sqlValue($value);
                if ($key == 'created_at') {

                } else if ($key == 'updated_at') {
                    $set_values[] = "updated_at = current_timestamp";
                } else {
                    $set_values[] = "{$key} = {$value}";
                }
            }
        }
        if ($set_values) $set_value = implode(', ', $set_values);
        $update_sql = "UPDATE SET {$set_value}";

        $sql = $insert_sql;
        $sql.= " ON CONFLICT ON CONSTRAINT {$this->upsert_constraint} DO ";
        $sql.= $update_sql;

        return $sql;
    }

    /**
     * updates Sql
     * 
     * @return string
     */
    private function updatesSql($values)
    {
        if (!$values) return;
        if (!$this->conditions) return;
        foreach ($values as $key => $value) {
            if ($key) {
                if (is_bool($value)) {
                    if ($value === true) {
                        $value = 'TRUE';
                    } else {
                        $value = 'FALSE';
                    }
                    $set_values[] = "{$key} = {$value}";
                } else {
                    $set_values[] = "{$key} = '{$value}'";
                }
            }
        }
        if (isset($this->columns['updated_at'])) $set_values[] = "updated_at = current_timestamp";
        if ($set_values) $set_value = implode(', ', $set_values);

        if ($set_value) {
            $condition = $this->sqlConditions($this->conditions);
            $sql = "UPDATE {$this->table_name} SET {$set_value} WHERE {$condition};";
        }
        return $sql;
    }

    /**
     * delete Sql
     * 
     * @return string
     */
    private function deleteSql()
    {
        $sql = '';
        if (!$this->id) return;
        $where = $this->whereSql();
        if ($where) $sql = "DELETE FROM {$this->table_name} {$where};";
        return $sql;
    }

    /**
     * deletes Sql
     * 
     * @return string
     */
    private function deletesSql()
    {
        $sql = '';
        $where = $this->whereSql();
        $sql = "DELETE FROM {$this->table_name} {$where};";
        return $sql;
    }

    /**
     * truncate Sql
     * 
     * RESTART IDENTITY
     * CONTINUE IDENTITY
     * 
     * CASCADE
     * RESTRICT
     * 
     * @param string $option
     * @return string
     */
    private function truncateSql($option = null)
    {
        $sql = "TRUNCATE {$this->table_name} {$option};";
        return $sql;
    }

    /**
     * sql condition
     * 
     * @param array $conditions
     * @return string
     **/
    function sqlConditions($conditions)
    {
        if (is_null($conditions)) return;
        if (is_string($conditions)) {
            $condition = $conditions;
        } elseif (is_array($conditions)) {
            $condition = implode(' AND ', $conditions);
        }
        return $condition;
    }

    /**
     * sql condition
     * 
     * @param array $conditions
     * @return string
     **/
    function sqlOR($conditions)
    {
        if (is_null($conditions)) return;
        if (is_string($conditions)) {
            $condition = $conditions;
        } elseif (is_array($conditions)) {
            $condition = implode(' OR ', $conditions);
        }
        return $condition;
    }

    /**
     * sql order
     * 
     * @param array $orders
     * @return string
     **/
    function sqlOrders($orders)
    {
        if (!$this->is_sort_order) return;
        if (!$orders) return;
        foreach ($orders as $order) {
            if ($order['column']) {
                $column = "{$this->table_name}.{$order['column']}";
                if ($order['column_type']['type'] == 'NUMBER') {
                    $format =  ($order['column_type']['format']) ? $order['column_type']['format'] : 99999999;
                    $column = "TO_NUMBER({$column}, '{$format}')";
                }
                $_orders[] = ($order['option']) ? "{$column} {$order['option']}": $column;
            }
        }
        if ($_orders) $results = implode(', ', $_orders);
        return $results;
    }

    /**
     * sql group by
     * 
     * @param array $conditions
     * @return string
     **/
    function sqlGroupBy($group_by_columns)
    {
        if (!is_array($group_by_columns)) return;
        $group_by_column = implode(',', $group_by_columns);
        return $group_by_column;
    }

    /**
     * postgres table attributes info
     *
     * @param string $table_name
     * @return array
     **/
    public function pgMetaData($table_name)
    {
        $connection = $this->connection();
        $values = pg_meta_data($connection, $table_name);
        return $values;
    }

    /**
     * postgres table attributes info
     *
     * @param string $table_name
     * @return string
     **/
    public function pgDBname()
    {
        $connection = $this->connection();
        $dbname = pg_dbname($connection);
        return $dbname;
    }

    /**
     * pg_class array with attribute
     *
     * @return array
     **/
    public function pgClassArrayByConstraints($pg_class, $pg_constraints)
    {
        $relids[$pg_class['pg_class_id']] = $pg_class['pg_class_id'];
        foreach ($pg_constraints as $contype => $_pg_constraints) {
            foreach ($_pg_constraints as $pg_constraint) {
                if ($pg_constraint['confrelid'] > 0) $relids[$pg_constraint['confrelid']] = $pg_constraint['confrelid'];
            }
        }
        return $this->pgClassesArray($relids);
    }

    /**
     * pg_classes array with attribute, constraint
     * 
     * @param int[] pg_class_id
     * @return array
     **/
    public function pgClassesArray($pg_class_ids = null)
    {
        $pg_classes = $this->pgClasses($pg_class_ids);
        if (!$pg_classes) return;
        $table_comments = $this->tableCommentsArray();

        foreach ($pg_classes as $pg_class) {
            $table_name = $pg_class['relname'];
            $is_numbering = self::isNumberingName($table_name);
            if ($table_name && $pg_class['pg_class_id'] && !$is_numbering) {
                $pg_class['comment'] = $table_comments[$table_name];
                $pg_attributes = $this->pgAttributes($table_name);
                if ($pg_attributes) {
                    $attributes = null;
                    $column_comments = $this->columnCommentArray($table_name);

                    $names = null;
                    foreach ($pg_attributes as $pg_attribute) {
                        if ($pg_attribute['attnum'] > 0) {
                            $pg_attribute['comment'] = $column_comments[$pg_attribute['attname']];
                            $attributes[] = $pg_attribute;
                        }
                    }
                }
                $pg_class['pg_attribute'] = $attributes;
                $pg_class['pg_constraint']['primary'] = $this->pgConstraints($pg_class['pg_class_id'], 'p');
                $pg_class['pg_constraint']['unique'] = $this->pgConstraints($pg_class['pg_class_id'], 'u');
                $pg_class['pg_constraint']['foreign'] = $this->pgForeignConstraints($pg_class['pg_class_id']);

                $values[] = $pg_class;
            }
        }
        return $values;
    }


    /**
     * pg_class array with attribute, constraint
     * 
     * @param int pg_class_id
     * @return array
     **/
    public function pgClassArray($pg_class_id)
    {
        $pg_class = $this->pgClass($pg_class_id);
        if (!$pg_class) return;

        $table_comment = $this->tableComment($pg_class['relname']);
        $pg_class['comment'] = $table_comment['description'];
        $pg_attributes = $this->pgAttributes($pg_class['relname']);

        if ($pg_attributes) {
            $attributes = null;
            $column_comments = $this->columnCommentArray($pg_class['relname']);

            $names = null;
            foreach ($pg_attributes as $pg_attribute) {
                if ($pg_attribute['attnum'] > 0) {
                    $pg_attribute['comment'] = $column_comments[$pg_attribute['attname']];
                    $attributes[] = $pg_attribute;
                }
            }
        }
        $pg_class['pg_attribute'] = $attributes;
        $pg_class['pg_constraint']['primary'] = $this->pgConstraints($pg_class['pg_class_id'], 'p');
        //$pg_class['pg_constraint']['unique'] = $this->pgConstraints($pg_class['pg_class_id'], 'u');
        $pg_class['pg_constraint']['unique'] = $this->pgUniqueConstraints($pg_class['pg_class_id']);
        $pg_class['pg_constraint']['foreign'] = $this->pgForeignConstraints($pg_class['pg_class_id']);

        return $pg_class;
    }

    /**
     * tableArray
     * 
     * @return array
     **/
    public function tableArray()
    {
        $pg_classes = $this->pgClasses();
        $comments = $this->tableCommentsArray();

        if ($pg_classes) {
            foreach ($pg_classes as $pg_class) {
                if ($pg_class['pg_class_id']) {
                    $pg_class['comment'] = $comments[$pg_class['relname']];
                    $values[$pg_class['relname']] = $pg_class;
                }
            }
        }
        return $values;
    }

    /**
     * attribute informations
     * 
     * TODO: pg_field_table ?
     * TODO: pg_field_type_oid ?
     * TODO: pg_field_type ?
     * 
     * @param string $table_name
     * @return array
     **/
    public function attributeArray($table_name)
    {
        if (!$table_name) return;
        $column_comments = $this->columnCommentArray($table_name);

        $pg_attributes = $this->pgAttributes($table_name);
        if ($pg_attributes) {
            foreach ($pg_attributes as $pg_attribute) {
                if ($pg_attribute['attnum'] > 0) {
                    $pg_attribute['comment'] = $column_comments[$pg_attribute['attname']];
                    $values[$pg_attribute['attnum']] = $pg_attribute;
                }
            }
        }
        return $values;
    }


    /**
     * pgFields
     * 
     * TODO: pg_field_table ?
     * TODO: pg_field_type_oid ?
     * TODO: pg_field_type ?
     * 
     * @param string $table_name
     * @return array
     **/
    public function pgFields($table_name)
    {
        if (!$table_name) return;

        return $values;
    }

    /**
     * databases
     * 
     * @param array $conditions
     * @return array
     **/
    function pgDatabases($conditions = null)
    {
        $this->dbname = null;
        $this->loadDBInfo();
        //$sql = "SELECT * FROM pg_database WHERE datacl IS NULL;";
        $conditions[] = 'datistemplate = false';
        $where = implode(' AND ', $conditions);
        $sql = "SELECT * FROM pg_database WHERE {$where};";
        return $this->fetchRows($sql);
    }

    /**
     * pg_database
     * 
     * @param string $name
     * @return array
     **/
    function pgDatabase($name = null)
    {
        if (!$name) $name = $this->dbname;
        $sql = "SELECT * FROM pg_database WHERE datname = '{$name}';";
        return $this->fetchRow($sql);
    }

    /**
     * pg_tables
     *
     * @param
     * @return array
     **/
    function pgTables($schema_name = 'public')
    {
        $sql = "SELECT * FROM pg_tables WHERE schemaname = '{$schema_name}' ORDER BY tablename;";
        return $this->fetchRows($sql);
    }

    /**
     * pg_class
     *
     * @param string $relname
     * @param string $relkind
     * @param string $schema_name
     * @return array
     **/
    function pgClassByRelname($relname, $relkind = 'r', $schema_name = 'public')
    {
        $sql = "SELECT pg_class.oid AS pg_class_id, * FROM pg_class 
        LEFT JOIN pg_tables ON pg_tables.tablename = pg_class.relname
        LEFT JOIN pg_namespace ON pg_namespace.oid = pg_class.relnamespace
        WHERE relkind = '{$relkind}' 
        AND relfilenode > 0 
        AND nspname = '{$schema_name}'
        AND relname = '{$relname}';
        ";
        return $this->fetchRow($sql);
    }

    /**
     * pg_class
     *
     * @param int $pg_class_id
     * @param array $conditions
     * @param string $relkind
     * @param string $schema_name
     * @return array
     **/
    function pgClass($pg_class_id, $conditions = null, $relkind = 'r', $schema_name = 'public')
    {
        $sql = "SELECT pg_class.oid AS pg_class_id, * FROM pg_class 
        LEFT JOIN pg_tables ON pg_tables.tablename = pg_class.relname
        LEFT JOIN pg_namespace ON pg_namespace.oid = pg_class.relnamespace";

        $conditions[] = "relkind = '{$relkind}'";
        $conditions[] = "relfilenode > 0";
        $conditions[] = "nspname = '{$schema_name}'";
        $conditions[] = "pg_class.oid = {$pg_class_id}";
        $condition = $this->sqlConditions($conditions);
        $sql .= " WHERE {$condition}";
        $sql .= " ORDER BY relname;";
        return $this->fetchRow($sql);
    }

    /**
     * pg_classes
     *
     * relkind
     * r: table
     * s: sequence
     * v: view
     * m: materialized view
     * c:
     * t: toast table
     * f: foreign table
     *
     * @param array $pg_class_ids
     * @param array $conditions
     * @param string $relkind
     * @param string $schema_name
     * @return array
     **/
    function pgClasses($pg_class_ids = null, $conditions = null, $relkind = 'r', $schema_name = 'public')
    {
        $sql = "SELECT pg_class.oid AS pg_class_id, * FROM pg_class 
        LEFT JOIN pg_tables ON pg_tables.tablename = pg_class.relname
        LEFT JOIN pg_namespace ON pg_namespace.oid = pg_class.relnamespace";

        $conditions[] = "relkind = '{$relkind}'";
        $conditions[] = "relfilenode > 0";
        $conditions[] = "relhasindex = TRUE";
        $conditions[] = "nspname = '{$schema_name}'";
        if ($pg_class_ids) {
            $pg_class_id = implode(',', $pg_class_ids);
            $conditions[] = "pg_class.oid in ({$pg_class_id})";
        }
        $condition = $this->sqlConditions($conditions);
        $sql .= " WHERE {$condition}";
        $sql .= " ORDER BY relname;";
        return $this->fetchRows($sql);
    }

    /**
     * pg_class
     *
     * @param int $pg_class_id
     * @param string $relkind
     * @param string $schema_name
     * @return array
     **/
    function pgClassById($pg_class_id, $relkind = 'r', $schema_name = 'public')
    {
        $sql = "SELECT * FROM pg_class 
        LEFT JOIN pg_tables ON pg_tables.tablename = pg_class.relname
        LEFT JOIN pg_namespace ON pg_namespace.oid = pg_class.relnamespace
        WHERE relkind = '{$relkind}' 
        AND nspname = '{$schema_name}'
        AND pg_class.oid = '{$pg_class_id}';";
        return $this->fetchRow($sql);
    }

    /**
     * pg_tables (table_name)
     *
     * @param string $table_name
     * @return array
     **/
    function pgTableByTableName($table_name, $schema_name = 'public')
    {
        if (!$table_name) return;
        $sql = "SELECT * FROM pg_tables WHERE schemaname = '{schema_name}' AND tablename = '{$table_name}';";
        return $this->fetchRow($sql);
    }

    /**
     * attributes
     *
     * TODO: select_columns
     *
     * @param string $table_name
     * @param array $select_columns
     * @return array
     **/
    function pgAttributes($table_name = null, $select_columns = null)
    {
        $sql = "SELECT pg_class.oid AS pg_class_id, * FROM pg_class 
                LEFT JOIN pg_attribute ON pg_class.oid = pg_attribute.attrelid
                LEFT JOIN information_schema.columns ON information_schema.columns.table_name = pg_class.relname
                AND information_schema.columns.column_name = pg_attribute.attname 
                WHERE pg_attribute.attnum > 0
                AND atttypid > 0";

        if ($table_name) $sql .= " AND relname = '{$table_name}'";
        $sql .= ' ORDER BY pg_attribute.attname;';
        return $this->fetchRows($sql);
    }

    /**
     * pg_attribute
     *
     * @param string $table_name
     * @param string $column_name
     * @return array
     **/
    function pgAttributeByColumn($table_name, $column_name)
    {
        $sql = "SELECT pg_class.oid AS pg_class_id, * FROM pg_class 
                LEFT JOIN pg_attribute ON pg_class.oid = pg_attribute.attrelid
                LEFT JOIN information_schema.columns ON information_schema.columns.table_name = pg_class.relname
                AND information_schema.columns.column_name = pg_attribute.attname 
                WHERE pg_attribute.attnum > 0
                AND atttypid > 0 
                AND attname = '{$column_name}'
                AND relname = '{$table_name}';";
        return $this->fetchRow($sql);
    }

    /**
     * pg_attribute by attnum
     *
     * @param int $pg_class_id
     * @param int $attnum
     * @return array
     **/
    function pgAttributeByAttnum($pg_class_id, $attnum, $schama_name = 'public')
    {
        if (!$pg_class_id) return;
        if (!$attnum) return;
        $sql = "SELECT * FROM pg_attribute
        WHERE attnum > 0
        AND attnum = '{$attnum}'
        AND attrelid = '{$pg_class_id}';";
        return $this->fetchRow($sql);
    }

    /**
     * pg_attribute
     *
     * @param int $pg_class_id
     * @return array
     **/
    function pgAttributeByAttrelid($pg_class_id)
    {
        if (!$pg_class_id) return;
        $sql = "SELECT * FROM pg_attribute WHERE attrelid = {$pg_class_id};";
        return $this->fetchRows($sql);
    }

    /**
     * pg_attribute
     *
     * @param int $pg_class_id
     * @param string $attnum
     * @return array
     **/
    function pgAttributeByIdName($pg_class_id, $attname)
    {
        if (!$pg_class_id) return;
        if (!$attname) return;
        $sql = "SELECT * FROM pg_attribute WHERE attrelid = {$pg_class_id} AND attname = '{$attname}';";
        return $this->fetchRow($sql);
    }

    /**
     * diff pg_attribute
     *
     * @param int $pg_class_id
     * @param string $attnum
     * @return array
     **/
    function diffPgAttributes($orign_pgsql, $orign_table_name)
    {
        $orign_pg_attributes = $orign_pgsql->pgAttributes($orign_table_name);
        foreach ($orign_pg_attributes as $orign_pg_attribute) {

        }
        $pg_attributes = $this->pgAttributes($this->name);
    }

    /**
     * update table comment
     *
     * @param  string $table_name
     * @param  string $comment
     * @return array
     */
    function updateTableComment($table_name, $comment)
    {
        if (!$table_name) return;
        if (!$comment) return;
        $sql = "COMMENT ON TABLE \"{$table_name}\" IS '{$comment}';";
        return $this->query($sql);
    }

    /**
     * update column comment
     *
     * @param  string $table_name
     * @param  string $column_name
     * @param  string $comment
     * @return array
     */
    function updateColumnComment($table_name, $column_name, $comment)
    {
        if (!$table_name) return;
        if (!$column_name) return;
        if (!$comment) return;
        $sql = "COMMENT ON COLUMN \"{$table_name}\".{$column_name} IS '{$comment}';";
        return $this->query($sql);
    }

    /**
     * table comments
     *
     * @param  string $table_name
     * @param  string $schama_name
     * @return array
     */
    function tableComments($table_name = null, $schama_name = 'public')
    {
        $sql = "SELECT psut.relname ,pd.description
                FROM pg_stat_user_tables psut ,pg_description pd
                WHERE psut.relid = pd.objoid
                AND schemaname = '{$schama_name}' 
                AND pd.objsubid = 0";

        if ($table_name) $sql .= "relfilename = '{$table_name}'";
        $sql .= ";";
        return $this->fetchRows($sql);
    }

    /**
     * table comments array
     *
     * @param  string $table_name
     * @param  string $schama_name
     * @return array
     */
    function tableCommentsArray($table_name = null, $schama_name = 'public')
    {
        $comments = $this->tableComments();
        if (!$comments) return;
        foreach ($comments as $comment) {
            $values[$comment['relname']] = $comment['description'];
        }
        return $values;
    }

    /**
     * table comment
     *
     * @param  string $table_name
     * @return array
     */
    function tableComment($table_name)
    {
        if (!$table_name) return;
        $sql = "SELECT pg_stat_all_tables.relname, pg_description.description
                FROM pg_stat_all_tables, pg_description
                WHERE pg_stat_all_tables.relname='\"{$table_name}\"'";
        return $this->fetchRow($sql);
    }

    /**
     * column comments
     *
     * @return array
     */
    function pgColumnComments()
    {
        $sql = "SELECT psat.relname, pa.attname, pd.description
                FROM pg_stat_all_tables psat ,pg_description pd ,pg_attribute pa
                WHERE psat.relid = pd.objoid
                AND pd.objsubid <> 0
                AND pd.objoid = pa.attrelid
                AND pd.objsubid = pa.attnum
                ORDER BY pd.objsubid;";
        return $this->fetchRows($sql);
    }

    /**
     * column comment
     *
     * @param  string $table_name
     * @return array
     */
    function pgColumnComment($table_name)
    {
        $sql = "SELECT psat.relname, pa.attname, pd.description
                FROM pg_stat_all_tables psat ,pg_description pd ,pg_attribute pa
                WHERE psat.schemaname = (SELECT schemaname FROM pg_stat_user_tables WHERE relname = '{$table_name}')
                AND psat.relname='{$table_name}'
                AND psat.relid=pd.objoid
                AND pd.objsubid <> 0
                AND pd.objoid = pa.attrelid
                AND pd.objsubid = pa.attnum
                ORDER BY pd.objsubid;";
        return $this->fetchRows($sql);
    }

    /**
     * columnCommentsArray
     * 
     * @param string $table_name
     * @return array
     **/
    public function columnCommentsArray()
    {
        $comments = $this->pgColumnComments();
        if ($comments) {
            foreach ($comments as $comment) {
                $column_comments[$comment['attname']] = $comment['description'];
            }
        }
        return $column_comments;
    }

    /**
     * columnCommentArray
     * 
     * @param string $table_name
     * @return array
     **/
    public function columnCommentArray($table_name)
    {
        $comments = $this->pgColumnComment($table_name);
        if ($comments) {
            foreach ($comments as $comment) {
                $column_comments[$comment['attname']] = $comment['description'];
            }
        }
        return $column_comments;
    }

    /**
     * pg constraints
     *
     * c = check
     * f = foreign key
     * p = primary key
     * u = unique
     * 
     * @param  int $pg_class_id
     * @param  string $type
     * @param  array $conditions
     * @return array
     */
    function pgConstraints($pg_class_id, $type = null, $conditions = null)
    {
        if (!$pg_class_id) return;

        $sql = "SELECT * FROM pg_constraint";
        $sql.= " LEFT JOIN pg_attribute ON";
        $sql.= " pg_constraint.conrelid = pg_attribute.attrelid";
        $sql.= " AND pg_attribute.attnum = ANY(pg_constraint.conkey)";

        if ($pg_class_id) $conditions[] = "pg_constraint.conrelid = '{$pg_class_id}'";
        if ($type) $conditions[] = "pg_constraint.contype = '{$type}'";

        if ($conditions) {
            $condition = $this->sqlConditions($conditions);
            $sql.= " WHERE {$condition}";
        }

        $sql.= ';';
        return $this->fetchRows($sql);
    }

    /**
     * pg constraints
     *
     * c = check
     * f = foreign key
     * p = primary key
     * u = unique
     * 
     * @param  int $pg_class_id
     * @param  int $attnum
     * @param  string $type
     * @return array
     */
    function pgConstraintByAttnum($pg_class_id, $attnum, $type = null)
    {
        if (!$pg_class_id) return;

        $sql = "SELECT * FROM pg_constraint";
        $sql.= " LEFT JOIN pg_attribute ON";
        $sql.= " pg_constraint.conrelid = pg_attribute.attrelid";
        $sql.= " AND pg_attribute.attnum = ANY(pg_constraint.conkey)";

        $conditions[] = "pg_constraint.conrelid = '{$pg_class_id}'";
        $conditions[] = "pg_attribute.attnum = '{$attnum}'";
        if ($type) $conditions[] = "pg_constraint.contype = '{$type}'";

        if ($conditions) {
            $condition = $this->sqlConditions($conditions);
            $sql.= " WHERE {$condition}";
        }
        $sql.= ';';
        return $this->fetchRow($sql);
    }

    /**
     * pg constraints (unique)
     *
     * @param  int $pg_class_id
     * @return array
     */
    function pgUniqueConstraints($pg_class_id)
    {
        $constraints = $this->pgConstraints($pg_class_id, 'u');
        if (!$constraints) return;
        foreach ($constraints as $constraint) {
            $results[$constraint['conname']][] = $constraint;
        }
        return $results;
    }

    /**
     * pg constraints (foreign)
     *
     * @param  int $pg_class_id
     * @return array
     */
    function pgForeignConstraints($pg_class_id)
    {
        if (!$pg_class_id) return;

        $sql = "SELECT 
                origin.*,
                pg_class.oid as foreign_class_id,
                pg_class.relname as foreign_relname,
                pg_attribute.attnum as foreign_attnum,
                pg_attribute.attname as foreign_attname
                FROM 
                (
                SELECT 
                    pg_constraint.conrelid
                    , pg_constraint.conname
                    , pg_class.oid as pg_class_id
                    , pg_class.relname
                    , pg_attribute.attnum
                    , pg_attribute.attname
                    , pg_constraint.confrelid
                    , pg_constraint.confkey
                    , pg_constraint.contype
                    , pg_constraint.confupdtype
                    , pg_constraint.confdeltype
                    FROM  pg_constraint
                    LEFT JOIN pg_attribute ON pg_constraint.conrelid = pg_attribute.attrelid 
                    AND pg_attribute.attnum = ANY(pg_constraint.conkey)
                    LEFT JOIN pg_class ON pg_constraint.conrelid = pg_class.oid
                    WHERE pg_constraint.contype = 'f' AND pg_constraint.conrelid = '{$pg_class_id}'
                ) AS origin
                LEFT JOIN pg_attribute ON origin.confrelid = pg_attribute.attrelid 
                AND pg_attribute.attnum = ANY(origin.confkey)
                LEFT JOIN pg_class ON origin.confrelid = pg_class.oid
                ;";

        return $this->fetchRows($sql);
    }

    /**
     * pg constraints by constraint name
     *
     * @param  string $name
     * @return array
     */
    function pgConstraintsByConstrainName($name)
    {
        $sql = "SELECT * FROM pg_constraint WHERE conname LIKE '%{$name}%';";
        return $this->fetchRows($sql);
    }

    /**
     * pg constraint groups
     *
     * @param  array $pg_class
     * @return array
     */
    function pgConstraintGroup($pg_class)
    {
        $constraints = $this->pgConstraintsByPgClassID($pg_class['pg_class_id']);
        if (!$constraints) return;

        foreach ($constraints as $constraint) {
            $values[$constraint['contype']][$constraint['conname']] = $constraint;
        }
        return $values;
    }

    /**
     * pg constraint by pg_class
     *
     * @param  string $table_name
     * @return array
     */
    function pgConstraintsByPgClassID($pg_class_id)
    {
        $constraints = $this->pgConstraints($pg_class_id);
        if (!$constraints) return;

        foreach ($constraints as $index => $constraint) {
            $is_numbering = self::isNumberingName($constraint['conname']);

            $conkeys = self::parseConstraintKeys($constraint['conkey']);
            $confkeys = self::parseConstraintKeys($constraint['confkey']);

            $constraints[$index]['conkey'] = $conkeys;
            $constraints[$index]['confkey'] = $confkeys;
        }
        return $constraints;
    }

    /**
     * add pg primary key
     *
     * @param  string $table_name
     * @param  array $columns
     * @return void
     */
    function addPgPrimaryKey($table_name, $column_name)
    {
        $sql = "ALTER TABLE {$table_name} ADD PRIMARY KEY ({$column_name});";
        return $this->query($sql);
    }

    /**
     * rename constraint name
     *
     * @param  string $table_name
     * @param  array $columns
     * @return void
     */
    function renamePgConstraint($table_name, $constraint_name, $new_constraint_name)
    {
        $sql = "ALTER TABLE {$table_name} RENAME CONSTRAINT {$constraint_name} TO {$new_constraint_name};";
        return $this->query($sql);
    }

    /**
     * add pg unique
     *
     * @param  string $table_name
     * @param  array $columns
     * @return void
     */
    function addPgUnique($table_name, $columns)
    {
        if (!$columns) return;
        $column = implode(', ', $columns);
        $sql = "ALTER TABLE {$table_name} ADD UNIQUE ({$column});";
        return $this->query($sql);
    }

    /**
     * add pg foreign key
     *
     * @param  string $table_name
     * @param  string $foreign_column
     * @param  string $reference_table_name
     * @param  string $reference_column
     * @return void
     */
    function addPgForeignKey(
        $table_name,
        $foreign_column,
        $reference_table_name,
        $reference_column,
        $update = null,
        $delete = null,
        $is_not_deferrable = true
    ) {
        $reference_column = "{$reference_table_name}({$reference_column})";
        $sql = "ALTER TABLE {$table_name} ADD FOREIGN KEY ({$foreign_column}) REFERENCES {$reference_column}";

        if ($update) {
            $action = self::$constraint_actions[$update];
            $sql .= " ON UPDATE {$action}";
        }
        if ($delete) {
            $action = self::$constraint_actions[$delete];
            $sql .= " ON DELETE {$action}";
        }
        if ($is_not_deferrable) $sql .= " NOT DEFERRABLE";

        $sql .= ';';
        return $this->query($sql);
    }

    /**
     * remove pg constraint
     *
     * @param  string $table_name
     * @return void
     */
    function removePgConstraint($table_name, $constraint_name)
    {
        $sql = "ALTER TABLE {$table_name} DROP CONSTRAINT {$constraint_name};";
        $results = $this->query($sql);
        return;
    }

    /**
     * remove pg constraints
     *
     * @param  string $table_name
     * @return void
     */
    function removePgConstraints($table_name, $type = null)
    {
        $pg_class = $this->pgClassByRelname($table_name);
        $constraints = $this->pgConstraints($pg_class['pg_class_id'], $type);
        if (!$constraints) return;

        foreach ($constraints as $constraint) {
            $sql = "ALTER TABLE {$table_name} DROP CONSTRAINT {$constraint['conname']};";
            $results = $this->query($sql);
        }
        return;
    }

    /**
     * primary keys
     *
     * @param  string $database_name
     * @param  string $table_name
     * @return array
     */
    function pgPrimaryKeys($database_name, $table_name = null)
    {
        $sql = "SELECT ccu.column_name, tc.table_name
                FROM
                information_schema.table_constraints tc
                ,information_schema.constraint_column_usage ccu
                WHERE
                tc.table_catalog='{$database_name}'
                AND tc.constraint_type='PRIMARY KEY'
                AND tc.table_catalog=ccu.table_catalog
                AND tc.table_schema=ccu.table_schema
                AND tc.table_name=ccu.table_name
                AND tc.constraint_name=ccu.constraint_name";

        if ($table_name) $sql .= " AND tc.table_name = '{$table_name}'";
        $sql .= ";";
        return $this->fetchRows($sql);
    }

    /**
     * change not null
     *
     * @param  string $table_name
     * @param  string $column_name
     * @return array
     */
    public function changeNotNull($table_name, $column_name, $is_required = true)
    {
        if ($is_required) {
            return $this->setNotNull($table_name, $column_name);
        } else {
            return $this->dropNotNull($table_name, $column_name);
        }
    }

    /**
     * not null
     *
     * @param  string $table_name
     * @param  string $column_name
     * @return array
     */
    public function setNotNull($table_name, $column_name)
    {
        $sql = "ALTER TABLE \"{$table_name}\" ALTER COLUMN {$column_name} SET NOT NULL;";
        return $this->query($sql);
    }

    /**
     * not null
     *
     * @param  string $table_name
     * @param  string $column_name
     * @return array
     */
    public function dropNotNull($table_name, $column_name)
    {
        $sql = "ALTER TABLE \"{$table_name}\" ALTER COLUMN {$column_name} DROP NOT NULL;";
        return $this->query($sql);
    }

    /**
     * sqlColumnType
     *
     * @param  string $type
     * @param  int $length
     * @return string
     */
    public function sqlColumnType($type, $length = 0)
    {
        if ($type == 'varchar' && $length > 0) $type.= "({$length})";
        return $type;
    }

    /**
     * Numbering Name
     *
     * @param  string $name
     * @return bool
     */
    static function isNumberingName($name)
    {
        $columns = explode('_', $name);
        foreach ($columns as $column) {
            if (is_numeric($column)) return true;
        }
        return false;
    }

    /**
     * set Model(table name, columns ...) By pg_attributes
     *
     * @param  string $model_name
     * @return PwPgsql
     */
    function table($table_name)
    {
        $this->id_column = 'id';
        $this->entity_name = PwFile::pluralToSingular($table_name);
        $this->name = $table_name;
        $this->from($this->name);

        $pg_attributes = $this->pgAttributes($this->name);
        if (!$pg_attributes) return;

        foreach ($pg_attributes as $pg_attribute) {
            if ($pg_attribute['attname'] != $this->id_column) {
                $value = null;
                $value['type'] = $pg_attribute['udt_name'];
                if ($pg_attribute['attnotnull'] == 't') {
                    $value['is_required'] = true;
                }
                if ($pg_attribute['attname']) {
                    $columns[$pg_attribute['attname']] = $value;
                }
            }
        }
        $this->columns = $columns;
        $this->sql = null;
        return $this;
    }

    /**
     * parseConstraintKeys
     *
     * TODO: preg
     *
     * @param  string $values
     * @return array
     */
    static function parseConstraintKeys($values)
    {
        if (!$values) return;
        $values = str_replace('{', '', $values);
        $values = str_replace('}', '', $values);
        $values = explode(',', $values);
        return $values;
    }

    /**
     * diff from vo model
     *
     * @return void
     */
    public function diffFromVoModel()
    {
        $model_path = BASE_DIR . "app/models/vo/*.php";
        foreach (glob($model_path) as $model_file) {
            $path_info = pathinfo($model_file);

            $model = null;
            $pg_class = null;
            if (class_exists($path_info['filename'])) {
                $model = new $path_info['filename'];
                $pg_class = $this->pgClassByRelname($model->name);
            }
            if (!$pg_class) {
                $sql = $this->createTableSql($model);
                $sql .= $this->constraintSql($model);

                if ($this->is_excute_sql) {
                    $this->query($sql);
                }

                $status = "---- Not Found table ----" . PHP_EOL;
                $status .= "{$path_info['filename']}" . PHP_EOL;
                $status .= "{$model->name}" . PHP_EOL;
                $status .= $sql . PHP_EOL;
                echo ($status);
            } else if ($model && $model->columns) {
                $pg_attributes = null;
                if ($attributes = $this->pgAttributes($model->name)) {
                    foreach ($attributes as $pg_attribute) {
                        if ($pg_attribute['attname']) {
                            $pg_attributes[$pg_attribute['attname']] = $pg_attribute;
                        }
                    }
                }
                if ($pg_attributes) {
                    foreach ($model->columns as $column_name => $column) {
                        $status = '';
                        $attribute = $pg_attributes[$column_name];
                        if (!$attribute) {
                            $options['type'] = $column['type'];
                            $options['length'] = $column['length'];
                            $options['is_required'] = $column['is_required'];
                            $options['is_default_null'] = $column['is_default_null'];

                            $this->addColumn($model->name, $column_name, $options);

                            //TODO status function
                            $status = "---- Not found column----" . PHP_EOL;
                            $status .= "{$model->name}.{$column_name}" . PHP_EOL;
                            $status .= $this->sql . PHP_EOL;
                        } else if ($column['type'] != $attribute['udt_name']) {
                            $options['type'] = $column['type'];
                            $options['length'] = $column['length'];

                            $this->changeColumnType($model->name, $column_name, $options);

                            //TODO status function
                            $status = "---- Alter column type ----" . PHP_EOL;
                            $status .= "{$model->name}.{$column_name} : {$column['type']} != {$attribute['udt_name']}" . PHP_EOL;
                            $status .= $this->sql . PHP_EOL;
                        }
                        echo ($status);
                    }
                }
            }
        }
    }

    /**
     * old db info by old host
     * 
     * @return string
     */
    static function oldDbInfoByOldHost($old_host, $old_db)
    {
        if (!defined('OLD_DB_INFO')) return;
        $old_db_infos = OLD_DB_INFO;
        foreach ($old_db_infos as $key => $_old_db_infos) {
            foreach ($_old_db_infos as $system => $old_db_info) {
                if ($old_db_info['host'] == $old_host && $old_db_info['dbname'] == $old_db) {
                    return $system;
                }
            }
        }
    }

    /**
     * delete records and reset sequence
     *
     * @return 
     */
    function deleteRecords($is_drop_primary = false) {
        $this->deletes();

        if ($this->sql_error) {
            echo($this->sql_error).PHP_EOL;
            exit;
        }
        if ($this->errors) {
            var_export($this->errors);
            exit;
        }

        if ($is_drop_primary) {
            $result = $this->deletePgPrimaryKey($this->name);
            if (!$result) {
                echo("Error: delete primary key").PHP_EOL;
                exit;
            }
            $result = $this->addPgPrimaryKey($this->name, $this->id_column);
            if (!$result) {
                echo("Error: add primary key").PHP_EOL;
                exit;
            }
        }
        $result = $this->resetSequence($this->name);
        if (!$result) {
            $sequence_name = PwPgsql::sequenceName($this->name);
            echo($sequence_name).PHP_EOL;
            echo("Error: reset sequence").PHP_EOL;
            exit;
        }
    }

    /**
     * update empty sort order
     *
     * @return PwPgsql
     */
    function updatesEmptySortOrder() {
        if (!array_key_exists('sort_order', $this->columns)) return $this;
        $this->select([$this->id_column, 'sort_order'])->where('sort_order IS NULL')->all();
        if (!$this->values) return $this;

        $sql = "UPDATE {$this->table_name} SET sort_order = {$this->id_column} WHERE sort_order IS NULL;";
        $results = $this->query($sql);
    }

}