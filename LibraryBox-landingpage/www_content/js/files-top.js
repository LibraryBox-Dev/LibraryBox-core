var $j = jQuery.noConflict();

$j(document).ready(function() {
    var top_files_para = $j('#files-top-default');
    // We don't need to specify top_max here to get a top 10 list, because
    // it defaults to 10.
    $j.get( "dl_statistics_display.php?sortBy=counter&list_type=top&output_type=json", function( data ) {
        var items = [];
        data_json = $j.parseJSON(data);
        // just to simplify testing. remove later.
        var jquery_testing = $j('#jquery-testing');
        jquery_testing.html(data_json)
        $.each( data_json, function( url, counter ) {
            // more testing
            alert("URL is " + url + " and counter is " + counter);
            items.push( '<li><a href="' + url '">' + url + '</a> (' + counter + 'download(s))</li>' );
        });
        var top_files_list = $j( "<ul/>", {
                                  "id": "files-top-generated",
                                  html: items.join( "" )
                              })        
        top_files_para.replaceWith(top_files_list);
    });
});