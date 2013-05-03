<?php
/*
Plugin Name: Author Search
Author: Aaron Brazell
Author URI: http://technosailor.com
Description: A simple plugin to return an author archive if a search term is an authors user_login, or display_name. Case insensitive.
Version: 0.1
*/

class WP_Author_Search {

	public function __construct() {
		$this->hooks();
	}

	public function hooks() {
		add_filter( 'pre_get_posts', array( $this, 'if_author_redirect' ) );
	}

	public function if_author_redirect( $query ) {
		if( is_admin() )
			return false;
		
		$user = false;
		$search_term = $_GET['s'];
		if( is_search() ) {
			add_action( 'pre_user_query', array( $this, 'modify_user_query' ) );
			$user = new WP_User_Query(
				array(
					'search' => $search_term,
					'number' => 1,
					'orderby' => 'post_count',
					'order' => 'desc'
				)
			);
			remove_action( 'pre_user_query', array( $this, 'modify_user_query' ) );
			$users = $user->get_results();
			if( empty( $users ) )
				return $query;

			foreach( $users as $user ) {
				$user_id = $user->ID;
				$permalink = get_author_posts_url( $user_id );
				wp_redirect( $permalink );
				exit;
			}
		}
	}

	public function modify_user_query( $query ) {
		global $wpdb;
		$display_name = urldecode( $_GET['s'] );
		if ( $use_like_syntax ) {
			$query->query_where .= $wpdb->prepare( " OR $wpdb->users.display_name LIKE %s", '%' . like_escape( $display_name ) . '%' );
		} else {
			$query->query_where .= $wpdb->prepare( " OR $wpdb->users.display_name = %s", $display_name );
		}
	}
}
new WP_Author_Search;
