var $j_vc = jQuery.noConflict();

var unix_timestamp = Math.round(new Date().getTime() / 1000)

$j_vc.getJSON("/vc_counter.php?client_date=" + unix_timestamp , function(data) {
    var visit_count = $j_vc('span#vc_count');
    var vc_body = '';
    visit_count.html(vc_body);

    //Testing
    //console.log(data);
});
