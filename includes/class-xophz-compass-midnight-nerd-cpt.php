<?php

class Xophz_Compass_Midnight_Nerd_CPT {

	public function register_cpt() {
		$labels = array(
			'name'               => _x( 'Tickets', 'post type general name', 'xophz-compass-midnight-nerd' ),
			'singular_name'      => _x( 'Ticket', 'post type singular name', 'xophz-compass-midnight-nerd' ),
			'menu_name'          => _x( 'Tickets', 'admin menu', 'xophz-compass-midnight-nerd' ),
			'name_admin_bar'     => _x( 'Ticket', 'add new on admin bar', 'xophz-compass-midnight-nerd' ),
			'add_new'            => _x( 'Add New', 'ticket', 'xophz-compass-midnight-nerd' ),
			'add_new_item'       => __( 'Add New Ticket', 'xophz-compass-midnight-nerd' ),
			'new_item'           => __( 'New Ticket', 'xophz-compass-midnight-nerd' ),
			'edit_item'          => __( 'Edit Ticket', 'xophz-compass-midnight-nerd' ),
			'view_item'          => __( 'View Ticket', 'xophz-compass-midnight-nerd' ),
			'all_items'          => __( 'All Tickets', 'xophz-compass-midnight-nerd' ),
			'search_items'       => __( 'Search Tickets', 'xophz-compass-midnight-nerd' ),
			'not_found'          => __( 'No tickets found.', 'xophz-compass-midnight-nerd' ),
			'not_found_in_trash' => __( 'No tickets found in Trash.', 'xophz-compass-midnight-nerd' )
		);

		$args = array(
			'labels'             => $labels,
			'description'        => __( 'Midnight Nerd Support Tickets.', 'xophz-compass-midnight-nerd' ),
			'public'             => false,
			'publicly_queryable' => false,
			'show_ui'            => false,
			'show_in_menu'       => false,
			'query_var'          => false,
			'rewrite'            => false,
			'capability_type'    => 'post',
			'has_archive'        => false,
			'hierarchical'       => false,
			'menu_position'      => null,
			'show_in_rest'       => true,
			'supports'           => array( 'title', 'editor', 'comments', 'custom-fields' )
		);

		register_post_type( 'midnight_ticket', $args );
	}
}
