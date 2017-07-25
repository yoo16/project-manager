<?php
require_once '_application.php';

$libs = array(
    'DB',
    'SendMail',
    'CsvLite',
    'FileManager',
    'FtpLite',
    'FormHelper',
    'TagHelper',
    'DateHelper',
    'AppSession',
    'ApplicationLocalize',
    'ApplicationLoader',
    'ApplicationLogger',
    );

Controller::loadLib($libs);
ApplicationLocalize::load();
