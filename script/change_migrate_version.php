<?php
/**
 * Copyright (c) 2013 Yohei Yoshikawa (http://yoo-s.com/)
 */

require_once dirname(__FILE__).'/../lib/setting.php';
require_once 'PgsqlEntity.php';

$schema_version = $argv[1];

if (!is_numeric($schema_version)) {
    echo('input migrate version!').PHP_EOL;
    return;
}

$pgsq_entity = new PgsqlEntity();
$schema_sql = 'SELECT version FROM schema_info;';
$current_version = (int) $pgsq_entity->fetch_result($schema_sql);

if (is_numeric($schema_version)) {
    $sql = "BEGIN;{$sql};\nUPDATE schema_info SET version = {$schema_version};COMMIT;";
    if ($pgsq_entity->query($sql)) {
        $updated = true;
    }
}

if ($updated) {
    exit("change schema version: {$current_version} => {$schema_version}\n");
} else {
    exit("no migrations for update\n");
}
?>