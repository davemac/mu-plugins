<?php
/**
 * Plugin Name: DMC Editor Manage Users
 * Version: 0.1-alpha
 * Description: Allows editor role to manage users
 * Author: David McDonald
 * Author URI: dmcweb.com.au
 * Plugin URI: PLUGIN SITE HERE
 * Text Domain: dmc-editors-manage-users
 * Domain Path: /languages
 * @package DMC Editor Manage Users
 */

/*
 * Let Editors manage users, and run this only once.
 * http://isabelcastillo.com/editor-role-manage-users-wordpress
 */
function isa_editor_manage_users() {

	if ( get_option( 'isa_add_cap_editor_once' ) !== 'done' ) {

		// let editor manage users

		$edit_editor = get_role( 'editor' ); // Get the user role
		$edit_editor->add_cap( 'edit_users' );
		$edit_editor->add_cap( 'list_users' );
		$edit_editor->add_cap( 'promote_users' );
		$edit_editor->add_cap( 'create_users' );
		$edit_editor->add_cap( 'add_users' );
		$edit_editor->add_cap( 'delete_users' );

		update_option( 'isa_add_cap_editor_once', 'done' );
	}

}
add_action( 'init', 'isa_editor_manage_users' );


//prevent editor from deleting, editing, or creating an administrator
// only needed if the editor was given right to edit users

class Dmc_Editors_Manage_Users {

	// Add our filters
	function isa_user_caps() {
		add_filter( 'editable_roles', array( &$this, 'editable_roles' ) );
		add_filter( 'map_meta_cap', array( &$this, 'map_meta_cap' ),10,4 );
	}
	// Remove 'Administrator' from the list of roles if the current user is not an admin
	function editable_roles( $roles ) {
		if ( isset( $roles['administrator'] ) && ! current_user_can( 'administrator' ) ) {
			unset( $roles['administrator'] );
		}
		return $roles;
	}
	// If someone is trying to edit or delete an
	// admin and that user isn't an admin, don't allow it
	function map_meta_cap( $caps, $cap, $user_id, $args ) {
		switch ( $cap ) {
			case 'edit_user':
			case 'remove_user':
			case 'promote_user':
				if ( isset( $args[0] ) && $args[0] === $user_id ) {
					break;
				} elseif ( ! isset( $args[0] ) ) {
					$caps[] = 'do_not_allow';
				}
				$other = new WP_User( absint( $args[0] ) );
				if ( $other->has_cap( 'administrator' ) ) {
					if ( ! current_user_can( 'administrator' ) ) {
						$caps[] = 'do_not_allow';
					}
				}
				break;
			case 'delete_user':
			case 'delete_users':
				if ( ! isset( $args[0] ) ) {
					break;
				}
				$other = new WP_User( absint( $args[0] ) );
				if ( $other->has_cap( 'administrator' ) ) {
					if ( ! current_user_can( 'administrator' ) ) {
						$caps[] = 'do_not_allow';
					}
				}
				break;
			default:
				break;
		}
		return $caps;
	}

}
$isa_user_caps = new Dmc_Editors_Manage_Users();


// Hide admin from user list

add_action( 'pre_user_query','isa_pre_user_query' );
function isa_pre_user_query( $user_search ) {
	$user = wp_get_current_user();
	if ( 1 !== $user->ID ) { // Is not administrator, remove administrator
		global $wpdb;
		$user_search->query_where = str_replace('WHERE 1=1',
		"WHERE 1=1 AND {$wpdb->users}.ID<>1",$user_search->query_where);
	}
}
