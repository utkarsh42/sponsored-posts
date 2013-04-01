<?php
/*
Plugin Name: Sponsored Posts
Plugin URI: http://github.com/Postmedia/sponsored-posts
Description: Inject ponsored posts into your index/archive pages
Author: Postmedia Inc.
Version: 0.1.0
Author URI: http://github.com/Postmedia
*/

/**
 * create custom post type for sponsored posts
 *
 * @since 1.0.0
 * @author Edward de Groot
 */
function pd_sponsored_posts_register() {
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
add_action( 'init', 'pd_sponsored_posts_register' );

function pd_sponsored_posts_inject( $posts , $query) {

  if ( $query->is_main_query() && ( $query->is_home() || $query->is_category() || $query->is_tag() ) ) {    
  
    $args = array( 
  		'post_type' => 'sponsor',
  		'posts_per_page' => 1,
  		'orderby' => 'rand'
  	);

  	if ( !empty( $wp_query->query['category_name'] ) )
  	  $args['category_name'] = $query->query['category_name'];

  	if ( !empty( $query->query['tag'] ) )
  	  $args['tag'] = $query->query['tag'];
	
  	$sponsors = get_posts( $args );
  	if ( count( $sponsors ) == 0 )
  	  return $posts;
	
  	$len = count( $posts );
  	$slot = $slot = wp_rand( 0, $len );
    $combined = array();
  
    for( $i = 0; $i < $len; $i++ ) {
      $combined[] = $posts[$i];
      if ( $slot == $i )
        $combined[] = $sponsors[0];
    }
  
    return $combined;
  } else {
    return $posts;
  }
}
add_action( 'the_posts', 'pd_sponsored_posts_inject', 10, 2);

/**
 * ensures external links for sponsored posts open in new window
 *
 * @uses get_post_type()
 *
 * @since 1.0.0
 * @author Edward de Groot
 */
function pd_sponsored_posts_target() {
	if ( get_post_type() == 'sponsor' )
		echo ' target="_blank" ';
}

/**
 * ensures sponsored posts are attributed as such
 *
 * @uses get_post_type()
 *
 * @since 1.0.0
 * @author Edward de Groot
 */
function pd_sponsored_posts_author( $display_name ) {
	if ( get_post_type() == 'sponsor' )
		return 'Sponsored Post';
	else
	  return $display_name;
}
add_filter( 'the_author', 'pd_sponsored_posts_author' )
?>