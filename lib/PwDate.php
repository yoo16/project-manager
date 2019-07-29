<?php
/**
 * PwDate 
 *
 * TODO: use DateTime in all
 * 
 * Copyright (c) 2017 Yohei Yoshikawa (https://github.com/yoo16/)
 */

class PwDate {

    public $datetime;
    public $number;
    public $string;
    public $time;
    public $datetimes;
    public $date_numbers;

    public $from_string;
    public $from_number;
    public $from_time;

    public $to_string;
    public $to_number;
    public $to_time;

    public $start_string;
    public $end_string;
    public $start_time;
    public $end_time;
    public $start_number;
    public $end_number;

    //TODO remove
    public $start_at;
    public $end_at;
    public $start_datetime;
    public $end_datetime;
    public $start_date;
    public $end_date;

    //TODO remove
    public $from_at;
    public $from_date;
    public $from_datetime;

    //TODO remove
    public $to_at;
    public $to_date;
    public $to_datetime;

    function __construct() {
    }

    /**
     * set from_at
     * 
     * @param integer $number
     * @param string $unit
     */
    function ago($number, $unit)
    {
        $number = -$number;
        $formatter = "{$number}{$unit}";
        $time = strtotime($formatter, strtotime($this->string));
        $this->setTime($time);
        return $this;
    }

    /**
     * set from_at
     * 
     * TODO validate for format
     *
     * @param integer $number
     */
    function setNumber($number, $format = 'Y/m/d H:i') {
        $this->number = $number;
        $this->string = PwDate::numberToString($this->number);
        $this->time = strtotime($this->string);
        $this->datetime = new Datetime($this->string);
        return $this;
    }

    /**
     * set string
     *
     * @param string $string
     */
    function setString($string) {
        $this->string = $string;
        $this->time = strtotime($this->string);
        $this->number = PwDate::stringToNumber($this->string);
        $this->datetime = new Datetime($this->string);
        return $this;
    }

    /**
     * set time
     *
     * @param string $time
     */
    function setTime($time, $format = 'Y/m/d H:i') {
        $this->time = $time;
        $this->string = date($format, $this->time);
        $this->number = PwDate::stringToNumber($this->string);
        $this->datetime = new Datetime($this->string);
        return $this;
    }

    /**
     * set string for from
     *
     * TODO remove from_at, from_datetime, from_date
     * 
     * @param string $string 
     */
    function setFromString($string) {
        if (!$string) return;
        $this->from_string = $string;
        $this->from_time = strtotime($string);
        $this->from_number = PwDate::stringToNumber($string);

        //TODO remove
        $this->from_at = $this->from_string;
        $this->from_datetime = $this->from_time;
        $this->from_date = $this->from_number;
        return $this;
    }

    /**
     * set string for to
     *
     * TODO remove to_at, to_datetime, to_date
     * 
     * @param string $string
     */
    function setToString($string) {
        if (!$string) return;
        $this->to_string = $string;
        $this->to_time = strtotime($string);
        $this->to_number = PwDate::stringToNumber($string);

        //TODO remove
        $this->to_at = $this->to_string;
        $this->to_datetime = $this->to_time;
        $this->to_date = $this->to_number;
        return $this;
    }

    /**
     * set from number
     *
     * TODO remove from_at, from_datetime, from_date
     * 
     * @param string $number 
     */
    function setFromNumber($number) {
        if (!is_numeric($number)) return;
        $this->from_number = $number;
        $this->from_string = PwDate::numberToString($number);
        $this->from_time = strtotime($this->from_string);

        //TODO remove
        $this->from_at = $this->from_string;
        $this->from_datetime = $this->from_time;
        $this->from_date = $this->from_number;
        return $this;
    }

    /**
     * set to number
     *
     * TODO remove to_at, to_datetime, to_date
     * 
     * @param string $to_string
     */
    function setToNumber($number) {
        if (!is_numeric($number)) return;
        $this->to_number = $number;
        $this->to_string = PwDate::numberToString($number);
        $this->to_time = strtotime($this->to_string);

        //TODO remove
        $this->to_at = $this->to_string;
        $this->to_datetime = $this->to_time;
        $this->to_date = $this->to_number;
        return $this;
    }

