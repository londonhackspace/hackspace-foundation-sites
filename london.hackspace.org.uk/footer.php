        <? if (!isset($hide_menu)) { ?>
        </div><!-- end of non-menu-content -->
        <? } ?>
    </div>
    <!-- End of Main Body section -->
    
    
    <div id="ft" class="container_12">
        <div class="grid_12">
            <p>
                Copyright &copy; <?=date('Y')?> London Hackspace Ltd. Site kindly hosted by <a href="http://www.bitfolk.com">Bitfolk</a>.<br/>
            </p>
        </div>
    </div>
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

</body>
</html>
