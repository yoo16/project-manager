/**
 * datepicker.js
 * 
 * Copyright (c) 2017 Yohei Yoshikawa (http://yoo-s.com/)
 */
$(document).ready(function() {

    var now = new Date();
    var end_year = now.getFullYear() + 10;
    var year_range = "2000:" + end_year;
    $('.datepicker').datepicker({
        showOn: "button",
        buttonImage: client_image_path + "calendar.gif",
        buttonImageOnly: true,
        changeMonth: true,
        changeYear: true,
        yearRange: year_range,
        monthNamesShort: [1,2,3,4,5,6,7,8,9,10,11,12],
        dateFormat: 'yy-mm-dd'
    });
    $('.datetimepicker').datepicker({
        showOn: "button",
        buttonImage: client_image_path + "calendar.gif",
        buttonImageOnly: true,
        changeMonth: true,
        changeYear: true,
        yearRange: year_range,
        monthNamesShort: [1,2,3,4,5,6,7,8,9,10,11,12],
        dateFormat: 'yy-mm-dd 00:00'
    });

});

$(document).on('change', '.datetimepicker', function() {
    var selector = $(this).attr('change_selector');

    if (!selector) return;

    var date_string = $(this).val();
    var date = new Date(date_string);

    date_number = dateNumber(date);

    $(number_selector).val(date_number);

    function dateNumber(date) {
        var year = date.getFullYear();
        var month = date.getMonth() + 1;
        var day = date.getDate();
        var hour = date.getHours();
        var minutes = date.getMinutes();

        year = ('0000' + year).slice(-4);
        month = ('00' + month).slice(-2);
        day = ('00' + day).slice(-2);
        hour = ('00' + hour).slice(-2);
        minutes = ('00' + minutes).slice(-2);
        return year + month + day + hour + minutes;
    }

 });