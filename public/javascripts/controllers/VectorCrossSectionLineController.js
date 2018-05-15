/**
 * @author  Yohei Yoshikawa
 *
 * Copyright (c) 2013 Yohei Yoshikawa (http://yoo-s.com/)
 */

var vector_cross_section_line;

$(document).ready(function(){
    vector_cross_section_line = new VectorCrossSectionLineController();
});

var VectorCrossSectionLineController = function() {
    this.params = {};
    this.name = 'vector_cross_section_line';
    this.plot_selector = {};
    this.plot_selectors = {
        'start':{'x':'#plot_start_x', 'y':'#plot_start_y'},
        'end':{'x':'#plot_end_x', 'y':'#plot_end_y'}
    };

    this.plot_image = function(dom) {
        if (!vector_cross_section_line.plot_selector.x) return;
        if (!vector_cross_section_line.plot_selector.y) return;

        var x = event.offsetX;
        var y = event.offsetY;

        x = Math.round(x);
        y = Math.round(y);

        $(vector_cross_section_line.plot_selector.x).val(x);
        $(vector_cross_section_line.plot_selector.y).val(y);

        var x1 = parseInt($(vector_cross_section_line.plot_selectors.start.x).val());
        var y1 = parseInt($(vector_cross_section_line.plot_selectors.start.y).val());
        var x2 = parseInt($(vector_cross_section_line.plot_selectors.end.x).val());
        var y2 = parseInt($(vector_cross_section_line.plot_selectors.end.y).val());

        var param = '';
        param+= '&cross_section_line_id=' + $('#cross_section_line_id').val();
        param+= '&start_x=' + x1;
        param+= '&start_y=' + y1;
        param+= '&end_x=' + x2;
        param+= '&end_y=' + y2;

        var serial =  '&serial=' + new Date().getTime();
        var src = $(dom).attr('org-src') + param + serial;
        $(dom).attr('src', src);
        
        showLoading();
    }

    this.change_plot_mode = function(dom) {
        var mode = $(dom).attr('mode');
        if (!mode) return;

        var selector = vector_cross_section_line.plot_selectors[mode];
        vector_cross_section_line.plot_selector.x = selector.x;
        vector_cross_section_line.plot_selector.y = selector.y;
    }

}
