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

    static $udt_types = array(
        'timestamp' => 't',
        'varchar' => 's',
        'text' => 's',
        'bool' => 'b',
        'int2' => 'i',
        'int4' => 'i',
        'int8' => 'i',
        'float' => 'f',
        'double' => 'd',
        );

    function __construct($params = null) {
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
        if (!defined('PG_INFO')) {
            echo('Not Defined: PG_INFO');
            exit;
        }
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
    * @param string $table
    * @param array $columns
    * @return resource
    */
    public function createTable($table, $columns) {
        if (!$table) return;
        if (!$columns) return;

        foreach ($columns as $column_name => $column) {
            $column_sqls[] = "{$column_name} {$column['type']} {$column['option']}";
        }
        $column_sql = implode(",\n", $column_sqls);
        $sql = "CREATE TABLE \"{$table}\" (\n{$column_sql}\n)";

        return $this->query($sql);
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

        $sql = "ALTER TABLE \"{$table}\" ALTER COLUMN \"{$column}\" TYPE {$type};";
        return $this->query($sql);
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
        $this->sql = $sql;
        $pg = $this->connection();
        if (defined('DEBUG') && DEBUG) error_log("<SQL> {$sql}");
        return pg_query($pg, $sql);
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
    * where
    * 
    * @param  string $condition
    * @return Class
    */
    public function where($condition) {
        $this->conditions[] = $condition; 
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
    * initWhere
    * 
    * @param  string $condition
    * @return Class
    */
    public function offset($offset) {
        $this->offset = $offset; 
        return $this;
    }

    /**
    * initWhere
    * 
    * @param  string $condition
    * @return Class
    */
    public function join($table, $type = 'INNER') {
        $this->joins = array();
        $this->add_join($table, $conditions, $type);
        return;
    }

    function add_join($table, $conditions, $type = 'INNER') {
        if (is_array($conditions)) {
            foreach ($conditions as $i => $condition) {
                $conditions[$i] = "(\"{$table}\".{$condition})";
            }
            $conditions = implode(' AND ', $conditions);
        } else {
            $conditions = "\"{$table}\".{$conditions}";
        }
        $this->joins[] = "{$type} JOIN \"{$table}\" ON {$conditions}";
    }

    function set_left_join($t, $c)  { $this->set_join($t, $c, 'LEFT'); }
    function set_right_join($t, $c) { $this->set_join($t, $c, 'RIGHT'); }
    function add_left_join($t, $c)  { $this->add_join($t, $c, 'LEFT'); }
    function add_right_join($t, $c) { $this->add_join($t, $c, 'RIGHT'); }

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
    * whereSql
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
        $sql = "SELECT {$this->name}.* FROM {$this->name}";
        $sql.= $this->whereSql($params);
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
                $name = $pg_class['relname'];
                $pg_class['comment'] = $comments[$name];
                $values[$name] = $pg_class;
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
    function pgDatabase() {
        $sql = "SELECT * FROM pg_database WHERE datname = '{$this->dbname}';";
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

    static function typeByPgAttribute($pg_attribute) {
        if ($pg_attribute['udt_name']) {
            $type = self::$udt_types[$pg_attribute['udt_name']];
        }
        return $type;
    }

}