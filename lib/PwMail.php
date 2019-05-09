<?php
/**
 * PwMail
 * 
 * Copyright (c) 2017 Yohei Yoshikawa (https://github.com/yoo16/)
 */

class PwMail {
    public $template = '';
    public $from;
    public $to;
    public $cc;
    public $bcc;
    public $body;
    public $setting_file_path;
    public $type;
    public $localize = 'ja';
    public $encode = 'UTF-8';
    public $convert_encode = 'JIS';
    public $iso_character = 'ISO-2022-JP';
    public $is_debug = false;
    public $is_test = false;
    public $boundary = '';
    public $is_attachment = false;

    /**
     * construct
     *
     * @param string $type
     * @return void
     **/
    function __construct($params = null) {
        if ($params) $this->init($params);
    }

    /**
     * init
     *
     * @param array $params
     * @return PwMail
     */
    public function init($params) {
        if ($params['is_test']) $this->is_test = true;
        if ($params['is_debug']) $this->is_debug = true;

        $this->subject = $params['subject'];
        $this->to = $this->multipleAddress($params['to']);
        if ($params['cc']) $this->cc = $this->multipleAddress($params['cc']);
        if ($params['bcc']) $this->bcc = $this->multipleAddress($params['bcc']);
        if ($params['from']) {
            if ($this->is_test){
                $this->from = PW_TEST_MAIL_FROM;
            } else {
                $this->from = self::convertAddress($params['from'], $params['from_name']);
            }
        }
        if ($params['template']) $this->template = BASE_DIR."app/pw_mail/{$this->type}.phtml";
        return $this;
    }

    /**
     * load mail template
     *
     * @return void
     */
    public function loadSubject($params) {
        $this->subject = $params['subject'];
        return $this;
    }

    /**
     * load mail template
     *
     * @return void
     */
    public function loadBody($params) {
        if ($params['body']) {
            $this->body = $params['body'];
        } else {
            $this->loadPwMailTemplate($params);
            if (file_exists($this->template)) {
                ob_start();
                include $this->template;
                $this->body = ob_get_contents();
                ob_end_clean();
            }
        }
        if ($params['signature']) $this->body.= "\n{$params['signature']}";
        if ($params['attachment_files']) $this->setAttachementFiles($params['attachment_files']);
        return $this;
    }

    /**
     * mail template path
     *
     * @return string
     **/
    function loadPwMailTemplate($params) {
        if ($params['template']) $this->template = BASE_DIR."app/views/pw_mail/{$params['template']}.phtml";
        return $this->template;
    }

    /**
     * has setting file
     *
     * @return array
     **/
    function hasSettingFile() {
        if (!file_exists($this->setting_file_path)) {
            exit("Not found {$this->setting_file_path}.");
        }
        return $this->setting_file;
    }

    /**
     * boundary
     *
     * @return array
     **/
    function createBoundary() {
        $this->boundary = "__Boundary__" . uniqid(rand(), true) . "__";
    }

    /**
     * create header
     *
     * @return void
     **/
    function createHeader() {
        $value = "";
        $value.= "From: {$this->from}\nCc: {$this->cc}\nBcc: {$this->bcc}";
        $value.= "MIME-Version: 1.0\n";
        $value.= "Content-Type: Multipart/Mixed; boundary=\"{$this->boundary}\"\n";
        $value.= "Content-Transfer-Encoding: 7bit"; 
        $this->header = $value;
    }

    /**
     * set attachements
     *
     * @param array $files
     * @return array
     **/
    public function setAttachementFiles($files) {
        if (!$files) return;

        $this->is_attachment = true;

        $body = "--__BOUNDARY__\n";
        $body.= "Content-Type: text/plain; charset=\"ISO-2022-JP\"\n\n";
        $body.= "{$this->body}\n";
        $body.= "--__BOUNDARY__\n";

        foreach ($files as $file) {
            $file_name = mb_convert_encoding($file['name'], "ISO-2022-JP", 'auto');
            $file_name = mb_encode_mimeheader($file_name);

            //TODO ssl
            $options['ssl']['verify_peer'] = false;
            $options['ssl']['verify_peer_name'] = false;
            $contents = file_get_contents($file['url'], false, stream_context_create($options));
            //$contents = file_get_contents($file['url']);
            if ($contents) {
                $body.= "Content-Type: application/octet-stream; name=\"{$file_name}\"\n";
                $body.= "Content-Disposition: attachment; filename=\"{$file_name}\"\n";
                $body.= "Content-Transfer-Encoding: base64\n";
                $body.= "\n";
                $body.= chunk_split(base64_encode($contents));
                $body.= "--__BOUNDARY__\n";
            }
        }
        $this->body = $body;

    }

