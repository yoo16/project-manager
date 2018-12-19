<?php
/**
 * Copyright (c) 2017 Yohei Yoshikawa (http://yoo-s.com/)
 */

echo('-- Create SQL --'.PHP_EOL);

require_once dirname(__FILE__).'/../../lib/Controller.php';
require_once 'PwPgsql.php';

$pgsql = new PwPgsql();
$sql = $pgsql->createTablesSQLForProject();

if (file_exists(DB_DIR)) {
    $sql_path = DB_DIR.'sql/create.sql';
    file_put_contents($sql_path, $sql);
}
