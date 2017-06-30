<?php
/**
 *  FTP Connection
 * 
 * Copyright (c) 2013 Yohei Yoshikawa (http://yoo-s.com/)
 */

class FtpLite {
    var $login_name = NULL;
    var $password = NULL;
    var $default_dir = NULL;
    var $ip = NULL;
    var $get_latest_prg = FTP_GET_LATEST_SHELL;
    var $local_dir = FILE_GET_DIR;
    var $log = NULL;
    var $error = array('login'=>0);
    var $ftp_open_cmd = null;
    var $ftp_quit_cmd = null;
    var $ch_dir_cmd = null;
    var $delete_file_cmd = null;
    var $target_dir = null;
    var $delete_limit = 50;
    var $ftp_cmd = 'ftp -i -n -v';
    var $last_exec_logfile = null;
    var $scp_port = 22;

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

?>