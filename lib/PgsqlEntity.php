<?php
/**
* PgsqlEntity 
*
* @copyright  Copyright (c) 2017 Yohei Yoshikawa (http://yoo-s.com/)
*/
require_once 'Entity.php';

//TODO pg_escape_identifier
//TODO pg_escape_literal
//TODO pg_field_num, pg_field_name

class PgsqlEntity extends Entity {
    var $pg_info = null;
    var $dbname = null;
    var $host = 'localhost';
    var $user = 'postgres';
    var $port = 5432;
    var $password = null;
    var $is_pconnect = false;
    var $is_connect_forece_new = false;
    var $table_name = null;
    var $values = null;
    var $value = null;
    var $conditions = null;
    var $orders = null;
    var $limits = null;
    var $extra_columns = false;
    var $group_columns = false;
    var $joins = array();
    var $sql = null;
    var $sqls = null;
    var $is_bulk_select = false;

    static $pg_info_columns = ['dbname', 'user', 'host', 'port', 'password'];
    static $constraint_keys = ['p' => 'Primary Key',
                              'u' => 'Unique',
                              'f' => 'Foreign Key',
                              'c' => 'Check'
                              ];
    static $constraint_actions = [
                               'a' => 'NOT ACTION',
                               'c' => 'CASCADE',
                              ];
    /**
    * constructor
    * 
    * @param array $params
    * @return array
    */
    function __construct($params = null) {
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
    function pgVersion() {
        $connection = $this->connection();
        $values = pg_version($connection);
        return $values;
    }

    /**
    * initDb
    * 
    * @return array
    */
    static function initDb() {
        $path = BASE_DIR."script/init_db";
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
    function createDatabase() {
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
    function sequenceName($table_name, $id_column = 'id') {
        if (!$table_name) return;
        $sequence_name = "{$table_name}_{$id_column}_seq";
        return $sequence_name;
    }

    /**
    * sequenceName
    * 
    * @param string $table_name
    * @param string $id_column
    * @return string
    */
    function createSequence($table_name, $id_column = 'id') {
        $sequence_name = self::sequenceName($table_name, $id_column);
        $sql = "CREATE SEQUENCE {$sequence_name};";
        //$sql = "CREATE SEQUENCE IF NOT EXISTS {$sequence_name};";
        return $this->query($sql);
    }

    /**
    * sequenceName
    * 
    * @param string $table_name
    * @param string $id_column
    * @return string
    */
    function dropSequence($table_name, $id_column = 'id') {
        $sequence_name = self::sequenceName($table_name, $id_column);
        $sql = "DROP SEQUENCE {$sequence_name};";
        //$sql = "DROP SEQUENCE IF EXISTS {$sequence_name};";
        return $this->query($sql);
    }

    /**
    * reset sequence
    * 
    * @param string $table_name
    * @param string $id_column
    * @return string
    */
    function resetSequence($table_name, $id_column = 'id') {
        $sequence_name = self::sequenceName($table_name, $id_column);
        $sql = "SELECT SETVAL ('{$sequence_name}', '1', false);";
        return $this->query($sql);
    }

    /**
    * dropDatabase
    * 
    * @return array
    * @return PgsqlEntity
    */
    function dropDatabase() {
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
    * @return PgsqlEntity
    */
    function defaultDBInfo() {
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
    * @return PgsqlEntity
    */
    function setDBName($name) {
        $this->dbname = $name;
        $this->loadDBInfo();
        return $this;
    }

    /**
    * database host
    * 
    * @param array $host
    * @return PgsqlEntity
    */
    function setDBHost($host) {
        $this->host = $host;
        $this->loadDBInfo();
        return $this;
    }

    /**
    * database user
    * 
    * @param array $user
    * @return PgsqlEntity
    */
    function setDBUser($user) {
        $this->user = $user;
        $this->loadDBInfo();
        return $this;
    }

    /**
    * database port
    * 
    * @param array $user
    * @return PgsqlEntity
    */
    function setDBPort($port) {
        $this->port = $port;
        $this->loadDBInfo();
        return $this;
    }

    /**
    * pgInfo
    * 
    * @param array $params
    * @return PgsqlEntity
    */
    function setDBInfo($params) {
        if (!$params) return;
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
    * @return PgsqlEntity
    */
    function loadDBInfo() {
        foreach (self::$pg_info_columns as $column) {
            if (isset($this->$column)) {
                $pg_infos[] = "{$column}={$this->$column}";
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
    function setDBInfoForString($pg_info_string) {
        if (!is_string($pg_info_string)) return;
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
    * column type SQL
    * 
    * @param array $values
    * @return string
    */
    static function columnTypeSql($values) {
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
    static function columnOptionSql($values) {
        $option = '';
        if ($values['is_required']) $option.= "NOT NULL";
        if ($values['is_default_null']) $option.= "DEFAULT NULL";
        return $option;
    }

    /**
    * create table SQL
    * 
    * @param array $models
    * @return string
    */
    function createTablesSQLFromModels($models) {
        foreach ($models as $model) {
            $sql.= $this->createTableSql($model);
            $sql.= PHP_EOL;
        }
        return $sql;
    }

    /**
    * create table SQL
    * 
    * @param PgsqlEntity $model
    * @return string
    */
    public function createTableSql($model) {
        if (!$model) return;

        $column_sqls[] = "{$model->id_column} SERIAL PRIMARY KEY NOT NULL";
        foreach ($model->columns as $column_name => $column) {
            if ($column['type']) {
                $type = self::columnTypeSql($column);
                $option = self::columnOptionSql($column);

                $column_sql = "{$column_name} {$type}";
                if ($option) $column_sql.= " {$option}";
                $column_sqls[] = $column_sql;
            }
        }
        $column_sql = implode("\n, ", $column_sqls);
        $sql = "CREATE TABLE IF NOT EXISTS \"{$model->name}\" (\n{$column_sql}\n);".PHP_EOL;
        $sql.= PHP_EOL;

        return $sql;
    }


    /**
    * create constraint SQL
    * 
    * @param PgsqlEntity $model
    * @return string
    */
    public function constraintSql($model) {
        if (!$model) return;

        if ($model->foreign) {
            foreach ($model->foreign as $conname => $foreign) {
                $sql.= "ALTER TABLE {$model->name}".PHP_EOL;
                $sql.= "      ADD CONSTRAINT {$conname} FOREIGN KEY ({$foreign['column']})".PHP_EOL;
                $sql.= "      REFERENCES {$foreign['foreign_table']}({$foreign['foreign_column']})".PHP_EOL;
                $sql.= "      ON NO ACTION".PHP_EOL;
                $sql.= "      ON UPDATE NO ACTION;".PHP_EOL;
                $sql.= PHP_EOL;
            }
        }

        if ($model->unique) {
            foreach ($model->unique as $conname => $uniques) {
                $unique_column = implode(', ', $uniques);
                $sql.= "ALTER TABLE {$model->name}".PHP_EOL;
                $sql.= "      ADD CONSTRAINT {$conname}".PHP_EOL;
                $sql.= "      UNIQUE ({$unique_column});".PHP_EOL;
                $sql.= PHP_EOL;
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
    public function createTableSqlByName($table_name, $columns) {
        if (!$table_name) return;
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
    function createTablesSQLForProject() {
        $vo_path = BASE_DIR."app/models/vo/";
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
    function createTablesSQLForPath($vo_path, $ext = 'php') {
        if (!file_exists($vo_path)) {
            $message = "Not exists : {$vo_path}";
            echo($message);
            exit;
        }
        $vo_files_path = "{$vo_path}*.{$ext}";

        foreach (glob($vo_files_path) as $file_path) {
            if (is_file($file_path)) {
                $file = pathinfo($file_path);
                $class_name = $file['filename'];
                require_once $file_path;
                $vo = new $class_name();

                $sql.= $this->createTableSql($vo);
            }
        }

        //$sql.= '/*** constraint ***/'.PHP_EOL;
        $sql.= PHP_EOL;
        foreach (glob($vo_files_path) as $file_path) {
            if (is_file($file_path)) {
                $file = pathinfo($file_path);
                $class_name = $file['filename'];
                require_once $file_path;
                $vo = new $class_name();

                $sql.= $this->constraintSql($vo);
            }
        }
        return $sql;
    }

    /**
    * create tables
    * 
    * @return resource
    */
    function createTable($table_name, $columns) {
        $sql = $this->createTableSqlByName($table_name, $columns);
        return $this->query($sql);
    }

    /**
    * create tables for project
    * 
    * @return resource
    */
    function createTablesForProject() {
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
    public function renameDatabase($db_name, $new_db_name) {
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
    public function renameTable($table_name, $new_table_name) {
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
    public function renameColumn($table_name, $column, $new_column) {
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
    public function changeColumnType($table_name, $column, $options) {
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
        $results = $this->query($sql);
        if ($this->sql_error) {
            return false;
        }
    }

    /**
    * dropTable
    * 
    * @param string $table_name
    * @return resource
    */
    public function dropTable($table_name) {
        if (!$table_name) return;

        $sql = "DROP TABLE \"{$table_name}\";";
        return $this->query($sql);
    }

    /**
    * dropColumn
    * 
    * @param string $table_name
    * @param string $column
    * @param string $column
    * @return resource
    */
    public function dropColumn($table_name, $column) {
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
    public function addColumn($table_name, $column, $options) {
        if (!$table_name) return;
        if (!$column) return;
        if (!$options) return;

        $sql = self::addColumnSql($table_name, $column, $options);
        return $this->query($sql);
    }

    public function addColumnSql($table_name, $column, $options) {
        $type = $this->sqlColumnType($options['type'], $options['length']);
        $option = $option = self::columnOptionSql($options);

        $sql = "ALTER TABLE \"{$table_name}\" ADD COLUMN \"{$column}\" {$type}{$option};";
        return $sql;
    }

    /**
    * connection
    * 
    * @return resource
    */
    function connection() {
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
    function checkConnection() {
        $connection = $this->connection();
        if (!$connection) {
            $this->is_connection_error = true;
        }
        return $connection;
    }

    /**
    * connection
    * 
    * @return resource
    */
    function query($sql) {
        $this->sql_error = null;
        $this->sql = $sql;
        $this->conditions = null;
        $this->joins = null;
        if (defined('SQL_LOG') && SQL_LOG) error_log("<SQL> {$sql}");
        if ($pg = $this->connection()) {
            if ($is_busy = pg_connection_busy($pg)) {
                exit('DB connection is busy.');
            }
            $results = pg_query($pg, $sql);
            //var_dump(pg_connection_status($pg) == PGSQL_CONNECTION_OK);
            //var_dump(pg_ping($pg));
            $this->pg_result_status = pg_result_status($results);
            $this->sql_error = pg_last_error($pg);
        }
        if ($pg) pg_close($pg);
        return $results;
    }

    /**
    * fetchRows
    * 
    * @param string $sql
    * @return array
    */
    function fetchRows($sql) {
        $rs = $this->query($sql);
        if ($rs) {
            if ($this->columns) {
                //cast
                if ($this->is_value_object) {
                    while ($row = pg_fetch_object($rs)) {
                        if (isset($this->id_index) && $this->id_index == true) {
                            $id = (int) $row[$this->id_column];
                            $rows[$id] = $this->castRow($row);
                        } else {
                            $rows[] = $this->castRow($row);
                        }
                    }
                } else {
                    while ($row = pg_fetch_assoc($rs)) {
                        if (isset($this->id_index) && $this->id_index == true) {
                            $id = (int) $row[$this->id_column];
                            $rows[$id] = $this->castRow($row);
                        } else {
                            $rows[] = $this->castRow($row);
                        }
                    }
                }
            } else {
                $rows = pg_fetch_all($rs);
            }
            return $rows;
        } else {
            return;
        }
    }

    /**
    * fetchRow
    * 
    * @return array
    */
    function fetchRow($sql) {
        $rs = $this->query($sql);
        if ($rs) {
            if ($this->is_value_object) {
                $row = pg_fetch_object($rs);
            } else {
                //$row = pg_fetch_array($rs, null, PGSQL_ASSOC);
                $row = pg_fetch_assoc($rs);
            }
            return ($row) ? $row : null;
        } else {
            return;
        }
    }

    /**
    * fetch_result
    * 
    * @return string
    */
    function fetch_result($sql) {
        $rs = $this->query($sql);
        if ($rs) {
            $result = pg_fetch_result($rs, 0, 0);
            return (isset($result)) ? $result : null;
        } else {
            return null;
        }
    }

    /**
    * 
    * @param string $key
    * @param string $separater
    * @return void
    */
    public function partition($key, $separater = '_') {
        if (!$key) return;
        $this->table_name = "{$this->name}{$separater}{$key}";
    }

    /**
    * fetch
    * 
    * @param  int $id
    * @param  array $params
    * @return Object
    */
    public function fetch($id, $params=null) {
        $this->conditions = null;
        $this->orders = null;
        $this->values = null;
        if (!$id) return $this;

        $this->where("{$this->id_column} = {$id}")->one($params);
        $this->_value = $this->value;
        return $this;
    }

    /**
    * find
    * 
    * @param  int $id
    * @return Object
    */
    public function find($id) {
        return $this->fetch($id);
    }

    /**
    * first
    * 
    * @param  int $id
    * @return Object
    */
    public function first() {
        $this->limit(1);
        return $this->all();
    }

    public function chunk($limit) {
        $count = $this->count();
        $offset = 0;
        $amount = ceil($count / $limit);

        if ($amount == 0) return;
        if ($amount == 1) return $this->all();

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
    * relation by model
    * 
    * @param  string $model_name
    * @param  string $foreign_key
    * @param  string $value_key
    * @return PgsqlEntity
    */
    public function bindOne($model_name, $foreign_key = null, $value_key = null) {
        if (!is_string($model_name)) exit('bindOne: $model_name is not string');
        $relation = DB::table($model_name);

        $column_name = $relation->entity_name;
        $relation = $this->hasOne(get_class($relation), $foreign_key, $value_key);
        $this->$column_name = $relation;
        return $this;
    }

    /**
    * relation by model
    * 
    * @param  string $model_name
    * @param  array $conditions
    * @param  string $foreign_key
    * @param  string $value_key
    * @return PgsqlEntity
    */
    public function bindMany($model_name, $foreign_key = null, $value_key = null) {
        if (!is_string($model_name)) exit('bindMany: $model_name is not string');
        $relation = DB::table($model_name);

        $column_name = $relation->entity_name;
        $relation = $this->hasMany(get_class($relation), $foreign_key, $value_key);
        $this->$column_name = $relation;
        return $this;
    }

    /**
    * relation by model
    * 
    * @param  string $model_name
    * @param  string $foreign_key
    * @param  string $value_key
    * @return PgsqlEntity
    */
    public function bindBelongsTo($model_name, $foreign_key = null, $value_key = null) {
        if (!is_string($model_name)) exit('bindBelongsTo: $model_name is not string');
        $relation = DB::table($model_name);

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
    * @return PgsqlEntity
    */
    public function hasOne($model_name, $foreign_key = null, $value_key = null) {
        if (is_null($this->value)) return $this;

        if (!is_string($model_name)) exit('hasOne: $model_name is not string');
        $relation = DB::table($model_name);

        if (!$foreign_key) $foreign_key = "{$this->entity_name}_id";
        if (!$value_key) $value_key = $this->id_column;

        $value = $this->value[$value_key];
        if (is_null($value)) return $this;

        $condition = "{$foreign_key} = '{$value}'";
        return $relation->where($condition)->one();
    }

    /**
    * relations SQL select by model
    * 
    * @param  string $class_name
    * @param  string $foreign_key
    * @param  string $value_key
    * @return PgsqlEntity
    */
    public function hasMany($class_name, $foreign_key = null, $value_key = null) {
        return $this->relationMany($class_name, $foreign_key, $value_key)->all();
    }

    /**
    * relations by model
    * 
    * @param  string $class_name
    * @param  string $foreign_key
    * @param  string $value_key
    * @return PgsqlEntity
    */
    public function relationMany($class_name, $foreign_key = null, $value_key = null) {
        if (is_null($this->value)) return $this;

        if (!is_string($class_name)) exit('hasMany: $class_name is not string');
        $relation = DB::table($class_name);

        if (!$foreign_key) $foreign_key = "{$this->entity_name}_id";
        if (!$value_key) $value_key = $this->id_column;

        $value = $this->value[$value_key];
        if (is_null($value)) return $this;

        $condition = "{$foreign_key} = '{$value}'";
        $relation->where($condition);
        return $relation;
    }

    /**
    * through relations by model
    * 
    * @param  string $class_name
    * @param  array $conditions
    * @param  string $foreign_key
    * @param  string $value_key
    * @return PgsqlEntity
    */
    public function hasManyThrough($class_name, $through_class_name, $foreign_key = null, $value_key = null) {
        if (!is_string($class_name)) exit('hasMany: $class_name is not string');
        $relation = DB::table($class_name);

        if (!is_string($through_class_name)) exit('hasMany: $through_class_name is not string');
        $through = DB::table($through_class_name);

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
    * @return PgsqlEntity
    */
    public function belongsTo($class_name, $foreign_key = null, $value_key = null) {
        if (is_null($this->value)) return $this;

        if (!is_string($class_name)) exit('belongsTo: $class_name is not string');
        $relation = DB::table($class_name);

        if (!$foreign_key) $foreign_key = $relation->id_column;
        if (!$value_key) $value_key = "{$relation->entity_name}_id";

        $value = $this->value[$value_key];
        if (is_null($value)) return $this;
        $condition = "{$foreign_key} = '{$value}'";
        return $relation->where($condition)->one();
    }

    /**
    * relation by model
    *
    * TODO
    * 
    * @param  string $class_name
    * @param  string $foreign_key
    * @param  string $value_key
    * @return PgsqlEntity
    */
    public function belongsToMany($class_name, $foreign_key = null, $value_key = null) {
        //TODO
        // foreach ($this->values as $value) {

        // }
        return $this;
    }


    /**
    * fetch
    * 
    * @param  int $id
    * @param  array $params
    * @return array
    */
    public function fetchValue($id, $params=null) {
        return $this->fetch($id, $params)->value;
    }

    /**
    * select
    * 
    * @param  array $params
    * @return PgsqlEntity
    */
    public function select($params = null) {
        $this->select_columns = null;
        if (is_array($params)) $this->select_columns = $params;
        unset($this->id);
        return $this;
    }

    /**
    * one
    * 
    * @return array
    */
    public function one() {
        $this->values = null;
        $sql = $this->selectSql();
        $value = $this->fetchRow($sql);

        $this->value = $this->castRow($value);
        if (is_array($this->value) && isset($this->value[$this->id_column])) {
            $this->id = (int) $this->value[$this->id_column];
        }
        $this->_value = $this->value;
        return $this;
    }

    /**
    * select all
    * 
    * @return PgsqlEntity
    */
    public function bulkSelect() {
        $this->is_bulk_select = true;
        return $this;
    }

    /**
    * select all
    * 
    * @param  array $params
    * @return PgsqlEntity
    */
    public function all() {
        if ($this->is_bulk_select) {
            return $this->bulkAll($this->limit);
        } else {
            $sql = $this->selectSql();
            $this->values = $this->fetchRows($sql);
            return $this;
        }
    }

    /**
    * select all
    * 
    * @param  array $params
    * @return PgsqlEntity
    */
    public function bulkAll($limit) {
        $this->sqls = null;
        $has_data = true;
        $offset = 0;
        $i = 0;

        if (!$limit) $limit = 100;
        $this->limit = $limit;
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
    public function get() {
        return $this->all();
    }

    /**
    * select all values
    * 
    * @return array
    */
    public function allValues() {
        return $this->all()->values;
    }

    /**
    * select one value
    * 
    * @return array
    */
    public function oneValue() {
        return $this->one()->value;
    }

    /**
    * save
    * 
    * @param  array $posts
    * @param  int $id
    * @return PgsqlEntity
    */
    public function save($posts = null, $id = null) {
        if ($id) $this->fetch($id);
        if ($this->isNew()) {
            $this->insert($posts);
        } else {
            $this->update($posts);
        }
    }

    /**
    * insert
    * 
    * @param  array $posts
    * @return PgsqlEntity
    */
    public function insert($posts = null) {
        $this->id = null;
        $this->values = null;
        if ($posts) $this->takeValues($posts);

        $this->validate();
        if ($this->errors) {
            return $this;
        }

        $sql = $this->insertSql();
        if (!$sql) {
            $this->addError('sql', 'error');
            return $this;
        }

        if ($result = $this->fetch_result($sql)) {
            $this->id = (int) $result;
            $this->value[$this->id_column] = $this->id;
        } else {
            //TODO session
            $this->addError('sql', 'error');
        }
        return $this;
    }

    /**
    * inserts
    * 
    * @param  array $rows
    * @return PgsqlEntity
    */
    public function copyFrom($rows) {
        $model_columns = array_keys($this->columns);
        pg_copy_from($this->connection(), $this->table_name, $rows);
    }

    /**
    * inserts
    * 
    * @param  array $posts
    * @return PgsqlEntity
    */
    public function inserts($posts) {
        $model_columns = array_keys($this->columns);

        pg_insert($this->connection(), $this->table_name, $posts);
    }

    /**
    * update
    * 
    * TODO update
    * mixed pg_update ( resource $connection , string $table_name , array $data , array $condition [, int $options = PGSQL_DML_EXEC ] )
    * 
    * @param  array $posts
    * @param  int $id
    * @return PgsqlEntity
    */
    public function update($posts = null, $id = null) {
        if ($id) $this->fetch($id);
        if ($posts) $this->takeValues($posts);

        $this->validate();
        if ($this->errors) {
            return $this;
        }
        $sql = $this->updateSql();
        if (!$sql) {
            return $this;
        }

        $result = $this->query($sql);
        if ($result !== false) {
            $this->_value = $this->value;
        } else {
            //TODO session
            $this->addError('sql', 'error');
        }
        return $this;
    }

    /**
    * updates
    * 
    * @param  array $posts
    * @return PgsqlEntity
    */
    public function updates($posts) {
        if (!$posts) return;
        $sql = $this->updatesSql($posts);
        if (!$sql) {
            $message = "SQL Error: {$sql}";
            echo($message);
            exit;
            return $this;
        }

        $result = $this->query($sql);
        if ($result !== false) {
            $this->_value = $this->value;
        } else {
            $this->addError('sql', 'error');
            $message = "SQL Error: {$sql}";
            echo($message);
            exit;
        }
        return $this;
    }

    /**
    * values from old table
    * 
    * @param  PgsqlEntity $old_pgsql
    * @return array
    */
    public function valuesFromOldTable($old_pgsql) {
        if (!$old_pgsql) exit('Not found old_pgsql');

        $sql = $this->selectSqlFromOldTable();
        $values = $old_pgsql->fetchRows($sql);
        return $values;
    }

    /**
    * copy from old table
    * 
    * @param  PgsqlEntity $old_pgsql
    * @return bool
    */
    public function copyFromOldTable($old_pgsql) {
        if (!$old_pgsql) exit('Not found old_pgsql');

        $values = $this->valuesFromOldTable($old_pgsql);
        $results = $this->copyFrom($values);
        return $results;
    }

    /**
    * insertsFromOldTable
    * 
    * @param  PgsqlEntity $old_pgsql
    * @return PgsqlEntity
    */
    public function insertsFromOldTable($old_pgsql) {
        if (!$old_pgsql) exit('Not found old_pgsql');

        $values = $this->valuesFromOldTable($old_pgsql);

        //TODO inserts
        if ($result !== false) {
            $this->_value = $this->value;
        } else {
            //TODO session
            $this->addError('sql', 'error');
        }
        return $this;
    }

    /**
    * delete
    *
    * TODO pg_delete ?
    * 
    * @param  int $id
    * @return PgsqlEntity
    */
    public function delete($id = null) {
        if (is_numeric($id)) $this->id = (int) $id;
        if (is_numeric($this->id)) $this->initWhere("{$this->id_column} = {$this->id}");

        $sql = $this->deleteSql();
        $result = $this->query($sql);

        if ($result === false) {
            $this->addError($this->name, 'delete');
        } else {
            unset($this->id);
        }
        return $this;
    }

    /**
    * delete
    * 
    * @param  int $id
    * @return PgsqlEntity
    */
    public function deletes() {
        $sql = $this->deletesSql();
        $result = $this->query($sql);

        if ($result === false) {
            $this->addError($this->name, 'delete');
        } else {
            unset($this->id);
        }
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
    * @return PgsqlEntity
    */
    public function truncate($option = null) {
        $sql = $this->truncateSql($option);
        $result = $this->query($sql);

        if ($result === false) {
            $this->addError($this->name, 'truncate');
        }
        return $this;
    }

    /**
    * wheres
    * 
    * @param  array $conditions
    * @return PgsqlEntity
    */
    public function wheres($conditions) {
        $this->conditions[] = $conditions; 
        $this->conditions = array_unique($this->conditions);
        return $this;
    }

    /**
    * where
    * 
    * @param  string $condition
    * @return PgsqlEntity
    */
    public function from($name) {
        $this->table_name = $name; 
        return $this;
    }

    /**
    * where
    * 
    * @param  string $condition
    * @return PgsqlEntity
    */
    public function where($condition, $value = null, $eq = null) {
        if (isset($value) && isset($eq)) {
            $this->conditions = "{$condition} {$eq} {$value}";
        } else {
            $this->conditions[] = $condition; 
        }
        $this->conditions = array_unique($this->conditions);
        return $this;
    }

    /**
    * initWhere
    * 
    * @param  string $condition
    * @return PgsqlEntity
    */
    public function initWhere($condition) {
        $this->conditions = null;
        return $this->where($condition);
    }

    /**
    * order
    * 
    * @param  string $column
    * @param  string $option
    * @return PgsqlEntity
    */
    public function order($column, $option = null) {
        $value['column'] = $column;
        $value['option'] = $option;
        $this->orders[] = $value; 
        $this->orders = array_unique($this->orders);
        return $this;
    }

    /**
    * initOrder
    * 
    * @param  string $column
    * @param  string $option
    * @return PgsqlEntity
    */
    public function initOrder($column, $option = null) {
        $this->orders = null;
        return $this->order($column, $option);
    }

    /**
    * limit
    * 
    * @param  int $limit
    * @return PgsqlEntity
    */
    public function limit($limit) {
        $this->limit = $limit; 
        return $this;
    }

    /**
    * offset
    * 
    * @param  string $condition
    * @return PgsqlEntity
    */
    public function offset($offset) {
        $this->offset = $offset; 
        return $this;
    }

    /**
    * select column
    * 
    * @param  string $model_name
    * @param  array $conditions
    * @return PgsqlEntity
    */
    public function selectColumn($join_class_name, $column, $join_column, $eq = '=', $type = 'LEFT') {
        if (!$join_class_name) return $this;
        if (!$column) return $this;
        if (!$join_column) return $this;

        $join_class = DB::table($join_class_name);

        $join['join_class'] = $join_class;
        $join['join_name'] = $join_class->name;
        $join['join_entity_name'] = $join_class->entity_name;
        //$join['condition'] = "{$join_class->name}.{$join_column} {$eq} {$this->table_name}.{$column}";
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
    * @param  string $eq
    * @param  string $type
    * @return PgsqlEntity
    */
    public function join($join_class_name, $join_column, $column, $class_name = null, $eq = '=', $type = 'LEFT') {
        //TODO join conditions
        if (!$join_class_name) return $this;
        if (!$join_column) return $this;
        if (!$column) return $this;
        if (!$class_name) $class_name = get_class($this);

        $origin_class = DB::table($class_name);
        $join_class = DB::table($join_class_name);

        $join['join_class'] = $join_class;
        $join['join_name'] = $join_class->name;
        $join['join_entity_name'] = $join_class->entity_name;
        $join['condition'] = "{$origin_class->table_name}.{$column} {$eq} {$join_class->table_name}.{$join_column}";
        $join['type'] = $type;
        $this->joins[] = $join;
        return $this;
    }

    /**
    * init Join
    * 
    * @param  string $model_name
    * @return PgsqlEntity
    */
    public function initJoin($class_name, $type = 'LEFT') {
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
    private function sqlValue($value, $type) {
        if (is_null($value)) {
            return "NULL";
        } elseif (is_bool($value)) {
            return ($value) ? 'TRUE' : 'FALSE';
        } elseif (is_array($value)) {
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
    private function whereSql() {
        $sql = '';
        if ($condition = $this->sqlConditions($this->conditions)) $sql = " WHERE {$condition}";
        return $sql;
    }

    /**
    * join Sql
    * 
    * @return string
    */
    private function joinSql() {
        $sql = '';
        if (is_array($this->joins)) {
            foreach ($this->joins as $join) {
                $joins[] = PHP_EOL." {$join['type']} JOIN \"{$join['join_name']}\" ON {$join['condition']}";
            }
            $sql = implode(' ', $joins).PHP_EOL;
        }
        return $sql;
    }

    /**
    * orderBySql
    * 
    * @return string
    */
    private function orderBySql() {
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
    private function limitSql() {
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
    private function offsetSql() {
        $sql = '';
        if (!isset($this->offset)) return;
        if (!is_int($this->offset)) return;
        $sql = " OFFSET {$this->offset}";
        return $sql;
    }

    /**
    * selectSql
    * 
    * @return string
    */
    private function selectSql() {
        if ($this->select_columns) {
            $column = implode(", \n", $this->select_columns).PHP_EOL;
        } else {
            if ($this->joins) {
                $columns[] = "{$this->table_name}.*";
                foreach ($this->joins as $join) {
                    $join_class = $join['join_class'];
                    foreach ($join_class->columns as $join_column_name => $join_column) {
                        $columns[] = "{$join['join_name']}.{$join_column_name} AS {$join['join_entity_name']}_{$join_column_name}";
                    }
                }
                $column = implode(", \n", $columns).PHP_EOL;
            } else {
                $column = "{$this->table_name}.*";
            }
        }

        $sql = "SELECT {$column} FROM {$this->table_name}";

        if ($this->joins) $sql.= $this->joinSql();

        $sql.= $this->whereSql();
        $sql.= $this->orderBySql();
        $sql.= $this->limitSql();
        $sql.= $this->offsetSql();
        $sql.= ";";
        return $sql;
    }


    /**
    * select sql for old table
    * 
    * @return string
    */
    public function selectSqlFromOldTable() {
        if (!$this->old_name) exit('Not found old_name');
        if (!$this->old_columns) exit('Not found old_columns');

        if ($this->old_columns) {
            foreach ($this->old_columns as $column_name => $old_column_name) {
                if ($old_column_name == $column_name) {
                    $select_column = $column_name;
                } else {
                    $select_column = "{$old_column_name} AS {$column_name}";
                }
                $select_columns[] = $select_column;
            }
            $column = implode(", ", $select_columns).PHP_EOL;
        }

        $sql = "SELECT {$column} FROM {$this->old_name}";

        $sql.= $this->whereSql();
        $sql.= $this->orderBySql();
        $sql.= $this->limitSql();
        $sql.= $this->offsetSql();
        $sql.= ";";
        return $sql;
    }

    /**
    * selectCountSql
    * 
    * @param  string $column
    * @return string
    */
    private function selectCountSql($column = null) {
        // TODO GROUP BY
        if (!$column) $column = $this->table_name;
        $sql = "SELECT count({$column}) FROM {$this->table_name}";
        $sql.= $this->whereSql();
        $sql.= ";";
        return $sql;
    }

    /**
    * selectMaxSql
    * 
    * @param  string $column
    * @return string
    */
    private function selectMaxSql($column = null) {
        // TODO GROUP BY
        if (!$column) $column = $this->table_name;
        $sql = "SELECT max({$column}) FROM {$this->table_name}";
        $sql.= $this->whereSql();
        $sql.= ";";
        return $sql;
    }

    /**
    * selectMaxSql
    * 
    * @param  string $column
    * @return string
    */
    private function selectMinSql($column = null) {
        // TODO GROUP BY
        if (!$column) $column = $this->table_name;
        $sql = "SELECT min({$column}) FROM {$this->table_name}";
        $sql.= $this->whereSql();
        $sql.= ";";
        return $sql;
    }

    /**
    * selectMaxSql
    * 
    * @param  string $column
    * @return string
    */
    private function selectSumSql($column = null) {
        // TODO GROUP BY
        if (!$column) $column = $this->table_name;
        $sql = "SELECT sum({$column}) FROM {$this->table_name}";
        $sql.= $this->whereSql();
        $sql.= ";";
        return $sql;
    }

    /**
    * selectMaxSql
    * 
    * @param  string $column
    * @return string
    */
    private function selectAvgSql($column = null) {
        // TODO GROUP BY
        if (!$column) $column = $this->table_name;
        $sql = "SELECT avg({$column}) FROM {$this->table_name}";
        $sql.= $this->whereSql();
        $sql.= ";";
        return $sql;
    }

    /**
    * insertSql
    *
    * TODO : pg_prepare, pg_execute
    * 
    * @return string
    */
    private function insertSql() {
        if (!$this->value) return;
        unset($this->value[$this->id_column]);
        unset($this->id);
        foreach ($this->columns as $key => $type) {
            $value = $this->sqlValue($this->value[$key]);
            if ($key == 'created_at') $value = 'current_timestamp';
            $columns[] = $key;
            $values[] = $value;
        }
        $column = implode(',', $columns);
        $value = implode(',', $values);

        $sql = "INSERT INTO {$this->table_name} ({$column}) VALUES ({$value});";

        $sequence_name = $this->sequenceName($this->table_name);
        $sql.= "SELECT lastval();";
        //$sql.= "SELECT currval('{$sequence_name}'::regclass);";
        return $sql;
    }

    /**
    * updateSql
    * 
    * @return string
    */
    private function updateSql() {
        $sql = '';
        $changes = $this->changes();
        if (!$changes) return;

        foreach ($changes as $key => $org_value) {
            $value = $this->sqlValue($this->value[$key]);
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
    private function updatesSql($values) {
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
    private function deleteSql() {
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
    private function deletesSql() {
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
    private function truncateSql($option = null) {
        $sql = "TRUNCATE {$this->table_name} {$option};";
        return $sql;
    }

    //TODO GROUP BY
    /**
    * count
    * 
    * @return int
    **/
    public function count($column = null) {
        //TODO GROUP BY
        $sql = $this->selectCountSql($column);
        $count = $this->fetch_result($sql); 
        if (is_null($count)) $count = 0;
        return $count;
    }

    /**
    * sql condition
    * 
    * @param array $conditions
    * @return string
    **/
    function sqlConditions($conditions) {
        if (is_null($conditions)) return;
        if (is_string($conditions)) {
            $condition = $conditions;
        } elseif (is_array($conditions)) {
            $condition = implode(' AND ', $conditions);
        }
        return $condition;
    }

    /**
    * sql order
    * 
    * @param array $orders
    * @return string
    **/
    function sqlOrders($orders) {
        if ($this->columns['sort_order']) $orders[] = array('column' => 'sort_order', 'option' => null);
        if (!$orders) return;
        foreach ($orders as $order) {
            if (array_key_exists($order['column'], $this->columns)) {
                $_orders[] = "{$this->table_name}.{$order['column']} {$order['option']}";
            }
        }
        $order = implode(', ', $_orders);
        return $order;
    }

    /**
    * postgres table attributes info
    *
    * @param string $table_name
    * @return array
    **/
    public function pgMetaData($table_name) {
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
    public function pgDBname() {
        $connection = $this->connection();
        $dbname = pg_dbname($connection);
        return $dbname;
    }

    /**
    * pg_class array with attribute
    *
    * @return array
    **/
    public function pgClassArrayByConstraints($pg_class, $pg_constraints) {
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
    public function pgClassesArray($pg_class_ids = null) {
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
                //array_multisort($names, SORT_ASC, $attributes);
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
    public function pgClassArray($pg_class_id) {
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
        $pg_class['pg_constraint']['unique'] = $this->pgConstraints($pg_class['pg_class_id'], 'u');
        $pg_class['pg_constraint']['foreign'] = $this->pgForeignConstraints($pg_class['pg_class_id']);

        return $pg_class;
    }

    /**
    * tableArray
    * 
    * @return array
    **/
    public function tableArray() {
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
    public function attributeArray($table_name) {
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
    public function pgFields($table_name) {
        if (!$table_name) return;

        return $values;
    }
    /**
    * databases
    * 
    * @return array
    **/
    function pgDatabases() {
        $this->dbname = null;
        $this->loadDBInfo();
        $sql = "SELECT * FROM pg_database WHERE datacl IS NULL;";
        return $this->fetchRows($sql);
    }

    /**
    * pg_database
    * 
    * @param string $name
    * @return array
    **/
    function pgDatabase($name = null) {
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
    function pgTables($schema_name = 'public') {
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
    function pgClassByRelname($relname, $relkind = 'r', $schema_name = 'public') {
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
    * @param array $conditinos
    * @param string $relkind
    * @param string $schema_name
    * @return array
    **/
    function pgClass($pg_class_id, $conditinos = null, $relkind = 'r', $schema_name = 'public') {
        $sql = "SELECT pg_class.oid AS pg_class_id, * FROM pg_class 
                LEFT JOIN pg_tables ON pg_tables.tablename = pg_class.relname
                LEFT JOIN pg_namespace ON pg_namespace.oid = pg_class.relnamespace";

        $conditions[] = "relkind = '{$relkind}'";
        $conditions[] = "relfilenode > 0";
        $conditions[] = "relallvisible = 0";
        $conditions[] = "nspname = '{$schema_name}'";
        $conditions[] = "pg_class.oid = {$pg_class_id}";
        $condition = implode(' AND ', $conditions);
        $sql.= " WHERE {$condition}";
        $sql.= " ORDER BY relname;";
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
    * @param array $conditinos
    * @param string $relkind
    * @param string $schema_name
    * @return array
    **/
    function pgClasses($pg_class_ids = null, $conditinos = null, $relkind = 'r', $schema_name = 'public') {
        $sql = "SELECT pg_class.oid AS pg_class_id, * FROM pg_class 
                LEFT JOIN pg_tables ON pg_tables.tablename = pg_class.relname
                LEFT JOIN pg_namespace ON pg_namespace.oid = pg_class.relnamespace";

        $conditions[] = "relkind = '{$relkind}'";
        $conditions[] = "relfilenode > 0";
        //TODO research relallvisible
        $conditions[] = "relallvisible = 0";
        $conditions[] = "relhasindex = TRUE";
        $conditions[] = "nspname = '{$schema_name}'";
        if ($pg_class_ids) {
            $pg_class_id = implode(',', $pg_class_ids);
            $conditions[] = "pg_class.oid in ({$pg_class_id})";
        }
        $condition = implode(' AND ', $conditions);
        $sql.= " WHERE {$condition}";
        $sql.= " ORDER BY relname;";
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
    function pgClassById($pg_class_id, $relkind = 'r', $schema_name = 'public') {
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
    function pgTableByTableName($table_name, $schema_name = 'public') {
        if (!$table_name) return;
        $sql = "SELECT * FROM pg_tables WHERE schemaname = '{schema_name}' AND tablename = '{$table_name}';";
        return $this->fetchRow($sql);
    }

    /**
    * attributes
    *
    * @param string $table_name
    * @return array
    **/
    function pgAttributes($table_name = null) {
        $sql = "SELECT pg_class.oid AS pg_class_id, * FROM pg_class 
                LEFT JOIN pg_attribute ON pg_class.oid = pg_attribute.attrelid
                LEFT JOIN information_schema.columns ON information_schema.columns.table_name = pg_class.relname
                          AND information_schema.columns.column_name = pg_attribute.attname 
                WHERE pg_attribute.attnum > 0
                AND atttypid > 0 
                AND relacl IS NULL";

        if ($table_name) $sql.= " AND relname = '{$table_name}'";
        $sql.= ' ORDER BY pg_attribute.attname;';
        return $this->fetchRows($sql);
    }

    /**
    * pg_attribute
    *
    * @param string $table_name
    * @param string $column_name
    * @return array
    **/
    function pgAttributeByColumn($table_name, $column_name) {
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
    function pgAttributeByAttnum($pg_class_id, $attnum, $schama_name = 'public') {
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
    function pgAttributeByAttrelid($pg_class_id) {
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
    function pgAttributeByIdName($pg_class_id, $attname) {
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
    function diffPgAttributes($orign_pgsql, $orign_table_name) {
        $orign_pg_attributes = $orign_pgsql->pgAttributes($orign_table_name);
        var_dump(array_keys($orign_pg_attributes));
        exit;
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
    function updateTableComment($table_name, $comment) {
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
    function updateColumnComment($table_name, $column_name, $comment) {
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
    function tableComments($table_name = null, $schama_name = 'public') {
        $sql = "SELECT psut.relname ,pd.description
                FROM pg_stat_user_tables psut ,pg_description pd
                WHERE psut.relid = pd.objoid
                    AND schemaname = '{$schama_name}' 
                    AND pd.objsubid = 0";

        if ($table_name) $sql.= "relfilename = '{$table_name}'";
        $sql.= ";";
        return $this->fetchRows($sql);
    }

    /**
     * table comments array
     *
     * @param  string $table_name
     * @param  string $schama_name
     * @return array
     */
    function tableCommentsArray($table_name = null, $schama_name = 'public') {
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
    function tableComment($table_name) {
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
    function pgColumnComments() {
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
    function pgColumnComment($table_name) {
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
    public function columnCommentsArray() {
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
    public function columnCommentArray($table_name) {
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
     * @return array
     */
    function pgConstraints($pg_class_id, $type = null) {
        if (!$pg_class_id) return;

        $sql = "SELECT * FROM pg_constraint";
        $sql.= " LEFT JOIN pg_attribute ON";
        $sql.= " pg_constraint.conrelid = pg_attribute.attrelid";
        $sql.= " AND pg_attribute.attnum = ANY(pg_constraint.conkey)";

        if ($pg_class_id) $conditions[] = "pg_constraint.conrelid = '{$pg_class_id}'";
        if ($type) $conditions[] = "pg_constraint.contype = '{$type}'";

        if ($conditions) $condition = implode(' AND ', $conditions);
        if ($condition) $sql.= " WHERE {$condition}";

        $sql.= ';';
        return $this->fetchRows($sql);
    }

    /**
     * pg indexes
     *
     * @param  string $table_name
     * @param  string $schema_name
     * @return array
     */
    function pgIndexes($table_name = null, $schema_name = 'public') {
        if (!$pg_class_id) return;

        $sql = "SELECT * FROM pg_indexes WHERE schemaname = '{$schema_name}'";

        $conditions[] = "schemaname = '{$schema_name}'";
        if ($table_name) $conditions[] = "table_name = '{$table_name}'";

        $condition = implode(' AND ', $conditions);
        $sql.= " WHERE {$condition};";
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
     * @param  string $type
     * @return array
     */
    function pgForeignConstraints($pg_class_id, $type = null) {
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
    function pgConstraintsByConstrainName($name) {
        $sql = "SELECT * FROM pg_constraint WHERE conname LIKE '%{$name}%'";
        $sql.= ';';
        return $this->fetchRows($sql);
    }

    /**
     * pg constraint groups
     *
     * @param  array $pg_class
     * @return array
     */
    function pgConstraintGroup($pg_class) {
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
    function pgConstraintsByPgClassID($pg_class_id) {
        $constraints = $this->pgConstraints($pg_class_id);
        if (!$constraints) return;

        foreach ($constraints as $index => $constraint) {
            //TODO
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
    function addPgPrimaryKey($table_name, $column_name) {
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
    function renamePgConstraint($table_name, $constraint_name, $new_constraint_name) {
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
    function addPgUnique($table_name, $columns) {
        $constraint_name = '';
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
    function addPgForeignKey($table_name, $foreign_column, $reference_table_name, $reference_column,
                             $is_not_deferrable = true, $update = 'NO ACTION', $delete = 'NO ACTION') {
        $reference_column = "{$reference_table_name}({$reference_column})";
        $sql = "ALTER TABLE {$table_name} ADD FOREIGN KEY ({$foreign_column}) REFERENCES {$reference_column}";

        if ($update) $sql.= " ON UPDATE {$update}";
        if ($delete) $sql.= " ON DELETE {$delete}";
        if ($is_not_deferrable) $sql.= " NOT DEFERRABLE";

        $sql.= ';';
        return $this->query($sql);
    }

    /**
     * remove pg constraint
     *
     * @param  string $table_name
     * @return void
     */
    function removePgConstraint($table_name, $constraint_name) {
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
    function removePgConstraints($table_name) {
        $constraints = $this->pgConstraints($table_name);
        if (!$constraints) return;

        foreach ($constraints as $constraint) {
            $constraint_groups[$constraint['constraint_name']][] = $constraint;
        }
        foreach ($constraint_groups as $constraint_name => $constraint) {
            $sql = "ALTER TABLE {$table_name} DROP CONSTRAINT {$constraint_name};";
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
    function pgPrimaryKeys($database_name, $table_name = null) {
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

        if ($table_name) $sql.= " AND tc.table_name = '{$table_name}'";
        $sql.= ";";
        return $this->fetchRows($sql);
    }

    /**
     * change not null
     *
     * @param  string $table_name
     * @param  string $column_name
     * @return array
     */
    public function changeNotNull($table_name, $column_name, $is_required = true) {
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
    public function setNotNull($table_name, $column_name) {
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
    public function dropNotNull($table_name, $column_name) {
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
    public function sqlColumnType($type, $length = 0) {
        if ($type == 'varchar' && $length > 0) {
            $type.= "({$length})";
        }
        return $type;
    }

    /**
     * Numbering Name
     *
     * @param  string $name
     * @return bool
     */
    static function isNumberingName($name) {
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
     * @return PgsqlEntity
     */
    function table($table_name) {
        $this->id_column = 'id';
        $this->entity_name = FileManager::pluralToSingular($table_name);
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
    static function parseConstraintKeys($values) {
        if (!$values) return;
        $values = str_replace('{', '', $values);
        $values = str_replace('}', '', $values);
        $values = explode(',', $values);
        return $values;
    }

}