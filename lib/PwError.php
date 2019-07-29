<?php
/**
 * PwError
 *
 * Copyright (c) 2017 Yohei Yoshikawa (https://github.com/yoo16/)
 **/

class PwError {

    /**
     * display
     *
     * @param string $message
     * @param array $params
     * @param string $model_name
     * @return string
     */
    static function displayForString($message, $params, $key) {
        $label_name = "LABEL_".strtoupper($key);
        $message_name = "MESSAGE_".strtoupper($message);
        if (defined($label_name)) $column = constant($label_name);
        if (defined($message_name)) $message = constant($message_name);

        $result = "{$column}: {$message}";
        return $result;
    }

    /**
     * display
     *
     * @param mixed $values
     * @param array $params
     * @param string $model_name
     * @return string
     */
    static function display($values, $params, $model_name) {
        return PwError::get($values, $params, $model_name);
    }

    /**
     * 
     *
     * @param
     * @return string
     */
    static function defaultMessage() {
        $messages = [];
        return $messages;
    }

    /**
     * get
     *
     * @param  array $values
     * @param  array $params
     * @param  string $model_name
     * @return string
     */
    static function get($values, $params, $model_name) {
        $column = $values['column'];
        $message = $values['message'];

        if ($column == 'pw_error') {
            $message_name = "MESSAGE_".strtoupper($message);
            if (defined($message_name)) $result = constant($message_name);
        } else if ($model_name) {
            $column = str_replace('_id', '', $column);

            $label_name = "LABEL_".strtoupper($model_name)."_".strtoupper($column);
            $message_name = "MESSAGE_".strtoupper($message);

            if (defined($label_name)) $column = constant($label_name);
            if (defined($message_name)) $message = constant($message_name);
            $result = "{$column} {$message}";
        } else if ($params) {
            $messages = PwError::defaultMessage();
            $column = $params['column'];
            if ($messages[$column]) $column = $messages[$column];
            if ($messages[$message]) $message = $messages[$message];
            $result = "{$column} {$message}";
        }
        return $result;
    }

}
