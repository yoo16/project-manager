<?php
/**
 * @author  Yohei Yoshikawa
 *
 * Copyright (c) 2013 Yohei Yoshikawa (http://yoo-s.com/)
 */

require_once dirname(__FILE__).'/../lib/Controller.php';
require_once 'PgsqlEntity.php';

$pgsq_entity = new PgsqlEntity();
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

$updated = false;

if ($dp = opendir(BASE_DIR . 'db/migrate')) {
    while ($file = readdir($dp)) {
        if (preg_match("/^(\d{3})_/", $file, $matches)) {
            $version = (int) $matches[1];
            if ($schema_version < $version) {
                $files[$version] = $file;
            }
        }
    }
}

if ($files) {
    foreach ($files as $version => $file) {
        $sql = trim(file_get_contents(BASE_DIR . "db/migrate/{$file}"));
        echo($file).PHP_EOL;
        if ($result = $pgsq_entity->query($sql)) {
            $update_version = $version;
            $updated = true;
        } else {
            echo("migrate error!! version: {$version}").PHP_EOL;
        }
    }
    $schema_sql = "UPDATE schema_info SET version = {$update_version};";
    $pgsq_entity->query($schema_sql);
    echo($schema_sql).PHP_EOL;
}

if ($updated) {
    exit("updated schema version: {$update_version}\n");
} else {
    exit("no migrations for update\n");
}
?>