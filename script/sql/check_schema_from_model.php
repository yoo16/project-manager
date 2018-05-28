<?php
/**
 * Copyright (c) 2017 Yohei Yoshikawa (http://yoo-s.com/)
 *
 */
require_once dirname(__FILE__).'/../../lib/Controller.php';

if ($argv[1] == 1) $is_excute_sql = true;
if ($argv[2]) {
  $host = $argv[2];
} else {
  $host = DB_HOST;
}
if (!$host) $host = 'localhost';

if ($is_excute_sql) {
    echo('--- Mode: excute SQL ---').PHP_EOL;
} else {
    echo('--- Mode: Do not excute SQL ---').PHP_EOL;
}
  echo("host: {$host}").PHP_EOL;
$pgsql = new PgsqlEntity();
$pgsql->is_excute_sql = $is_excute_sql;
$pgsql->setDBHost($host);
$pgsql->diffFromVoModel();