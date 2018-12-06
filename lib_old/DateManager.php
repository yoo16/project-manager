<?php
/**
 * DateManager 
 *
 * @author Yohei Yoshikawa
 * @create   
 */

class DateManager {

    public $from_date;
    public $to_date;
    public $from_datetime;
    public $to_datetime;
    public $from_at;
    public $to_at;
    public $start_at;
    public $end_at;
    public $start_datetime;
    public $end_datetime;
    public $start_date;
    public $end_date;

    function __construct() {
    }

    /**
     * init
     *
     * @return void
     */
    function init() {
        $this->setFromAt(date('Y/m/d 00:00'));
        $this->setOneDay();
        $this->setStartAt(date('Y/m/d 00:00'));
        $this->setEndAt($this->to_at);
    }

    static function instance($from_at, $to_at)
    {
        $date = new DateManager();
        $date->setToAt($to_at);
        $date->setFromAt($from_at);
        return $date;
    }

    /**
     * set session
     *
     * @return void
     */
    function clearAppSession($key = 'app_date') {
        if (!$key) $key = 'app_date';
        AppSession::clearWithKey($key, 'date');
    }

    /**
     * set session
     *
     * @return void
     */
    function storeAppSession($key = 'app_date') {
        if (!$key) $key = 'app_date';
        AppSession::setWithKey($key, 'date', $this);
    }

    /**
     * load session
     *
     * @return DateManager
     */
    function loadAppSession($key = 'app_date') {
        if (!$key) $key = 'app_date';
        $pw_date = AppSession::getWithKey($key, 'date');
        if ($pw_date) {
            $this->setFromAt($pw_date->from_at);
            $this->setToAt($pw_date->to_at);
            $this->setStartAt($pw_date->start_at);
            $this->setEndAt($pw_date->end_at);
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
            $this->setFromDate($_REQUEST['from_date']);
        } else if ($_REQUEST['from_at']) {
            $this->setFromAt($_REQUEST['from_at']);
        }
        return $this->from_at;
    }

    /**
     * request ToDate
     * 
     * @return string
     */
    function requestTo() {
        if ($_REQUEST['to_date']) {
            $this->setToDate($_REQUEST['to_date']);
        } else if ($_REQUEST['to_at']) {
            $this->setToAt($_REQUEST['to_at']);
        }
        return $this->to_at;
    }

