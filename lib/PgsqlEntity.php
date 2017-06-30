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
    * createDatabase
    * 
    * @param  array $values
    * @return array
    */
    static function createDatabase($values) {
        if (!$values) return;

        $database_name = $values['dbname'];
        if (!$database_name) return;

        $user = $values['user']? $values['user'] : 'postgres';
        $host = $values['host']? $values['host'] : 'localhost';
        $port = $values['port']? $values['port'] : '5432';

        $cmd = "createdb -U {$user} -E UTF8 --host {$host} --port {$port} {$database_name} 2>&1";

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

        $user = $values['user']? $values['user'] : 'postgres';
        $host = $values['host']? $values['host'] : 'localhost';
        $port = $values['port']? $values['port'] : '5432';

        $cmd = "dropdb -U {$database_user} --host {$host} --port {$port} {$database_name} 2>&1";

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
    * addTable
    * 
    * @return resource
    */
    public function createTable($table_name, $type) {
        $sql = "CREATE TABLE {$table_name} (
                id SERIAL PRIMARY KEY NOT NULL
                , created_at TIMESTAMP NULL DEFAULT NULL 
                , updated_at TIMESTAMP NULL
               )";
        return $this->query($sql);
    }

    /**
    * renameTable
    * 
    * @return resource
    */
    public function renameTable($table_name, $new_name) {
        $sql = "ALTER TABLE {$table_name} RENAME TO {$new_name};";
        return $this->query($sql);
    }

    /**
    * dropTable
    * 
    * @return resource
    */
    public function dropTable($table_name, $type) {
        $sql = "DROP TABLE {$table_name};";
        return $this->query($sql);
    }


    /**
    * addColumn
    * 
    * @return resource
    */
    public function addColumn($column, $type) {
        $sql = "ALTER TABLE {$this->name} ADD COLUMN {$column} {$type};";
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

        exit;

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

    public function where($condition) {
        $this->conditions[] = $condition; 
        return $this;
    }

    public function order($column, $desc=false) {
        $value['column'] = $column;
        $value['desc'] = $desc;
        $this->orders[] = $value; 
        return $this;
    }

    public function initWhere($condition) {
        $this->conditions = null;
        return $this->where($condition);
    }

    public function initOrder($order) {
        $this->orders = null;
        return $this->order($order);
    }

    public function limit($limit) {
        $this->limit = $limit; 
        return $this;
    }

    public function offset($offset) {
        $this->offset = $offset; 
        return $this;
    }

    public function join($table, $type = 'INNER') {
        $this->joins = array();
        $this->add_join($table, $conditions, $type);
        return;
    }

    function add_join($table, $conditions, $type = 'INNER') {
        if (is_array($conditions)) {
            foreach ($conditions as $i => $condition) {
                $conditions[$i] = "({$table}.{$condition})";
            }
            $conditions = implode(' AND ', $conditions);
        } else {
            $conditions = "{$table}.{$conditions}";
        }
        $this->joins[] = "{$type} JOIN {$table} ON {$conditions}";
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
    // GROUP BY {$group_str}) AS t";
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


    //TODO
    function results_sql() {
        if (is_bool($this->group_columns) && $this->group_columns) {
            $this->group_columns = array_keys($this->columns);
            array_unshift($this->group_columns, "{$this->name}.{$this->id_column}");
        }
        if (empty($this->group_columns)) {
            $select_str = $this->name . '.*';
        } else {
            foreach ($this->group_columns as $i => $group_column) {
                if (!strpos($group_column, '.') && array_key_exists($group_column, $this->columns)) {
                    $this->group_columns[$i] = $this->name . "." . $group_column;
                    if (!empty($select_str)) $select_str .= ", ";
                    $select_str .=  $this->group_columns[$i];
                } elseif (!array_key_exists($group_column, $this->extra_columns)) {
                    if (!empty($select_str)) $select_str .= ", ";
                    $select_str .=  $group_column;
                }
            }
            $group_str = implode(', ', $this->group_columns);
        }

        if (is_array($this->extra_columns)) {
            foreach ($this->extra_columns as $extra_column => $def) {
                $extra_clause = substr($def, 2);
                if ($extra_clause) {
                    if (!empty($select_str)) $select_str .= ", ";
                    $select_str .= "{$extra_clause} AS {$extra_column}";
                }
            }
        }

        $from_str = ($this->from_sql) ? "({$this->from_sql}) AS {$this->name}" : $this->name;
        if (is_array($this->joins)) {
            foreach ($this->joins as $join) {
                $from_str .= " " . $join;
            }
        }

        $sql = "SELECT {$select_str} FROM {$from_str}";

        if (!empty($this->conditions)) {
            foreach ($this->conditions as $condition) {
                if (isset($conditions)) $conditions .= ' AND ';
                $conditions .= "({$condition})";
            }
        }
        if (isset($conditions)) $sql .= " WHERE {$conditions}";

        if (isset($group_str)) {
            $sql .= " GROUP BY {$group_str}";
        }

        if (!empty($this->orders)) {
            foreach ($this->orders as $order) {
                if (isset($orders)) $orders .= ', ';
                if (!strpos($order['column'], '.') && array_key_exists($order['column'], $this->columns)) {
                    $orders .= $this->name . '.' . $order['column'];
                } else {
                    $orders .= $order['column'];
                }
                if ($order['desc']) {
                    $orders .= ' DESC';
                }
            }
        }
        if (isset($orders)) $sql .= " ORDER BY {$orders}";
        return $sql;
    }

    /**
    * count
    * 
    * @param array $conditions
    * @return int
    **/
    public function count() {
    //TODO
        $from_str = ($this->from_sql) ? "({$this->from_sql}) AS {$this->name}" : $this->name;
        if (is_array($this->joins)) {
            foreach ($this->joins as $join) {
                $from_str .= " " . $join;
            }
        }

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
        if ($this->columns['sort_order']) $orders[] = array('column' => 'sort_order', 'desc' => false);
        if (!$orders) return;
        foreach ($orders as $order) {
            if (array_key_exists($order['column'], $this->columns)) {
                $asc_desc = ($order['desc']) ? 'DESC' : 'ASC';
                $_orders[] = "{$this->name}.{$order['column']} {$asc_desc}";
            }
        }
        $order = implode(', ', $_orders);
        return $order;
    }

    /**
    * attribute informations
    * 
    * @param string $name
    * @return array
    **/
    public function attributeValues($table_name) {
        $comments = $this->columnComment($table_name);
        if ($comments) {
            foreach ($comments as $comment) {
                $column_name = $comment['attname'];
                $column_comments[$column_name] = $comment['description'];
            }
        }

        $_attributes = $this->attribute($table_name);
        if ($_attributes) {
            foreach ($_attributes as $attribute) {
                $column_name = $attribute['attname'];
                $attributes[$column_name] = $attribute;
            }
        }

        $pg_primary_keys = $this->peimaryKeys($this->database->value['name'], $table_name);
        if ($pg_primary_keys) {
            foreach ($pg_primary_keys as $primary_key) {
                $primary_keys = $primary_key['column_name'];
            }
        }

        $pg_attributes = $this->pgAttributes($table_name);
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
    * pg_class
    * relnamespace = 2200 は public
    *
    * @param string $relname
    * @return array
    **/
    function pgClassByRelname($relname) {
        $sql = "SELECT * FROM pg_class WHERE relnamespace = 2200 AND relname = '{$relname}';";
        return $this->fetch_row($sql);
    }

    /**
    * pg_tables
    *
    * @param
    * @return array
    **/
    function pgTables() {
        $sql = "SELECT * FROM pg_tables WHERE schemaname = 'public' ORDER BY tablename;";
        return $this->fetch_rows($sql);
    }

    /**
    * pg_tables (table_name)
    *
    * @param string $table_name
    * @return array
    **/
    function pgTableByTableName($table_name) {
        if (!$table_name) return;
        $sql.= "SELECT * FROM pg_tables WHERE schemaname = 'public' AND tablename = '{$table_name}';";
        return $this->fetch_row($sql);
    }

    /**
    * columns
    *
    * @param string $table_name
    * @return array
    **/
    function pgAttributes($table_name = null) {
        $sql = "SELECT * FROM information_schema.columns";
        if ($table_name) $sql.= " WHERE table_name = '{$table_name}'";
        $sql.= " ORDER BY ordinal_position;";
        return $this->fetch_rows($sql);
    }

    /**
    * pg_attribute
    *
    * @param string $table_name
    * @param string $attribute_name
    * @return array
    **/
    function pgAttributeByTablenameAttributename($table_name, $attribute_name) {
        $sql.= "SELECT * FROM pg_attribute WHERE";
        $sql.= " attnum > 0";
        $sql.= " AND attrelid = (SELECT oid FROM pg_class WHERE relname = '{$table_name}' AND relnamespace = 2200)";
        $sql.= " AND attname = '{$attribute_name}';";
        return $this->query($sql);
    }

    /**
    * pg_attribute
    *
    * @param string $table_name
    * @param int $attnum
    * @return array
    **/
    function pgAttributeByTablenameAttnum($table_name, $attnum) {
        $sql.= "SELECT * FROM pg_attribute WHERE";
        $sql.= " attnum = {$attnum}";
        $sql.= " AND attrelid = (SELECT oid FROM pg_class WHERE relname = '{$table_name}' AND relnamespace = 2200);";
        return $this->fetch_row($sql);
    }

    /**
    * pg_attribute
    *
    * @param int $attrelid
    * @param int $attnum
    * @return array
    **/
    function pgAttributeByIdName($attrelid, $attname) {
        $sql.= "SELECT * FROM pg_attribute WHERE attrelid = {$attrelid} AND attname = '{$attname}';";
        return $this->query($sql);
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

    function updateTableComment($table_name, $comment) {
        $sql = "COMMENT ON TABLE {$table_name} IS '{$comment}';";
        return $this->fetch_row($sql);
    }

    function updateColumnComment($table_name, $column_name, $comment) {
        $sql = "COMMENT ON COLUMN {$table_name}.{$column_name} IS '{$comment}';";
        return $this->fetch_row($sql);
    }

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
     * @param  string $table_name
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
     * @param  string $table_name
     * @return array
     */
    function tableComment($table_name) {
        if (!$table_name) return;
        $sql = "SELECT pg_stat_all_tables.relname, pg_description.description
        FROM pg_stat_all_tables, pg_description
        WHERE pg_stat_all_tables.relname='{$table_name}'";
        return $this->fetch_row($sql);
    }

    /**
     * column comment
     *
     * @param  string $database_name
     * @param  string $table_name
     * @return array
     */
    function columnComment($table_name) {
        $sql = "SELECT psat.relname, pa.attname, pd.description
        FROM pg_stat_all_tables psat ,pg_description pd ,pg_attribute pa
        WHERE
        psat.schemaname=(SELECT schemaname FROM pg_stat_user_tables WHERE relname = '{$table_name}')
        AND psat.relname='{$table_name}'
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
     * @param  string $table_name
     * @return array
     */
    function peimaryKeys($database_name, $table_name = null) {
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

        return $this->fetch_rows($sql);
    }

}