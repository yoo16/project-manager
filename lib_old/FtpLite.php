<?php

/**
 *  FTP Connection
 * 
 * Copyright (c) 2013 Yohei Yoshikawa (https://github.com/yoo16/)
 */

class FtpLite
{
    public $connect = null;
    public $is_login = false;
    public $is_connect_error = false;
    public $is_success_download = false;
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
     * @param string $host
     * @return FtpLite
     */
    function setHost($host)
    {
        $this->host = $host;
        return $this;
    }

    /**
     * set login_name
     * @param string $login_name
     * @return FtpLite
     */
    function setLoginName($login_name)
    {
        $this->login_name = $login_name;
        return $this;
    }

    /**
     * set password
     * @param string $password
     * @return FtpLite
     */
    function setPassword($password)
    {
        $this->password = $password;
        return $this;
    }

    /**
     * set is_scp
     * @param boolean $is_scp
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
     * @return boolean
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
     * @return boolean
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
     * @return boolean
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
     * @param  string $file_path
     * @param  string $remote_dir
     * @param  string $upload_mode
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
     * @param  string $file_path   [description]
     * @param  string $remote_path [description]
     * @param  string $upload_mode [description]
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
        if (!$this->connect) $this->is_connect_error = true;
        return $this;
    }

    /**
     * ftp close
     * 
     * @return Bool
     */
    function ftpClose()
    {
        if ($this->connect) ftp_close($this->connect);
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
        if (!$this->connect) $this->is_connect_error = true;
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
     * @return mixed
     */
    function ftpList($path)
    {
        $result = ftp_chdir($this->connect, $path);
        return ftp_mlsd($this->connect, '.');
    }


    /**
     * ftpList
     *
     * @param String $path
     * @return mixed
     */
    function ftpFileList($path)
    {
        return ftp_nlist($this->connect, $path);
    }

    /**
     * ftpLatestList
     *
     * @param String $path
     * @return mixed
     */
    function ftpLatestFileList($path, $search_fix = '.CSV')
    {
        $files = $this->ftpFileList($path);
        foreach ($files as $file) {
            $file_name = str_replace($path, '', $file);
            if (strpos($file_name, $search_fix)) {
                $time = ftp_mdtm($this->connect, $file);
                if ($time > $latest_time) {
                    $latest_time = $time;
                    $latest_file = $file_name;
                }
            }
        }
        return $latest_file;
    }

    /**
     * ftpLatestList
     *
     * @param String $path
     * @return mixed
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
     * @param  string $file_path
     * @param  string $remote_path
     * @param  string $upload_mode
     * @return FtpLite
     */
    function ftpDownloadFile($file_path, $remote_path, $upload_mode = FTP_BINARY)
    {
        $this->is_success_download = false;
        if (!$this->is_login) return $this;
        ftp_pasv($this->connect, true);
        $this->is_success_download = ftp_get($this->connect, $file_path, $remote_path, $upload_mode);

        $this->changeMod($file_path);
        return $this;
    }

    /**
     * scp Download File
     * 
     * @param  string $file_path
     * @param  string $remote_path
     * @return FtpLite
     */
    function scpDownloadFile($file_path, $remote_path)
    {
        $this->is_success_download = false;
        if (!$this->is_login) return $this;
        $this->is_success_download = ssh2_scp_recv($this->connect, $remote_path, $file_path);

        $this->changeMod($file_path);
        return $this;
    }

    /**
     * ftp upload file
     * 
     * @param  string $file_path
     * @param  string $remote_dir
     * @param  string $upload_mode
     * @return FtpLite
     */
    function ftpUploadFile($file_path, $remote_dir, $upload_mode = FTP_BINARY)
    {
        if (!$this->is_login) return $this;
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
     * @param  string $file_path
     * @param  string $remote_path
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
     * @param  string $file_path
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