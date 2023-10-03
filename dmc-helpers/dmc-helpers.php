<?php
/*
Plugin Name: DMC Web Helpers
Plugin URI: https://github.com/davemac/mu-plugins
Description: Common helper functions used on most projects
Version: 1.0
Author: David McDonald
Author URI: https://dmcweb.com.au
License: GPLv2
Copyright 2018  David McDonald (email : info@davidmcdonald.org, twitter : @davemac)
*/


// disable default dashboard widgets
function dmc_disable_default_dashboard_widgets() {
	//QuickPress
	remove_meta_box( 'dashboard_quick_press', 'dashboard', 'side' );
	// WordPress Development Blog Feed
	remove_meta_box( 'dashboard_primary', 'dashboard', 'side' );
	// Other WordPress News Feed
	remove_meta_box( 'dashboard_secondary', 'dashboard', 'side' );
	//Plugins - Popular, New and Recently updated WordPress Plugins
	remove_meta_box( 'dashboard_plugins', 'dashboard', 'normal' );
	// Recent Comments
	remove_meta_box( 'dashboard_recent_comments', 'dashboard', 'core' );
}
add_action( 'wp_dashboard_setup', 'dmc_disable_default_dashboard_widgets' );


/**
 * Removes specified admin menu items based on user roles.
 *
 * @return void
 */
function dmc_remove_admin_menu_items() {

	// Ensure the global $current_user is available
	global $current_user;
	wp_get_current_user();

	if ( ! $current_user->roles || empty( $current_user->roles ) ) {
		return;
	}

	// Remove comments altogether
	remove_menu_page( 'edit-comments.php' );

	$roles = array( 'contributor', 'author' );

	foreach ( $roles as $role ) {
		if ( in_array( $role, $current_user->roles, true ) ) {

			remove_menu_page( 'index.php' ); //Dashboard
			// Additional menu pages to remove
			remove_menu_page( 'edit.php' );  //Posts
			remove_menu_page( 'tools.php' ); //Tools
			remove_menu_page( 'site-global-settings' );
		}
	}
}

// Hook into 'admin_menu' instead of 'admin_init'
add_action( 'admin_menu', 'dmc_remove_admin_menu_items' );


// allow editors to manage gravity forms
// allow editors to use Appearance menu
// allow editors to manage co-authors plus plugin, create guest authors
// allow editors to manage Privacy sub-menu under Settings
function dmc_modify_editor_role() {
	$role = get_role( 'editor' );

	$capabilities = array(
		'gform_full_access',
		'edit_theme_options',
		'coauthors_guest_author_manage_cap',
		'manage_options',
		'manage_privacy_options',
		'list_users',
		'edit_users',
		'create_users',
		'delete_users',
	);

	if ( $role ) {
		foreach ( $capabilities as $cap ) {
			$role->add_cap( $cap );
		}
	}
}
add_filter( 'gform_default_address_type', 'dmc_set_default_country', 10, 2 );

function dmc_set_default_country( $default_address_type, $form_id ) {
	return 'australian';
}


// Check if page is a child
function is_tree( $pid ) {
	if ( ! is_404() ) {
		global $post;
		if ( ! is_search() ) :
			if ( is_page( $pid ) ) {
				return true;
			}
			$anc = get_post_ancestors( $post->ID );
			foreach ( $anc as $ancestor ) {
				if ( is_page() && $ancestor === $pid ) {
					return true;
				}
			}
			return false;
		endif;
	}
}

function dmc_custom_login_logo_url() {
	return get_bloginfo( 'url' );
}
add_filter( 'login_headerurl', 'dmc_custom_login_logo_url' );

function dmc_custom_login_logo_url_title() {
	return get_bloginfo( 'name' );
}
add_filter( 'login_headertext', 'dmc_custom_login_logo_url_title' );

function dmc_custom_login_logo() {
	?>
	<style type="text/css">
		body.login div#login{
			padding-top: 70px;
		}
		body.login div#login h1 a {
			width: 320px;
			height: 129px;
			margin-left: 4px;
			background-image: url('<?php echo esc_url( get_bloginfo( 'template_directory' ) ); ?>/source/images/logo-med.png');
			background-size: 320px 129px;
			padding-bottom: 30px;
		}
	</style>
	<?php
}
add_action( 'login_enqueue_scripts', 'dmc_custom_login_logo' );
