        <? if (!isset($hide_menu)) { ?>
        </div><!-- end of non-menu-content -->
        <? } ?>
    </div>
    <!-- End of Main Body section -->
<? if (!isset($hide_copyright)){
    <footer id="ft">
        <p>Copyright &copy; <?=date('Y')?> London Hackspace Ltd. Site kindly hosted by <a href="http://www.bitfolk.com">Bitfolk</a>.<br/></p>
    </footer>
<? } ?>
</div><!-- doc -->

<? if (isset($show_twitter_feed)) { ?>
<script>
!function(d,s,id){
    var js,fjs=d.getElementsByTagName(s)[0];
    if(!d.getElementById(id)){
        js=d.createElement(s);
        js.id=id;
        js.src="//platform.twitter.com/widgets.js";
        fjs.parentNode.insertBefore(js,fjs);
    }
}(document,"script","twitter-wjs");
</script>
<? } ?>


<script type="text/javascript">

  var _gaq = _gaq || [];
  _gaq.push(['_setAccount', 'UA-7698227-2']);
  _gaq.push(['_trackPageview']);

  (function() {
    var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
  })();

</script>
<script type="text/javascript" src="/javascript/jquery-1.10.2.min.js"></script>
<script type="text/javascript">
// homepage cleanup events for touch devices and prevent collapseable sections for wide screens
if($('#home-page-container').length > 0) {
	var moved = false;
    $('#home-page-container h3').bind('touchmove', function(e) {
    	moved = true;
	});
    $('#home-page-container h3').bind('touchend', function(e) {
    	e.preventDefault();
    	e.stopPropagation();
        if($(window).width() <= 480 && !moved) {
        	$(this).trigger('click');
        }
        moved = false;
       	return false;
	});
    $('#home-page-container h3').bind('click', function(e) {
        //alert($(window).width());
        if($(window).width() > 480) {
	        e.preventDefault();
            e.stopPropagation();
        }
    });
}
$(window).bind('resize', function() {
    $('.collapse, .navbar-collapse').removeAttr('style');
})
</script>
<script type="text/javascript" src="/javascript/bootstrap.min.js"></script>
</body>
</html>
