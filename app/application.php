<?php

if (defined('DB_SETTING_FILE')) {
    if (!file_exists(DB_SETTING_FILE)) exit('Not found : DB_SETTING_FILE');
    require_once DB_SETTING_FILE;
}
require_once 'application_setting.php';
require_once 'application_function.php';

$libs = [
    'Helper',
    'DB',
    'SendMail',
    'CsvLite',
    'DataMigration',
    'FileManager',
    'FtpLite',
    'FormHelper',
    'TagHelper',
    'DateHelper',
    'AppSession',
    'ApplicationLocalize',
    'ApplicationLoader',
    'ApplicationLogger',
    'BenchmarkTimer',
    'ErrorMessage',
    ];

Controller::loadLib($libs);
ApplicationLocalize::load();
