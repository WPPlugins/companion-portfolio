
<div class="wrap about-wrap">
	
	<h1><?php _e('Portfolio Shortcode', 'companion-portfolio'); ?></h1>
	<p class="about-text"><?php _e('Welcome to the portfolio shortcode generator, here you can quickly generate a shortcode to display your portfolio items.', 'companion-portfolio'); ?></p>

	<h2 class="nav-tab-wrapper wp-clearfix">
		<a href="" class="nav-tab nav-tab-active"><?php _e('Dashboard', 'companion-portfolio'); ?></a>
		<a href="https://wordpress.org/plugins/companion-portfolio/changelog/" target="_blank" class="nav-tab"><?php _e('What\'s new', 'companion-portfolio'); ?></a>
		<a href="https://wordpress.org/support/plugin/companion-portfolio" target="_blank" class="nav-tab"><?php _e('Support', 'companion-portfolio'); ?></a>
	</h2>
	
	<br />
	<h3><?php _e('Set Configurations', 'companion-portfolio'); ?></h3>

	<div class="two-col">
		<div class="col">
			<ul>
				<li><?php _e('Change the number of columns to:', 'companion-portfolio'); ?><br />
				<input type='number' min='1' max='5' style='width: 100%' id='numrowsnumber' placeholder='(min. 1 max. 5)'></li>

				<li><?php _e('Change the limit to:', 'companion-portfolio'); ?><br />
				<input type='number' style='width: 100%' id='showlimitnumber'></li>
				
				<li><?php _e('Filter by category:', 'companion-portfolio'); ?><br />
				<input type='text' style='width: 100%' id='categoryFilterName'></li>
			</ul>
		</div>
		<div class="col">
			<ul>
				<li><label for="order"><input type='checkbox' name='order' id='order'> <?php _e('Revert Order', 'companion-portfolio'); ?></label></li>
				<li><label for="showdate"><input type='checkbox' name='showdate' id='showdate'> <?php _e('Hide the date', 'companion-portfolio'); ?></label></li>
				<li><label for="showexcerpt"><input type='checkbox' name='showexcerpt' id='showexcerpt'> <?php _e('Show the excerpt', 'companion-portfolio'); ?></label></li>
			</ul>
		</div>

		<div style='clear: both;'></div>

		<button class='button button-primary button-hero cp-gen-shortcode'><?php _e('Update shortcode', 'companion-portfolio'); ?></button><br />
		
		<br />
		<h3 style='margin-bottom: 0px;'><?php _e('Insert this Shortcode', 'companion-portfolio'); ?></h3>
		<p class='description'><?php _e('Copy this shortcode and paste it into your post or page:', 'companion-portfolio'); ?></p>

		<p><code class='cp_portfolio_shortcode'>[companion-portfolio<span class='cp_portfolio_shortcode_confs'></span>]</code></p>

	</div>

</div>

<script>
jQuery( document ).ready( function() {

	var confs = jQuery('.cp_portfolio_shortcode_confs').text();
	jQuery('.cp_portfolio_shortcode_confs').text('');

	jQuery(".cp-gen-shortcode").click(function() {

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

		jQuery('.cp_portfolio_shortcode_confs').text( newConfs );

	});


});
</script>