    /**
     * load header
     *
     * @param array $params
     * @return void
     */
    public function loadHeader($params) {
        $header = '';
        if ($this->cc) $this->cc = mb_convert_encoding($this->cc, $this->convert_encode, $this->encode);
        if ($this->bcc) $this->bcc = mb_convert_encoding($this->bcc, $this->convert_encode, $this->encode);
        if ($this->is_attachment) {
            $header.= "Content-Type: multipart/mixed;boundary=\"__BOUNDARY__\"\n";
            if ($params['return_path']) $header.= "Return-Path: {$params['return_path']} \n";
            $header.= "From: " . $this->from ." \n";
            if ($this->cc) $header.= "Cc: {$this->cc}\n";
            if ($this->bcc) $header.= "Bcc: {$this->bcc}\n";
            $header.= "Sender: " . $this->from ." \n";
            $header.= "Reply-To: " . $this->from . " \n";
            if ($org = $params['org']) {
                $header.= "Organization: {$org} \n";
                $header.= "X-Sender: {$org} \n";
            }
            $header.= "X-Priority: 3 \n";
        } else {
            $header = "From: {$this->from}\n";
            if ($this->cc) $header.= "Cc: {$this->cc}\n";
            if ($this->bcc) $header.= "Bcc: {$this->bcc}";
        }
        $this->header = $header;
    }

    /**
     * send
     * 
     * TODO localize
     *
     * @param array $params
     * @return boolean
     **/
    function send($params) {
        if ($params) $this->init($params);
        if ($this->localize = 'ja') mb_language('Japanese');;
        mb_internal_encoding($this->encode);

        if (!$this->subject) return;
        if (!$this->to) return;
        if (!$this->from) return;

        $this->loadBody($params);
        $this->loadHeader($params);

        if ($this->is_debug) {
            echo($this->subject).PHP_EOL;
            echo($this->header).PHP_EOL;
            echo($this->to).PHP_EOL;
            echo($this->body).PHP_EOL;
            exit;
        } else {
            $is_send = mb_send_mail($this->to, $this->subject, $this->body, $this->header);
            return $is_send;
        }
    }

    /**
     * send For ISO
     *
     * @param array $params
     * @return void
     */
    public function sendForISO($params) {
        if ($this->localize = 'ja') mb_language('Japanese');
        mb_internal_encoding($this->encode);

        if (!$this->subject) return;
        if (!$this->to) return;
        if (!$this->from) return;
        $this->subject = "=?{$this->iso_character}?B?".base64_encode(mb_convert_encoding($this->subject, $this->convert_encode, $$this->encode))."?=";

        //8bit
        //Base64
        //7bit
        $transfer_encoding = '7bit';
        $headers['MIME-Version'] = "1.0";
        $headers['Content-Type'] = "text/plain; charset={$this->iso_character}";
        $headers['Content-Transfer-Encoding'] = $transfer_encoding;
        $headers['From'] = $this->from;
        if ($this->cc) $headers['Cc'] = $this->cc;
        if ($this->bcc) $headers['Bcc'] = $this->bcc;
        foreach ($headers as $key => $value) {
            $arrheader[] = $key . ': ' . $value;
        }
        $header = implode("\n", $arrheader);

        $option = null;
        return mail($this->to, $this->subject, $this->body, $header, $option);
    }

    /**
     * multiple address
     *
     * @param array $values
     * @return string
     **/
    function multipleAddress($values) {
        if ($this->is_test) return PW_TEST_MAIL_TO;
        if (is_array($values)) return implode(',', $values); 
        return $values;
    }

    /**
     * convert mail address
     *
     * @param string $email
     * @param string $name
     * @return string
     **/
    static function convertAddress($email, $name = null){
        if ($name) $email = mb_encode_mimeheader($name)."<{$email}>";
        return $email;
    }

}