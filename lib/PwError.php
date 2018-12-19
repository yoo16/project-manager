<?php
/**
 * エラーメッセージ
 *
 * @param
 * @return String
 */

class PwError {
    /**
     * エラー
     *
     * @param
     * @return String
     */
    static function display($values, $params, $model) {
        return PwError::get($values, $params, $model);
    }

    /**
     * 
     *
     * @param
     * @return String
     */
    static function defaultMessage() {
        return $errors;
    }

    /**
     * メッセージ取得
     *
     * @param Array $values
     * @param Array $params
     * @param Array $model
     * @return String
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
    * error優先順位
    *
    * @param Array $errors
    * @return Array
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
