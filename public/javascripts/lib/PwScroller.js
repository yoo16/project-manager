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
    this.motion = '';
    this.speed = 500;

    $(document).on('click', '.pw-scroller', function() {
        var action = $(this).attr('pw-action');
        if (action) pw_scroller[action](this)

        var href = $(this).attr("href");
        if (href) {
            var target = $(href == "#" || href == "" ? 'html' : href);
            if (target) pw_scroller.move(target)
            console.log(target);
        }
    });
    this.move = function(target) {
        var position = target.offset().top;
        $("html, body").animate({scrollTop: position}, pw_scroller.speed);
        return false;
    }
    this.top = function(dom) {
        $('body,html').animate({ scrollTop: 0 }, pw_scroller.speed);
    }
    this.up = function(dom) {
        var height  = $(window).height();
        var current  = $(window).scrollTop();
        $('body,html').animate({ scrollTop: current - height }, pw_scroller.speed);
    }
    this.down = function(dom) {
        $('body,html').animate({ scrollTop: $(dom).offset().top }, pw_scroller.speed);
    }
    this.bottom = function(dom) {
        $('body,html').animate({ scrollTop: $('#pw_scroller_end').offset().top }, pw_scroller.speed);
    }
}