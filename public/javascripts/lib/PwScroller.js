/**
 * @author  Yohei Yoshikawa
 *
 * Copyright (c) 2017 Yohei Yoshikawa (https://github.com/yoo16/)
 */

'use strict';
var PwScroller = function () {
    this.motion = '';
    this.speed = 1000;
    this.is_moving = false;

    this.move = function(node) {
        this.scroll(node.top());
        return false;
    }
    this.top = function(node) {
        this.scrollUp(0);
    }
    this.up = function(node) {
        var current = window.pageYOffset;
        var scroll_range = window.parent.screen.height / 2;
        var y = current - scroll_range;
        if (y < 0) y = 0;
        this.scrollUp(y);
    }
    this.down = function(node) {
        var current = window.pageYOffset;
        var scroll_range = window.parent.screen.height / 2;
        var y = current + scroll_range;
        this.scrollDown(y)
    }
    this.bottom = function(node) {
        var element = document.documentElement;
        var y = element.scrollHeight - element.clientHeight;
        this.scrollDown(y);
    }
    this.scroll = function (to_y) {
        if (to_y < 0) return;
        if (window.pageYOffset > to_y) {
            this.scrollUp(to_y);
        } else if (window.pageYOffset > to_y) {
            this.scrollDown(to_y);
        }
    };
    this.scrollUp = function (to_y) {
        if (to_y < 0) return;
        var range = this.calculateRange();
        var start_y = window.pageYOffset;
        var current_y = 0;
        var progress = 0;
        var move = function () {
            current_y = start_y - range * pw_animation.easing(progress++ / 100);
            if (current_y < to_y) current_y = to_y;
            window.scrollTo(0, current_y);
            if (window.pageYOffset <= to_y) return;
            if (window.pageYOffset > to_y) requestAnimationFrame(move);
        };
        requestAnimationFrame(move);
    };
    this.scrollDown = function (to_y) {
        if (to_y < 0) return;
        var range = this.calculateRange();
        var start_y = window.pageYOffset;
        var current_y = 0;
        var progress = 0;
        var move = function () {
            current_y = start_y + range * pw_animation.easing(progress++ / 100);
            window.scrollTo(0, current_y);
            if (current_y >= to_y) return;
            if (current_y < to_y) requestAnimationFrame(move);
        };
        requestAnimationFrame(move);
    };
    this.easing = function(p) {
        return p * p; 
    }
    this.calculateRange = function() {
        var start_y = window.pageYOffset
        var height = window.parent.screen.height;
        var range = Math.abs(height - start_y) * this.speed * 0.1;
        return range;
    }
}

var pw_scroller = new PwScroller();
document.addEventListener('DOMContentLoaded', function() {
    let elements = document.querySelectorAll('.pw-scroller');
    [].forEach.call(elements, function(element) {
        element.addEventListener('click', function(event) {
            var node = PwNode.byElement(element);
            var action = node.action();
            if (action) pw_scroller[action](node)
        });
    });
});
