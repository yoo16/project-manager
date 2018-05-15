var is_show_sortable = false;

/**
 * hide sortable
 *
 * @param
 * @return
 */
 $(function(){
    $('.update-sortable').hide();
    $('.close-sortable').hide();
});

/**
 * change slortable
 *
 * @param
 * @return
 */
 $(document).on('click', '.change-sortable', function() {
    if (is_show_sortable) return;

    var table_id = $(this).attr('table_id');
    if (!table_id) table_id = 'sortable-table';
    var id = '#' + table_id + ' tbody';
    var table_tr_selector = id + ' tr';

    $(id).sortable({
        cursor: 'move',
        opacity: 0.8,
        placeholder: 'sortable-selected',
        scroll: true,
        axis: 'y',
        delay: 100
    });
    $(id).sortable('enable');
    $(id).sortable({
        update: function(ev, ui) {
            after_sort_orders = $(id).sortable('toArray');
        }
    });

    var before_sort_orders = $(id).sortable('toArray');
    var after_sort_orders = before_sort_orders;

    $('.update-sortable').show();
    $('.close-sortable').show();
    $('.sortable-control').show();

    showSortableControl(table_id);

    /**
     * sortable control
     * 
     * @return void
     */
    function showSortableControl(table_id) {
        is_show_sortable = true;
        var header_selector = '#' + table_id + ' tr';
        var header_tr = $(header_selector).first();

        var sortable_control_header_tag = '<th class="sortable-control"></th>';
        if (header_tr) header_tr.prepend(sortable_control_header_tag);

        var sortable_control_tag = '<td class="sortable-control" nowrap="nowrap"></td>';
        $(table_tr_selector).map(function() {
            $(this).prepend(sortable_control_tag);
        });

        var link_tag = '';
        link_tag+= '<a><i class="fa fa-align-justify"></i></a>';
        link_tag+= '<a class="change-sortable-top btn btn-sm"><i class="fa fa-angle-double-up"></i></a>';
        link_tag+= '<a class="change-sortable-bottom btn btn-sm"><i class="fa fa-angle-double-down"></i></a>';
        $('td.sortable-control').html(link_tag);
        $('.sortable-control').show();
    }

    /**
     * hide sortable control
     * 
     * @return void
     */
    function hideSortableControl() {
        $('.sortable-control').remove();
    }

    /**
     * close sortable
     * 
     * @return void
     */
    function finishSortable() {
        is_show_sortable = false;
        $(id).sortable('disable');
        $('.update-sortable').hide();
        $('.close-sortable').hide();
        $('.sortable-control').hide();
        hideSortableControl();
        hideLoading();
    }

    $(document).on('click', '.change-sortable-top', function() {
         var first_tr = $(table_tr_selector).first();
         if (first_tr) {
             var row = $(this).closest('tr');
             row.insertBefore(first_tr);
             after_sort_orders = $(id).sortable('toArray');
         }
    });

    $(document).on('click', '.change-sortable-bottom', function() {
         var last_tr = $(table_tr_selector).last();
         if (last_tr) {
             var row = $(this).closest('tr');
             row.insertAfter(last_tr);
             after_sort_orders = $(id).sortable('toArray');
         }
    });

    $(document).on('click', '.update-sortable', function() {
        var sort_orders = {};
        $.each(before_sort_orders, function(index, value){
            var update_id = after_sort_orders[index];
            if (value != update_id) {
                sort_orders[update_id] = index;
            }
        });
        if (!sort_orders) return;
        
        showLoading();

        var params = {sort_order: sort_orders};
        pw_app.post(this, params, callback);

        function callback(data) {
            $(id).sortable('refresh');
            before_sort_orders = after_sort_orders;
            finishSortable();
        } 
    });

    $(document).on('click', '.close-sortable', function() {
        finishSortable();
    });

});