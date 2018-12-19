<?php
/**
 * Copyright (c) 2017 Yohei Yoshikawa (http://yoo-s.com/)
 */

echo('DB rollback').PHP_EOL;

require_once dirname(__FILE__).'/../lib/setting.php';
require_once 'PwPgsql.php';

$pgsq_entity = new PwPgsql();
$schema_sql = 'SELECT version FROM schema_info;';
$schema_version = (int) $pgsq_entity->fetch_result($schema_sql);
echo($schema_sql).PHP_EOL;

echo "current schema version: {$schema_version}".PHP_EOL;

if (!$schema_version && $schema_version === 0) return;

$rollback = false;

if ($dp = opendir(BASE_DIR . 'db/migrate/rollback')) {
    while ($file = readdir($dp)) {
        if (preg_match("/^(\d{3})_/", $file, $matches)) {
            $version = (int) $matches[1];
            if ($schema_version == $version) {
                $sql = trim(file_get_contents(BASE_DIR . "db/migrate/rollback/{$file}"));
                echo($file).PHP_EOL;
                echo($sql).PHP_EOL;

                $update_version = $version - 1;
                $sql = "BEGIN;{$sql};\nUPDATE schema_info SET version = {$update_version};COMMIT;";
                $result = $pgsq_entity->query($sql);
               if ($result) {
                    $rollback = true;
                }
                break;
            }
        }
    }
}

if ($rollback) {
    exit("rollback schema version: {$schema_version}\n");
} else {
    exit("no rollback\n");
}
?>