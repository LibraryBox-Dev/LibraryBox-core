$(document).ready(function() {
	// toggle nav on-click	
	$('#menu-icon').click(function () {
		$('#top-nav ul').slideToggle(500);
		console.log('clicked menu icon');
	});

	// get forban
	$.get('/forban_link.html', function(data) {
        $('div#forban_link').html(data);
    });
	
	// get station counter
	$.get('/station_cnt.txt', function(data) {
        $('div#station').html(data);
    });
   	
   	// tells you there is a ShoutBox error
   	$('div#shoutbox').ajaxError(function() {
        $(this).text( "Triggered ajaxError handler on shoutbox" );
    });
	
	// submits new text to ShoutBox
	$("#sb_form").submit(function(event) {
	    /* stop form from submitting normally */
        event.preventDefault();
	    post_shoutbox();
    });

    display_shoutbox();
});


function refresh_shoutbox () {
    $.get('/chat_content.html', function(data) {
   		$('div#shoutbox').html(data);
   	});
}
  
function refresh_time_sb () {
    // Refresh rate in milli seconds
    mytime=setTimeout('display_shoutbox()', 10000);
}

function post_shoutbox () {
	$.post("/cgi-bin/psowrte.py" , $("#sb_form").serialize())
	.success(function() { 
		refresh_shoutbox(); 
	});
	$('#shoutbox-input .message').val('');
}

function display_shoutbox() {
	refresh_shoutbox();
	refresh_time_sb();
}

function fnGetDomain(url) {
	return url.match(/:\/\/(.[^/]+)/)[1];
}
