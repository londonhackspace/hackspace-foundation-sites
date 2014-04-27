$(window).bind('resize', function() {
      $('.collapse, .navbar-collapse').removeAttr('style');
})

/* Persist closed alerts in the session. Set
 * data-persist="session_attribute_name" on the close button
 * and modify set_session.php to whitelist. */
$('.alert .close[data-persist]').click(function(e) {
  var attribute = $(this).attr('data-persist');
  $.ajax({
    type: 'post',
    url: '/set_session.php',
    data: {attribute: 'true'}
  })
})
