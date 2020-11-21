<?php
//TODO under construction
require_once 'PwSetting.php';
if (php_sapi_name() === 'cli') return;

$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

$fcpath = $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR;

$path = $fcpath . ltrim($uri, '/');

if ($uri !== '/' && (is_file($path) || is_dir($path))) return;

$dispatch_path = $fcpath . PwSetting::$dispatch;
echo($dispatch_path);
//require_once $dispatch_path;