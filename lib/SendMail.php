<?php
/**
 * SendMail
 * 
 * Copyright (c) 2013 Yohei Yoshikawa (http://yoo-s.com/)
 */

class SendMail {
    public $from;
    public $to;
    public $cc;
    public $bcc;
    public $body;
    public $setting_file_path;
    public $type;

    /**
     * コンストラクタ
     *
     * @param string $type
     * @return void
     **/
    function SendMail($type) {
        if (is_null($type)) exit('SendMail require type.');

        $this->type = $type;

        $setting_file = MAIL_SETTING_FILE;
        if (!defined('MAIL_SETTING_FILE')) {
            exit("Not set 'MAIL_SETTING_FILE' in setting file.");
        }
        $this->setting_file = $setting_file;
    }

    /**
     * メール本文生成
     *
     * @param array $values
     * @return string
     **/
    function mail_body($values) {
        mb_language('Japanese');
        mb_internal_encoding('UTF-8');
        
        $template_path = $this->mail_template_path();

        ob_start();
        include $template_path;
        $body = ob_get_contents();
        ob_end_clean();

        return $body;
    }

    /**
     * メール設定ファイルパス取得
     *
     * @return string
     **/
    function mail_template_path() {
        if ($this->type) {
            $path = BASE_DIR."app/views/mail/{$this->type}.phtml";
            if (!file_exists($path)) {
                exit("{$path} is not exists.");
            }
            $this->template_path = $path;
        }
        return $path;
    }

    /**
     * メール設定ファイル判別
     *
     * @return array
     **/
    function has_setting_file() {
        if (!file_exists($this->setting_file_path)) {
            exit("Not found {$this->setting_file_path}.");
        }
        return $this->setting_file;
    }

    /**
     * メール設定取得
     *
     * @param string $file
     * @return array
     **/
    function mail_setting() {
        $values = mail_settings($this->setting_file);
        if (is_array($values)) {
            foreach ($values as $key => $value) {
                if ($value['type'] == $this->type) {
                    return $value;
                }
            }
        }
        exit("Not found setting '{$this->type}'");
    }


    /**
     * メール設定一覧取得
     *
     * @param string $file
     * @return array
     **/
    function mail_settings($file) {
        if (file_exists($file)) {
            $values = read_csv($file);
        }
        return $values;
    }

    /**
     * メール送信
     *
     * @param array $values
     * @return boolean
     **/
    function send($values) {
        mb_language('Japanese');
        mb_internal_encoding('UTF-8');

        $csv_lite = new CsvLite($this->setting_file);
        $settings = $csv_lite->fetch($this->type);

        $subject = ($values['subject']) ? $values['subject'] : $settings['subject'];
        $to = ($values['to']) ? $values['to'] : $settings['to'];
        if ($settings['bcc']) $bcc = $settings['bcc'];
        if ($bcc) $bcc = mb_convert_encoding($bcc, "JIS", "UTF-8");

        if ($values['from']) {
            $from = $values['from'];
        } else {
            $from = $settings['from'];
            if ($settings['from_jp']) {
                $from = mb_encode_mimeheader($settings['from_jp'])."<{$from}>";
            }
        }

        //body
        if (is_null($values['body'])) {
            $body = $this->mail_body($values);
        } else {
            $body = $values['body'];
        }

        $option = null;
        $header = "From: {$from}\nBcc: {$bcc}";

        return mb_send_mail($to, $subject, $body, $header, $option);
    }

    function sendForISO($values) {
        mb_language('Japanese');
        mb_internal_encoding('UTF-8');

        $csv_lite = new CsvLite($this->setting_file);
        $settings = $csv_lite->fetch($this->type);

        $subject = ($values['subject']) ? $values['subject'] : $settings['subject'];
        $subject = "=?iso-2022-jp?B?".base64_encode(mb_convert_encoding($subject, "JIS", "UTF-8"))."?=";
        
        $to = ($values['to']) ? $values['to'] : $settings['to'];
        if ($settings['bcc']) $bcc = $settings['bcc'];

        if ($values['from']) {
            $from = $values['from'];
        } else {
            $from = $settings['from'];
            if ($settings['from_jp']) {
                $from = mb_encode_mimeheader($settings['from_jp'])."<{$from}>";
            }
        }

        //body
        if (is_null($values['body'])) {
            $body = $this->mail_body($values);
        } else {
            $body = $values['body'];
        }

        //header
        //UTF-8
        $character = 'ISO-2022-JP';
        //8bit
        //Base64
        //7bit
        $transfer_encoding = '7bit';
        $headers['MIME-Version'] = "1.0";
        $headers['Content-Type'] = "text/plain; charset={$character}";
        $headers['Content-Transfer-Encoding'] = $transfer_encoding;
        $headers['From'] = $from;
        if ($settings['cc']) $headers['Cc'] = $settings['cc'];
        if ($settings['bcc']) $headers['Bcc'] = $settings['bcc'];
        foreach ($headers as $key => $value) {
            $arrheader[] = $key . ': ' . $value;
        }
        $header = implode("\n", $arrheader);

        $option = null;
        return mail($to, $subject, $body, $header, $option);
    }

    /**
     * TOアドレス成形
     *
     * @param array $values
     * @return string
     **/
    function to_address($values) {
        if (is_array($values)) {
            $to = implode(',', $values); 
            return $to;
        } else {
            return $values;
        }
    }

}
?>