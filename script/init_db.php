<?php
/**
 * @author  Yohei Yoshikawa
 *
 * Copyright (c) 2017 Yohei Yoshikawa (http://yoo-s.com/)
 */

require_once dirname(__FILE__).'/../lib/Controller.php';
require_once 'PwPgsql.php';

$pgsq_entity = new PwPgsql();
$schema_sql = 'SELECT version FROM schema_info;';
$schema_version = $pgsq_entity->fetch_result($schema_sql);

if (!is_null($schema_version)) $schema_version = (int) $schema_version;
echo($schema_sql).PHP_EOL;
echo("current schema version: {$schema_version}").PHP_EOL;

if (!is_int($schema_version)) {
    $schema_info_sql  = "CREATE TABLE schema_info (version integer NOT NULL);";
    $schema_info_sql .= "INSERT INTO schema_info (version) VALUES (0);";
    $pgsq_entity->query($schema_info_sql);
    $schema_version = 0;
    echo($schema_info_sql).PHP_EOL;
}

$file = BASE_DIR . "db/sql/create.sql";
if (file_exists($file)) {
    $sql = trim(file_get_contents($file));
    $result = $pgsq_entity->query($sql);
    echo($sql).PHP_EOL;
    echo($result).PHP_EOL;
    if (!$result) $is_faild = true;

    $file = BASE_DIR . "db/sql/create_sq.sql";
    if (file_exists($file)) {
        $sql = trim(file_get_contents($file));
        $result = $pgsq_entity->query($sql);
        echo($sql).PHP_EOL;
        echo($result).PHP_EOL;
        if (!$result) $is_faild = true;
    }
} else {
    exit('not found create.sql.');
}

if ($is_faild) {
    exit("faild!!");
} else {
    exit("success!!");
}