    /**
     * set today interval
     *
     */
    function setTodayInterval($interval_string) {
        $to_at = date('Y/m/d H:00');
        $this->setIntervalByToAt($to_at, $interval_string);
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
        $this->start_date = DateManager::datetimeToNumber($this->start_at);
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
        $this->end_date = DateManager::datetimeToNumber($this->end_at);
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
    function fromAtZeroHour() {
        if (!$this->from_at) return;
        $from_at = date('Y/m/d 00:00', strtotime($this->from_at));
        $this->setFromAt($from_at);
    }

    /**
     * to_at is zero hour 
     *
     * @return void
     */
    function toAtZeroHour() {
        if (!$this->from_at) return;
        $to_at = date('Y/m/d 00:00', strtotime($this->from_at));
        $this->setToAt($to_at);
    }

    /**
     * clear from date
     *
     * @return void
     */
    function clearFromDate() {
        $this->from_at = null;
        $this->from_datetime = null;
        $this->from_date = null;
    }

    /**
     * clear to date
     *
     * @return void
     */
    function clearToDate() {
        $this->to_at = null;
        $this->to_datetime = null;
        $this->to_date = null;
    }

    /**
     * set from_at
     *
     * @param string $from_at
     */
    function setFromAt($from_at) {
        $this->from_at = $from_at;
        $this->from_datetime = strtotime($this->from_at);
        $this->from_date = DateManager::datetimeToNumber($this->from_at);
    }

    /**
     * set to_at
     *
     * @param string $to_at
     */
    function setToAt($to_at) {
        $this->to_at = $to_at;
        $this->to_datetime = strtotime($this->to_at);
        $this->to_date = DateManager::datetimeToNumber($this->to_at);
    }

    /**
     * set from_date
     *
     * @param string $from_date
     */
    function setFromDate($from_date) {
        $this->from_date = $from_date;
        $this->from_at = DateManager::numberToDatetime($this->from_date);
        $this->from_datetime = strtotime($this->from_at);
    }

    /**
     * set to_at
     *
     * @param string $to_at
     */
    function setToDate($to_at) {
        $this->to_date = $to_at;
        $this->to_at = DateManager::numberToDatetime($this->to_date);
        $this->to_datetime = strtotime($this->to_at);
    }

    /**
     * initDayInterval
     *
     * @param string $start_at
     * @param string $end_at
     * @param integer $days
     */
    function initDayInterval($start_at, $end_at, $days = 1) {
        $this->setStartAt($start_at);
        $this->setEndAt($end_at);

        if (!$this->from_at) $this->setFromAt($end_at);
        if (!$this->from_at) $this->setFromAt(date('Y-m-01'));

        $this->limitDate();

        $this->setOneDay($days);
        $this->setNextPrevDatesForFromDate($days, 'days');
    }

    /**
     * set interval by from_at
     *
     * @param string $from_at
     * @param string $interval_string
     */
    function limitNow() {
        if (!$this->to_datetime) return;
        $now = time();
        if ($this->to_datetime > $now) $this->to_datetime = $now;
        $this->setToDatetime($this->to_datetime);
    }

    /**
     * limitDate
     *
     * @return DateManager
     */
    function limitDate() {
        if ($this->start_datetime && (!$this->from_datetime || $this->from_datetime < $this->start_datetime)) {
            $this->setFromAt($this->start_at);
        }
        if ($this->end_datetime && (!$this->to_datetime || $this->to_datetime > $this->end_datetime)) {
            $this->setToAt($this->end_at);
        }
        $now = time();
        if ($this->to_datetime > $now) {
            $this->setToAt($now);
        }
        return $this;
    }

    /**
     * set One day
     *
     */
    function setOneDay() {
        if ($this->from_datetime) {
            $from_at = date('Y/m/d 00:00', $this->from_datetime);
            $this->setIntervalByFromAt($from_at, '+1day');
        }
    }

    /**
     * set interval days
     *
     * @param integer $days
     */
    function setIntervalDays($days) {
        if ($this->from_datetime && $days) {
            $from_at = date('Y/m/d 00:00', $this->from_datetime);
            $format = "{$days}day";
            $this->setIntervalByFromAt($from_at, $format);
        }
    }

    /**
     * set interval by from_at
     *
     * @param string $from_at
     * @param string $interval_string
     * @param boolean $is_limit_now
     */
    function setIntervalByFromAt($from_at, $interval_string, $is_limit_now = true) {
        $this->setFromAt($from_at);
        $to_datetime = strtotime($interval_string, $this->from_datetime);
        $this->setToDatetime($to_datetime);

        if ($is_limit_now) $this->limitNow();
    }

    /**
     * set interval by to_at
     *
     * @param string $to_at
     * @param string $interval_string
     */
    function setIntervalByToAt($to_at, $interval_string) {
        $this->setToAt($to_at);
        $from_datetime = strtotime($interval_string, $this->to_datetime);
        $this->setFromDatetime($from_datetime);
    }

    /**
     * set interval days from end_at
     *
     * @param integer $days
     */
    function setIntervalDaysFromToAt($days, $is_zero_hours = false) {
        if ($this->to_datetime && $days) {
            $formatter = "-{$days}days";
            $from_datetime = strtotime($formatter, $this->to_datetime);
            $this->setFromDatetime($from_datetime);
            if ($is_zero_hours) $this->setFromAt(date('Y/m/d 00:00', $this->from_datetime));
        }
    }

    /**
     * set from_datetime
     *
     * @param string $from_datetime
     */
    function setFromDatetime($from_datetime) {
        $this->from_datetime = $from_datetime;
        $this->from_at = date('Y/m/d H:i', $from_datetime);
        $this->from_date = DateManager::datetimeToNumber($this->from_at);
    }

    /**
     * set to_datetime
     *
     * @param string $to_datetime
     */
    function setToDatetime($to_datetime) {
        $this->to_datetime = $to_datetime;
        $this->to_at = date('Y/m/d H:i', $to_datetime);
        $this->to_date = DateManager::datetimeToNumber($this->to_at);
    }

    /**
     * set interval values
     *
     * @param integer $interval
     * @param string $unit
     */
    function setNextPrevDatesForFromDate($interval, $unit) {
        if ($this->from_datetime) {
            $interval_string = "-{$interval}{$unit}";
            $this->prev_datetime = strtotime($interval_string, $this->from_datetime);
            $this->prev_at = date('Y/m/d H:i', $this->prev_datetime);
            $this->prev_date = DateManager::datetimeToNumber($this->prev_at);

            $interval_string = "+{$interval}{$unit}";
            $this->next_datetime = strtotime($interval_string, $this->from_datetime);
            $this->next_at = date('Y/m/d H:i', $this->next_datetime);
            $this->next_date = DateManager::datetimeToNumber($this->next_at);
        }
    }

    /**
     * interval fro to_date
     *
     * @param integer $interval
     * @param string $unit
     * @param integer $limit_time
     */
    function calculateDatetimes($interval, $unit, $limit_time = null) {
        if ($this->from_datetime) {
            $this->datetimes = null;
            $interval_string = "+{$interval}{$unit}";

            $datetime = $this->from_datetime;

            $now = time();
            while ($datetime < $this->to_datetime) {
                if ($limit_time && $datetime > $limit_time) {

                } else {
                    $this->datetimes[] = $datetime;
                    $datetime = strtotime($interval_string, $datetime);
                }
            }
        }
    }

    /**
     * reverse datetimes
     *
     * @return void
     */
    function reverseDatetimes() {
        $this->datetimes = array_reverse($this->datetimes);
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
            $format = self::datetimeFormatForLang($locale);
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
    static function datetimeFormatForLang($lang = 'ja', $has_time = true) {
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
    static function numberToDatetime($number) {
        if ($number) {
            $pattern = "/^(\d{4})(\d{2})(\d{2})(\d{2})(\d{2})$/";
            $replacement = "\$1-\$2-\$3 \$4:\$5";
            $string = preg_replace($pattern, $replacement, $number);

            $time = strtotime($string);
            $date = date("Y/m/d H:i", $time);
            return $date;
        }
    }

    /**
    * datetimeから連番
    *
    * @param String $value
    * @return string
    */
    static function datetimeToNumber($string) {
        if ($string) {
            return date('YmdHi', strtotime($string));
        }
    }

    /**
    * graph label format
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

}