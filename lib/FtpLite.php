<?php

/**
 *  FTP Connection
 * 
 * Copyright (c) 2013 Yohei Yoshikawa (http://yoo-s.com/)
 */

class FtpLite
{
    public $connect = null;
    public $is_login = false;
    public $is_connect_error = false;
    public $login_name = null;
    public $password = null;
    public $default_dir = null;
    public $ip = null;
    //public $get_latest_prg = FTP_GET_LATEST_SHELL;
    //public $local_dir = FILE_GET_DIR;
    public $local_dir = '';
    public $log = null;
    public $error = array('login' => 0);
    public $ftp_open_cmd = null;
    public $ftp_quit_cmd = null;
    public $ch_dir_cmd = null;
    public $delete_file_cmd = null;
    public $target_dir = null;
    public $delete_limit = 50;
    public $ftp_cmd = 'ftp -i -n -v';
    public $last_exec_logfile = null;
    public $scp_port = 22;

    function __construct()
    {
    }

    /**
     * set Host
     * @param String $host
     * @return FtpLite
     */
    function setHost($host)
    {
        $this->host = $host;
        return $this;
    }

    /**
     * set login_name
     * @param String $login_name
     * @return FtpLite
     */
    function setLoginName($login_name)
    {
        $this->login_name = $login_name;
        return $this;
    }

    /**
     * set password
     * @param String $password
     * @return FtpLite
     */
    function setPassword($password)
    {
        $this->password = $password;
        return $this;
    }

    /**
     * set is_scp
     * @param String $host
     * @return FtpLite
     */
    function setScp($is_scp)
    {
        $this->is_scp = $is_scp;
        return $this;
    }

    /**
     * test connect
     *
     * @return Bool
     */
    function connectTest()
    {
        if ($this->is_scp) {
            $is_success = $this->scpConnectTest();
        } else {
            $is_success = $this->ftpConnectTest();
        }
        return $is_success;
    }

    /**
     * test ftp connect
     *
     * @return Bool
     */
    function ftpConnectTest()
    {
        $this->connect = ftp_connect($this->host);
        $result = @ftp_login($this->connect, $this->login_name, $this->password);
        return $result;
    }

    /**
     * test scp connect
     *
     * @return Bool
     */
    function scpConnectTest()
    {
        $this->connect = ssh2_connect($this->host, $this->scp_port);
        $result = ssh2_auth_password($this->connect, $this->login_name, $this->password);
        return $result;
    }

    /**
     * upload file
     * 
     * @param  String $file_path
     * @param  String $remote_dir
     * @param  String $upload_mode
     * @return void
     */
    function uploadFile($file_path, $remote_dir, $upload_mode = FTP_BINARY)
    {
        if ($this->is_scp) {
            $this->scpUploadFile($file_path, $remote_dir);
        } else {
            $this->ftpUploadFile($file_path, $remote_dir, $upload_mode);
        }
    }

    /**
     * download file
     * 
     * @param  String $file_path   [description]
     * @param  String $remote_path [description]
     * @param  String $upload_mode [description]
     * @return void
     */
    function downloadFile($file_path, $remote_path, $upload_mode = FTP_BINARY)
    {
        if ($this->is_scp) {
            $this->scpDownloadFile($file_path, $remote_path);
        } else {
            $this->ftpDownloadFile($file_path, $remote_path, $upload_mode);
        }
    }

    /**
     * ftp connect
     * 
     * @return Bool
     */
    function ftpConnect()
    {
        $this->is_connect_error = false;
        $this->connect = ftp_connect($this->host);
        if ($this->connect) $this->is_connect_error = true;
        return $this;
    }

    /**
     * ftp login
     * 
     * @return Bool
     */
    function ftpLogin()
    {
        $this->ftpConnect();
        $this->is_login = ftp_login($this->connect, $this->login_name, $this->password);
        return $this;
    }

    /**
     * scp connect
     * 
     * @return Bool
     */
    function scpConnect()
    {
        $this->is_connect_error = false;
        $this->connect = ssh2_connect($this->host, $this->scp_port);
        if ($this->connect) $this->is_connect_error = true;
        return $this;
    }

    /**
     * scp login
     * 
     * @return Bool
     */
    function scpLogin()
    {
        $this->is_login = ssh2_auth_password($this->connect, $this->login_name, $this->password);
        return $this;
    }

    /**
     * ftpList
     *
     * @param String $path
     * @return mix
     */
    function ftpList($path)
    {
        $result = ftp_chdir($this->connect, $path);
        return ftp_mlsd($this->connect, '.');
    }

    /**
     * ftpLatestList
     *
     * @param String $path
     * @return mix
     */
    function ftpLatestList($path, $search_fix = '.CSV')
    {
        $modify = 0;
        $files = $this->ftpList($path);
        foreach ($files as $file) {
            if (strpos($file['name'], $search_fix) && $file['modify'] > $modify) {
                $modify = $file['modify'];
                $latest_file = $file;
            }
        }
        return $latest_file;
    }


    /**
     * ftp Download File
     * 
     * @param  String $file_path
     * @param  String $remote_path
     * @param  String $upload_mode
     * @return FtpLite
     */
    function ftpDownloadFile($file_path, $remote_path, $upload_mode = FTP_BINARY)
    {
        if (!$this->is_login) return;
        ftp_pasv($this->connect, true);
        $this->is_success_download = ftp_get($this->connect, $file_path, $remote_path, $upload_mode);
        ftp_close($this->connect);

        $this->changeMod($file_path);
        return $this;
    }

    /**
     * scp Download File
     * 
     * @param  String $file_path
     * @param  String $remote_path
     * @return FtpLite
     */
    function scpDownloadFile($file_path, $remote_path)
    {
        if (!$this->is_login) return;
        $this->is_success_download = ssh2_scp_recv($this->connect, $remote_path, $file_path);

        $this->changeMod($file_path);
        return $this;
    }

    /**
     * ftp upload file
     * 
     * @param  String $file_path
     * @param  String $remote_dir
     * @param  String $upload_mode
     * @return FtpLite
     */
    function ftpUploadFile($file_path, $remote_dir, $upload_mode = FTP_BINARY)
    {
        if (!$this->is_login) return;
        ftp_pasv($this->connect, true);
        if (file_exists($file_path)) {
            $this->is_success_upload = ftp_put($this->connect, $remote_dir, $file_path, $upload_mode);
        }
        ftp_close($this->connect);
        return $this;
    }

    /**
     * scp upload File
     * 
     * @param  String $file_path
     * @param  String $remote_path
     * @return FtpLite
     */
    function scpUploadFile($file_path, $remote_dir)
    {
        if (!$this->is_login) return;
        if (file_exists($file_path)) {
            $this->is_success_upload = ssh2_scp_send($this->connect, $file_path, $remote_dir);
        }
        return $this;
    }

    /**
     * change mode
     * 
     * @return void
     */
    function changeMod($file_path)
    {
        if (file_exists($file_path)) {
            $cmd = "chmod 666 {$file_path}";
            exec($cmd);
        }
    }

}