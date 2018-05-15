/**
 * @author  Yohei Yoshikawa
 *
 * Copyright (c) 2013 Yohei Yoshikawa (http://yoo-s.com/)
 */

var vector_diagram_slope_group;

$(document).ready(function(){
    vector_diagram_slope_group = new VectorDiagramSlopeGroupController();
});

var VectorDiagramSlopeGroupController = function() {
    this.params = {};
    this.name = 'vector_diagram_slope_group';
    this.plot_selector = {};
    this.plot_selectors = {
        'plot':{'x':'#plot_x', 'y':'#plot_y'},
        'hole_top':{'x':'#plot_hole_top_x', 'y':'#plot_hole_top_y'},
        'hole_bottom':{'x':'#plot_hole_bottom_x', 'y':'#plot_hole_bottom_y'}
    };

    this.plot_image = function(dom) {
        if (!vector_diagram_slope_group.plot_selector.x) return;
        if (!vector_diagram_slope_group.plot_selector.y) return;

        var x = event.offsetX;
        var y = event.offsetY;

        x = Math.round(x);
        y = Math.round(y);

        $(vector_diagram_slope_group.plot_selector.x).val(x);
        $(vector_diagram_slope_group.plot_selector.y).val(y);

        var plot_x = $(vector_diagram_slope_group.plot_selectors.plot.x).val();
        var plot_y = $(vector_diagram_slope_group.plot_selectors.plot.y).val();
        var x1 = parseInt($(vector_diagram_slope_group.plot_selectors.hole_top.x).val());
        var y1 = parseInt($(vector_diagram_slope_group.plot_selectors.hole_top.y).val());
        var x2 = parseInt($(vector_diagram_slope_group.plot_selectors.hole_bottom.x).val());
        var y2 = parseInt($(vector_diagram_slope_group.plot_selectors.hole_bottom.y).val());

        if (x2 < x1) {
            x2 = x1 + 50;
            $(vector_diagram_slope_group.plot_selectors.hole_bottom.x).val(x2);
        }
        if (y2 < y1) {
            y2 = y1;
            $(vector_diagram_slope_group.plot_selectors.hole_bottom.y).val(y2);
        }

        var param = '';
        param+= '&plot_x=' + plot_x;
        param+= '&plot_y=' + plot_y;
        param+= '&hole_top_x=' + x1;
        param+= '&hole_top_y=' + y1;
        param+= '&hole_bottom_x=' + x2;
        param+= '&hole_bottom_y=' + y2;

        var serial =  '&serial=' + new Date().getTime();
        var src = $(dom).attr('org-src') + param + serial;

        $(dom).attr('src', src);

        showLoading();
    }

    this.change_plot_mode = function(dom) {
        var mode = $(dom).attr('mode');
        if (!mode) return;

        var selector = vector_diagram_slope_group.plot_selectors[mode];
        vector_diagram_slope_group.plot_selector.x = selector.x;
        vector_diagram_slope_group.plot_selector.y = selector.y;
    }

}
