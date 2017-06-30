<?php
/**
 * DateHelper
 *
 * @author  Yohei Yoshikawa 
 * @create  2010/02/06 
 */

class DateHelper {
    /**
     * 日時フォーマット（時間付き）
     *
     * @param String $value
     * @param String $separate
     * @return String
     */
    static function datetimeFormat($value, $separate='s') {
        if ($value) {
            $format = self::formatter($separate, true);
            return date($format, strtotime($value));
        }
    }

    /**
     * 日付フォーマット
     *
     * @param String $value
     * @param String $separate
     * @return String
     */
    static function dateFormat($value, $separate='s') {
        if ($value) {
            $format = self::formatter($separate);
            return date($format, strtotime($value));
        }
    }

    /**
     * 日付フォーマット取得
     *
     * @param String $separate
     * @param Boolean $is_time
     * @return String
     */
    static function formatter($separate, $is_time=false) {
        $formatters = self::formatters($separate);
        $year = $formatters['y'];
        $month = $formatters['m'];
        $day = $formatters['d'];
        if ($is_time) {
            $format = "Y{$year}m{$month}d{$day} H:i";
        } else {
            $format = "Y{$year}m{$month}d{$day}";
        }
        return $format;
    }

    /**
     * 日付フォーマット群
     *
     * @param String $key
     * @return String
     */
    static function formatters($key) {
        $formatters['s'] = array ('y' => '/', 'm' => '/', 'd' => '');
        $formatters['h'] = array ('y' => '-', 'm' => '-', 'd' => '');
        $formatters['j'] = array ('y' => '年', 'm' => '月', 'd' => '日');
        return $formatters[$key];
    }

    /**
     * formSelect
     *
     * selectタグ
     *
     * @param Array $params
     * @param Object $selected
     * @return String
     */
    static function selectAttributeDate($params, $date_key) {
        $attributes = array('id', 'name', 'class', 'js');
        if (is_array($params)) {
            foreach ($params as $key => $param) {
                if (in_array($key, $attributes)) {
                    if ($key == 'js') {
                        $event = $param['event'];
                        $triger = $param['triger'];
                        $attribute.= " {$event}=\"{$triger}\"";
                    } elseif ($key == 'name') {
                        $attribute.= " {$key}=\"{$param}:{$date_key}\"";
                    } elseif ($key == 'id') {
                        $attribute.= " {$key}=\"{$param}_{$date_key}\"";
                    } else {
                        $attribute.= " {$key}=\"{$param}\"";
                    }
                }
            }
        }
        return $attribute;
    }

    /**
     * 日付配列から日付変更
     *
     * @param Array $values
     * @return String
     **/
    static function convertAtArrayToAt($values) {
        $date = '';
        if (is_array($values)) {
            $year = $values['year'];
            $month = $values['month'];
            $day = $values['day'];
            $hour = (int) $values['hour'];
            $minute = (int) $values['minute'];

            if (!$day) $day = 1;
            $time = mktime($hour, $minute, 0, $month, $day, $year);
            $date = date('Y-m-d H:i', $time);
        }
        return $date;
    }
}