<?php
/**
 * MysqlEntity 
 *
 * under construction
 * 
 * Copyright (c) 2013 Yohei Yoshikawa (http://yoo-s.com/)
 */

//check
if (!defined('DB_HOST')) exit('Not found DB_HOST in setting file.');
if (!defined('DB_USER')) exit('Not found DB_USER in setting file.');
if (!defined('DB_PASS')) exit('Not found DB_PASS in setting file.');
if (!defined('DB_NAME')) exit('Not found DB_NAME in setting file.');

require_once 'Entity.php';

class MysqlEntity extends Entity {
    var $extra_columns = false;
    var $group_columns = false;
    var $joins = array();

    function connection() {
        $connect = mysql_connect(DB_HOST, DB_USER, DB_PASS);
		if (defined('DB_OUTPUT_ENCODING') && DB_OUTPUT_ENCODING) {
		     $result = mysql_query("SET NAMES utf8", $connect);
		}
        $db = mysql_select_db(DB_NAME);
		if (!$db) {
    		die('error: ' . mysql_error());
		}
        return $db;
    }

    function query($sql) {
        $mysql = MysqlEntity::connection();
		
        if (defined('DEBUG') && DEBUG) error_log("<SQL> {$sql}");
        return mysql_query($sql);
    }
    
    function mysql_rows($rs) {
        while ($row = mysql_fetch_assoc($rs)) {
			if (defined('DB_ENCODING') && defined('DB_CONVERT_ENCODING')) {
			    foreach ($row as $key => $value) {
				    $values[$key] = mb_convert_encoding($value, DB_CONVERT_ENCODING, DB_ENCODING);
			    }
			} else {
				$values = $row;
			}
           $rows[] = $values; 
        }
        return $rows;
    }
	
    function fetch_rows($sql) {
        $rs = MysqlEntity::query($sql);
        if ($rs) {
            return MysqlEntity::mysql_rows($rs);
        } else {
            return false;
        }
    }

    function fetch_row($sql) {
        $rs = MysqlEntity::query($sql);
        if ($rs) {
            $row = mysql_fetch_assoc($rs);
            if (defined('DB_ENCODING') && defined('DB_CONVERT_ENCODING') && is_array($row)) {
                foreach ($row as $key => $value) {
    				$values[$key] = mb_convert_encoding($value, DB_CONVERT_ENCODING, DB_ENCODING);
    			 }
			 } else {
			     $values = $row;
			 }
            return ($values) ? $values : null;
        } else {
            return false;
        }
    }

    function fetch_result($sql) {
        $rs = MysqlEntity::query($sql);
        if ($rs) {
			$obj = mysql_fetch_array($rs);
			$result = $obj[0];
			return ($result) ? $result : null;
        } else {
            return false;
        }
    }

    function fetch_all($order = null) {
        $sql = "SELECT {$this->name}.* FROM {$this->name}";
        if (isset($order)) {
            $sql .= " ORDER BY {$order}";
        }
        $sql .= ";";
        $rows = MysqlEntity::fetch_rows($sql);
        $this->_cast_rows($rows);
        return $rows;
    }

    function fetch($conditions, $order = null) {
        if (is_null($conditions)) {
            return false;
        } elseif (is_numeric($conditions)) {
            $conditions = "id = {$conditions}";
        } elseif (is_array($conditions)) {
            foreach ($conditions as $i => $condition) {
                $conditions[$i] = "({$condition})";
            }
            $conditions = implode(' AND ', $conditions);
        }
        $sql = "SELECT {$this->name}.* FROM {$this->name} WHERE {$conditions}";
        if (isset($order)) {
            $sql .= " ORDER BY {$order}";
        }
        $sql .= ";";
        
        $this->value = MysqlEntity::fetch_row($sql);
        $this->applyCast();
        if (is_array($this->value) && isset($this->value['id'])) {
            $this->id = (int) $this->value['id'];
        }
        $this->_value = $this->value;
        return $this->value;
    }

