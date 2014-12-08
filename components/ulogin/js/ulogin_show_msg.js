if ( (typeof jQuery === 'undefined') && !window.jQuery ) {
    document.write(unescape("%3Cscript type='text/javascript' src='//ajax.googleapis.com/ajax/libs/jquery/1.8.0/jquery.min.js'%3E%3C/script%3E%3Cscript type='text/javascript'%3EjQuery.noConflict();%3C/script%3E"));
} else {
    if((typeof jQuery === 'undefined') && window.jQuery) {
        jQuery = window.jQuery;
    } else if((typeof jQuery !== 'undefined') && !window.jQuery) {
        window.jQuery = jQuery;
    }
}


jQuery(function(){
    var ulogin_messages_module = jQuery('.ulogin_messages_module');
    if (ulogin_messages_module.length == 0) { return; }

    var ulogin_messages = ulogin_messages_module.find('.ulogin_messages_modulebody');
    if (ulogin_messages.html() != '') {
        ulogin_messages.addClass('sess_messages');
        ulogin_messages_module.show();
    }
});