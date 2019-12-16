<?php
/**
 * iPwSQL 
 *
 * @copyright  Copyright (c) 2019 Yohei Yoshikawa (https://github.com/yoo16/)
 */

//TODO any methods move to PwEntity.php
//TODO pg_escape_identifier
//TODO pg_escape_literal
//TODO pg_field_num, pg_field_name

interface iPwSQL
{
    public function fetch($id);
    public function select($columns = null, $as_columns = null);
    public function insert($posts = null);
    public function inserts($rows);
    public function update($posts = null, $id = null);
    public function delete($id = null);
    public function upsert($posts, $upsert_constraint = null);
    public function fetchRows($sql);
    public function refresh();
    static function initDb();
}