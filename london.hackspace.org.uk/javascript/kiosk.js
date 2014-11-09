$(function() {
    var functionKeys = new Array(112, 113, 114, 115, 116, 117, 118, 119, 120, 121, 123);
    $(document).keydown(function(e) {
        if (functionKeys.indexOf(e.keyCode) > -1 || functionKeys.indexOf(e.which) > -1) {
                e.preventDefault();
        }
    })
});
