<?php
/**
 * Message Helper
 *
 * @author  Yohei Yoshikawa 
 * @create  2010/02/06 
 */

/**
 * display_error
 *
 * エラーメッセージ表示(Model連携)
 *
 * @param Array $values
 * @param Array $params
 * @param Array $model
 * @return String
 */
function display_error($values, $params, $model) {
    $messages = _error_jp_messages();
    return _get_message($values, $messages, $params, $model);
}

/**
 * display_message
 *
 * メッセージ表示
 *
 * @param Array $values
 * @return String
 */
function display_message($values) {
    $messages = _jp_messages();
    return _get_message($values, $messages);
}

/**
 * _get_message
 *
 * メッセージ取得(Model連携)
 *
 * @param Array $values
 * @param Array $messages
 * @param Array $params
 * @return String
 */
function _get_message($values, $messages, $params, $model) {
    $key = $values['column'];
    $message = $values['message'];

    if ($model) {
        $label = "LABEL_".strtoupper($model)."_".strtoupper($key);
        if (defined($label)) $key = constant($label);
        $label = "MESSAGE_".strtoupper($message);
        if (defined($label)) $message = constant($label);
        return "{$key} {$message}";
    }

    if ($params['controller'] && $message[$params['controller']]) {
        $messages = $messages[$params['controller']];
    }

    if (is_null($messages[$key][$message])) {
        $result = "{$key} : {$message}";
    } else {
        $result = $messages[$key][$message];
    }
    return $result;
}

?>