    /**
     * set from number
     *
     * TODO remove from_at, from_datetime, from_date
     * 
     * @param integer $number 
     */
    function setFromTime($time, $formatter = 'Y/m/d H:i') {
        $this->from_time = $time;
        $this->from_string = date($formatter, $time);
        $this->from_number = PwDate::stringToNumber($this->from_string);

        //TODO remove
        $this->from_at = $this->from_string;
        $this->from_datetime = $this->from_time;
        $this->from_date = $this->from_number;
        return $this;
    }

    /**
     * set to number
     *
     * TODO remove to_at, to_datetime, to_date
     * 
     * @param integer $time
     */
    function setToTime($time, $formatter = 'Y/m/d H:i') {
        $this->to_time = $time;
        $this->to_string = date($formatter, $time);
        $this->to_number = PwDate::stringToNumber($this->to_string);

        //TODO remove
        $this->to_at = $this->to_string;
        $this->to_datetime = $this->to_time;
        $this->to_date = $this->to_number;
        return $this;
    }

    /**
     * set start_at
     *
     * @param string $string
     */
    function setStartString($string) {
        if (!$string) return;
        $this->start_string = $string;
        $this->start_time = strtotime($string);
        $this->start_number = PwDate::stringToNumber($string);

        //TODO remove
        $this->start_at = $this->start_string;
        $this->start_datetime = $this->start_time;
        $this->start_date = $this->start_number;
        return $this;
    }

    /**
     * set end_at
     *
     * @param string $string
     */
    function setEndString($string) {
        if (!$string) return;
        $this->end_string = $string;
        $this->end_time = strtotime($string);
        $this->end_number = PwDate::stringToNumber($string);

        //TODO remove
        $this->end_at = $this->end_string;
        $this->end_datetime = $this->end_time;
        $this->end_date = $this->end_number;
        return $this;
    }

    //old
    /**
     * set from_at
     * 
     * TODO remove (old function)
     *
     * @param string $string
     */
    function setFromAt($string) {
        $this->setFromString($string);
        return $this;
    }

    /**
     * set to_at
     *
     * TODO remove (old function)
     *
     * @param string $string
     */
    function setToAt($string) {
        $this->setToString($string);
        return $this;
    }

    /**
     * set from date number
     *
     * TODO remove (old function)
     *
     * @param string $number
     */
    function setFromDate($number) {
        $this->setFromNumber($number);
        return $this;
    }

    /**
     * set to date number
     *
     * TODO remove (old function)
     *
     * @param string $number
     */
    function setToDate($number) {
        $this->setToNumber($number);
        return $this;
    }

    /**
     * to date convert first day in monthly
     *
     * @param string $number
     */
    function fromDateFirstDay() {
        if (!$this->from_time) $this->from_time = time();
        $string = date('Y/m/01 00:00', $this->from_time);
        $this->setFromString($string);
        return $this;
    }

    /**
     * to date convert first day in monthly
     *
     * @param string $number
     */
    function toDateFirstDay() {
        if (!$this->to_time) $this->to_time = time();
        $string = date('Y/m/01 00:00', $this->to_time);
        $this->setToString($string);
        return $this;
    }

    /**
     * set from_datetime
     *
     * TODO remove (old function)
     *
     * @param integer $time 
     */
    function setFromDatetime($time) {
        $this->setFromTime($time);
        return $this;
    }

    /**
     * set to_datetime
     *
     * TODO remove (old function)
     *
     * @param integer $time
     */
    function setToDatetime($time) {
        $this->setFromTime($time);
        return $this;
    }

    /**
     * set start_at
     *
     * @param string $start_at
     */
    function setStartAt($start_at) {
        if (!$start_at) return;
        $this->start_at = $start_at;
        $this->start_datetime = strtotime($this->start_at);
        $this->start_date = PwDate::stringToNumber($this->start_at);
        return $this;
    }

