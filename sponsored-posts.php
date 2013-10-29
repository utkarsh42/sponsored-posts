<?php
/*
Plugin Name: Sponsored Posts
Plugin URI: http://github.com/Postmedia/sponsored-posts
Description: Inject sponsored posts into your index/archive pages
Author: Postmedia Inc.
Version: 0.8.0
Author URI: http://github.com/Postmedia
License: MIT	
*/

/*
Copyright (c) 2013 Postmedia Network Inc.

Permission is hereby granted, free of charge, to any person obtaining
a copy of this software and associated documentation files (the
"Software"), to deal in the Software without restriction, including
without limitation the rights to use, copy, modify, merge, publish,
distribute, sublicense, and/or sell copies of the Software, and to
permit persons to whom the Software is furnished to do so, subject to
the following conditions:

The above copyright notice and this permission notice shall be included
in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT.
IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY
CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT,
TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE
SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
*/

/**
 * @package Sponsored Posts
 * @author SMRT Team (Donnie Marges, Keith Benedect, Andrew Spearin, Edward de Groot)
 * @since 0.8.0
 */

define( 'SMRT_SPONSORED_POSTS', '0.8.0');

class SMRT_Sponsored_Posts {

	public function __construct() {
		
		// register new post type
		add_action( 'init', array( $this, 'register' ) );
		
		// for debugging, support public sponsored_posts query var
		add_filter( 'query_vars', array( $this, 'add_query_vars_filter' ) );
		
		// support automatic injection via pre_get_posts hook
		add_action( 'pre_get_posts', array( $this, 'auto_inject' ) );
		
		// sponsored ads are injected via the_posts hook
		add_action( 'the_posts', array( $this, 'inject'), 10, 2);
		
		// Add admin hooks for Sponsored Posts
		if ( is_admin() ) {
			add_action( 'admin_menu', array( $this, 'add_options_page' ) );
			add_action( 'admin_init', array( $this, 'options_init' ) );
		}
	}
	
	/**
	 * create custom post type for sponsored posts
	 *
	 * @since 0.8.0
	 */
	public function register() {
		$args = array(
			'public' => true,
			'label' => 'Sponsors',
			'description' => 'Sponsored posts',
			'menu_position' => 5,
			'supports' => array( 'title', 'editor', 'thumbnail', 'excerpt', 'post-formats' ),
			'taxonomies' => array( 'category', 'post_tag', 'post_format' )
		);
	  register_post_type( 'sponsor', $args );
	}
	
	/**
	 * for debugging, support public sponsored_posts query var
	 */
	public function add_query_vars_filter( $vars ){
  	$vars[] = "sponsored_posts";
  return $vars;
}
	
	/**
	 * validate sponsored_posts query var
	 */
	public function validate_sponsored_posts_var( $var ) {
		
		$valid = array();
		
		if ( !empty( $var ) ) {
			
			if ( !is_array( $var ) ) {
				$var = explode( ',', $var );
			}
			
			foreach( $var as $value ) {
				if ( is_numeric( $value ) ) {
					$valid[] = intval( $value );
				}
			}
			
			if ( !empty( $valid ) ) {
				sort( $valid );
				if ( $valid[0] < 0 ) {
					$valid = array( -1 );
				}
			}
			
		}
		
		return $valid;
	}
		
	/**
	 * retrieve and cache sponsored posts
	 *
	 * @since 0.8.0
	 */
	public function get_posts( $max = 1, $filter = array() ) {
		
    $args = array( 
  		'post_type' => 'sponsor',
  		'posts_per_page' => $max,
			'no_found_rows' => false,
  		'orderby' => 'rand'
  	);

  	if ( isset( $filter['category_name'] ) )
  	  $args['category_name'] = $filter['category_name'];

  	if ( isset( $filter['tag'] ) )
  	  $args['tag'] = $filter['tag'];

  	return get_posts( $args );	
	}
	
	/**
	 * support automatic injection via insertion of sponsored_posts query param
	 *
	 * @since 0.8.0
	 */
	public function auto_inject( $query ) {
		if ( is_admin() || !$query->is_main_query() ) {
			return;
		}
		
		if ( !$query->is_home() && !$query->is_category() && !$query->is_tag() ) {
			return;
		}
		
		// if not sponsored_posts specified, support automatic injection via settings
		$var = $query->get( 'sponsored_posts');
		if ( empty( $var ) ) {
			$options = get_option( 'smrt_sponsored_options' );
			if ( isset( $options['auto'] ) && !empty( $options['auto'] ) ) {
				$query->set( 'sponsored_posts' , $options['auto'] );
			}
		}
	}
	
