/**
 * @author  Yohei Yoshikawa
 *
 * Copyright (c) 2013 Yohei Yoshikawa (http://yoo-s.com/)
 */

var spot_diagram_member;

$(document).ready(function(){
    spot_diagram_member = new SpotDiagramMemberController();
});

var SpotDiagramMemberController = function() {
    this.params = {};
    this.name = 'spot_diagram_member';
    this.plot_selectors = {x:'#plot_x', y:'#plot_y'};

    this.plot_image = function(dom) {
        var x = event.offsetX;
        var y = event.offsetY;

        x = Math.round(x);
        y = Math.round(y);

        $(spot_diagram_member.plot_selectors.x).val(x);
        $(spot_diagram_member.plot_selectors.y).val(y);

        var param = '';
        param+= '&plot_x=' + x;
        param+= '&plot_y=' + y;

        var serial =  '&serial=' + new Date().getTime();
        var src = $(dom).attr('org-src') + param + serial;
        $(dom).attr('src', src);

        showLoading();
    }

}
