<?php
/**
 *  FTP Connection
 * 
 * Copyright (c) 2013 Yohei Yoshikawa (http://yoo-s.com/)
 */

class FtpLite {
    public $login_name = NULL;
    public $password = NULL;
    public $default_dir = NULL;
    public $ip = NULL;
    public $get_latest_prg = FTP_GET_LATEST_SHELL;
    public $local_dir = FILE_GET_DIR;
    public $log = NULL;
    public $error = array('login'=>0);
    public $ftp_open_cmd = null;
    public $ftp_quit_cmd = null;
    public $ch_dir_cmd = null;
    public $delete_file_cmd = null;
    public $target_dir = null;
    public $delete_limit = 50;
    public $ftp_cmd = 'ftp -i -n -v';
    public $last_exec_logfile = null;
    public $scp_port = 22;

    function __construct() {
    }

    function connectTest() {
        if ($this->is_scp) {
            $is_success = $this->scpConnectTest();
        } else {
            $is_success = $this->ftpConnectTest();
        }
        return $is_success;
    }

    function ftpConnectTest() {
        $connect = ftp_connect($this->host);
        $result = @ftp_login($connect , $this->login_name, $this->password);
        return $result;
    }

    function scpConnectTest() {
        $connect = ssh2_connect($this->host, $this->scp_port);
        $result = ssh2_auth_password($connect, $this->login_name, $this->password);
        return $result;
    }

    function uploadFile($file_path, $remote_dir, $upload_mode=FTP_BINARY) {
        if ($this->is_scp) {
            $this->scpUploadFile($file_path, $remote_dir);
        } else {
            $this->ftpUploadFile($file_path, $remote_dir, $upload_mode);
        }
    }

    function downloadFile($file_path, $remote_path, $upload_mode=FTP_BINARY) {
        if ($this->is_scp) {
            $this->scpDownloadFile($file_path, $remote_path);
        } else {
            $this->ftpDownloadFile($file_path, $remote_path, $upload_mode);
        }
    }

    function ftpUploadFile($file_path, $remote_dir, $upload_mode=FTP_BINARY) {
        $errors = null;
        $connect = ftp_connect($this->host);
        if (!$connect) return 'connect';

        $is_auth = @ftp_login($connect , $this->login_name, $this->password);
        if (!$is_auth) return 'login';

        ftp_pasv($connect, true);
        if (file_exists($file_path)) {
            $is_success = ftp_put($connect, $remote_dir, $file_path, $upload_mode);
        }
        ftp_close($connect);
        return $is_success;
    }

    function ftpDownloadFile($file_path, $remote_path, $upload_mode=FTP_BINARY) {
        $errors = null;
        $connect = ftp_connect($this->host);
        if (!$connect) return 'connect';

        $is_auth = @ftp_login($connect , $this->login_name, $this->password);
        if (!$is_auth) return 'login';

        ftp_pasv($connect, true);
        $is_success = ftp_get($connect, $file_path, $remote_path, $upload_mode);

        ftp_close($connect);
        if (!$is_success) return 'download';

        return false;
    }


    function scpUploadFile($file_path, $remote_dir) {
        $connect = ssh2_connect($this->host, $this->scp_port);
        if (!$connect) return 'connect';

        $is_auth = ssh2_auth_password($connect, $this->login_name, $this->password);
        if (!$is_auth) return 'login';

        if (file_exists($file_path)) {
            $is_success = ssh2_scp_send($connect, $file_path, $remote_dir);
        }
        if (!$is_success) return 'download';

        return false;
    }

    function scpDownloadFile($file_path, $remote_path) {
        $connect = ssh2_connect($this->host, $this->scp_port);
        if (!$connect) return 'connect';

        $is_auth = ssh2_auth_password($connect, $this->login_name, $this->password);
        if (!$is_auth) return 'login';

        $is_success = ssh2_scp_recv($connect, $remote_path, $file_path);
        if (!$is_success) return 'download';

        return false;
    }

}