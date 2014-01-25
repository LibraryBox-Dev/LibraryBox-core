var $j = jQuery.noConflict();

// Totally unfinished until vc_display.php works

$j.getJSON("/vc_display.php?sortBy=day&output_type=json" , function(data) {
    var files_top = $j('p#visitors-top-statspage');
    var tbl_body = '<ul style="list-style: none; -webkit-padding-start:0px;">';
    $j.each(data, function() {
        var tbl_row = "";
        tbl_row += '<span class="sp-tops-title"><a href="' + this.url + '">' + this.filename + '</a></span><br /><span class="sp-tops-count">&nbsp;&nbsp;' + this.counter + ' download(s)</span>';
        tbl_body += "<li>"+tbl_row+"</li>";                 
    })
    tbl_body += "</ul>"
    files_top.html(tbl_body);

    //Testing
    //console.log(data);
});