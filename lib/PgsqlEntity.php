<?php
/**
* PgsqlEntity 
*
* @copyright  Copyright (c) 2017 Yohei Yoshikawa (http://yoo-s.com/)
*/

if (!defined('DB_NAME')) exit('not found DB_NAME');

require_once 'Entity.php';

class PgsqlEntity extends Entity {
    var $extra_columns = false;
    var $group_columns = false;
    var $joins = array();
    var $pg_info = null;
    var $dbname = null;
    var $host = 'localhost';
    var $user = 'postgres';
    var $port = 5432;
    var $password = null;
    var $values = null;
    var $value = null;
    var $conditions = null;
    var $orders = null;
    var $limits = null;
    var $is_pconnect = false;
    var $is_connect_forece_new = false;
    var $table_name = null;

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

    function __construct($params = null) {
        parent::__construct($params);
        if (!$params) {
            $this->defaultDBInfo();
        } else {
            $this->setDBInfo($params);
        }
        $this->table_name = $this->name;
        if ($params['table_name']) $this->table_name = $params['table_name'];
        if (!$this->dbname) exit("Not Found: dbname");
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
        if (defined('DB_NAME')) $this->dbname = DB_NAME;
        if (defined('DB_HOST')) $this->host = DB_HOST;
        if (defined('DB_PORT')) $this->port = DB_PORT;
        if (defined('DB_USER')) $this->user = DB_USER;
        if (defined('DB_PASS')) $this->password = DB_PASS;
        $this->loadDBInfo();
        return $this;
    }

    /**
    * pgInfo
    * 
    * @param array $database_name
    * @return PgsqlEntity
    */
    function setDBName($database_name) {
        $this->dbname = $database_name;
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
    * pgInfo
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
    * pgInfo
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
        return $option;
    }

