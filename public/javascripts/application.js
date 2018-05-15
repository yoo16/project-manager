var loc = location.pathname,
dir = loc.substring(0, loc.lastIndexOf('/')) + '/';

var client_image_path = '';
if (pw_project_name) {
    client_image_path = '/' + pw_project_name + '/images/';
} else {
    client_image_path = '/images/';
}