<?php
/**
 * Database
 *
 * @package 
 * @author  Yohei Yoshikawa
 * @create  2013-04-15 16:33:13
 */
require_once 'vo/_Database.php';

class Database extends _Database {

    function __construct($params=null) {
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

    /**
     * pg_connect info
     *
     * @return string
     */
    function convertPgConnectionString() {
        $dbname = '';
        $host = 'localhost';
        $port = '5432';
        $user = 'postgres';

        if ($this->value['name']) $dbname = $this->value['name'];
        if ($this->value['hostname']) $host = $this->value['hostname'];
        if ($this->value['port']) $port = $this->value['port'];
        if ($this->value['user_name']) $user = $this->value['user_name'];

        if (!$dbname) return;

        $result = "host={$host} port={$port} dbname={$dbname} user={$user}";

        return $result;
    }


    /**
     * pg_connect info
     *
     * @return array
     */
    function pgConnectArray() {
        $result['dbname'] = '';
        $result['host'] = 'localhost';
        $result['port'] = '5432';
        $result['user'] = 'postgres';

        if ($this->value['name']) $result['dbname'] = $this->value['name'];
        if ($this->value['hostname']) $result['host'] = $this->value['hostname'];
        if ($this->value['port']) $result['port'] = $this->value['port'];
        if ($this->value['user_name']) $result['user'] = $this->value['user_name'];

        if (!$result['dbname']) return;
        return $result;
    }

}