    function results_sql() {
        if (is_bool($this->group_columns) && $this->group_columns) {
            $this->group_columns = array_keys($this->columns);
            array_unshift($this->group_columns, "{$this->name}.id");
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
        if (defined('DB_ENCODING') && defined('DB_CONVERT_ENCODING')) {
            $sql = mb_convert_encoding($sql, DB_ENCODING, DB_CONVERT_ENCODING);
        }
        return $sql;
    }

    function results($offset = null, $limit = null) {
        $sql = $this->results_sql();
		
        $_limit = 0;
        if (isset($offset)) {
            if ($offset > 0) {
                $_offset = $offset - 1;
                if (isset($limit)) {
                    $_limit = 1;
                }
            } else {
                $_offset = $offset;
            }
            $sql .= " LIMIT {$_offset}";
        }
        if (isset($limit)) {
            $_limit += $limit + 1;
            $sql .= " , {$_limit}";
        }
        $sql .= ";";
        $this->prev_result = $this->next_result = false;
        $rows = MysqlEntity::fetch_rows($sql);
		
        if ($rows) {
            $this->_cast_rows($rows);
            if (isset($offset) && ($offset > 0)) {
                $this->prev_result = array_shift($rows);
            }
            if (isset($limit) && count($rows) > $limit) {
                $this->next_result = array_pop($rows);
            }
        }
        return $rows;
    }

    function count() {

        $from_str = ($this->from_sql) ? "({$this->from_sql}) AS {$this->name}" : $this->name;
        if (is_array($this->joins)) {
            foreach ($this->joins as $join) {
                $from_str .= " " . $join;
            }
        }

        if (is_bool($this->group_columns) && $this->group_columns) {
            $this->group_columns = array_keys($this->columns);
            array_unshift($this->group_columns, 'id');
        }
        if (empty($this->group_columns)) {
            $select_str = "COUNT({$this->name}.id)";
        } else {
            $select_str = $this->group_columns[0];
            foreach ($this->group_columns as $i => $group_column) {
                if (!strpos($group_column, '.')) {
                    $this->group_columns[$i] = $this->name . "." . $group_column;
                }
            }
            $group_str = implode(', ', $this->group_columns);
        }
        $sql = "SELECT {$select_str} FROM {$from_str}";

        if (!empty($this->conditions)) {
            foreach ($this->conditions as $condition) {
                if (isset($conditions)) $conditions .= ' AND ';
                $conditions .= "({$condition})";
            }
        }
        if (isset($conditions)) {
            $sql .= " WHERE {$conditions}";
        }

        if (isset($group_str)) {
            $sql = "SELECT COUNT(*) FROM ({$sql} GROUP BY {$group_str}) AS t";
        }
        $sql .= ";";

        $count = MysqlEntity::fetch_result($sql); 
        if (is_null($count)) $count = 0;
        return $count;
    }

    function delete() {
        if (is_int($this->id)) {
            $conditions = "id = {$this->id}";
        } else {
            if (!empty($this->conditions)) {
                foreach ($this->conditions as $condition) {
                    if (isset($conditions)) $conditions .= ' AND ';
                    $conditions .= "({$condition})";
                }
            }
        }
        if (isset($conditions)) {
            $sql = "DELETE FROM {$this->name} WHERE {$conditions}";
            $result = MysqlEntity::query($sql);
            if ($result !== false) {
                unset($this->id);
                return true;
            }
        }
        return false;
    }

    function insert() {
        foreach ($this->columns as $key => $type) {
            $value = $this->_sql_value($this->value[$key]);
            if (isset($str_keys) && isset($str_values)) {
                $str_keys .= ',';
                $str_values .= ',';
            }
            if ($key == 'created_at') {
                $value = 'current_timestamp';
            }
            $str_keys .= $key;
            $str_values .= $value;
        }
        $sql = "INSERT INTO {$this->name} ({$str_keys}) VALUES ({$str_values});";
        if (defined('DB_ENCODING') && defined('DB_CONVERT_ENCODING')) {
            $sql = mb_convert_encoding($sql, DB_ENCODING, DB_CONVERT_ENCODING);
		 }
        if (defined('DEBUG') && DEBUG) error_log("<SQL> {$sql}");

        $result = MysqlEntity::query($sql);
        $result = ($result) ? true : null;

        if ($result) {
            $sql ="SELECT * FROM {$this->name} ORDER BY id DESC LIMIT 1;";
            if (defined('DEBUG') && DEBUG) error_log("<SQL> {$sql}");
            $value = MysqlEntity::fetch_row($sql);
            if ($value) {
                $this->id = (int) $value['id'];
                $this->value['id'] = $this->id;
                return true;
            }
        }
        return null;
    }

    function update() {
        $changes = $this->changes();
        if (isset($this->columns['updated_at'])) {
            $str_key_values = "updated_at = current_timestamp";
        }
        foreach ($changes as $key => $org_value) {
            $value = $this->_sql_value($this->value[$key]);
            if (isset($str_key_values)) {
                $str_key_values .= ',';
            }
            $str_key_values .= "{$key} = {$value}";
        }
        $sql = "UPDATE {$this->name} SET {$str_key_values} WHERE id = {$this->id};";
        if (defined('DB_ENCODING') && defined('DB_CONVERT_ENCODING')) {
            $sql = mb_convert_encoding($sql, DB_ENCODING, DB_CONVERT_ENCODING);
		 }
		 
		if (defined('DEBUG') && DEBUG) error_log("<SQL> {$sql}");
				
        $result = MysqlEntity::query($sql);
        if ($result !== false) {
            $this->_value = $this->value;
            return true;
        } else {
            return false;
        }
    }

    function set_join($table, $conditions, $type = 'INNER') {
        $this->joins = array();
        $this->add_join($table, $conditions, $type);
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

    function _cast_row(&$row) {
        parent::_cast_row($row);
        if (is_array($row) && is_array($this->extra_columns)) {
            foreach ($this->extra_columns as $column => $def) {
                $type = substr($def, 0, 1);
                $row[$column] = $this->_cast($type, $row[$column]);
            }
        }
    }

    function _sql_value($value) {
        if (is_null($value)) {
            return "NULL";
        } elseif (is_int($value) || is_float($value)) {
            return (string) $value;
        } elseif (is_bool($value)) {
            return ($value) ? 'TRUE' : 'FALSE';
        } elseif (is_array($value)) {
            return "'" . mysql_escape_string(serialize($value)) . "'";
        } else {
            return "'" . mysql_escape_string($value) . "'";
        }
    }
}