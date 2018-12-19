/**
 * function.js
 * 
 * Copyright (c) 2017 Yohei Yoshikawa (http://yoo-s.com/)
 */

$(function() {
    $('#plot_image').load(function() {
        hideLoading();
    });
});

 $(document).on('click', '.sensor-duplicate-window', function() {
    $('#sensor-duplicate-window').show();
    var sensor_id = $(this).attr('duplicate_sensor_id');
    $('#duplicate_sensor_id').val(sensor_id);

    var sensor_name = $(this).attr('duplicate_sensor_name');
    $('#duplicate_sensor_name').html(sensor_name);
});


 $(document).on('click', '.instrument-duplicate-window', function() {
    $('#instrument-duplicate-window').show();
    var instrument_id = $(this).attr('duplicate_instrument_id');
    $('#duplicate_instrument_id').val(instrument_id);

    var instrument_name = $(this).attr('duplicate_instrument_name');
    $('#duplicate_instrument_name').html(instrument_name);
});


 $(document).on('change', '#form-search', function() {
    showLoading()
    $(this).submit();
});

 $(document).on('change', '#form-spot_list', function() {
    showLoading()
    $(this).submit();
});

/**
 * input displace_type
 * @param
 * @return
 */
 $(function(){
    var value = $('#displace_type-moveing_average').val();
    if (value > 0) {
        $('.select-displace_type').val(1);
    } else {
        $('.select-displace_type').val(0);
    }
});

/**
 * change select displace_type
 * @param
 * @return
 */
 $(document).on('change', '.select-displace_type', function() {
    var index = $(this).val();
    if (index == 0) {
        $('#displace_type-moveing_average').val(0)
    } else if (index = 1) {
        $('#displace_type-moveing_average').val(24)
    }
});

