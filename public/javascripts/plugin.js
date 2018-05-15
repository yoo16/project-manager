/**
 * plugin.js
 * 
 * Copyright (c) 2013 Yohei Yoshikawa (http://yoo-s.com/)
 */

$(document).on('click', '.confirm-delete', function() {
    var delete_id = $(this).attr('delete-id');
    $('#from-delete-id').val(delete_id);

    var title = $(this).attr('title');
    if (title) $('#from-delete-title').html(title);

    $('.delete-window').modal();
});

 $(document).on('click', '.action-loading', function() {
    showLoading();
});

/**
 * show loading
 * 
 * @return
 */
 function showLoading() {
    $(document).ready(function() {
        $.LoadingOverlay("show");
    });
}

/**
 * hide loading
 * 
 * @return
 */
 function hideLoading() {
    $(document).ready(function() {
        $.LoadingOverlay("hide");
    });
}

/**
 * delete check
 * @param
 * @return
 */
 $(document).on('click', '.delete_checkbox', function(event) {
    var target = $(this).attr('rel');
    var selector = '#' + target;
    console.log(selector);
    if ($(this).prop('checked')) {
        $(selector).attr('disabled', false);
    } else {
        $(selector).attr('disabled', true);
    }
});

/**
 * confirm dialog
 * @param
 * @return
 */
 $(document).on('click', '.confirm-dialog', function() {
    var message = '';
    if ($(this).attr('message')) {
        message = $(this).attr('message');
    }
    if (window.confirm(message)) {
        showLoading();
        return true;
    } else {
        hideLoading();
        return false;
    }
});

/**
 * close 
 *
 * @param
 * @return
 */
 $(document).on('click', '.action-close', function() {
    var window_id = '#' + $(this).attr('window-id');
    $(window_id).hide();
});

/**
 * change date
 *
 * @param
 * @return
 */
 $(document).on('change', '.action-change-date', function() {
    checkDate(this);

    function checkDate(target) {
        var date_name = $(target).attr('name');

        date_names = date_name.split('[');

        if (!date_names) return;

        date_name = date_names[0];
        var year_column = '[name="' + date_name + '[year]"]';
        var month_column = '[name="' + date_name + '[month]"]';
        var day_column = '[name="' + date_name + '[day]"]';

        var year = $(year_column).val();
        var month = $(month_column).val();
        var day = $(day_column).val();

        if (year && month && day) {
            var date_string = year + '-' + month + '-' + day;
            date_column = '[name="' + date_name + '"]';
            $(date_column).val(date_string);
        }
    }
});


/**
 * 改行をBRタグに変換
 * 
 * @param String str 変換したい文字列
 */
 var nl2br = function (str) {
    return str.replace(/\n/g, '<br>');
};

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

/**
 * PageScroll
 **/
 $(function() {
     $('a.scroll').click(function() {
      if (location.pathname.replace(/^\//,'') == this.pathname.replace(/^\//,'') && location.hostname == this.hostname) {
       var target = $(this.hash);
       target = target.length && target;
       var targetPosition = target.offset().top;
       $('html,body').animate({ scrollTop: targetPosition }, 'slow');
       return false;
   }
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

function initShowHideElement(id) {
    var is_open = _load_cookie(id);
    if (is_open == 0) {
        $(id).hide();
    }
}

function showElement(id) {
    $(id).slideDown('normal');
    _save_cookie(id, 1);
}

function hideElement(id) {
    $(id).slideUp('normal');
    _save_cookie(id, 0);
}

function _save_cookie(id, value) {
    var cookiename = 'ini' + id;
    $.cookie(cookiename, value, { expires: 180, path: '/'});
}

function _load_cookie(id) {
    var cookiename = 'ini' + id;
    return $.cookie(cookiename);
}

function showErrorMessage() {
    blindDownEffect('message_dialog');
}

function displayText(id, str, isShow) {
    if(isShow) {
        if($(id).value == '') {
            $(id).value = str;
        }
    } else {
        if($(id).value == str) {
            $(id).value = '';
        }
    }
}

function showhide(id) {
    if ($(id)) {
        if ($(id).style.display == "none")
            $(id).style.display = "block";
        else
            $(id).style.display = "none";
    }
}

function selectToday(id) {
    var year_id = id + '[year]';
    var month_id = id + '[month]';
    var date_id = id + '[day]';

    var nowdate = new Date();
    var year = nowdate.getFullYear();
    var month  = nowdate.getMonth() + 1;
    var date = nowdate.getDate();

    $(year_id).item(0).value = year;
    $(month_id).item(0).value = month;
    $(date_id).item(0).value = date;
}

function selectDefaultDate(id) {
    var year_id = id + '[year]';
    var month_id = id + '[month]';
    var date_id = id + '[day]';

    $(year_id).item(0).selectedIndex = null;
    $(month_id).item(0).selectedIndex = null;
    $(date_id).item(0).selectedIndex = null;
}

function searchAddress(zip1, zip2, prefecture, city, address) {
    var zip = $(zip1).val() + $(zip2).val();

    $.ajax({
        url : url,
        dataType : "jsonp",
        data : {
            zip : zip
        },
        jsonp: 'jsonp',
        success : function(json){
            //set
            $(prefecture).val(json.prefecture);
            $(city).attr('value', json.city);
            $(address).attr('value', json.address);
        },
        error : function(){
            alert('error');
        }
    });
}