    /**
    * create table SQL
    * 
    * @param PgsqlEntity $model
    * @return string
    */
    public function createTableSql($model) {
        if (!$model) return;

        $column_sqls[] = "{$model->id_column} BIGSERIAL PRIMARY KEY NOT NULL";
        //$column_sqls[] = "{$model->id_column} SERIAL PRIMARY KEY NOT NULL";
        foreach ($model->columns as $column_name => $column) {
            if ($column['type']) {
                $type = self::columnTypeSql($column);
                $option = self::columnOptionSql($column);

                $column_sqls[] = "{$column_name} {$type} {$option}";
            }
        }
        $column_sql = implode(",\n", $column_sqls);
        $sql = "CREATE TABLE IF NOT EXISTS \"{$model->name}\" (\n{$column_sql}\n);\n";

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
    * create table SQL
    * 
    * @return void
    */
    function createTablesSql() {
        $vo_path = BASE_DIR."app/models/vo/*.php";
        foreach (glob($vo_path) as $file_path) {
            if (is_file($file_path)) {
                $file = pathinfo($file_path);
                $class_name = $file['filename'];
                $vo = new $class_name();

                $this->sql.= $this->createTableSql($vo);
            }
        }
        return $this->sql;
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
    * create tables
    * 
    * @return resource
    */
    function createTables() {
        $this->createTablesSql();
        return $this->query($this->sql);
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

        $type = $this->sqlColumnType($options['type'], $options['length']);
        $option = $option = self::columnOptionSql($options);

        $sql = "ALTER TABLE \"{$table_name}\" ADD COLUMN \"{$column}\" {$type}{$option};";
        return $this->query($sql);
    }

    /**
    * connection
    * 
    * @return resource
    */
    function connection() {
        try {
            if (!$this->is_pconnect) {
                return pg_connect($this->pg_info);
            } else {
                if ($this->is_connect_forece_new) {
                    return pg_pconnect($this->pg_info, PGSQL_CONNECT_FORCE_NEW);
                } else {
                    return pg_pconnect($this->pg_info);
                }
            }
        } catch (Exception $e) {
            var_dump($e);
            exit;
        }
    }

    /**
    * connection
    * 
    * @return resource
    */
    function query($sql) {
        $this->sql_error = null;
        $this->sql = $sql;
        if (defined('SQL_LOG') && SQL_LOG) error_log("<SQL> {$sql}");
        if ($pg = $this->connection()) {
            if ($is_busy = pg_connection_busy($pg)) {
                exit('DB connection is busy.');
            }
            $results = pg_query($pg, $sql);
            $this->sql_error = pg_last_error($pg);
            return $results;
        } else {
            exit('DB connection error.');
        }
    }

    /**
    * fetch_rows
    * 
    * @return array
    */
    function fetch_rows($sql) {
        $rs = $this->query($sql);
        if ($rs) {
            $rows = pg_fetch_all($rs);
            return $rows;
        } else {
            return;
        }
    }

    /**
    * fetch_row
    * 
    * @return array
    */
    function fetch_row($sql) {
        $rs = $this->query($sql);
        if ($rs) {
            $row = pg_fetch_array($rs, null, PGSQL_ASSOC);
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
        $this->values = null;
        if (!$id) return $this;
        $this->where("{$this->id_column} = {$id}")->selectOne($params);
        $this->_value = $this->value;
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
    public function bindMany($model_name, $conditions = null, $foreign_key = null, $value_key = null) {
        if (!is_string($model_name)) exit('bindMany: $model_name is not string');
        $relation = DB::table($model_name);

        $column_name = $relation->entity_name;
        $relation = $this->hasMany(get_class($relation), $conditions, $foreign_key, $value_key);
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
    public function bindBelongTo($model_name, $foreign_key = null, $value_key = null) {
        if (!is_string($model_name)) exit('bindBelongTo: $model_name is not string');
        $relation = DB::table($model_name);

        $column_name = $relation->entity_name;
        $relation = $this->belongTo($relation, $foreign_key, $value_key);
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
        return $relation->where($condition)->selectOne();
    }

    /**
    * relations by model
    * 
    * @param  string $class_name
    * @param  array $conditions
    * @param  string $foreign_key
    * @param  string $value_key
    * @return PgsqlEntity
    */
    public function hasMany($class_name, $conditions = null, $foreign_key = null, $value_key = null) {
        if (is_null($this->value)) {
            echo('Not Found: value');
            exit;
        }

        if (!is_string($class_name)) exit('hasMany: $class_name is not string');
        $relation = DB::table($class_name);

        if (!$foreign_key) $foreign_key = "{$this->entity_name}_id";
        if (!$value_key) $value_key = $this->id_column;

        $value = $this->value[$value_key];
        if (is_null($value)) return $this;

        $conditions[] = "{$foreign_key} = '{$value}'";
        foreach ($conditions as $condition) {
            $relation->where($condition);
        }
        return $relation->select();
    }

    /**
    * relation by model
    * 
    * @param  string $class_name
    * @param  string $foreign_key
    * @param  string $value_key
    * @return PgsqlEntity
    */
    public function belongTo($class_name, $foreign_key = null, $value_key = null) {
        if (is_null($this->value)) return $this;

        if (!is_string($class_name)) exit('belongTo: $class_name is not string');
        $relation = DB::table($class_name);

        if (!$foreign_key) $foreign_key = $relation->id_column;
        if (!$value_key) $value_key = "{$relation->entity_name}_id";

        $value = $this->value[$value_key];
        if (is_null($value)) return $this;
        $condition = "{$foreign_key} = '{$value}'";
        return $relation->where($condition)->selectOne();
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
        if (isset($params['id_index'])) $this->id_index = $params['id_index'];
        $sql = $this->selectSql($params);
        $values = $this->fetch_rows($sql);
        $this->values = $this->castRows($values, $params);
        unset($this->id);
        return $this;
    }

    /**
    * select
    * 
    * @param  array $params
    * @return array
    */
    public function selectValues($params = null) {
        return $this->select($params)->values;
    }

    /**
    * selectOne
    * 
    * @param  array $params
    * @return array
    */
    public function selectOne($params = null) {
        $this->values = null;
        $sql = $this->selectSql($params);
        $value = $this->fetch_row($sql);

        $this->value = $this->castRow($value);
        if (is_array($this->value) && isset($this->value[$this->id_column])) {
            $this->id = (int) $this->value[$this->id_column];
        }
        $this->_value = $this->value;
        return $this;
    }

    /**
    * selectOneValue
    * 
    * @param  array $params
    * @return array
    */
    public function selectOneValue($params = null) {
        return $this->selectOne($params)->value;
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
    * update
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
    * delete
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
    public function where($condition) {
        $this->conditions[] = $condition; 
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
        $join['condition'] = "{$this->table_name}.{$column} = {$join_class->name}.{$join_column}";
        $join['type'] = $type;
        $this->joins[] = $join;
        return $this;
    }

    /**
    * join
    * 
    * @param  string $model_name
    * @param  array $conditions
    * @return PgsqlEntity
    */
    public function join($join_class_name, $column, $join_column, $eq = '=', $type = 'LEFT') {
        if (!$join_class_name) return $this;
        if (!$column) return $this;
        if (!$join_column) return $this;

        $join_class = DB::table($join_class_name);

        $join['join_class'] = $join_class;
        $join['join_name'] = $join_class->name;
        $join['condition'] = "{$this->table_name}.{$column} = {$join_class->name}.{$join_column}";
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
    private function sqlValue($value) {
        if (is_null($value)) {
            return "NULL";
        } elseif (is_numeric($value)) {
            return (string) $value;
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
    * @param  array $params
    * @return string
    */
    private function whereSql($params = null) {
        $sql = '';
        if (isset($params['conditions'])) $this->conditions = $params['conditions'];
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
                $joins[] = " {$join['type']} JOIN \"{$join['join_name']}\" ON {$join['condition']}";
            }
            $sql = implode(' AND ', $joins);
        }
        return $sql;
    }

    /**
    * orderBySql
    * 
    * @param  array $params
    * @return string
    */
    private function orderBySql($params = null) {
        $sql = '';
        if (isset($params['orders'])) $this->orders = $params['orders'];
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
    * @param  array $params
    * @return string
    */
    private function selectSql($params = null) {
        //TODO join column
        // if ($this->joins) {
        //     if ($this->columns) {
        //         $columns[] = "{$this->table_name}.{$this->id_column}";
        //         foreach ($this->columns as $key => $value) {
        //             $columns[] = "{$this->table_name}.{$key}\n";
        //         }
        //     }
        //     foreach ($this->joins as $join) {
        //         $join_class = $join['join_class'];
        //         if ($join_class->columns) {
        //             foreach ($join_class->columns as $key => $value) {
        //                 $columns[] = "{$join['join_name']}.{$key}\n";
        //             }
        //         }
        //     }
        //     $column = implode(', ', $columns);
        // } else {
        //     $column = "{$this->table_name}.*";
        // }
        $column = "{$this->table_name}.*";

        $sql = "SELECT {$column} FROM {$this->table_name}";
        $sql.= $this->whereSql($params);

        if ($this->joins) {
            $sql.= $this->joinSql();
        }

        $sql.= $this->orderBySql($params);
        $sql.= $this->offsetSql($params);
        $sql.= $this->limitSql($params);
        $sql.= ";";
        return $sql;
    }

    /**
    * selectCountSql
    * 
    * @param  array $params
    * @return string
    */
    private function selectCountSql($params = null) {
        $sql = "SELECT count({$this->table_name}.*) FROM {$this->table_name}";
        $sql.= $this->whereSql($params);
        // TODO GROUP BY
        $sql.= ";";
        return $sql;
    }

    /**
    * insertSql
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
        $sql.= "SELECT currval('{$this->table_name}_id_seq');";
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
        $where = $this->whereSql($params);
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
        $where = $this->whereSql($params);
        $sql = "DELETE FROM {$this->table_name} {$where};";
        return $sql;
    }

    //TODO GROUP BY

    /**
    * count
    * 
    * @param array $conditions
    * @return int
    **/
    public function count() {
        //TODO GROUP BY
        if (is_bool($this->group_columns) && $this->group_columns) {
            $this->group_columns = array_keys($this->columns);
            array_unshift($this->group_columns, $this->id_column);
        }
        if (empty($this->group_columns)) {
            $select_str = "COUNT({$this->table_name}.{$this->id_column})";
        } else {
            $select_str = $this->group_columns[0];
            foreach ($this->group_columns as $i => $group_column) {
                if (!strpos($group_column, '.')) {
                    $this->group_columns[$i] = $this->table_name . "." . $group_column;
                }
            }
            $group_str = implode(', ', $this->group_columns);
        }

        $sql = $this->selectCountSql();
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
        if (is_int($conditions)) {
            $condition = "{$this->table_name}.{$this->id_column} = {$conditions}";
        } elseif (is_string($conditions)) {
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
    * pg_class array with attribute
    *e
    * @return array
    **/
    public function pgClassArrayByConstraints($pg_class, $pg_constraints) {
        $relids[$pg_class['pg_class_id']] = $pg_class['pg_class_id'];
        foreach ($pg_constraints as $pg_constraint) {
            if ($pg_constraint['confrelid'] > 0) $relids[$pg_constraint['confrelid']] = $pg_constraint['confrelid'];
        }
        return $this->pgClassArray($relids);
    }

    /**
    * pg_class array with attribute
    * 
    * @return array
    **/
    public function pgClassArray($pg_class_ids = null) {
        $pg_classes = $this->pgClasses($pg_class_ids);
        $table_comments = $this->tableCommentsArray();

        if ($pg_classes) {
            foreach ($pg_classes as $pg_class) {
                $table_name = $pg_class['relname'];
                $is_numbering = self::isNumberingName($table_name);
                if ($table_name && $pg_class['pg_class_id'] && !$is_numbering) {
                    $pg_class['comment'] = $table_comments[$table_name];
                    $pg_attributes = $this->pgAttributes($table_name);
                    if ($pg_attributes) {
                        $attributes = null;
                        $column_comments = $this->columnCommentArray($table_name);
                        foreach ($pg_attributes as $pg_attribute) { 
                            if ($pg_attribute['attnum'] > 0) {
                                $pg_attribute['comment'] = $column_comments[$pg_attribute['attname']];
                                $attributes[$pg_attribute['attnum']] = $pg_attribute;
                            }
                        }
                    }
                    $pg_class['pg_attribute'] = $attributes;
                    $pg_class['pg_constraint'] = $this->pgConstraintsByPgClassID($pg_class['pg_class_id']);
                    $values[$pg_class['pg_class_id']] = $pg_class;
                }
            }
        }
        return $values;
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
    * databases
    * 
    * @return array
    **/
    function pgDatabases() {
        $sql = "SELECT * FROM pg_database WHERE datacl IS NULL;";
        return $this->fetch_rows($sql);
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
        return $this->fetch_row($sql);
    }

    /**
    * pg_tables
    *
    * @param
    * @return array
    **/
    function pgTables($schema_name = 'public') {
        $sql = "SELECT * FROM pg_tables WHERE schemaname = '{$schema_name}' ORDER BY tablename;";
        return $this->fetch_rows($sql);
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
        return $this->fetch_row($sql);
    }

    /**
    * pg_classes
    *
    * @param array $pg_class_ids
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
        $conditions[] = "nspname = '{$schema_name}'";
        if ($pg_class_ids) {
            $pg_class_id = implode(',', $pg_class_ids);
            $conditions[] = "pg_class.oid in ({$pg_class_id})";
        }
        $condition = implode(' AND ', $conditions);
        $sql.= " WHERE {$condition};";
        return $this->fetch_rows($sql);
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
        return $this->fetch_row($sql);
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
        return $this->fetch_row($sql);
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
        $sql.= ';';
        return $this->fetch_rows($sql);
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
        return $this->fetch_row($sql);
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
        return $this->fetch_row($sql);
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
        return $this->fetch_rows($sql);
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
        return $this->fetch_row($sql);
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
        return $this->fetch_rows($sql);
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
        return $this->fetch_row($sql);
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
        return $this->fetch_rows($sql);
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
        return $this->fetch_rows($sql);
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
        if ($pg_class_id) $conditions[] = "conrelid = '{$pg_class_id}'";
        if ($type) $conditions[] = "contype = '{$type}'";
        if ($conditions) $condition = implode(' AND ', $conditions);
        if ($condition) $sql.= " WHERE {$condition}";

        $sql.= ';';
        return $this->fetch_rows($sql);
    }

    /**
     * pg constraints by constraint name
     *
     * @param  string $name
     * @param  int $pg_class_id
     * @return array
     */
    function pgConstraintsByConstrainName($name, $pg_class_id = null) {
        $sql = "SELECT * FROM pg_constraint WHERE conname LIKE '%{$name}%'";
        if ($table_name) $sql.= " AND conrelid = '{$pg_class_id}'"; 
        $sql.= ';';
        return $this->fetch_rows($sql);
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

        return $constraints;
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
            $is_numbering = self::isNumberingName($constraint['conname']);
            $constraints[$index]['conkey'] = self::parseConstraintKeys($constraint['conkey']);
            $constraints[$index]['confkey'] = self::parseConstraintKeys($constraint['confkey']);
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
                             $is_not_deferrable = true, $update = 'NO ACTION', $delete = 'CASCADE') {
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
        return $this->fetch_rows($sql);
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