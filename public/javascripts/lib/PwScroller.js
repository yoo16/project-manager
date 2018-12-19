/**
 * @author  Yohei Yoshikawa
 *
 * Copyright (c) 2017 Yohei Yoshikawa (https://github.com/yoo16/)
 */

'use strict';
var pw_scroller;
$(document).ready(function () {
    pw_scroller = new PwScroller();
});

var PwScroller = function () {
    $(document).on('click', '.pw-scroller', function() {
        var action = $(this).attr('pw-action');
        if (action) pw_scroller[action](this)
    });
    this.top = function(dom) {
        $('body,html').animate({ scrollTop: 0 }, 500);
    }
    this.up = function(dom) {
        var height  = $(window).height();
        var current  = $(window).scrollTop();
        $('body,html').animate({ scrollTop: current - height }, 500);
    }
    this.down = function(dom) {
        $('body,html').animate({ scrollTop: $(dom).offset().top }, 500);
    }
    this.bottom = function(dom) {
        $('body,html').animate({ scrollTop: $('#pw_scroller_end').offset().top }, 500);
    }
}