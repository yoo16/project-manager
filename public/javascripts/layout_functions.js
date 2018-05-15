$(document).ready(function() {

    var client_image_path = '';
    if (project_name) {
        client_image_path = '/' + project_name + '/images/';
    } else {
        client_image_path = '/images/';
    }
   
    var now = new Date();
    var end_year = now.getFullYear() + 10;
    var year_range = "2000:" + end_year;
    $('.datepicker').datepicker({
        showOn: "button",
        buttonImage: client_image_path + "calendar.gif",
        buttonImageOnly: true,
        changeMonth: true,
        changeYear: true,
        yearRange: year_range,
        showButtonPanel: true,
        monthNamesShort: [1,2,3,4,5,6,7,8,9,10,11,12],
        dateFormat: 'yy-mm-dd',
    });
    $('.datetimepicker').datepicker({
        showOn: "button",
        buttonImage: client_image_path + "calendar.gif",
        buttonImageOnly: true,
        changeMonth: true,
        changeYear: true,
        yearRange: year_range,
        showButtonPanel: true,
        monthNamesShort: [1,2,3,4,5,6,7,8,9,10,11,12],
        dateFormat: 'yy-mm-dd 00:00',
    });

    jQuery.datepicker.setDefaults(jQuery.datepicker.regional['ja']);
    $.fn.jPicker.defaults.images.clientPath = client_image_path;
    $('.colorpicker').jPicker(
       {
       },
       function(color, context)
       {
          var all = color.val('all');
          if (all.hex) {
              var color_string = '#' + all.hex;
              $(this).val(color_string);
          }
        },
    );
});
