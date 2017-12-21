<?php
/**
 * DateHelper
 *
 * @author  Yohei Yoshikawa 
 * @create  2010/02/06 
 */

class DateHelper {

    /**
     * datetimeFormat
     *
     * @param string $value
     * @param string $separate
     * @return string
     */
    static function datetimeFormat($value, $separate = 's') {
        if ($value) {
            $format = self::formatter($separate, true);
            return date($format, strtotime($value));
        }
    }

    /**
     * dateFormat
     *
     * @param string $value
     * @param string $separate
     * @return string
     */
    static function dateFormat($value, $separate = 's') {
        if ($value) {
            $format = self::formatter($separate);
            return date($format, strtotime($value));
        }
    }

    /**
     * formatter
     *
     * @param string $separate
     * @param bool $is_time
     * @return string
     */
    static function formatter($separate, $is_time = false) {
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
     * formatters
     *
     * @param string $key
     * @return string
     */
    static function formatters($key) {
        if (!$key) return;
        $formatters['s'] = array ('year' => '/', 'month' => '/', 'day' => '');
        $formatters['h'] = array ('year' => '-', 'month' => '-', 'day' => '');
        $formatters['j'] = array ('year' => '年', 'month' => '月', 'day' => '日');
        return $formatters[$key];
    }


    /**
     * convert string (remove micro second)
     *
     * @param string $value
     * @return string
     **/
    static function convertString($value) {
        preg_match('/^(\d{4})-(\d{1,2})-(\d{1,2}) ?(\d{1,2})?:?(\d{1,2})?:?(\d{1,2})?/', $value, $m);
        if (checkdate($m[2], $m[3], $m[1])) {
            return sprintf('%4d-%02d-%02d %02d:%02d:%02d', $m[1], $m[2], $m[3], $m[4], $m[5], $m[6]);
        } else {
            $time = strtotime($value);
            if ($time >= 0) return date('Y-m-d H:i:s', $time);
            return null;
        }
    }

    /**
     * convert array to string
     *
     * @param array $values
     * @return string
     **/
    static function arrayToString($values) {
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