/**
 * jQueryRollover v1.0
 * http://rewish.org/javascript/jquery_rollover_plugin
 *
 * Copyright (c) 2009 Rewish (http://rewish.org/)
 *
 * Licensed under the MIT:
 * [en] http://www.opensource.org/licenses/mit-license.php
 * [ja] http://sourceforge.jp/projects/opensource/wiki/licenses%2FMIT_license
 *
 * Inspired by:
 * Telepath Labs (http://dev.telepath.co.jp/labs/article.php?id=15)
 *
 * Usage:
 * jQuery(document).ready(function($) {
 *   // <img>
 *   $('div#nav a img').rollover();
 *
 *   // <input type="image">
 *   $('form input:image').rollover();
 *
 *   // set postfix
 *   $('div#nav a img').rollover('_over');
 * });
 *
 **/
(function($) {
	$.fn.rollover = function(postfix) {
		postfix = (postfix != null) ? postfix : '_on';
		return this.not('[src*="'+ postfix +'."]').each(function() {
			var img = $(this);
			var src = img.attr('src');
			var src_on = src.substr(0, src.lastIndexOf('.'))
			           + postfix
			           + src.substring(src.lastIndexOf('.'));
			$('<img>').attr('src', src_on);
			img.hover(
				function() {
					img.attr('src', src_on);
				},
				function() {
					img.attr('src', src);
				}
			);
		});
	};
})(jQuery);
