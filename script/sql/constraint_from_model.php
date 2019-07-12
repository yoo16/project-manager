<?php
/**
 * Copyright (c) 2017 Yohei Yoshikawa 
 *
 */
require_once dirname(__FILE__) . '/../../lib/Controller.php';

echo('-- Constraint SQL --'.PHP_EOL);
$pgsql = new PwPgsql();
$pgsql->constraintSQLForProject();

if ($pgsql->sql_files && file_exists(DB_DIR)) {
    echo('-- export --').PHP_EOL;
    foreach ($pgsql->sql_files as $file_name => $sql) {
        if ($file_name) {
            if ($sql['add']) {
                $sql_path = DB_DIR."sql/add_constraint_{$file_name}.sql";
                file_put_contents($sql_path, $sql['add']);
                echo($sql_path).PHP_EOL;
            }
            if ($sql['drop']) {
                $sql_path = DB_DIR."sql/drop_constraint_{$file_name}.sql";
                file_put_contents($sql_path, $sql['drop']);
                echo($sql_path).PHP_EOL;
            }
        }
    }
}
