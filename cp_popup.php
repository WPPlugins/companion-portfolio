<style>
	.cp-overlay {
		display: none;
		position: fixed;
		z-index: 1000;
		left: 0;
		right: 0;
		top: 0;
		bottom: 0;
		background: #000;
		opacity: .6;
	}
	#cp-popup {
		display: none;
		position: fixed; 
		z-index: 1001; 
		top: 100px; 
		left: calc(50% - 225px); 
		width: 450px; 
		background: #FFF; 
		padding: 20px; 
		border: 1px solid #DDD;
		box-shadow: 1px 1px 8px #000;
	}
	#cp-popup #link-modal-title {
		margin: -20px;
		margin-bottom: 20px;
	}
	#cp-popup ul {
		margin: 25px 0;
	}
	.cp-button-area {
		text-align: right;
		border-top: 1px solid #DDD;
		margin: -20px;
		margin-top: 20px;
		padding: 10px;
	}
	@media screen and (max-width: 900px) {
		#cp-popup {
			width: auto;
			left: 5%;
			right: 5%;
			top: 50px;
		}
	}

</style>

<div class='cp-overlay'></div>

<div id='cp-popup' class='cp-popup'>

	<h1 id="link-modal-title"><?php _e('Set Configurations', 'companion-portfolio'); ?></h1>

	<div id='cp-content'>

		<ul>
			<li><?php _e('Change the number of columns to:', 'companion-portfolio'); ?><br />
			<input type='number' min='1' max='5' style='width: 100%' id='numrowsnumber' placeholder='(min. 1 max. 5)'></li>

			<li><?php _e('Change the limit to:', 'companion-portfolio'); ?><br />
			<input type='number' style='width: 100%' id='showlimitnumber'></li>
			
			<li><?php _e('Filter by category:', 'companion-portfolio'); ?><br />
			<input type='text' style='width: 100%' id='categoryFilterName'></li>

			<li>&nbsp;</li>

			<li><label for="order"><input type='checkbox' name='order' id='order'> <?php _e('Revert Order', 'companion-portfolio'); ?></label></li>
			<li><label for="showdate"><input type='checkbox' name='showdate' id='showdate'> <?php _e('Hide the date', 'companion-portfolio'); ?></label></li>
			<li><label for="showexcerpt"><input type='checkbox' name='showexcerpt' id='showexcerpt'> <?php _e('Show the excerpt', 'companion-portfolio'); ?></label></li>
		</ul>

	</div>

	<div class='cp-button-area'>
		<div class='button' id="cp-gen-cancel"><?php _e('Cancel', 'companion-portfolio'); ?></div>
		<a class='button button-primary' id="cp-gen-insert"><?php _e('Insert shortcode', 'companion-portfolio'); ?></a>
	</div>

	<div style='display: none;' class='hidden-shortcode-cp'>[companion-portfolio<span class='hidden-shortcode-cp_confs'></span>]</div>

</div>