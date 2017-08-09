<?php
/*
Plugin Name: Companion Portfolio
Plugin URI:  http://qreative-web.com
Description: A fully responsive portfolio plugin, looking sharp on every device! Easy to use and fully configurable.
Version:     1.9.5
Author:      Qreative-Web
Author URI:  http://papinschipper.nl
License:     GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: companion-portfolio
Domain Path: /languages/

The WordPress plugin Companion Sitemap Generator is licensed under the GPL v2 or later.
This program is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License, version 2, as published by the Free Software Foundation.
This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
You should have received a copy of the GNU General Public License along with this program; if not, write to the Free Software Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA
*/

// Disable direct access
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

// (De)Activation settings
register_deactivation_hook( __FILE__, 'flush_rewrite_rules' );
register_activation_hook( __FILE__, 'flush_rewrite_rules' );

// Load translation files first
function cp_load_textdomain() {
	load_plugin_textdomain( 'companion-portfolio', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' ); 
}
add_action( 'init', 'cp_load_textdomain', 0 );

// Load JS files for Generator Popup
function enqueue_plugin_scripts($plugin_array) {
    $plugin_array["cp_mce_button"] =  plugin_dir_url(__FILE__) . "backend/js/script.js";
    return $plugin_array;
}
add_filter("mce_external_plugins", "enqueue_plugin_scripts");

// Register custom post type
function cp_custom_pt() {

	$pluginRoot = plugin_dir_path( __FILE__ );

	$slug = get_option( 'cp_cpt_base' );
    if( ! $slug ) $slug = 'portfolio';

    register_post_type( 'portfolio',
        array(
	        'labels' => array(
				'name' 					=> __( 'Portfolio', 'companion-portfolio' ),
				'singular_name' 		=> __( 'Portfolio', 'companion-portfolio' ),
				'add_new' 				=> __( 'New Project', 'companion-portfolio'),
				'add_new_item' 			=> __( 'Add New Project', 'companion-portfolio' ),
				'new_item' 				=> __( 'New Project', 'companion-portfolio' ),
				'all_items' 			=> __( 'All Projects', 'companion-portfolio' ),
				'search_items' 			=> __( 'Search Projects', 'companion-portfolio' ),
				'not_found' 			=> __( 'No projects found', 'companion-portfolio'),
				'not_found_in_trash' 	=> __( 'No projects found in Trash', 'companion-portfolio' ),
				'edit_item'	 			=> __( 'Edit Project', 'companion-portfolio' ),
				'view_item'				=> __( 'View Project', 'companion-portfolio' ),
	        ),
	        'description' 	=> __( 'Create portfolio items', 'companion-portfolio' ),
	        'public' 		=> true,
	        'has_archive' 	=> false, // "Archive" is loaded by shortcode
	        'menu_position' => 25, // Below comments
	        'rewrite' 		=> array( 'slug' => $slug ),
	        'supports' 		=> array( 
	        	'title', 
	        	'editor', 
	        	'thumbnail', 
	        	'excerpt', 
	        	'comments' 
	        ),
	        'menu_icon' 	=> 'dashicons-category',
        )
    ); 
}
add_action( 'init', 'cp_custom_pt', 1 );

function register_custom_taxonomies() {

    register_taxonomy( 'portfolio_cat', 'portfolio', array(
		"hierarchical" 		=> true,
		"label" 			=> __( 'Categories', 'companion-portfolio' ),
		"singular_label" 	=> __( 'Categorie', 'companion-portfolio' ),
		'query_var' 		=> true,
		'rewrite' 			=> array( 'slug' => 'portfolio_cat', 'with_front' => false ),
		'public' 			=> true,
		'show_ui' 			=> true,
		'show_tagcloud' 	=> false,
		'_builtin' 			=> false,
		'show_in_nav_menus' => false
    ));

}
add_action( 'init', 'register_custom_taxonomies', 2 );

// parse the generated links
function cp_reg_permalink( $permalink, $post, $leavename, $sample ) {

    if ( $post->post_type == 'portfolio' && get_option( 'permalink_structure' ) ) {

		$slug = get_option( 'cp_cpt_base' );
	    if( ! $slug ) $slug = 'portfolio';

        $struct = '/'.$slug.'/%postname%/';

        $rewritecodes = array(
            '%postname%'
        );

        // setup data
        $unixtime = strtotime( $post->post_date );

        $replacements = array(
            $post->post_name
        );

        // finish off the permalink
        $permalink = home_url( str_replace( $rewritecodes, $replacements, $struct ) );
        $permalink = user_trailingslashit($permalink, 'single');
    }

    return $permalink;
}
add_filter( 'post_type_link', 'cp_reg_permalink', 10, 4 );

// Add the URL Metabox
function cp_porfolio_url_metaboxes() {
	add_meta_box('cp_portfolio', __('Website URL', 'companion-portfolio' ), 'cp_portfolio_url', 'portfolio', 'side', 'default');
}
add_action( 'add_meta_boxes', 'cp_porfolio_url_metaboxes' );

// The URL Metabox
function cp_portfolio_url() {
	global $post;
	
	// Noncename needed to verify where the data originated
	echo '<input type="hidden" name="portfoliourl_noncename" id="portfoliourl_noncename" value="' .wp_create_nonce( plugin_basename(__FILE__) ). '" />';
	
	// Get the location data if its already been entered
	$url = get_post_meta($post->ID, 'portfolio_url', true);
	
	// Echo out the field
	echo '<input type="text" name="portfolio_url" value="' .$url. '" class="widefat" placeholder="http://example.com" />';
	echo '<p class="howto">'.__('When an URL is filled this portfolio item will link to the website, otherwise it will redirect to a page.', 'companion-portfolio' ).'</p>';

}

// Save the Metabox Data
function cp_save_portfoliourl_meta($post_id, $post) {

	if ( $post->post_type == 'portfolio' && isset( $_POST['portfolio_url'] ) ) {
	
		// verify this came from the our screen and with proper authorization,
		// because save_post can be triggered at other times
		if ( !wp_verify_nonce( $_POST['portfoliourl_noncename'], plugin_basename(__FILE__) )) {
			return $post->ID;
		}

		// Is the user allowed to edit the post or page?
		if ( !current_user_can( 'edit_post', $post->ID )) {
			return $post->ID;
		}

		// OK, we're authenticated: we need to find and save the data
		// We'll put it into an array to make it easier to loop though.
		$portfolio_meta['portfolio_url'] = $_POST['portfolio_url'];
		
		// Add values of $portfolio_meta as custom fields
		foreach ($portfolio_meta as $key => $value) {	// Cycle through the $portfolio_meta array!

			if( $post->post_type == 'revision' ) {
				return; // Don't store custom data twice
			}

			$value = implode(',', (array)$value); // If $value is an array, make it a CSV (unlikely)

			if(get_post_meta($post->ID, $key, FALSE)) { // If the custom field already has a value
				update_post_meta($post->ID, $key, $value);
			} else { // If the custom field doesn't have a value
				add_post_meta($post->ID, $key, $value);
			}

			if(!$value) delete_post_meta($post->ID, $key); // Delete if blank
		}
	}

}
add_action('save_post', 'cp_save_portfoliourl_meta', 1, 2); // save the custom fields

// The front end loop
function cp_frontEnd_loop( $attributes ) {

	$conf = shortcode_atts( array(
		'limit' 		=> '-1',
		'sortby' 		=> 'name',
		'columns' 		=> '2',
		'showdate' 		=> 'true',
		'showexcerpt' 	=> 'false',
		'order' 		=> 'ASC',
		'cat' 			=> '',
	), $attributes );

	$catClass = '';

	// I may have had a bit too much to drink and I couldn't think of an easier solution
	if( $conf['cat'] != '') {

		$args = array(
			'post_type' 		=> 'portfolio', 
			'post_status' 		=> 'publish', 
			'orderby' 			=> $conf['sortby'], 
			'posts_per_page' 	=> $conf['limit'], 
			'order' 			=> $conf['order'],
			'tax_query' => array(
				array(
					'taxonomy' => 'portfolio_cat',
					'field'    => 'name',
					'terms'    => $conf['cat'] ,
				),
			),
		);

		$catClass = 'cat-'.str_replace("-", " ", $conf['cat']);

	} else {

		$args = array(
			'post_type' 		=> 'portfolio', 
			'post_status' 		=> 'publish', 
			'orderby' 			=> $conf['sortby'], 
			'posts_per_page' 	=> $conf['limit'], 
			'order' 			=> $conf['order'],
		);
	}

	$wp_query = new WP_Query($args);

	// Make sure no illegal amount of columns is given
	if( $conf['columns'] > '5' ) {
		$columns = '2';
	} elseif ( $conf['columns'] < '1' ) {
		$columns = '2';
	} else {
		$columns = $conf['columns'];
	}

	$return = '';

	if($wp_query->have_posts()) : 
		while($wp_query->have_posts()) : $wp_query->the_post(); 

			if( get_post_meta(get_the_ID(), 'portfolio_url', TRUE) != '') {
				$url = get_post_meta( get_the_ID(), 'portfolio_url', TRUE );
				$target = '_blank';
			} else {
				$url = get_the_permalink();
				$target = '_self';
			}

			$thumb = wp_get_attachment_image_src( get_post_thumbnail_id( get_the_ID() ), 'large' );
			$imgUrl = $thumb['0'];

			$return .= '<a href="'.$url.'" target="'.$target.'" alt="Portfolio item: '.get_the_title().'" class="cp_portfolio_item cp_columns-'.$columns.' '.$catClass.'">

				<div class="cp_portfolio_image" style="background-image: url('.$imgUrl.');">
				</div>
				<h2 class="cp_portfolio_title">'.get_the_title().'</h2>';
				if( $conf['showexcerpt'] == 'true') {
					$return .= '<p class="cp_portfolio_excerpt">'.get_the_excerpt().'</p>';
				}
				if( $conf['showdate'] == 'true') {
					$return .= '<p class="cp_portfolio_date">'.get_the_date().'</p>';
				}

			$return .= '</a>';


		endwhile; 
	else:

		$return .= '<div class="no-projects-found"><p>'.__('Oops: No projects where found', 'companion-portfolio').'.</p></div>';
		wp_reset_postdata(); // reset the query

	endif;

	return $return;

}

// Add a shortcode
add_shortcode( 'companion-portfolio' , 'cp_frontEnd_loop' );

// Adds styling
function cp_frontend_style() {
	wp_enqueue_style( 'cp-styling', plugin_dir_url( __FILE__ ) . 'frontend/style.css', array(), '1.0.0', 'all'  );
}
add_action( 'wp_enqueue_scripts', 'cp_frontend_style' );

function cp_portfolio_frontend() {
	include_once( 'cp_dashboard.php' );
}

// Add settings link on plugin page
function cp_settings_link( $links ) { 

	$settings_link = '<a href="edit.php?post_type=portfolio&page=cp-portfolio-settings">'.__('Settings', 'companion-portfolio' ).'</a>'; 
	array_unshift($links, $settings_link); 
	return $links; 

}
$plugin = plugin_basename(__FILE__); 
add_filter("plugin_action_links_$plugin", 'cp_settings_link' );

// Add plugin to menu
function register_cp_menu_page() {
	add_submenu_page( 'edit.php?post_type=portfolio', __('Portfolio', 'companion-portfolio'), __('Shortcodes', 'companion-portfolio'), 'manage_options', 'cp-portfolio-settings', 'cp_portfolio_frontend' );
}
add_action( 'admin_menu', 'register_cp_menu_page' );

// Add table items
function add_cp_columns( $plugin_columns ) {

    $new_columns['cb'] 				= '<input type="checkbox" />';
    $new_columns['title'] 			= __( 'Title' , 'companion-portfolio' );
    $new_columns['author'] 			= __( 'Author' , 'companion-portfolio' );
    $new_columns['portfolio_cat'] 	= __( 'Categories' , 'companion-portfolio' );
    $new_columns['date'] 			= __( 'Date' , 'companion-portfolio' );
 
    return $new_columns;
}
add_filter('manage_edit-portfolio_columns', 'add_cp_columns');

// Create columns for custom taxonym portfolio_cat
function cp_set_custom_columns( $columns ) {
    $new_columns['portfolio_cat'] 	= __( 'Categories' , 'companion-portfolio' );
    return $columns;
}
function cp_custom_column( $column, $post_id ) {
    switch ( $column ) {

        case 'portfolio_cat' :
            $terms = get_the_term_list( $post_id , 'portfolio_cat' , '' , ',' , '' );
            if ( is_string( $terms ) )
                echo $terms;
            else
                echo "&#8212;";
            break;
    }
}
add_filter( 'manage_portfolio_posts_columns', 'cp_set_custom_columns' );
add_action( 'manage_portfolio_posts_custom_column' , 'cp_custom_column', 10, 2 );

// Add permalink settings to default permalink page
function cp_load_permalinks() {
    if( isset( $_POST['cp_cpt_base'] ) ) {
        update_option( 'cp_cpt_base', esc_attr( $_POST['cp_cpt_base'] ) );
    }

    // Add a settings field to the permalink page
    add_settings_field( 'cp_cpt_base', __( 'Project-pages', 'companion-portfolio' ), 'cp_field_callback', 'permalink', 'optional' );
}
add_action( 'load-options-permalink.php', 'cp_load_permalinks' );

// The input field
function cp_field_callback() {
    $value = get_option( 'cp_cpt_base' );  
    echo '<input type="text" value="' . esc_attr( $value ) . '" name="cp_cpt_base" id="cp_cpt_base" class="regular-text" placeholder="portfolio"/><code>/project-name/</code>';
}
add_action( 'init', 'cp_custom_pt' );

// Create widget
class cp_portfolio_widget extends WP_Widget {

    function __construct() {

		parent::__construct(
			'cp_portfolio_widget_base',
			__( 'Portfolio Widget', 'companion-portfolio'),
			array(
				'description' =>  __( 'Show latest projects.', 'companion-portfolio')
			)
		);

    }

    function update( $new_instance, $old_instance ) {

            $instance 						= $old_instance;
            $instance['title'] 				= strip_tags( $new_instance['title'] );
            $instance['category'] 			= strip_tags( $new_instance['category'] );
            $instance['numberOfListings'] 	= strip_tags( $new_instance['numberOfListings'] );
            $instance['showdate'] 			= $new_instance['showdate'];

            return $instance;

    }

    function form( $instance ) {

	    if( $instance) {

	        $title 				= esc_attr( $instance['title'] );
	        $category 			= esc_attr( $instance['category'] );
	        $numberOfListings 	= esc_attr( $instance['numberOfListings'] );
	        $showdate 			= $instance['showdate'];

	    } else { 

	        $title 				= __('Latest Projects', 'companion-portfolio');
	        $numberOfListings 	= '5';
	        $category 			= '';
	        $showdate			= 'on';

	    }    

        echo '<p><label for="'.$this->get_field_id('title').'">'.__('Title', 'companion-portfolio').':</label>
        <input class="widefat" id="'.$this->get_field_id('title').'" name="'.$this->get_field_name('title').'" type="text" value="'.$title.'" /></p>';

        echo '<p><label for="'.$this->get_field_id('category').'">'.__('Category', 'companion-portfolio').':</label>
        <input class="widefat" id="'.$this->get_field_id('category').'" name="'.$this->get_field_name('category').'" type="text" value="'.$category.'" /></p>';

       	echo '<p><label for="'.$this->get_field_id('numberOfListings').'">'.__('Number of projects to show', 'companion-portfolio').':</label>
        <input id="'.$this->get_field_id('numberOfListings').'" name="'.$this->get_field_name('numberOfListings').'" type="number" value="'.$numberOfListings.'" min="1" max="10" /></p>';

        echo '<p><input id="'.$this->get_field_id('showdate').'" name="'.$this->get_field_name('showdate').'" type="checkbox" ';
        if(  $showdate == 'on' ) {
        	echo 'checked="checked"';
        }
        echo ' />
        <label for="'.$this->get_field_id('showdate').'">'.__('Display project date?', 'companion-portfolio').'</label></p>';

  
    }

    function widget( $args, $instance ) {

        extract( $args );

        $title 				= apply_filters( 'widget_title', $instance['title'] );
        $numberOfListings 	= $instance['numberOfListings'];
        $category 			= $instance['category'];
        $showdate 			= $instance['showdate'];

        echo $before_widget;

        if ( $title ) {

            echo $before_title . $title . $after_title;

        }

        $this->getPortfolioListings( $numberOfListings, $showdate, $category );

        echo $after_widget;
    }

    function getPortfolioListings( $numberOfListings = '5', $showdate = true, $category = '' ) {

		// I may have had a bit too much to drink and I couldn't think of an easier solution
		if( $category != '') {

			$args = array(
				'post_type' 		=> 'portfolio', 
				'post_status' 		=> 'publish', 
				'orderby' 			=> 'name', 
				'posts_per_page' 	=> $numberOfListings, 
				'order' 			=> 'ASC',
				'tax_query' => array(
					array(
						'taxonomy' => 'portfolio_cat',
						'field'    => 'name',
						'terms'    => $category,
					),
				),
			);

		} else {

			$args = array(
				'post_type' 		=> 'portfolio', 
				'post_status' 		=> 'publish', 
				'orderby' 			=> 'name', 
				'posts_per_page' 	=> $numberOfListings, 
				'order' 			=> 'ASC',
			);
		}

		$wp_query = new WP_Query($args);

		$return = '';

		$return .= '<div class="cp_portfolio_widget">';

		if($wp_query->have_posts()) : 
			while($wp_query->have_posts()) : $wp_query->the_post(); 

				if( get_post_meta(get_the_ID(), 'portfolio_url', TRUE) != '') {
					$url = get_post_meta( get_the_ID(), 'portfolio_url', TRUE );
					$target = '_blank';
				} else {
					$url = get_the_permalink();
					$target = '_self';
				}

				$thumb = wp_get_attachment_image_src( get_post_thumbnail_id( get_the_ID() ), 'large' );
				$imgUrl = $thumb['0'];

				$return .= '<a href="'.$url.'" target="'.$target.'" alt="Portfolio item: '.get_the_title().'" class="cp_widget_item">

					<div class="cp_widget_content">
						<b class="cp_widget_title">'.get_the_title().'</b>';

						if( $showdate ) {
							$return .= '<p class="cp_widget_date">'.get_the_date().'</p>';
						}
						
					$return .= '</div><div class="cp_widget_image" style="background-image: url('.$imgUrl.');"></div>

				</a>';


			endwhile; 
		else:

			$return .= '<div class="widget-no-projects-found"><p>'.__('Oops: No projects where found', 'companion-portfolio').'.</p></div>';
			wp_reset_postdata(); // reset the query

		endif;
		
		$return .= '</div>';

		echo $return;

    }

} 
function cp_register_widgets() {
	register_widget( 'cp_portfolio_widget' );
}
add_action( 'widgets_init', 'cp_register_widgets' );

// Add button to editor
function cp_portfolio_config_btn() {
    echo '<a id="insert-my-portfolio" class="button"><span class="dashicons dashicons-category" style="position: relative; bottom: -2px;"></span> '.__('Portfolio Shortcode', 'companion-portfolio').'</a>';
    load_cp_popup();
}
add_action('media_buttons', 'cp_portfolio_config_btn', 15);

// The insert into page popup
function load_cp_popup() {
	include_once( 'cp_popup.php' );
}

?>