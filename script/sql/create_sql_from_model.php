<?php
/**
 * Copyright (c) 2017 Yohei Yoshikawa 
 *
 */
require_once dirname(__FILE__) . '/../../lib/Controller.php';

echo('-- Create SQL --'.PHP_EOL);
$pgsql = new PwPgsql();
$pgsql->createTablesSQLForProject();

if ($pgsql->sql_files && file_exists(DB_DIR)) {
    foreach ($pgsql->sql_files as $file_name => $sql) {
        if ($file_name) {
            $sql_path = DB_DIR."sql/{$file_name}.sql";
            file_put_contents($sql_path, $sql);
        }
    }
}
