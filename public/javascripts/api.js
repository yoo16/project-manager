/**
 * @author  Yohei Yoshikawa
 *
 * Copyright (c) 2017 Yohei Yoshikawa (http://yoo-s.com/)
 */

var base_url = '';
var project_name = '';

$(function() {
    $(document).on('click', '.action-api_request', function() {
        $('#result-json').html('');
        $('#result-html').html('');
        
        var method_name = $(this).attr('method-name');
        var url = projectUrl() + 'api/' + method_name;
        var params = {};
        $('.api-params').each(function(index, value) {
            var key = $(this).attr('name');
            var value = $(this).val();

            //TODO ES6
            params[key] = value; 
        });
        // console.log(url);
        jsonApi(url, params, render);

        function render(json) {
            var values = {};

            var render_type = $('input[name=render_type]:checked').val();
            $("#result-json").append(json);
            return;
            if (render_type == 'json') {
                $("#result-json").append(json);
            } else if (render_type == 'html') {
                values = JSON.parse(json);
                for (var key in values) {
                    var type = '';
                    type = Object.prototype.toString.call(values[key]);
                    if (type === '[object Object]' || type === '[object Array]') {
                        $('#result-html').append('<h3>' + key + '</h3>');
                        $('#result-html').append('<table class="table">');
                        for (var column in values[key]) {
                            var value = values[key][column];
                            type = Object.prototype.toString.call(value);

                            if (type === '[object Object]') {
                                for (var column2 in value) {
                                    renderHtml(column2, value[column2]);
                                }
                            } else {
                                renderHtml(column, value);
                            }

                        }
                        $('#result-html').append('</table>');
                    } else {
                         renderHtml(key, values[key]);   
                    }
                }
            }

            function renderHtml(column, value) {
                $('#result-html').append('<tr>');

                th = "<th>" + column + "</th>";
                $('#result-html').append(th);

                td = "<td>" + value + "</td>";
                $('#result-html').append(td);

                $('#result-html').append('</tr>'); 
            }
        }
    });

    $(document).on('click', '.action-api_image', function() {
        $('#result-json').html('');
        $('#result-html').html('');
        
        var method_name = $(this).attr('method-name');
        var url = http_base() + 'image/' + method_name;
        var params = {};
        $('.api-params').each(function(index, value) {
            var key = $(this).attr('name');
            var value = $(this).val();

            if (value) params[key] = value;
        });

        var param = $.param(params);
        url+= '?' + param; 

        $('#url').html(url);
        $('#image').attr('src', url);
    });

    $(document).on('click', '.action-api_test', function() {
        var requests = [
            {
                url: http_base() + 'api/xxx',
                params: {'id': 394},
                callback: callBack1
            },
            {
                url: http_base() + 'api/xxx',
                params: {'id': 396,},
                callback: callBack2
            },
        ];

        parallelAjax(requests, allDoneCallback);

        function callBack1(results) {
            console.log(results);
        }
        function callBack2(results) {
            console.log(results);
        }
        function allDoneCallback(results) {
            console.log(results);
        }
    });

});

var requestAjax = function(values){
    var $ajax = $.ajax(values);
    var defer = new $.Deferred();
    $ajax.done(function(data, status, $ajax) {
        defer.resolveWith(this, arguments);
    });
    $ajax.fail(function(data, status, $ajax) {
        defer.resolveWith(this, arguments);
    });
    return $.extend({}, $ajax, defer.promise());
};

function parallelAjax(requests, callback) {
    var results = [];
    $.each (requests, function(index, value) {
        var $ajax = requestAjax({url: value.url, data: value.params}).done(function(res, status) {
            if (value.callback) {
                value.callback(res);
            }
        });
        results.push($ajax);
    });
    $.when.apply(null, results).done(function(){
        if (callback) callback(results);
    });
    $.when.apply(null, results).fail(function(){
    });
}

function parallelRequest(requests, callback) {
    var XHRList = [];
    $.each (requests, function(index, value) {
        XHRList.push($.ajax({
            type: "POST",
            cache: false,
            url: value.url,
            data: value.params,
        }));
    });
    $.when.apply($, XHRList).done(function () {
        callback(arguments);
    }).fail(function (ex) {
        alert('ajax error');
    });
}


/**
* URL生成    
*
* @param 
* @return String
**/
function http_base() {
    var domain = location.hostname;
    var url;
    if (base_url) {
        url = base_url;
    } else {
        url = '//' + domain + '/';
    }
    return url;
}

/**
* プロジェクトURL生成    
*
* @param 
* @return String
**/
function projectUrl() {
  var base_url = http_base();
  var url = base_url;
  if (project_name) {
      url+= project_name + '/';
  }
  return url;
}


function apiUrl(controller, action) {
    url = projectUrl() + controller + '/' + action;
    return url;
}

/**
* HTML API（POSTアクセス、HTML表示）    
*
* @param String html_id
* @param String url
* @param Object params
* @return void
**/
function htmlApi(url, params, html_id, callback) {
    html_id = '#' + html_id;
    showIndicator();
    $.ajax({
        type: 'POST',
        cache: false,
        url: url,
        data: params,
        dataType: 'html',
        success: function(data) {
            hideIndicator();
            $(html_id).html(data);
            if (callback) callback(data);
        },
        error:function() {
            hideIndicator();
        }
    });
}

/**
* APIアクセス（POSTアクセス、HTML表示）    
*
* @param String html_id
* @param String url
* @param Object params
* @return void
**/
function postApi(url, params, callback) {
    showIndicator();
    $.ajax({
        type: 'POST',
        cache: false,
        url: url,
        data: params,
        dataType: 'html',
        success: function(data) {
            hideIndicator();
            if (callback) callback(data);
        },
        error:function() {
            hideIndicator();
        }
    });
}


var formParseJson = function(form_id) {
    var form = $(form_id);
    var values = {};
    $(form.serializeArray()).each(function(i, v) {
        values[v.name] = v.value;
    });
    var json = JSON.stringify(values)
    return json;
}

/**
* APIアクセス（POSTアクセス、HTML表示）    
*
* @param String html_id
* @param String url
* @param Object params
* @return void
**/
function postForm(form_id, url, params, callback) {
    form_id = '#' + form_id;
    data = parseJson(form_id);

    showIndicator();
    $.ajax({
        type: 'post',
        url: url,
        data: data,
        dataType: 'json',
        success: function(data) {
            hideIndicator();
            if (callback) callback(data);
        },
        error:function() {
            hideIndicator();
        }
    });
}
/**
* APIアクセス（POSTアクセス、HTML表示）    
*
* @param String url
* @param Object params
* @return void
**/
function jsonApi(url, params, callback) {
    $.ajax({
        type: 'POST',
        cache: false,
        url: url,
        data: params,
        dataType: 'json',
        success: function(data) {
            if (callback) callback(data);
        },
        error:function() {
        }
    });
}

/**
* インジケーター表示
*
* @param
* @return void
**/
function showIndicator() {
    window.scroll(0, 0);
    $('#indicator').css('display', 'block');
}

/**
* インジケーター非表示
*
* @param
* @return void
**/
function hideIndicator() {
    $('#indicator').css('display', 'none');
}

/**
 * render html
 * 
 * @param  String html_id [description]
 * @param  String data    [description]
 * @return void
 */
 function renderHtml(html_id, data) {
    $(html_id).html(data);
}