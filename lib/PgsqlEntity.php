<?php
/**
* PgsqlEntity 
*
* @copyright  Copyright (c) 2017 Yohei Yoshikawa (http://yoo-s.com/)
*/
if (!defined('PG_INFO')) exit('not found PG_INFO');

require_once 'Entity.php';

class PgsqlEntity extends Entity {
    var $extra_columns = false;
    var $group_columns = false;
    var $joins = array();
    var $pg_info = PG_INFO;
    var $dbname = null;
    var $host = 'localhost';
    var $user = 'postgres';
    var $port = 5432;
    var $values = null;
    var $value = null;
    var $conditions = null;
    var $orders = null;
    var $limits = null;

    function __construct($params = null) {
        parent::__construct();
        $this->defaultPgInfo();
        if ($params) $this->setPgInfo($params);

        if (!$this->dbname) {
            echo("Not Found: dbname");
            exit;
        }
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
    * pgInfo
    * 
    * @param array $params
    */
    function setPgInfo($params) {
        if (!$params) return;
        if ($params['dbname']) $this->dbname = $params['dbname'];
        if ($params['host']) $this->host = $params['host'];
        if ($params['user']) $this->user = $params['user'];
        if ($params['port']) $this->port = $params['port'];

        $pg_infos[] = "dbname={$this->dbname}";
        $pg_infos[] = "host={$this->host}";
        $pg_infos[] = "user={$this->user}";
        $pg_infos[] = "port={$this->port}";

        $this->pg_info = implode(' ', $pg_infos);
    }

    /**
    * pgInfo
    * 
    * @return void
    */
    function defaultPgInfo() {
        $values = explode(' ', $this->pg_info);
        foreach ($values as $value) {
            if (is_numeric(strpos($value, 'dbname='))) {
                $this->dbname = trim(str_replace('dbname=', '', $value));
            }
            if (is_numeric(strpos($value, 'user='))) {
                $this->user = trim(str_replace('user=', '', $value));
            }
            if (is_numeric(strpos($value, 'port='))) {
                $this->port = trim(str_replace('port=', '', $value));
            }
            if (is_numeric(strpos($value, 'host='))) {
                $this->host = trim(str_replace('host=', '', $value));
            }
        }
    }

    /**
    * createTable
    * 
    * @param Class $model
    * @param array $columns
    * @return resource
    */
    public function createTableSql($model) {
        if (!$model) return;

        $column_sqls[] = "{$model->id_column} SERIAL PRIMARY KEY NOT NULL";
        foreach ($model->columns as $column_name => $option) {
            if ($option['type']) {
                if ($option['length']) {
                    $type = "{$option['type']}({$option['length']})";
                } else {
                    $type = $option['type'];
                }
                $type = strtoupper($type);

                if ($option['required']) {
                    $option['option'].= "NOT NULL";
                }
                $column_sqls[] = "{$column_name} {$type} {$option['option']}";
            }
        }
        $column_sql = implode(",\n", $column_sqls);
        $sql = "CREATE TABLE IF NOT EXISTS \"{$model->name}\" (\n{$column_sql}\n);\n";

        return $sql;
    }

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

    function createTables() {
        $this->createTablesSql();
        return $this->query($this->sql);
    }

    /**
    * renameTable
    * 
    * @param string $table
    * @param string $new_table
    * @return resource
    */
    public function renameTable($table, $new_table) {
        if (!$table) return;
        if ($table == $new_table) return;

        $sql = "ALTER TABLE \"{$table}\" RENAME TO \"{$new_table}\";";
        return $this->query($sql);
    }

    /**
    * renameColumn
    * 
    * @param string $table
    * @param string $column
    * @param string $new_column
    * @return resource
    */
    public function renameColumn($table, $column, $new_column) {
        if (!$table) return;
        if (!$column) return;
        if ($column == $new_column) return;

        $sql = "ALTER TABLE \"{$table}\" RENAME COLUMN \"{$column}\" TO \"{$new_column}\";";
        return $this->query($sql);
    }

    /**
    * renameColumn
    * 
    * @param string $table
    * @param string $column
    * @param string $type
    * @return resource
    */
    public function changeColumnType($table, $column, $type) {
        if (!$table) return;
        if (!$column) return;
        if (!$type) return;

        $using = '';
        //TODO float double
        if (strstr($type, 'int')) {
            $sql = "ALTER TABLE \"{$table}\" ALTER COLUMN \"{$column}\" TYPE {$type}";
            $using = " USING {$column}::int";
        }
        $sql = "ALTER TABLE \"{$table}\" ALTER COLUMN \"{$column}\" TYPE {$type}{$using};";
        $results = $this->query($sql);
        if ($this->sql_error) {
            return false;
        }
    }

    /**
    * dropTable
    * 
    * @param string $table
    * @return resource
    */
    public function dropTable($table) {
        if (!$table) return;

        $sql = "DROP TABLE \"{$table}\";";
        return $this->query($sql);
    }

    /**
    * dropColumn
    * 
    * @param string $table
    * @param string $column
    * @param string $column
    * @return resource
    */
    public function dropColumn($table, $column) {
        if (!$table) return;
        if (!$column) return;
        $sql = "ALTER TABLE \"{$table}\" DROP COLUMN \"{$column}\";";
        return $this->query($sql);
    }

    /**
    * addColumn
    * 
    * @param string $table
    * @param string $column
    * @param string $type
    * @return resource
    */
    public function addColumn($table, $column, $type) {
        if (!$table) return;
        if (!$column) return;
        if (!$type) return;
        $sql = "ALTER TABLE \"{$table}\" ADD COLUMN \"{$column}\" {$type};";
        return $this->query($sql);
    }

    /**
    * connection
    * 
    * @return resource
    */
    function connection() {
        return pg_connect($this->pg_info);
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
            $results = pg_query($pg, $sql);
            $this->sql_error = pg_last_error($pg);
        }
        return $results;
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
    * get
    * 
    * @param  int $id
    * @param  array $params
    * @return array
    */
    public function get($id, $params=null) {
        $this->values = null;
        if (!$id) return $this;
        $this->where("{$this->id_column} = {$id}")->selectOne($params);
        $this->_value = $this->value;
        return $this;
    }

    /**
    * fetch
    * 
    * @param  int $id
    * @param  array $params
    * @return Object
    */
    public function fetch($id, $params=null) {
       return $this->get($id, $params);
    }

    /**
    * relation by model
    * 
    * @param  string $relation_name
    * @return Class
    */
    public function bindRelationId($relation_name, $relation_column = null) {
        if (!is_string($relation_name)) return $this;
        //if (!class_exists($relation_name)) return $this;

        $relation = DB::table($relation_name);

        $value_column = "{$relation->entity_name}_id";
        $value = $this->value[$value_column];

        if (!isset($value)) return $this;

        $condition = "{$relation->id_column} = '{$value}'";
        $column_name = $relation->entity_name;

        $this->$column_name = $relation->selectOne();
        return $this;
    }

    /**
    * relation by model
    * 
    * @param  string $relation_name
    * @return Class
    */
    public function relationBindId($relation_name, $relation_column = null) {
        if (!$this->id) return $this;
        if (!is_string($relation_name)) return $this;
        //if (!class_exists($relation_name)) return $this;

        $relation = DB::table($relation_name);

        if ($relation_column) {
            $conditions[] = "{$relation_column} = '{$this->id}'";
        } else {
            $conditions[] = "{$this->entity_name}_id = '{$this->id}'";
        }
        foreach ($conditions as $condition) {
            $relation->where($condition);
        }
        $column_name = $relation->entity_name;
        $this->$column_name = $relation->selectOne();
        return $this;
    }

    /**
    * relation by model
    * 
    * @param  string $relation_name
    * @return Class
    */
    public function relationsBindId($relation_name, $conditions = null, $relation_column = null) {
        if (!$this->id) return $this;
        if (!is_string($relation_name)) return $this;
        //if (!class_exists($relation_name)) return $this;

        $relation = DB::table($relation_name);
        if ($relation_column) {
            $conditions[] = "{$relation_column} = '{$this->id}'";
        } else {
            $conditions[] = "{$this->entity_name}_id = '{$this->id}'";
        }
        foreach ($conditions as $condition) {
            $relation->where($condition);
        }
        $column_name = $relation->entity_name;
        $this->$column_name = $relation->select();
        return $this;
    }

    /**
    * relation by model
    * 
    * @param  Class $relation
    * @param  string $relation_value_column
    * @return Class
    */
    public function relation($relation, $relation_value_column = null) {
        if ($relation_value_column) {
            $condition = "{$this->id_column} = '{$relation->value[$relation_value_column]}'";
        } else {
            $relation_column = "{$relation->entity_name}_id";
            if (isset($relation->id)) {
                $condition = "{$relation_column} = '{$relation->id}'";
            }
        }
        if ($condition) return $this->where($condition)->selectOne();
        return $this;
    }

    /**
    * relations by model
    * 
    * @param  Class $relation
    * @param  string $relation_value_column
    * @return Class
    */
    public function relations($relation, $relation_value_column = null) {
        if ($relation_value_column) {
            $condition = "{$this->id_column} = '{$relation->value[$relation_value_column]}'";
        } else {
            $relation_column = "{$relation->entity_name}_id";
            if (isset($relation->id)) {
                $condition = "{$relation_column} = '{$relation->id}'";
            }
        }
        if ($condition) return $this->where($condition)->select();
        return $this;
    }

    /**
    * relation
    * 
    * @param  string $model_name
    * @param  string $relation_value_column
    * @return Class
    */
    public function relationByName($model_name, $relation_value_column = null) {
        $relation = DB::table($model_name);
        return $this->relation($relation, $relation_value_column);
    }

    /**
    * relations
    * 
    * @param  string $model_name
    * @param  string $relation_value_column
    * @return Class
    */
    public function relationsByName($model_name, $relation_value_column = null) {
        $relation = DB::table($model_name);
        return $this->relations($relation, $relation_value_column);
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
    * @return Class
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
    * @return Class
    */
    public function save($posts = null, $id = null) {
        if ($id) $this->get($id);
        if ($this->id) {
            $this->update($posts, $this->id);
        } else {
            $this->insert($posts);
        }
    }

    /**
    * insert
    * 
    * @param  array $posts
    * @return Class
    */
    public function insert($posts = null) {
        $this->id = null;
        $this->values = null;
        if ($posts) $this->takeValues($posts);

        $this->validate();
        if ($this->errors) return $this;

        $sql = $this->insertSql();
        if (!$sql) {
            $this->addError('sql', 'error');
            return $this;
        }

        if ($this->is_none_id_column) {
            $result = $this->query($sql);
            return $this;
        } else {
            $result = $this->fetch_result($sql);
            if ($result) {
                $this->id = (int) $result;
                $this->value[$this->id_column] = $this->id;
            } else {
                $this->addError('sql', 'error');
            }
        }
        return $this;
    }

    /**
    * update
    * 
    * @param  array $posts
    * @param  int $id
    * @return Class
    */
    public function update($posts = null, $id = null) {
        if ($id) $this->get($id);
        if ($posts) $this->takeValues($posts);

        $this->validate();
        if ($this->errors) {
            return $this;
        }

        $sql = $this->updateSql();
        if (!$sql) {
            $this->addError('sql', 'error');
            return $this;
        }

        $result = $this->query($sql);
        if ($result !== false) {
            $this->_value = $this->value;
        } else {
            $this->addError('sql', 'error');
        }
        return $this;
    }


    /**
    * updates
    * 
    * @param  array $posts
    * @return Class
    */
    public function updates($posts) {
        if (!$posts) return;
        $sql = $this->updatesSql($posts);
        if (!$sql) {
            //$this->addError('sql', 'error');
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
    * @return Class
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
    * @return Class
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
    * @return Class
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
    * @return Class
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
    * @return Class
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
    * @return Class
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
    * @return Class
    */
    public function initOrder($column, $option = null) {
        $this->orders = null;
        return $this->order($column, $option);
    }

    /**
    * limit
    * 
    * @param  int $limit
    * @return Class
    */
    public function limit($limit) {
        $this->limit = $limit; 
        return $this;
    }

    /**
    * offset
    * 
    * @param  string $condition
    * @return Class
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
    * @return Class
    */
    public function selectColumn($join_class_name, $column, $join_column, $eq = '=', $type = 'LEFT') {
        if (!$join_class_name) return $this;
        if (!$column) return $this;
        if (!$join_column) return $this;

        $join_class = DB::table($join_class_name);

        $join['join_class'] = $join_class;
        $join['join_name'] = $join_class->name;
        $join['condition'] = "{$this->name}.{$column} = {$join_class->name}.{$join_column}";
        $join['type'] = $type;
        $this->joins[] = $join;
        return $this;
    }

    /**
    * join
    * 
    * @param  string $model_name
    * @param  array $conditions
    * @return Class
    */
    public function join($join_class_name, $column, $join_column, $eq = '=', $type = 'LEFT') {
        if (!$join_class_name) return $this;
        if (!$column) return $this;
        if (!$join_column) return $this;

        $join_class = DB::table($join_class_name);

        $join['join_class'] = $join_class;
        $join['join_name'] = $join_class->name;
        $join['condition'] = "{$this->name}.{$column} = {$join_class->name}.{$join_column}";
        $join['type'] = $type;
        $this->joins[] = $join;
        return $this;
    }

    /**
    * init Join
    * 
    * @param  string $model_name
    * @return Class
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
        //         $columns[] = "{$this->name}.{$this->id_column}";
        //         foreach ($this->columns as $key => $value) {
        //             $columns[] = "{$this->name}.{$key}\n";
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
        //     $column = "{$this->name}.*";
        // }
        $column = "{$this->name}.*";

        $sql = "SELECT {$column} FROM {$this->name}";
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
        $sql = "SELECT count({$this->name}.*) FROM {$this->name}";
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

        $sql = "INSERT INTO {$this->name} ({$column}) VALUES ({$value});";
        $sql.= "SELECT currval('{$this->name}_id_seq');";
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
            $sql = "UPDATE {$this->name} SET {$set_value} WHERE {$condition};";
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
            $sql = "UPDATE {$this->name} SET {$set_value} WHERE {$condition};";
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
        if ($where) $sql = "DELETE FROM {$this->name} {$where};";
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
        $sql = "DELETE FROM {$this->name} {$where};";
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
            $select_str = "COUNT({$this->name}.{$this->id_column})";
        } else {
            $select_str = $this->group_columns[0];
            foreach ($this->group_columns as $i => $group_column) {
                if (!strpos($group_column, '.')) {
                    $this->group_columns[$i] = $this->name . "." . $group_column;
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
            $condition = "{$this->name}.{$this->id_column} = {$conditions}";
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
                $_orders[] = "{$this->name}.{$order['column']} {$order['option']}";
            }
        }
        $order = implode(', ', $_orders);
        return $order;
    }

    /**
    * attribute informations
    * 
    * @param string $table
    * @return array
    **/
    public function attributeArray($table) {
        if (!$table) return;
        $column_comments = $this->columnCommentArray($table);

        //TODO
        $pg_primary_keys = $this->pgPrimaryKeys($this->dbname, $table);
        $primary_key = $pg_primary_keys[0]['column_name'];

        $pg_attributes = $this->pgAttributes($table);
        if ($pg_attributes) {
            foreach ($pg_attributes as $pg_attribute) {
                $pg_attribute['is_primary_key'] = ($primary_key && $pg_attribute['attname'] == $primary_key);
                $pg_attribute['comment'] = $column_comments[$pg_attribute['attname']];
                $values[$pg_attribute['attname']] = $pg_attribute;
            }
        }
        return $values;
    }

    /**
    * columnCommentArray
    * 
    * @param string $table
    * @return array
    **/
    public function columnCommentArray($table) {
        $comments = $this->columnComment($table);
        if ($comments) {
            foreach ($comments as $comment) {
                $column_name = $comment['attname'];
                $column_comments[$column_name] = $comment['description'];
            }
        }
        return $column_comments;
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
                    $name = $pg_class['relname'];
                    $pg_class['comment'] = $comments[$name];
                    $values[$name] = $pg_class;
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
    * @param string $relkind
    * @param string $schema_name
    * @return array
    **/
    function pgClasses($relkind = 'r', $schema_name = 'public') {
        $sql = "SELECT pg_class.oid AS pg_class_id, * FROM pg_class 
                LEFT JOIN pg_tables ON pg_tables.tablename = pg_class.relname
                LEFT JOIN pg_namespace ON pg_namespace.oid = pg_class.relnamespace
                WHERE relkind = '{$relkind}' 
                AND relfilenode > 0 
                AND nspname = '{$schema_name}';";
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
    * @param string $table
    * @return array
    **/
    function pgTableByTableName($table, $schema_name = 'public') {
        if (!$table) return;
        $sql = "SELECT * FROM pg_tables WHERE schemaname = '{schema_name}' AND tablename = '{$table}';";
        return $this->fetch_row($sql);
    }

    /**
    * attributes
    *
    * @param string $table
    * @return array
    **/
    function pgAttributes($table) {
        $sql = "SELECT pg_class.oid AS pg_class_id, * FROM pg_class 
                LEFT JOIN pg_attribute ON pg_class.oid = pg_attribute.attrelid
                LEFT JOIN information_schema.columns ON information_schema.columns.table_name = pg_class.relname
                AND information_schema.columns.column_name = pg_attribute.attname 
                WHERE pg_attribute.attnum > 0
                AND atttypid > 0 
                AND relacl IS NULL  
                AND relname = '{$table}';";
        return $this->fetch_rows($sql);
//                AND attnum > 0 
    }

    /**
    * pg_attribute
    *
    * @param string $table
    * @param string $column
    * @return array
    **/
    function pgAttributeByColumn($table, $column) {
        $sql = "SELECT pg_class.oid AS pg_class_id, * FROM pg_class 
                LEFT JOIN pg_attribute ON pg_class.oid = pg_attribute.attrelid
                LEFT JOIN information_schema.columns ON information_schema.columns.table_name = pg_class.relname
                AND information_schema.columns.column_name = pg_attribute.attname 
                WHERE pg_attribute.attnum > 0
                AND atttypid > 0 
                AND attname = '{$column}'
                AND relname = '{$table}';";
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
     * @param  string $table
     * @param  string $comment
     * @return array
     */
    function updateTableComment($table, $comment) {
        if (!$table) return;
        if (!$comment) return;
        $sql = "COMMENT ON TABLE \"{$table}\" IS '{$comment}';";
        return $this->query($sql);
    }

    /**
     * update column comment
     *
     * @param  string $table
     * @param  string $column_name
     * @param  string $comment
     * @return array
     */
    function updateColumnComment($table, $column_name, $comment) {
        if (!$table) return;
        if (!$column_name) return;
        if (!$comment) return;
        $sql = "COMMENT ON COLUMN \"{$table}\".{$column_name} IS '{$comment}';";
        return $this->query($sql);
    }

    /**
     * table comments
     *
     * @param  string $table
     * @param  string $schama_name
     * @return array
     */
    function tableComments($table = null, $schama_name = 'public') {
        $sql = "SELECT psut.relname ,pd.description
                FROM pg_stat_user_tables psut ,pg_description pd
                WHERE psut.relid = pd.objoid
                    AND schemaname = '{$schama_name}' 
                    AND pd.objsubid = 0";

        if ($table) $sql.= "relfilename = '{$table}'";
        $sql.= ";";
        return $this->fetch_rows($sql);
    }

    /**
     * table comments array
     *
     * @param  string $table
     * @param  string $schama_name
     * @return array
     */
    function tableCommentsArray($table = null, $schama_name = 'public') {
        $comments = $this->tableComments();
        if (!$comments) return;
        foreach ($comments as $comment) {
            $table_name = $comment['relname'];
            $values[$table_name] = $comment['description'];
        }
        return $values;
    }

    /**
     * comments
     *
     * @param  string $database_name
     * @param  string $table
     * @return array
     */
    function comments() {
        $sql = "SELECT psat.relname, pa.attname, pd.description
                FROM pg_stat_all_tables psat ,pg_description pd ,pg_attribute pa
                WHERE
                psat.relid = pd.objoid
                AND pd.objsubid <> 0
                AND pd.objoid = pa.attrelid
                AND pd.objsubid = pa.attnum
                ORDER BY
                pd.objsubid";
                
        return $this->fetch_rows($sql);
    }

    /**
     * table comment
     *
     * @param  string $table
     * @return array
     */
    function tableComment($table) {
        if (!$table) return;
        $sql = "SELECT pg_stat_all_tables.relname, pg_description.description
                FROM pg_stat_all_tables, pg_description
                WHERE pg_stat_all_tables.relname='\"{$table}\"'";
        return $this->fetch_row($sql);
    }

    /**
     * column comment
     *
     * @param  string $database_name
     * @param  string $table
     * @return array
     */
    function columnComment($table) {
        $sql = "SELECT psat.relname, pa.attname, pd.description
                FROM pg_stat_all_tables psat ,pg_description pd ,pg_attribute pa
                WHERE
                psat.schemaname=(SELECT schemaname FROM pg_stat_user_tables WHERE relname = '{$table}')
                AND psat.relname='{$table}'
                AND psat.relid=pd.objoid
                AND pd.objsubid <> 0
                AND pd.objoid = pa.attrelid
                AND pd.objsubid = pa.attnum
                ORDER BY pd.objsubid;";
        return $this->fetch_rows($sql);
    }

    /**
     * primary keys
     *
     * @param  string $database_name
     * @param  string $table
     * @return array
     */
    function pgPrimaryKeys($database_name, $table = null) {
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

        if ($table) $sql.= " AND tc.table_name = '{$table}'";
        $sql.= ";";
        return $this->fetch_rows($sql);
    }

    /**
     * not null
     *
     * @param  string $table
     * @param  string $column
     * @return array
     */
    public function setNotNull($table, $column) {
        $sql = "ALTER TABLE \"{$table}\" ALTER COLUMN {$column} SET NOT NULL;";
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
            $type.= "({$posts['length']})";
        }
        return $type;
    }

}