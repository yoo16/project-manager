<?php
//TODO under construction
class PwServe {

    public $webroot_path = '';
    public $rewrite_path = '';
    public $host = 'localhost';
    public $port = '8888';

    public function start() {
        $this->webroot_path = BASE_DIR.'public';
        $this->rewrite_path = BASE_DIR.'server.php';
        //$this->rewrite_path = WEB_DIR.'dispatch.php';
        //$this->rewrite_path = LIB_DIR.'rewrite.php';
        // var_dump($this->webroot_path);
        // var_dump($this->rewrite_path);
        // exit;
        if ($this->webroot_path && file_exists($this->webroot_path)) {
            $cmd = "php -S {$this->host}:{$this->port} -t '{$this->webroot_path}' '{$this->rewrite_path}'";
            //$cmd = "php -S {$this->host}:{$this->port} -t '{$this->rewrite_path}'";
            passthru($cmd, $status);
            echo($status);
        }
    }
}