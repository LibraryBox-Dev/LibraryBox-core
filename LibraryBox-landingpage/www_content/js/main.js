var $j = jQuery.noConflict();
$j(document).ready(function() {
	// toggle nav on-click	
	$j('#menu-icon').click(function () {
		$j('#top-nav ul').slideToggle(500);
		console.log('clicked menu icon');
	});

	// get forban
	$j.get('/forban_link.html', function(data) {
		$j('div#forban_link').html(data);
	});
	
	// get station counter
	$j.get('/station_cnt.txt', function(data) {
		$j('div#station').html(data);
	});

 	// tells you there is a ShoutBox error
 	$j('div#shoutbox').ajaxError(function() {
 		$j(this).text( "Triggered ajaxError handler on shoutbox" );
 	});

	// submits new text to ShoutBox
	$j("#sb_form").submit(function(event) {

		event.preventDefault();
		post_shoutbox();
	});

	$j.getJSON("/config.json" , function(data) {
		var showbox = data.librarybox.module.shoutbox.status;
		if (showbox) {
			display_shoutbox();
		} else {
			document.getElementById("chatcontainer").style.display="none";
		}
	});
	
});

function refresh_shoutbox () {
	$j.get('/chat_content.html', function(data) {
		$j('div#shoutbox').html(data);
	});
}

function refresh_time_sb () {
    // Refresh rate in milli seconds
    mytime=setTimeout('display_shoutbox()', 10000);
  }

  function post_shoutbox () {
  	$j.post("/cgi-bin/psowrte.py" , $j("#sb_form").serialize())
  	.success(function() { 
  		refresh_shoutbox(); 
  	});
  	$j('#shoutbox-input .message').val('');
  }

  function display_shoutbox() {
  	refresh_shoutbox();
  	refresh_time_sb();
  }

  function fnGetDomain(url) {
  	return url.match(/:\/\/(.[^/]+)/)[1];
  }
