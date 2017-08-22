/**
 * @author  Yohei Yoshikawa
 *
 * Copyright (c) 2013 Yohei Yoshikawa (http://yoo-s.com/)
 */

$(document).on('click', '.change-list-col', function() {
    var target = $(this).attr('target');
    if (target) {
        var data = $(this).attr('data');
        if (data) {
            if (data == 1) {
                $(target).removeClass('col-6').addClass('col-12');
            } else if (data == 2) {
                $(target).removeClass('col-12').addClass('col-6');
            }
        }
    }
});
