<?php
require_once dirname(__FILE__).'/../../lib/Controller.php';

ApplicationLoader::autoloadModel();

$project_name = $argv[1];
$is_not_export = $argv[2];
if (!$project_name) {
    $msg = 'Input project_name!';
    echo("{$msg}").PHP_EOL;
    exit;
}
$project = DB::table('Project')
                ->where("name = '{$project_name}'")
                ->one();

$file_path = BASE_DIR."tmp/{$project_name}.xlsx";

if (!$is_not_export) {
    $database = DB::table('Database')
                ->fetch($project->value['database_id'])
                ->exportDatabase($file_path);
}

$host = SCP_UPLOAD_HOST;
$user = SCP_UPLOAD_USER;
$upload_dir = SCP_UPLOAD_DIR;
$cmd = "scp {$file_path} {$user}@{$host}:{$upload_dir}";

echo($cmd).PHP_EOL;
exec($cmd);
