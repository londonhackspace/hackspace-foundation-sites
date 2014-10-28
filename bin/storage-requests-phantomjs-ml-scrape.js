var system = require('system');
var args = system.args;
var page = require('webpage').create();

page.open('https://groups.google.com/forum/#!topicsearchin/london-hack-space-test/subject$3A%22Storage$20Request$20$23'+args[1]+'$3A%22', function() {
	page.includeJs("http://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js", function() {
		search = page.evaluate(function() { 
			return $("table[role='list']").html().match(/\d+ post/g);
		});

		var countSinglePosts = 0;
		for(var x=0,len=search.length;x<len;x++) {
			countSinglePosts += parseInt(search[x]);
		}
		console.log('Posts found '+countSinglePosts);
		phantom.exit()
	});
})