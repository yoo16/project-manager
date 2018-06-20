<?php
require_once dirname(__FILE__).'/../../lib/Controller.php';

ApplicationLoader::autoloadModel();

if (!defined('EXCEL_EXPORT_DIR')) exit('Not defined EXCEL_EXPORT_DIR');
if (!defined('EXCEL_SCP_UPLOAD_HOST')) exit('Not defined EXCEL_SCP_UPLOAD_HOST');
if (!defined('EXCEL_SCP_UPLOAD_USER')) exit('Not defined EXCEL_SCP_UPLOAD_USER');
if (!defined('EXCEL_SCP_UPLOAD_DIR')) exit('Not defined EXCEL_SCP_UPLOAD_DIR');

$project_name = $argv[1];
$is_not_export = $argv[2];
if (!$project_name) {
    $msg = 'Input project_name!';
    echo("{$msg}").PHP_EOL;
    exit;
}
$project = DB::model('Project')
                ->where("name = '{$project_name}'")
                ->one();

$file_path = EXCEL_EXPORT_DIR."{$project_name}.xlsx";

if (!$is_not_export) {
    $database = DB::model('Database')
                ->fetch($project->value['database_id'])
                ->exportDatabase($file_path);
}

$host = EXCEL_SCP_UPLOAD_HOST;
$user = EXCEL_SCP_UPLOAD_USER;
$upload_dir = EXCEL_SCP_UPLOAD_DIR;
$cmd = "scp {$file_path} {$user}@{$host}:{$upload_dir}";

echo($cmd).PHP_EOL;
exec($cmd);
