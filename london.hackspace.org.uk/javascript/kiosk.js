$(function() {
    // Swallow function key presses so F1 doesn't load the help page
    var functionKeys = new Array(112, 113, 114, 115, 116, 117, 118, 119, 120, 121, 123);
    $(document).keydown(function(e) {
        if (functionKeys.indexOf(e.keyCode) > -1 || functionKeys.indexOf(e.which) > -1) {
                e.preventDefault();
        }
    });

    /*
     * this swallows backspace keys on any non-input element.
     * stops backspace -> back
     */
    var rx = /INPUT|SELECT|TEXTAREA/i;

    $(document).bind("keydown keypress", function(e){
        if( e.which == 8 ){ // 8 == backspace
            if(!rx.test(e.target.tagName) || e.target.disabled || e.target.readOnly ){
                e.preventDefault();
            }
        }
        if (e.which == 27) { // Send people back to the root page on escape
            window.location = '/kiosk/';
        }
    });

    $(document).bind("contextmenu", function (e) {
        e.preventDefault();
    });
});
