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
        $pg_info = '';
        if ($params['host']) $pg_info = "host={$params['host']}";
        if ($params['user']) $pg_info.= " user={$params['user']}";
        if ($params['dbname']) $pg_info.= " dbname={$params['dbname']}";
        if ($pg_info) $this->pg_info = $pg_info;
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
    * checkInfo
    * 
    * @param  array $values
    * @return array
    */
    static function checkInfo($values) {
        if (!$values['user']) $values['user'] = 'postgres';
        if (!$values['host']) $values['host'] = 'localhost';
        if (!$values['port']) $values['port'] = '5432';
        return $values;
    }

    /**
    * createDatabase
    * 
    * @param  array $values
    * @return array
    */
    static function createDatabase($values) {
        if (!$values) return;

        $database_name = $values['dbname'];
        if (!$database_name) return;

        $values = self::checkInfo($values);

        $cmd = "createdb -U {$values['user']} -E UTF8 --host {$values['host']} --port {$values['port']} {$database_name} 2>&1";

        exec($cmd, $output, $return);

        $results['cmd'] = $cmd;
        $results['output'] = $output;
        $results['return'] = $return;
        return $results;
    }

    /**
    * dropDatabase
    * 
    * @param  array $values
    * @return array
    */
    static function dropDatabase($values) {
        if (!$values) return;

        $database_name = $values['dbname'];
        if (!$database_name) return;

        $values = self::checkInfo($values);

        $cmd = "dropdb -U {$values['user']} --host {$values['host']} --port {$values['port']} {$database_name} 2>&1";

        exec($cmd, $output, $return);

        $results['cmd'] = $cmd;
        $results['output'] = $output;
        $results['return'] = $return;
        return $results;
    }

    /**
    * pgInfo
    * 
    * @return array
    */
    static function pgInfo() {
        if (!defined('PG_INFO')) return;
        $values = explode(' ', PG_INFO);
        foreach ($values as $value) {
            if (is_numeric(strpos($value, 'dbname='))) {
                $results['dbname'] = trim(str_replace('dbname=', '', $value));
            }
            if (is_numeric(strpos($value, 'user='))) {
                $results['user'] = trim(str_replace('user=', '', $value));
            }
            if (is_numeric(strpos($value, 'port='))) {
                $results['port'] = trim(str_replace('port=', '', $value));
            }
            if (is_numeric(strpos($value, 'host='))) {
                $results['host'] = trim(str_replace('host=', '', $value));
            }
        }
        $results['pg_info'] = PG_INFO;
        return $results;
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

        $sql = "ALTER TABLE \"{$table}\" ALTER COLUMN \"{$column}\" TYPE \"{$type}\";";
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
        if (!$id) return;
        $this->where("{$this->id_column} = {$id}")
        ->selectOne($params);
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
        $this->values = null;
        if (!$id) return $this;
        $this->where("{$this->id_column} = {$id}")
        ->selectOne($params);
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
    * selectOne
    * 
    * @param  array $params
    * @return array
    */
    public function selectOneValue($params = null) {
        return $this->selectOne($params)->value;
    }

    /**
    * insert
    * 
    * @param  array $posts
    * @return Class
    */
    public function insert($posts=null) {
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
    * insert
    * 
    * @param  array $posts
    * @param  int $id
    * @return Class
    */
    public function update($posts = null, $id = null) {
        if ($id) $this->get($id);
        if ($posts) $this->takeValues($posts);

        $this->validate();
        if ($this->errors) return $this;

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
    * deleteSql
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
    public function attributeValues($table) {
        if (!$table) return;
        $column_comments = $this->columnCommentArray($table);

        $attributes = $this->attributeArray($table);

        $pg_primary_keys = $this->peimaryKeys($this->database->value['name'], $table);
        if ($pg_primary_keys) {
            foreach ($pg_primary_keys as $primary_key) {
                $primary_keys = $primary_key['column_name'];
            }
        }

        $pg_attributes = $this->pgAttributes($table);
        if ($pg_attributes) {
            foreach ($pg_attributes as $pg_attribute) {
                $column_name = $pg_attribute['column_name'];

                $pg_attribute['is_primary_key'] = ($primary_keys && $pg_attribute['column_name'] == $primary_keys);
                $pg_attribute['comment'] = $column_comments[$column_name];

                if ($attribute = $attributes[$column_name]) {
                    $pg_attribute = array_merge($pg_attribute, $attribute);
                }

                $values[] = $pg_attribute;
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
    * attributeArray
    * 
    * @param string $table
    * @return array
    **/
    public function attributeArray($table) {
        $_attributes = $this->attribute($table);

        if ($_attributes) {
            foreach ($_attributes as $attribute) {
                $column_name = $attribute['attname'];
                $attributes[$column_name] = $attribute;
            }
        }
        return $attributes;
    }


    /**
    * databases
    * 
    * @param string $name
    * @return array
    **/
    function pgDatabases($name) {
        $sql = "SELECT * FROM pg_database;";
        return $this->fetch_rows($sql);
    }

    /**
    * pg_database
    * 
    * @param string $name
    * @return array
    **/
    function pgDatabase($name) {
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
        $sql = "SELECT * FROM pg_class 
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
        $sql = "SELECT * FROM pg_class 
                LEFT JOIN pg_tables ON pg_tables.tablename = pg_class.relname
                LEFT JOIN pg_namespace ON pg_namespace.oid = pg_class.relnamespace
                WHERE relkind = '{$relkind}' 
                AND relfilenode > 0 
                AND nspname = '{$schema_name}';";
        return $this->fetch_rows($sql);
    }

    /**
    * pg_class (relfilenode)
    * relnamespace = 2200 は public
    *
    * @param int $relfilenode
    * @return array
    **/
    function pgClassByRelfilenode($relfilenode) {
        $sql = "SELECT * FROM pg_class WHERE relnamespace = 2200 AND relfilenode = '{$relfilenode}';";
        return $this->fetch_row($sql);
    }

    /**
    * pg_tables (table_name)
    *
    * @param string $table
    * @return array
    **/
    function pgTableByTableName($table) {
        if (!$table) return;
        $sql = "SELECT * FROM pg_tables WHERE schemaname = 'public' AND tablename = '{$table}';";
        return $this->fetch_row($sql);
    }

    /**
    * columns
    *
    * @param string $table
    * @return array
    **/
    function pgAttributes($table = null) {
        $sql = "SELECT * FROM information_schema.columns";
        if ($table) $sql.= " WHERE table_name = '{$table}'
                             AND is_updatable = 'YES'
                             ORDER BY ordinal_position;";
        return $this->fetch_rows($sql);
    }

    /**
    * column
    *
    * @param string $table
    * @return array
    **/
    function pgAttribute($table, $column) {
        $sql = "SELECT * FROM information_schema.columns
                         WHERE table_name = '{$table}'
                         AND column_name = '{$column}'
                         AND is_updatable = 'YES'
                         ORDER BY ordinal_position;";
        return $this->fetch_row($sql);
    }

    /**
    * pg_attribute
    *
    * @param string $table
    * @param string $column
    * @return array
    **/
    function pgAttributeByColumn($table, $column) {
        $sql = "SELECT * FROM pg_attribute WHERE attnum > 0
                AND attname = '{$column}';";
        //AND attrelid = (SELECT oid FROM pg_class WHERE relname = '\"{$table}\"')
        return $this->fetch_row($sql);
    }

    /**
    * pg_attribute
    *
    * @param string $table
    * @param int $attnum
    * @return array
    **/
    function pgAttributeByTablenameAttnum($table, $attnum) {
        $sql = "SELECT * FROM pg_attribute WHERE
                attnum = {$attnum}
                AND attrelid = (SELECT oid FROM pg_class WHERE relname = '{$table}' AND relnamespace = 2200);";
        return $this->fetch_row($sql);
    }

    /**
    * pg_attribute
    *
    * @param int $attrelid
    * @return array
    **/
    function pgAttributeByAttrelid($attrelid) {
        $sql = "SELECT * FROM pg_attribute WHERE attrelid = {$attrelid};";
        return $this->fetch_rows($sql);
    }

    /**
    * pg_attribute
    *
    * @param int $attrelid
    * @param string $attnum
    * @return array
    **/
    function pgAttributeByIdName($attrelid, $attname) {
        $sql = "SELECT * FROM pg_attribute WHERE attrelid = {$attrelid} AND attname = '{$attname}';";
        return $this->fetch_row($sql);
    }

    /**
    * pg_attribute
    *
    * @param string $relname
    * @return array
    **/
    function attribute($relname) {
        $sql = "SELECT attrelid, attname , attnum , atttypid , atttypmod , typname , attnotnull
                ,CASE con_u.contype WHEN 'u' THEN true ELSE false END AS is_unique
                ,CASE con_p.contype WHEN 'p' THEN true ELSE false END AS is_primary_key
                FROM pg_stat_user_tables stat
                INNER JOIN pg_attribute att ON att.attrelid = stat.relid
                INNER JOIN pg_type type ON att.atttypid = type.typelem
                INNER JOIN pg_class class ON class.relname = stat.relname
                LEFT JOIN pg_constraint con_u ON con_u.conkey[1] = att.attnum
                AND con_u.contype = 'u' AND con_u.conrelid = class.oid
                LEFT JOIN pg_constraint con_p ON att.attnum = ANY (con_p.conkey)
                AND con_p.contype = 'p' AND con_p.conrelid = class.oid
                WHERE 1 = 1
                AND stat.schemaname = 'public'
                AND att.attnum > 0
                AND substr(type.typname,1,1) = '_'
                AND stat.relname = '{$relname}'";
        return $this->fetch_rows($sql);
    }


    /**
     * update table comment
     *
     * @param  string $table
     * @param  string $comment
     * @return array
     */
    function updateTableComment($table, $comment) {
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
        $sql = "COMMENT ON COLUMN \"{$table}\".{$column_name} IS '{$comment}';";
        return $this->query($sql);
    }

    /**
     * table comments
     *
     * @param  string $database_name
     * @param  string $table
     * @return array
     */
    function tableComments() {
        $sql = "SELECT psut.relname ,pd.description
                FROM pg_stat_user_tables psut ,pg_description pd
                WHERE
                    psut.relid = pd.objoid
                    AND pd.objsubid = 0";
        return $this->fetch_rows($sql);
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
                psat.schemaname=(SELECT schemaname FROM pg_stat_user_tables WHERE relname = '\"{$table}\"')
                AND psat.relname='\"{$table}\"'
                AND psat.relid=pd.objoid
                AND pd.objsubid <> 0
                AND pd.objoid = pa.attrelid
                AND pd.objsubid = pa.attnum
                ORDER BY
                pd.objsubid";
        return $this->fetch_rows($sql);
    }

    /**
     * primary keys
     *
     * @param  string $database_name
     * @param  string $table
     * @return array
     */
    function peimaryKeys($database_name, $table = null) {
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

        if ($table) $sql.= " AND tc.table_name = '\"{$table}\"'";

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

}