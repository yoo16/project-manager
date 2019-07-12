/**
 * @author  Yohei Yoshikawa
 *
 * Copyright (c) 2018 Yohei Yoshikawa (https://github.com/yoo16/)
 */

var PwDate = function() {
    this.from_at_selector = '#from-at';
    this.to_at_selector = '#to-at';

    this.unixToString = function(time: any) {
        var date = new Date(time);
        var year  = date.getFullYear();
        var month = date.getMonth() + 1;
        var day  = date.getDate();
        var hour = ( '0' + date.getHours() ).slice(-2);
        var min  = ( '0' + date.getMinutes() ).slice(-2);
        var sec   = ( '0' + date.getSeconds() ).slice(-2);
        return (year + '/' + month + '/' + day + ' ' + hour + ':' + min);
    }
    this.unixToDateString = function(time) {
        var date = new Date(time);
        var year  = date.getFullYear() - 100;
        var month = date.getMonth() + 1;
        var day  = date.getDate();
        // var hour = ( '0' + date.getHours() ).slice(-2);
        // var min  = ( '0' + date.getMinutes() ).slice(-2);
        // var sec   = ( '0' + date.getSeconds() ).slice(-2);
        return (year + '/' + month + '/' + day);
    }
    this.nextDate = function(value: any) {
        if (!value) return;

        var date = new Date(value)
        date.setDate(date.getDate() + 1);
        var value = this.string(date);
        return value;
    }  
    this.prevDate = function(value: any) {
        var date = new Date(value)
        date.setDate(date.getDate() - 1);
        var value = this.string(date);
        return value;
    }
    this.replaceAllHyphen = function(value: any) {
        if (!value) return;
        value = value.toString();
        value = value.replace(/-/g, "/");
        return value;
    }
    this.string = function(value: any) {
        if (!value) return;
        value = this.replaceAllHyphen(value);

        var year = 0;
        var month = 0;
        var day = 0;
        var hour = 0;
        var minute = 0;
        var date = new Date(value)
        year = date.getFullYear();
        month = date.getMonth() + 1;
        day = date.getDate();
        hour = date.getHours();
        minute = date.getMinutes();

        if (!(year > 1900)) return;
        if (!(month > 0)) return;
        if (!(day > 0)) return;

        var year_string: string = ('0000' + year).slice(-4);
        var month_string: string = ('00' + month).slice(-2);
        var day_string: string = ('00' + day).slice(-2);
        var hour_string: string = ('00' + hour).slice(-2);
        var minute_string = ('00' + minute).slice(-2);

        var number = year_string + '/' + month_string + '/' + day_string + ' ' + hour_string + ':' + minute_string;

        return number;
    }    
    this.number = function(value: any) {
        if (!value) return;
        value = this.replaceAllHyphen(value);

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
    this.zeroMinute = function(value: any) {

    }
    this.limitToday = function(value: any) {
        if (!value) return;
        value = this.replaceAllHyphen(value);

        var date = new Date(value);
        var today = new Date();
        if (date.getTime() > today.getTime()) {
            today.setMinutes(0);
            return this.string(today);
        } else {
            return this.string(value);
        }
    }
    this.updateFromToComponent = function(value: any) {
        if (value.from_at) {
            var from_at = this.string(value.from_at);
            PwNode.id(this.from_at_selector).setValue(from_at);
        }
        if (value.to_at) {
            var to_at = this.string(value.to_at);
            PwNode.id(this.to_at_selector).setValue(to_at);
        }
    }
    this.convertGraphDate = function(value: any) {
        var date = new Date(value * 1000); 
        var year = date.getFullYear() - 100;
        var month = date.getMonth() + 1;
        var day = date.getDate();
        //var hour = date.getHours();
        //var date_string = year + '/' + month + '/' + day + ' ' + hour;
        var date_string = year + '/' + month + '/' + day;
        return date_string;
    }

}

var pw_date =  new PwDate();
document.addEventListener('DOMContentLoaded', function() {
});
