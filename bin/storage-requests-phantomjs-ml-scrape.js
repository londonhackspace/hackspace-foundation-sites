var system = require('system');
var args = system.args;
var page = require('webpage').create();

setTimeout(function() {
        phantom.exit(1);
}, 15000);

page.onError = function(msg, trace) {
};

page.onConsoleMessage = function(msg) {
//        console.log('> ' + msg);
};

page.open('https://groups.google.com/forum/#!topicsearchin/london-hack-space/subject$3A%22Storage$20Request$20$23'+args[1]+'$3A%22', function() {
        page.includeJs("https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js", function() {
                page.onCallback = function(data) {
                        if (data.posts) {
                                setTimeout(function() {
                                        console.log('Posts found ' + data.posts);
                                        phantom.exit();
                                }, 0);
                        }
                };

                var checkPosts = function() {
                        page.evaluate(function() {
                                var threadsSelector = "table[role='list'],div[role='list']";
                                if (jQuery(threadsSelector).length) {
                                        var threads = jQuery(threadsSelector).html();
                                        var re = />(\d+) posts?</g;
                                        var posts = 0;
                                        var match = null;
                                        while (match = re.exec(threads)) {
                                                posts += Number(match[1]);
                                        }
                                        callPhantom({'posts': posts});
                                };
                        });
                        setTimeout(checkPosts, 1000);
                };
                checkPosts();
        });
})
