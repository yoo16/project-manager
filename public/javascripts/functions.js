/**
 * function.js
 * 
 * Copyright (c) 2013 Yohei Yoshikawa (http://yoo-s.com/)
 */

 $(document).on('click', '.change-show-table', function() {
    var target = '#' + $(this).attr('rel');
    if ($(this).prop('checked')) {
        $(target).show();
    } else {
        $(target).hide();
    }
});


$(document).on('click', '.change-show-all-table', function() {
    $('.db-table').show();
    $('.change-show-table').prop('checked', true);
});

$(document).on('click', '.change-hide-all-table', function() {
    $('.db-table').hide();
    $('.change-show-table').prop('checked', false);
});

 $(document).on('click', '.confirm-dialog', function() {
    $message = $(this).attr('message');
    return confirm($message);
});

 $(document).on('click', '.action-close', function() {
    var window_id = '#' + $(this).attr('window-id');
    $(window_id).hide();
});

 $(document).on('change', '.action-change-date', function() {
    checkDate(this);
});

 $(function(){
    $('.update-sortable').hide();
});

$(document).on('click', '.change-sortable', function() {
    var id = '#' + $(this).attr('rel') + ' tbody';
    var sort_orders;
    $('.update-sortable').show();

    $(id).sortable();
    $(id).disableSelection();
    $(id).sortable({
        update: function(ev, ui) {
            sort_orders = $(id).sortable('toArray');
        }
    });

    $(document).on('click', '.update-sortable', function() {
        var controller = $(this).attr('controller');
        var url = projectUrl() + controller + '/update_sort';
        var params = {sort_order: sort_orders};
        postApi(url, params, callback);


        function callback(data) {
            $(id).sortable('disable');
            $('.update-sortable').hide();
        } 
    });
});


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

/**
 * 改行をBRタグに変換
 * 
 * @param String str 変換したい文字列
 */
 var nl2br = function (str) {
    return str.replace(/\n/g, '<br>');
};

function renderHtml(html_id, data) {
    console.log(html_id);
    $(html_id).html(data);
}

function _add_sort_handler(id_table_name) {
    $(document).ready(function() {
        $(id_table_name).tableDnD();
        $(id_table_name).tableDnD({
            onDrop: function(table, row) {
                var rows = table.tBodies[0].rows;
                var sort_order = 0;
                for (var i=0; i<rows.length; i++) {
                    var sort_order_id = "#sort_order_" + rows[i].id;
                    if ($(sort_order_id) != undefined) {
                        $(sort_order_id).val(sort_order);
                        sort_order+= 10;
                    }
                }
            },
            onDragClass: "dragHandle"
        });
    });
}

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
 * autoInputTel()
 *
 **/
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
 * validate()
 *
 **/
 function validateEmpty(id, message) {
    if ($(id).value == '') {
        alert(message);
        return true;
    } else {
        return false;
    }
}

function auto_item_name_input(from_id, to_id) {
    var value = $(from_id).children(':selected').text();
    $(to_id).attr('value', value);
}

function furiganaCheck(id) {
   var value = $(id).attr('value');
   if(value.match( /[^ぁ-んァ-ン　\s]+/ ) ) {
      alert("ふりがなは、「ひらがな」・「カタカナ」のみで入力して下さい。");
      return 1;
  }
  return 0;
}

function alphabetCheck(id) {
   var value = $(id).attr('value');
   if(value.match( /[^A-Za-z\s.-]+/ ) ) {
      alert("半角英文字のみで入力して下さい。");
      return 1;
  }
  return 0;
}

function numberCheck(id) {
   var value = $(id).attr('value');
   if(value.match( /[^0-9]+/ ) ) {
      alert("半角数字のみで入力して下さい。");
      /* $(id).attr('value', ''); */
      return 1;
  }
  return 0;
}

function numbersCheck(id) {
   var value = $(id).attr('value');
   if(value.match( /[^-0-9]+/ ) ) {
      alert("半角数字のみで入力して下さい。");
      /* $(id).attr('value', ''); */
      return 1;
  }
  return 0;
}

function AllCheck() {
   var check = 0;
   check += furiganaCheck();
   check += AlphabetCheck();
   check += numberCheck();
   if( check > 0 ) {
      return false;
  }
  return check;
}

function form_action_confirm(){
    if (confirm('削除してもよろしいですか？')){
        showIndicator();
        return true;
    } else {
        return false;
    }
}

