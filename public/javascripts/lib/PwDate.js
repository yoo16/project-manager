/**
 * @author  Yohei Yoshikawa
 *
 * Copyright (c) 2018 Yohei Yoshikawa (https://github.com/yoo16/)
 */

var pw_date;
$(document).ready(function(){
    pw_date = new PwDate();
});

var PwDate = function() {
    this.from_at_selector = '#from-at';
    this.to_at_selector = '#to-at';

    this.nextDate = function(value) {
        if (!value) return;
        var date = new Date(value)
        date.setDate(date.getDate() + 1);
        var value = this.string(date);
        return value;
    }  
    this.prevDate = function(value) {
        if (!value) return;
        var date = new Date(value)
        date.setDate(date.getDate() - 1);
        var value = this.string(date);
        return value;
    }
    this.string = function(value) {
        if (!value) return;
        var year = 0;
        var month = 0;
        var day = 0;
        var date = new Date(value)
        year = date.getFullYear();
        month = date.getMonth() + 1;
        day = date.getDate();
        hour = date.getHours();
        minute = date.getMinutes();

        if (!(year > 1900)) return;
        if (!(month > 0)) return;
        if (!(day > 0)) return;

        var year = ('0000' + year).slice(-4);
        var month = ('00' + month).slice(-2);
        var day = ('00' + day).slice(-2);
        var hour = ('00' + hour).slice(-2);
        var minute = ('00' + minute).slice(-2);

        var number = year + '-' + month + '-' + day + ' ' + hour + ':' + minute;
        return number;
    }    
    this.number = function(value) {
        if (!value) return;
        var date = new Date(value)
        year = date.getFullYear();
        month = date.getMonth() + 1;
        day = date.getDate();
        hour = date.getHours();
        minute = date.getMinutes();

        var year = ('0000' + year).slice(-4);
        var month = ('00' + month).slice(-2);
        var day = ('00' + day).slice(-2);
        var hour = ('00' + hour).slice(-2);
        var minute = ('00' + minute).slice(-2);

        var number = year + month + day + hour + minute;
        return number;
    }
    this.zeroMinute = function(value) {

    }
    this.limitToday = function(value) {
        var date = new Date(value);
        var today = new Date();
        if (date.getTime() > today.getTime()) {
            today.setMinutes(0);
            return this.string(today);
        } else {
            return this.string(value);
        }
    }
    this.updateFromToComponent = function(value, params) {
        if (value.from_at) {
            $(this.from_at_selector).val(this.string(value.from_at));
        }
        if (value.to_at) {
            $(this.to_at_selector).val(this.string(value.to_at));
        }
    }
}
