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
     * @param
     * @return string
     */
    static function display($values, $params, $model) {
        return PwError::get($values, $params, $model);
    }

    /**
     * 
     *
     * @param
     * @return string
     */
    static function defaultMessage() {
        return $errors;
    }

    /**
     * メッセージ取得
     *
     * @param  array $values
     * @param  array $params
     * @param  array $model
     * @return string
     */
    static function get($values, $params, $model) {
        $messages = PwError::defaultMessage();
        $column = $values['column'];
        $message = $values['message'];

        if ($model) {
            $column = str_replace('_id', '', $column);

            //column
            $label = "LABEL_".strtoupper($model)."_".strtoupper($column);
            if (defined($label)) $column = constant($label);

            //message
            $label = "MESSAGE_".strtoupper($message);
            if (defined($label)) $message = constant($label);
        }

        $result = "{$column} {$message}";
        return $result;
    }

    /**
    * error message
    *
    * @param  array $errors
    * @return array 
    **/
    static function unify_error_messages($errors) {
        foreach($errors as $key => $error) {
            $column = $error['column'];
            $message = $error['message'];

            $unify_message = $unify_messages[$column]['message'];
            
            if($unify_message) {
                if($message == 'required') {
                    $unify_messages[$column] = $error;
                } else if($message == 'is not valid') {
                    if($unify_message != 'required') {
                        $unify_messages[$column] = $error;
                    }
                } else {
                    if($unify_message != 'required' && $unify_message != 'is not valid') {
                        $unify_messages[$column] = $error;
                    }
                }
            } else {
                $unify_messages[$column] = $error;
            } 
        }

        return $unify_messages;
    }
}
