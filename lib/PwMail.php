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

    /**
     * construct
     *
     * @param string $type
     * @return void
     **/
    function __construct($params = null) {
        $this->init($params);
    }

    /**
     * init
     *
     * @param array $params
     * @return void
     */
    function init($params) {
        if ($params['subject']) $this->subject = $params['subject'];
        if ($params['to'] || defined('IS_PW_TEST_MAIL')) $this->to = $this->multipleAddress($params['to']);
        if ($params['cc']) $this->cc = $this->multipleAddress($params['cc']);
        if ($params['bcc']) $this->bcc = $this->multipleAddress($params['bcc']);
        if ($params['from']) $this->from = $this->convertAddress($params['from'], $params['from_name']);
        if ($params['template']) $this->template = BASE_DIR."app/pw_mail/{$this->type}.phtml";
    }

    /**
     * load mail template
     *
     * @return void
     */
    public function loadBody($params) {
        $this->loadPwMailTemplate($params);
        if (file_exists($this->template)) {
            ob_start();
            include $this->template;
            $this->body = ob_get_contents();
            ob_end_clean();
        }
        if ($params['signature']) $this->body.= "\n{$params['signature']}";
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
        if ($this->bcc) $this->bcc = mb_convert_encoding($this->bcc, $this->convert_encode, $$this->encode);
        $this->loadBody($params);

        $option = null;
        $header = "From: {$this->from}\n";
        if ($this->cc) $header.= "Cc: {$this->cc}\n";
        if ($this->bcc) $header.= "Bcc: {$this->bcc}";

        if ($this->is_debug) {
            echo($this->subject).PHP_EOL;
            echo($header).PHP_EOL;
            echo($this->to).PHP_EOL;
            echo($this->body).PHP_EOL;
        } else {
            return mb_send_mail($this->to, $this->subject, $this->body, $header, $option);
        }
    }

    /**
     * send For ISO
     *
     * @param array $params
     * @return void
     */
    function sendForISO($params) {
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
        if (defined('IS_PW_TEST_MAIL') && IS_PW_TEST_MAIL && defined('PW_TEST_MAIL_TO')) return PW_TEST_MAIL_TO;
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
    function convertAddress($email, $name = null){
        if ($name) $email = mb_encode_mimeheader($name)."<{$email}>";
        return $email;
    }

}