    /**
     * set end_at
     *
     * @param string $end_at
     */
    function setEndAt($end_at) {
        if (!$end_at) return;
        $this->end_at = $end_at;
        $this->end_datetime = strtotime($this->end_at);
        $this->end_date = PwDate::stringToNumber($this->end_at);
        return $this;
    }

    /**
     * diff
     *
     * @param integer $value
     * @param string $unit
     * @return PwDate
     */
    function interval($value, $unit) {
        $formatter = "{$value}{$unit}";
        $time = strtotime($formatter, strtotime($this->string));

        $diff = new PwDate();
        $diff->setTime($time);
        return $diff;
    }

    /**
     * init
     *
     * @return void
     */
    function init() {
        $this->setString(date('Y/m/d 00:00'));
        $this->setFromString(date('Y/m/d 00:00'));
        $this->setOneDay();
        $this->setStartString(date('Y/m/d 00:00'));
        $this->setEndString($this->to_string);
        return $this;
    }

    /**
     * pereoid dates 
     *
     * @return PwDate
     */
    function periodDates($params = null) {
        if (!$this->from_time) return $this;
        if (!$this->to_time) return $this;
        $time = $this->from_time;
        $i = 0;
        if (!$params['unit']) $interval_unit = 'month';
        if (!$params['interval']) $interval = 1;
        while ($time < $this->to_time) {
            $update_date = new PwDate();
            $update_date->setTime($time);
            $update_date->setFromTime($time);
            $update_date->setToTime($time);
            $update_date->nextToDate($interval, $interval_unit);
            $update_date->toDateFirstDay();
            $update_date->limitToDate($this->to_time, $params);

            $this->dates[] = $update_date;

            $time = $update_date->to_time;
            $i++;
            if ($i > 1000) break;
        }
    }

    /**
     * set session
     *
     * @return void
     */
    function clearPwSession($key = 'app_date') {
        if (!$key) $key = 'app_date';
        PwSession::clearWithKey($key, 'date');
    }

    /**
     * set session
     *
     * @return void
     */
    function storePwSession($key = 'app_date', $pw_multi_sid = 0) {
        if (!$key) $key = 'app_date';
        PwSession::setWithKey($key, 'date', $this, $pw_multi_sid);
    }

    /**
     * load session
     *
     * @return PwDate
     */
    function loadPwSession($key = 'app_date', $pw_multi_sid = 0) {
        if (!$key) $key = 'app_date';
        $pw_date = PwSession::getWithKey($key, 'date', $pw_multi_sid);
        if ($pw_date) {
            $this->setFromString($pw_date->from_string);
            $this->setToString($pw_date->to_string);
            $this->setStartString($pw_date->start_string);
            $this->setEndString($pw_date->end_string);
        }
        return $pw_date;
    }

    /**
     * loadRequest
     * 
     * @return void
     */
    function loadRequest() {
        $this->requestFrom();
        $this->requestTo();
    }

    /**
     * request FromDate
     * 
     * @return string
     */
    function requestFrom() {
        if ($_REQUEST['from_date']) {
            $this->setFromNumber($_REQUEST['from_date']);
        } else if ($_REQUEST['from_at']) {
            $this->setFromString($_REQUEST['from_at']);
        }
        return $this->from_string;
    }

    /**
     * request ToDate
     * 
     * @return string
     */
    function requestTo() {
        if ($_REQUEST['to_date']) {
            $this->setToNumber($_REQUEST['to_date']);
        } else if ($_REQUEST['to_at']) {
            $this->setToString($_REQUEST['to_at']);
        }
        return $this->to_string;
    }

    /**
     * set today interval
     *
     */
    function setTodayInterval($interval_string) {
        $to_string = date('Y/m/d H:00');
        $this->setIntervalByToString($to_string, $interval_string);
    }

    /**
     * zero hour 
     *
     * @param string $date_string
     * @return void
     */
    function zeroHour($date_string) {
        if (!$date_string) return;
        return date('Y/m/d 00:00', strtotime($date_string));
    }

