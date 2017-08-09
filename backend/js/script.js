jQuery("#insert-my-portfolio").click(function() {
    jQuery("#cp-popup").show();
    jQuery(".cp-overlay").show();
});

jQuery(".cp-overlay, #cp-gen-cancel").click(function() {
    jQuery("#cp-popup").hide();
    jQuery(".cp-overlay").hide();
});


var confs = jQuery('.hidden-shortcode-cp_confs').text();
jQuery('.hidden-shortcode-cp_confs').text('');

jQuery("#cp-gen-insert").click(function() {

    var newConfs = '';

    if ( jQuery('#order').is(':checked') ) {
        newConfs = newConfs+' order="DESC"';
    }

    if ( jQuery('#showdate').is(':checked') ) {
        newConfs = newConfs+' showdate="false"';
    } 

    if ( jQuery('#showexcerpt').is(':checked') ) {
        newConfs = newConfs+' showexcerpt="true"';
    } 

    var numColumns = jQuery('#numrowsnumber').val();
    if ( numColumns != '' ) {

        if( numColumns < 1 ) numColumns = 1;
        if ( numColumns > 5 ) numColumns = 5;

        newConfs = newConfs+' columns="'+numColumns+'"';
    } 
    
    var showlimitnumber = jQuery('#showlimitnumber').val();
    if ( showlimitnumber != '' ) {
        newConfs = newConfs+' limit="'+showlimitnumber+'"';
    } 

    var categoryFilterName = jQuery('#categoryFilterName').val();
    if ( categoryFilterName != '' ) {
        newConfs = newConfs+' cat="'+categoryFilterName+'"';
    } 

    jQuery('.hidden-shortcode-cp_confs').text( newConfs );

    var cpShortcodeToInsert = '[companion-portfolio'+newConfs+']';

    window.send_to_editor( cpShortcodeToInsert );
    
    jQuery("#cp-popup").hide();
    jQuery(".cp-overlay").hide();

});