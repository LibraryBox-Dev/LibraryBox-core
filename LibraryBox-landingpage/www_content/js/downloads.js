// $.get(
//     "/dl_statistics_display.php?sortOrder=DESC&sortBy=counter&list_type=top&top-max=50",

// );

var $j_d = jQuery.noConflict();

$j_d.getJSON("/dl_statistics_display.php?sortBy=counter&sortOrder=DESC&list_type=top&top-max=50&output_type=json" , function(data) {
    var files_top = $j_d('p#files-top-statspage');
    var tbl_body = '<ul style="list-style: none; -webkit-padding-start:0px;">';
    $j_d.each(data, function() {
        var tbl_row = "";
        tbl_row += '<span class="sp-tops-title"><a href="/dl_statistics_counter.php?DL_URL=' + this.url_encoded + '">' + decodeURIComponent ( this.filename_encoded )  + '</a></span><br /><span class="sp-tops-count">&nbsp;&nbsp;' + this.counter + ' download(s)</span>';
        tbl_body += "<li>" + tbl_row + "</li>";                 
    })
    tbl_body += "</ul>"
    files_top.html(tbl_body);

    //Testing
    //console.log(data);
});