    /**
     * from_at is zero hour 
     *
     * @return void
     */
    function fromStringZeroHour() {
        if (!$this->from_string) return;
        $string = date('Y/m/d 00:00', strtotime($this->from_string));
        $this->setFromString($string);
    }

    /**
     * to_at is zero hour 
     *
     * @return void
     */
    function toStringZeroHour() {
        if (!$this->from_string) return;
        $string = date('Y/m/d 00:00', strtotime($this->from_string));
        $this->setToString($string);
    }

    /**
     * clear from date
     * 
     * TODO remove from_at, from_datetime, from_date
     *
     * @return void
     */
    function clearFromDate() {
        $this->from_string = null;
        $this->from_time = null;
        $this->from_number = null;

        //TODO remove
        $this->from_at = null;
        $this->from_datetime = null;
        $this->from_date = null;
    }

    /**
     * clear to date
     *
     * TODO remove to_at, to_datetime, to_date
     * 
     * @return void
     */
    function clearToDate() {
        $this->to_string = null;
        $this->to_time = null;
        $this->to_number = null;

        //TODO remove
        $this->to_at = null;
        $this->to_datetime = null;
        $this->to_date = null;
    }

    /**
     * initDayInterval
     *
     * @param string $start_at
     * @param string $end_at
     * @param integer $days
     */
    function initDayInterval($start_at, $end_at, $days = 1) {
        $this->setStartString($start_at);
        $this->setEndString($end_at);

        if (!$this->from_string) $this->setFromString($end_at);
        if (!$this->from_string) $this->setFromString(date('Y/m/01'));

        $this->limitDate();

        $this->setOneDay($days);
        $this->setNextPrevDatesForFromDate($days, 'days');
        return $this;
    }

    /**
     * set interval by time and from_string
     *
     * @param array $params
     */
    function limitNow($params = null) {
        if ($this->time) {
            $now = time();
            if ($this->time > $now) $this->time = $now;
            $this->setTime($this->time, $params['formatter']);
        }
        if (!$this->to_time) return $this;
        $now = time();
        if ($this->to_time > $now) {
            $this->to_time = $now;
        } else {
            return $this;
        }
        if ($params['formatter']) $formatter = $params['formatter'];
        if ($formatter) {
            $this->setToTime($this->to_time, $formatter);
        } else {
            $this->setToTime($this->to_time);
        }
        return $this;
    }

    /**
     * limit to date
     *
     * @param array $params
     */
    function limitFromDate($params = null) {
        if ($this->from_time) {
            $now = time();
            if ($this->from_time > $now) $this->setFromTime($now, $params['formatter']);
        }
        return $this;
    }

    /**
     * limit to date
     *
     * @param array $params
     */
    function limitToDate($time, $params = null) {
        if ($this->to_time) {
            if ($this->to_time > $time) $this->setToTime($time, $params['formatter']);
        }
        return $this;
    }

    /**
     * limitDate
     *
     * @param string $formatter
     * @return PwDate
     */
    function limitDate($formatter = 'Y/m/d H:i') {
        if ($this->start_time && (!$this->from_time || $this->from_time < $this->start_time)) {
            $this->setFromString($this->start_at);
        }
        if ($this->end_time && (!$this->to_time || $this->to_time > $this->end_time)) {
            $this->setToString($this->end_at);
        }
        $now = time();
        if ($this->to_time > $now) {
            $this->setToTime($now, $formatter);
        }
        return $this;
    }

    /**
     * set One day
     *
     */
    function setOneDay() {
        if ($this->from_time) {
            $string = date('Y/m/d 00:00', $this->from_time);
            $this->setIntervalByFromString($string, '+1day');
        }
        return $this;
    }

    /**
     * set interval days
     *
     * @param integer $days
     */
    function setIntervalDays($days) {
        if ($this->from_time && $days) {
            $string = date('Y/m/d 00:00', $this->from_time);
            $format = "{$days}day";
            $this->setIntervalByFromString($string, $format);
        }
        return $this;
    }

