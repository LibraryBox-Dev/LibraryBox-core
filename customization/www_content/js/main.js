$(document).ready(function() {
	
	$('#menu-icon').click(function () {
		$('#top-nav ul').slideToggle(500);
		console.log('clicked menu icon');
	});

	$(window).resize(function() {
        if ($('#menu-icon').is(':visible')) {
            $('#top-nav ul').hide();
        } else {
            $('#top-nav ul').show();
        }
    });

});