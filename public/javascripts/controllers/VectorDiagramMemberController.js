/**
 * @author  Yohei Yoshikawa
 *
 * Copyright (c) 2013 Yohei Yoshikawa (http://yoo-s.com/)
 */

var vector_diagram_member;

$(document).ready(function(){
    vector_diagram_member = new VectorDiagramMemberController();
});

var VectorDiagramMemberController = function() {
    this.params = {};
    this.name = 'vector_diagram_member';
    this.plot_selectors = {x:'#plot_x', y:'#plot_y'};

    this.plot_image = function(dom) {
        var x = event.offsetX;
        var y = event.offsetY;

        x = Math.round(x);
        y = Math.round(y);

        $(vector_diagram_member.plot_selectors.x).val(x);
        $(vector_diagram_member.plot_selectors.y).val(y);

        var plot = '';
        plot+= '&plot_x=' + x;
        plot+= '&plot_y=' + y;

        var serial =  '&serial=' + new Date().getTime();
        var src = $(dom).attr('org-src') + plot + serial;
        $(dom).attr('src', src);

        showLoading();
    }

}
