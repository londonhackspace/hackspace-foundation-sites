            </div>
        </div>
    </div>

    <div id="ft">
        Copyright &copy; <?=date('Y')?> London Hackspace Ltd.
    </div>
</div>
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
</body>
</html>