	/**
	 * inject sponsored post(s) into the query results
	 *
	 * @since 0.8.0
	 */	
	public function inject( $posts , $query) {

		// double safety check, as there should never be sponsored posts inject in admin
		if ( !is_admin() && isset( $query->query_vars['sponsored_posts'] ) && !empty( $query->query_vars['sponsored_posts'] ) ) {
			
			$locations = $this->validate_sponsored_posts_var( $query->query_vars['sponsored_posts'] );
			if ( !empty( $locations ) ) {
		  	$sponsors = $this->get_posts( count( $locations ), $query->query );
				if ( !empty( $sponsors ) ) {

					$len = min( count( $sponsors ), count( $locations) );
			    for( $i = 0; $i < $len; $i++ ) {
						if ( -1 === $locations[$i] ) {
							$slot = wp_rand( 0, count( $posts ) );
						} else {
							$slot = $locations[$i] + $i;
						}
			      array_splice( $posts, $slot, 0, array( $sponsors[$i] ) );
			    }
  
				}
			}
			
	  }
		
		return $posts;
	}
	
	/** Adds the Sponsored Posts Options page to WordPress menu
	 *
	 * @uses add_options_page()
	 *
	 * @since 0.8.0
	
	**/
	public function add_options_page() {
		add_options_page( 'Sponsored Posts Settings', 'Sponsored Posts', 'manage_options', 'smrt_sponsored_options', array( $this, 'create_settings_page' ) );
	}
	
	/** 
	 * Creates the markup for the Sponsored Posts settings page
	 *
	 * @uses settings_fields()
	 * @uses do_settings_sections()
	 * @uses submit_button()
	 *
	 * @since 0.8.0
	**/
	public function create_settings_page() {
		?>
		<div class="wrap">
			<?php screen_icon(); ?>
			<h2>Sponsored Posts Settings</h2>
			<form action="options.php" method="post">
				<?php settings_fields('smrt_sponsored_options'); ?>
				<?php do_settings_sections('smrt_sponsored_options'); ?>
				<?php submit_button(); ?>
			</form>
		</div>
		<?php
	}

	/** 
	 * Initializes Sponsored Posts theme options
	 *
	 * @uses register_setting()
	 * @uses add_settings_section()
	 * @uses add_settings_field()
	 *
	 * @since 0.8.0
	**/	 
	public function options_init() {
		// register sections and fields
		register_setting('smrt_sponsored_options', 'smrt_sponsored_options', array( $this, 'options_validate' ) );

		add_settings_section( 'smrt_sponsored_main', 'General Settings', array( $this, 'main_help' ), 'smrt_sponsored_options' );
		add_settings_field( 'smrt_sponsored_auto', 'Auto Inject Sponsored Posts', array( $this, 'options_auto' ), 'smrt_sponsored_options', 'smrt_sponsored_main' );		
	}
	
	/** 
	 * Displays help for general section
	 *
	 * @since 0.8.0
	**/
	public function main_help() {
		//echo '<p>Sponsored Posts General Settings</p>';
	}
	
	/** 
	 * Displays "Auto Inject Sponsored Posts" field for Sponsored Posts Options Page
	 *
	 * @uses esc_attr()
	 *
	 * @since 0.8.0
	**/
	public function options_auto() {
		$options = get_option( 'smrt_sponsored_options' );
		$auto = isset( $options['auto'] ) ? implode( ',', $options['auto'] ) : '';
		?>
		<input id="smrt_sponsored_auto" size="50" name="smrt_sponsored_options[auto]" value="<?php echo esc_attr( $auto ); ?>"/>
		<?php
	}
	
	/** 
	 * Sanitizes input fields from Sponsored Posts Options Page
	 *
	 * uses esc_attr()
	 *
	 * @since 0.8.0
	**/
	public function options_validate( $input ) {
		$valid = array();

		// general settings
		if ( isset( $input['auto'] ) ) {
			$valid['auto'] = $this->validate_sponsored_posts_var( sanitize_text_field( $input['auto'] ) );
		}
				
		return $valid;
	}
}
$smrt_sponsored_posts = new SMRT_Sponsored_Posts();
?>