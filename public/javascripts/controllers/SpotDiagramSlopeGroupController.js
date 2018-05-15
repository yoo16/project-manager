/**
 * @author  Yohei Yoshikawa
 *
 * Copyright (c) 2013 Yohei Yoshikawa (http://yoo-s.com/)
 */

var spot_diagram_slope_group;

$(document).ready(function(){
    spot_diagram_slope_group = new SpotDiagramSlopeGroupController();
});

var SpotDiagramSlopeGroupController = function() {
    this.params = {};
    this.name = 'spot_diagram_slope_group';
    this.plot_selector = {};
    this.plot_selectors = {
        'plot':{'x':'#plot_x', 'y':'#plot_y'}
    };

    this.plot_image = function(dom) {
        spot_diagram_slope_group.plot_selector = spot_diagram_slope_group.plot_selectors.plot;

        if (!spot_diagram_slope_group.plot_selector.x) return;
        if (!spot_diagram_slope_group.plot_selector.y) return;

        var x = event.offsetX;
        var y = event.offsetY;

        x = Math.round(x);
        y = Math.round(y);

        $(spot_diagram_slope_group.plot_selector.x).val(x);
        $(spot_diagram_slope_group.plot_selector.y).val(y);

        var plot_x = $(spot_diagram_slope_group.plot_selectors.plot.x).val();
        var plot_y = $(spot_diagram_slope_group.plot_selectors.plot.y).val();

        var param = '';
        param+= '&slope_group_id=' + $('#plot_slope_group_id').val();
        param+= '&plot_x=' + plot_x;
        param+= '&plot_y=' + plot_y;

        var serial =  '&serial=' + new Date().getTime();
        var src = $(dom).attr('org-src') + param + serial;

        $(dom).attr('src', src);

        showLoading();
    }

    this.change_plot_mode = function(dom) {
        var mode = $(dom).attr('mode');
        if (!mode) return;

        var selector = spot_diagram_slope_group.plot_selectors[mode];
        spot_diagram_slope_group.plot_selector.x = selector.x;
        spot_diagram_slope_group.plot_selector.y = selector.y;
    }

}
