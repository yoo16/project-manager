/**
 * plugin.js
 * 
 * Copyright (c) 2013 Yohei Yoshikawa (https://github.com/yoo16/)
 */
//TODO lib


/**
 * delete check
 * @param
 * @return
 */
$(document).on('click', '.delete_checkbox', function(event) {
    var target = $(this).attr('rel');
    var selector = '#' + target;
    if ($(this).prop('checked')) {
        $(selector).attr('disabled', false);
    } else {
        $(selector).attr('disabled', true);
    }
});

/**
 * autoInputZip()
 *
 **/
 function autoInputZip(target_id, zip_id1, zip_id2) {
    var zip1 = '#' + zip_id1;
    var zip2 = '#' + zip_id2;
    var target = '#' + target_id;

    if (($zip1).val() && $(zip2).val()) {
        var zip = $(zip1).val() + '-' + $(zip2).val();
        $(target).val(zip);
    } else {
        $(target).val('');
    }
}

 /**
  * autoInputTel
  * 
  * @param  Integer target_id [description]
  * @param  String tel_id1   [description]
  * @param  String tel_id2   [description]
  * @param  String tel_id3   [description]
  * @return
  */
  function autoInputTel(target_id, tel_id1, tel_id2, tel_id3) {
    var tel1 = '#' + tel_id1;
    var tel2 = '#' + tel_id2;
    var tel3 = '#' + tel_id3;
    var target = '#' + target_id;

    if (($tel1).val() && $(tel2).val() && $(tel3).val()) {
        var tel = $(tel1).val() + '-' + $(tel2).val() + '-' + $(tel3).val();
        $(target).val(tel);
    } else {
        $(target).val('');
    }
}

/**
 * check furigana
 * 
 * @param  String id
 * @return
 */
function furiganaCheck(id) {
   var value = $(id).attr('value');
   if(value.match( /[^ぁ-んァ-ン　\s]+/ ) ) {
      alert("ふりがなは、「ひらがな」・「カタカナ」のみで入力して下さい。");
      return 1;
  }
  return 0;
}

/**
 * check alphabet
 * 
 * @param  String id
 * @return
 */
function alphabetCheck(id) {
   var value = $(id).attr('value');
   if(value.match( /[^A-Za-z\s.-]+/ ) ) {
      alert("半角英文字のみで入力して下さい。");
      return 1;
  }
  return 0;
}

/**
 * check number
 * 
 * @param  String id
 * @return
 */
function numberCheck(id) {
   var value = $(id).attr('value');
   if(value.match( /[^0-9]+/ ) ) {
      alert("半角数字のみで入力して下さい。");
      return 1;
  }
  return 0;
}

$(function() {
    var popupEvent = function(event) {
        var option = this.href.replace(/^[^\?]+\??/,'').replace(/&/g, ',');
        window.open(this.href, this.rel, option).focus();
        event.preventDefault();
        event.stopPropagation();
    }
    $("a.popup").each(function(i) {
        $(this).click(popupEvent);
        $(this).keypress(popupEvent);
    });
});

function openWindow(url) {
    if (url) {
        window.open(url, 'new', 'scrollbars=yes,width=520,height=640');
    }
}

function rollover() {
    $('.swap').rollover();
}

function openElement(id) {
    $(id).hide();
    $(id).slideDown('normal');
}

function closeElement(id) {
    $(id).slideUp('normal');
}