    /**
     * set interval by from string
     *
     * @param string $string
     * @param string $interval_string
     * @param boolean $is_limit_now
     */
    function setIntervalByFromString($string, $interval_string, $is_limit_now = true) {
        $this->setFromString($string);
        $to_time = strtotime($interval_string, $this->from_time);
        $this->setToTime($to_time);
        if ($is_limit_now) $this->limitNow();
        return $this;
    }

    /**
     * set interval by from time
     *
     * @param string $string
     * @param string $interval_string
     * @param boolean $is_limit_now
     */
    function setIntervalByFromTime($time, $interval_string, $is_limit_now = true) {
        $this->setFromTime($time);
        $to_time = strtotime($interval_string, $this->from_time);
        $this->setToTime($to_time);
        if ($is_limit_now) $this->limitNow();
        return $this;
    }

    /**
     * set interval by to string
     *
     * @param string $string
     * @param string $interval_string
     */
    function setIntervalByToString($string, $interval_string, $is_limit_now = true) {
        $this->setToString($string);
        $time = strtotime($interval_string, $this->to_time);
        $this->setFromTime($time);
        if ($is_limit_now) $this->limitNow();
        return $this;
    }

    /**
     * set interval by to time
     *
     * @param string $string
     * @param string $interval_string
     */
    function setIntervalByToTime($time, $interval_string, $is_limit_now = true) {
        $this->setToTime($time);
        $time = strtotime($interval_string, $this->to_time);
        $this->setFromTime($time);
        if ($is_limit_now) $this->limitNow();
        return $this;
    }

    /**
     * set interval days from end_at
     *
     * @param integer $days
     */
    function setIntervalDaysFromToString($days, $is_zero_hours = false) {
        if ($this->to_time && $days) {
            $formatter = "-{$days}days";
            $time = strtotime($formatter, $this->to_time);
            $this->setFromTime($time);
            if ($is_zero_hours) $this->setFromString(date('Y/m/d 00:00', $this->from_time));
        }
        return $this;
    }

    /**
     * set interval values
     *
     * @param integer $interval
     * @param string $unit
     */
    function setNextPrevDatesForFromDate($interval, $unit) {
        if ($this->from_time) {
            $interval_string = "-{$interval}{$unit}";
            $this->prev_datetime = strtotime($interval_string, $this->from_time);
            $this->prev_at = date('Y/m/d H:i', $this->prev_datetime);
            $this->prev_date = PwDate::stringToNumber($this->prev_at);

            $interval_string = "+{$interval}{$unit}";
            $this->next_datetime = strtotime($interval_string, $this->from_time);
            $this->next_at = date('Y/m/d H:i', $this->next_datetime);
            $this->next_date = PwDate::stringToNumber($this->next_at);
        }
        return $this;
    }

    /**
     * interval for datetimes
     *
     * @param integer $interval
     * @param string $unit
     * @param integer $limit_time
     */
    function calculateDatetimes($interval, $unit, $limit_time = null) {
        if (!is_numeric($interval)) return;
        if (!$unit) return;
        if ($this->from_time) {
            $this->datetimes = [];
            $interval_string = "+{$interval}{$unit}";
            $time = $this->from_time;
            //$now = time();
            while ($time < $this->to_time) {
                if ($limit_time && $time > $limit_time) {
                    break;
                } else {
                    $this->datetimes[] = $time;
                    $time = strtotime($interval_string, $time);
                }
                $i++;
                if ($i > 100000) break;
            }
        }
        return $this;
    }

    /**
     * interval for datetimes
     *
     * @param integer $interval
     * @param string $unit
     * @param integer $limit_time
     */
    function calculateNumber($interval, $unit, $limit_time = null) {
        $this->calculateDatetimes($interval, $unit, $limit_time = null);
        if ($this->datetimes) {
            foreach ($this->datetimes as $time) {
                $this->date_numbers[] = date('Ymdhi', $time);
            }
        }
        return $this;
    }

    /**
     * reverse datetimes
     *
     * @return PwDate
     */
    function reverseDatetimes() {
        $this->datetimes = array_reverse($this->datetimes);
        return $this;
    }

