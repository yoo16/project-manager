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

    function __construct() {
    }

    /**
     * init
     *
     * @return void
     */
    function init() {
        $this->setFromAt(date('Y-m-d 00:00'));
        $this->setOneDay();
        $this->setStartAt(date('Y-m-d 00:00'));
        $this->setEndAt($this->to_at);
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
     * initOnedayInterval
     *
     * @param string $start_at
     * @param string $end_at
     * @param integer $days
     */
    function initOnedayInterval($start_at, $end_at, $days = 1) {
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
        return $this;
    }

    /**
     * set One day
     *
     */
    function setOneDay() {
        if ($this->from_datetime) {
            $from_at = date('Y-m-d 00:00', $this->from_datetime);
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
            $from_at = date('Y-m-d 00:00', $this->from_datetime);
            $format = "{$days}day";
            $this->setIntervalByFromAt($from_at, $format);
        }
    }

    /**
     * set interval days from end_at
     *
     * @param integer $days
     */
    function setIntervalDaysFromToAt($days) {
        if ($this->to_datetime && $days) {
            $formatter = "-{$days}days";
            $from_datetime = strtotime($formatter, $this->to_datetime);
            $this->setFromDatetime($from_datetime);
        }
    }

    /**
     * set from_datetime
     *
     * @param string $from_datetime
     */
    function setFromDatetime($from_datetime) {
        $this->from_datetime = $from_datetime;
        $this->from_at = date('Y-m-d H:i', $from_datetime);
        $this->from_date = DateManager::datetimeToNumber($this->from_at);
    }

    /**
     * set to_datetime
     *
     * @param string $to_datetime
     */
    function setToDatetime($to_datetime) {
        $this->to_datetime = $to_datetime;
        $this->to_at = date('Y-m-d H:i', $to_datetime);
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
            $this->prev_at = date('Y-m-d H:i', $this->prev_datetime);
            $this->prev_date = DateManager::datetimeToNumber($this->prev_at);

            $interval_string = "+{$interval}{$unit}";
            $this->next_datetime = strtotime($interval_string, $this->from_datetime);
            $this->next_at = date('Y-m-d H:i', $this->next_datetime);
            $this->next_date = DateManager::datetimeToNumber($this->next_at);
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
     * @param String $to_at
     * @param String $interval_string
     */
    function setIntervalByToAt($to_at, $interval_string) {
        $this->setToAt($to_at);
        $from_datetime = strtotime($interval_string, $this->to_datetime);
        $this->setFromDatetime($from_datetime);
    }

    /**
     * interval fro to_date
     *
     * @param Integer $interval
     * @param String $unit
     */
    function calculateDatetimes($interval, $unit) {
        if ($this->from_datetime) {
            $this->datetimes = null;
            $interval_string = "+{$interval}{$unit}";

            $datetime = $this->from_datetime;

            while ($datetime < $this->to_datetime) {
                $this->datetimes[] = $datetime;
                $datetime = strtotime($interval_string, $datetime);
            }
        }
    }

    /**
     * request FromDate
     * 
     * @return String
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
     * @return String
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
            $date = date("Y-m-d H:i", $time);
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
    * グラフ用日時
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
     * ten minutes
     *
     * @param string $date
     * @return void
     */
    function nowForTenMinutes() {
        $now_minute = date('i');
        $minute = floor($now_minute);
        $minute = sprintf("%02d", $minute);
        $now = date("Y-m-d H:{$minute}");
        return $now;
    }

}