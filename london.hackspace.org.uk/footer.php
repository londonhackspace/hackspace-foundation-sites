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

<script type="text/javascript">
var gaJsHost = (("https:" == document.location.protocol) ? "https://ssl." : "http://www.");
document.write(unescape("%3Cscript src='" + gaJsHost + "google-analytics.com/ga.js' type='text/javascript'%3E%3C/script%3E"));
</script>

<script type="text/javascript">
try {
    var pageTracker = _gat._getTracker("UA-7698227-2");
    <? if ($user) {?>
        pageTracker._setVar("Logged In");
    <? }?>
    pageTracker._trackPageview();
} catch(err) {}</script>

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

</body>
</html>