    /**
     * next from date
     *
     * @return PwDate
     */
    function nextFromDate($value, $unit = 'hour') {
        $format = "+{$value}{$unit}";
        $this->setFromTime(strtotime($format, $this->from_time));
        return $this;
    }

    /**
     * next from date
     *
     * @return PwDate
     */
    function nextToDate($value, $unit = 'hour') {
        $format = "+{$value}{$unit}";
        $this->setToTime(strtotime($format, $this->to_time));
        return $this;
    }

    /**
     * interval datetime
     *
     * @param string $date_at
     * @param integer $interval
     * @param string $unit
     * @param string $locale
     * @return string
     **/
    static function intervalDatetime($date_at, $interval, $unit = 'day', $locale = LOCALE) {
        if ($date_at && is_numeric($interval)) {
            $interval = "{$interval} {$unit}";
            $format = self::formatForLang($locale);
            return date($format, strtotime($interval, strtotime($date_at)));
        }
    }

    /**
     * datetime format for lang
     *
     * @param string $lang
     * @param integer $interval
     * @return string
     **/
    static function formatForLang($lang = 'ja', $has_time = true) {
        if ($has_time) $time = ' H:i';
        $formats['ja'] = 'Y/m/d';
        $formats['en'] = 'd M Y';
        $format = $formats[$lang];

        if (!$format) $format = "Y/m/d{$time}";
        $format = "{$format}{$time}";

        return $format;
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
            $date = date('Y/m/d H:i', $time);
        }
        return $date;
    }

    /**
    * convert number to datetime
    *
    * @param string $number
    * @return string
    */
    static function numberToString($number, $format = 'Y/m/d H:i') {
        if ($number) {
            $pattern = "/^(\d{4})(\d{2})(\d{2})(\d{2})(\d{2})$/";
            $replacement = "\$1-\$2-\$3 \$4:\$5";
            $string = preg_replace($pattern, $replacement, $number);
            $date = date($format, strtotime($string));
            return $date;
        }
    }

    /**
    * datetime to number
    *
    * @param string $value
    * @param string $format
    * @return string
    */
    static function stringToNumber($string, $format = 'YmdHi') {
        if ($string) {
            return date($format, strtotime($string));
        }
    }

    /**
    * graph label format
    *
    * TODO function name
    * TODO format lang list
    *
    * @param int $datetime
    * @return string
    */
    static function graphLabelFormat($datetime, $lang = 'ja') {
        if ($lang != 'ja') {
            $date = date('Y/m/d H:i', $datetime);
        } else {
            $date = date('Y年m月d日H時i分', $datetime);
        }
        return $date;
    }
    
    /**
    * label format
    *
    * @param string $date_string
    * @param string $formatter
    * @return string
    */
    static function dateFormat($date_string, $formatter = 'Y/m/d H:i') {
        if (!$date_string) return;
        $date = date($formatter, strtotime($date_string));
        return $date;
    }

    /**
     * ten minutes
     *
     * @param string $date
     * @return void
     */
    function nowForTenMinutes() {
        $now_minute = date('i');
        $minute = floor($now_minute);
        $minute = sprintf("%02d", $minute);
        $now = date("Y/m/d H:{$minute}");
        return $now;
    }

    //TODO
    //from DatePwPwHelper functions
    /**
     * datetimeFormat
     *
     * @param string $value
     * @param string $separate
     * @return string
     */
    static function datetimeFormat($value, $separate = 's') {
        if (!$separate) $separate = 's';
        if ($value) {
            $format = self::formatter($separate, true);
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
        if (!$separate) $separate = 's';
        $formatters = self::formatters($separate);
        $year = $formatters['year'];
        $month = $formatters['month'];
        $day = $formatters['day'];
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
        $formatters['j'] = array ('year' => '?', 'month' => '?', 'day' => '?');
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
            return sprintf('%4d/%02d/%02d %02d:%02d:%02d', $m[1], $m[2], $m[3], $m[4], $m[5], $m[6]);
        } else {
            $time = strtotime($value);
            if ($time >= 0) return date('Y/m/d H:i:s', $time);
            return null;
        }
    }

}