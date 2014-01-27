
function parse_date(string) {
	var date = new Date();
	var parts = String(string).split(/[- :]/);

	date.setFullYear(parts[0]);
	date.setMonth(parts[1] - 1);
	date.setDate(parts[2]);
	date.setHours(parts[3]);
	date.setMinutes(parts[4]);
	date.setSeconds(parts[5]);
	date.setMilliseconds(0);

	return date;
}


var $j_v = jQuery.noConflict();

$j_v.getJSON("/vc_display.php?sortBy=day&output_type=json&top_max=10" , function(data) {
    var visit_count = $j_v('p#visitors-top-statspage');
    var tbl_body = '<ul style="list-style: none; -webkit-padding-start:0px;">';
    $j_v.each(data, function() {
        var tbl_row = "";
        tbl_row += '<span class="sp-visitors-date">' + this.day + ' → </span>' + '<span class="sp-visitors-count">' + this.counter + '</span>'
        tbl_body += "<li>" + tbl_row + "</li>";                 
    })
    tbl_body += "</ul>"
    visit_count.html(tbl_body);

    //Testing
    //console.log(data);

    //JSON example: [{"day":"2014-01-26","counter":"1"},{"day":"2014-01-25","counter":"1"},{"day":"2014-01-24","counter":"1"}]
});