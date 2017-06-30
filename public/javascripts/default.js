/**
 * @author  Yohei Yoshikawa
 *
 * Copyright (c) 2013 Yohei Yoshikawa (http://yoo-s.com/)
 */

/**
 * draggable
 */
$(function() {
	$(".draggable").draggable({
		snap: ".gray",
		snapTolerance: 50,
		opacity: 0.5
	});
});

/**
 * layzy load
 **/
$(function() {
    $("img.lazy").lazyload({
            placeholder : "images/loading-01.gif",
            effect : "fadeIn"
        }
    );
});

/**
 * layzy load sample
 **/
function loadSampleHtmlTag(i) {
    var tag = '';
    var file = 'sample1.jpg';
    if (i % 2 == 0) {
        file = 'sample2.jpg';
    } else if (i % 3 == 0) {
        file = 'sample3.jpg';
    } else {
        file = 'sample1.jpg';
    }
    tag+= '<div>';
    tag+= '<h2>' + file + '</h2>';
    tag+= '<p><img src="../images/' + file + '" /></p>';
    tag+= '</div>';
    return tag;
}

/**
 * bottom scroll sample
 **/
$(function() {
    var bottom_id = '#bottom';
    var loading_image_url = 'images/loader.gif';
    var loading_tag = '<div id="loading" style="text-align: center;"><img src="' + loading_image_url +'" /></div>';
    var i = 1;
    var delay = 1000;

    //Add HTML tag
    var html = '';
    for (var i = 1; i <=3; i++) {
        html+= loadSampleHtmlTag(i);
    }

    $(window).bottom({proximity: 0.05});
    $(window).on('bottom', function() {
        var obj = $(this);
        if (!obj.data('loading')) {
            obj.data('loading', true);

            //AJAX URL
            //var url = '';
            $(bottom_id).append(loading_tag);
            setTimeout(function() {
                obj.data('loading', false);
                $('#loading').remove();
                $(bottom_id).append(html);

                //AJAX connect
                /*
                $.ajax({
                    type: 'GET',
                    url: url,
                    dataType: 'html',
                    success: function(data) {
                        $(bottom_id).append(data);
                    },
                    error:function() {
                    }
                });
                */
            }, delay);
        }
    });
    $('html,body').animate({ scrollTop: 0 }, '1');
});

