/**
 * colorpicker.js
 * 
 * Copyright (c) 2013 Yohei Yoshikawa (http://yoo-s.com/)
 */
$(document).ready(function() {

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
        }
    );

});