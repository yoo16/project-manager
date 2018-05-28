<?php
/**
 * Copyright (c) 2013 Yohei Yoshikawa (http://yoo-s.com/)
 */

echo('-- Create SQL --'.PHP_EOL);

require_once dirname(__FILE__).'/../lib/Controller.php';
require_once 'PgsqlEntity.php';

$pgsql = new PgsqlEntity();
$sql = $pgsql->createTablesSQLForProject();

if (file_exists(DB_DIR)) {
    $sql_path = DB_DIR.'sql/create.sql';
    file_put_contents($sql_path, $sql);
}
