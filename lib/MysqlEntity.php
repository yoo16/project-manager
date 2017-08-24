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

}