function openWindow(id) {
    var display = ($(id).css('display'));
    if (display == "none") {
        $(id).fadeIn("normal");
    } else {
        $(id).fadeOut("normal");
    }
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

$(function() {
    var clickEvent = function(event) {
        var id = '#' + $(this).attr('rel');
        $('div.form').css('display', 'none'); 
        $(id).css('display', 'block'); 
    }
    $("a.change_page").each(function(i) {
        $(this).click(clickEvent);
        $(this).keypress(clickEvent);
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


 function onClickUrlLink(url) {
    if (url) {
        window.open(url, 'new', 'scrollbars=yes,width=520,height=640');
    }
}

function rollover() {
    $('.swap').rollover();
}

function highlight_image(target) {
    var img = $(target);
    var src = img.attr('src');
    if (src.lastIndexOf('_on.') > 0) {
        return;
    }
    var src_on = src.substr(0, src.lastIndexOf('.'))
    + '_on'
    + src.substring(src.lastIndexOf('.'));
    img.attr('src', src_on);
}

function highlight_off_image(target) {
    var img = $(target);
    var src = img.attr('src');
    if (src.lastIndexOf('_on.') > 0) {
        var src_on = src.substr(0, src.lastIndexOf('_on'))
        + src.substring(src.lastIndexOf('.'));
        img.attr('src', src_on);
    }
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

function showIndicator() {
    id = 'indicator'
    window.scroll(0, 0);
    $(id).style.display = "block";
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


// function drawRelation(from_pg_class_id, from_attnum, to_pg_class_id, to_attnum) {
//     var from_jquery_id = '#' + from_pg_class_id + '-' + from_attnum
//     var to_jquery_id = '#' + to_pg_class_id + '-' + to_attnum

//     var from = new Object();
//     var to = new Object();
//     var position1 = new Object();
//     var position2 = new Object();
//     var offset_x = 5;
//     var offset_y = 15;

//     var from_position = $(from_jquery_id).offset();
//     var to_position = $(to_jquery_id).offset();

//     from.x = from_position.left + offset_x;
//     from.y = from_position.top + offset_y;
//     to.x = to_position.left + offset_x;
//     to.y = to_position.top + offset_y;

//     var offset_x = - 10 + line_offset_x;
//     var offset_y = 0;
//     if ((from.y >= to.y) && (from.x >= to.x)) {
//        arrow_direction = 'r';
//        offset_y = -2;
//        position1.x = to.x + offset_x;
//        position1.y = from.y;
//        position2.x = position1.x;
//        position2.y = to.y; 
//     } else if ((from.y >= to.y) && (from.x < to.x)) {
//        arrow_direction = 'l';
//        offset_y = 2;
//        position1.x = to.x + offset_x;
//        position1.y = from.y;
//        position2.x = to.x + offset_x;
//        position2.y = to.y;
//     } else if ((from.y < to.y) && (from.x >= to.x)) {
//        arrow_direction = 'r';
//        offset_y = 4;
//        position1.x = to.x + offset_x;
//        position1.y = from.y;
//        position2.x = to.x + offset_x;
//        position2.y = to.y;
//     } else if ((from.y < to.y) && (from.x < to.x)) {
//        arrow_direction = 'r';
//        offset_y = -4;
//        from.y+= offset_y;
//        position1.x = from.x + offset_x;
//        position1.y = from.y;
//        position2.x = from.x + offset_x;
//        position2.y = to.y;
//     }

//     console.log(from_jquery_id);
//     console.log(from_position.left);
//     var canvas = document.getElementById("db_canvas");
//     var context = canvas.getContext("2d");
//     context.beginPath();

//     context.moveTo(from.x, from.y);
//     context.lineTo(position1.x, position1.y);

//     context.moveTo(position1.x, position1.y);
//     context.lineTo(position2.x, position2.y);

//     context.moveTo(position2.x, position2.y);
//     context.lineTo(to.x, to.y);

//     lineJoin = "bevel";
//     context.lineWidth = 1;
//     context.strokeStyle = "#dc143c";
//     context.globalAlpha = 0.4;
//     context.stroke(); 

//     //drawMark(from.x, from.y, 3);
//     drawArrow(from.x, from.y);

//     line_offset_x-= 2;
//     if (line_offset_x < -20) {
//     line_offset_x = 0;
// }