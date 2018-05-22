<?php
/*
Plugin Name: DMC Web Helpers
Plugin URI: http://www.dmcweb.com.au
Description: Common helper functions used on most projects using customised Reverie theme
Version: 1.0
Author: David McDonald
Author URI: http://www.davidmcodnald.org
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
	// Yoast SEO
	remove_meta_box( 'wpseo-dashboard-overview', 'dashboard', 'side' );
}
add_action( 'wp_dashboard_setup', 'dmc_disable_default_dashboard_widgets' );


// remove certain admin menu items for specific user roles
function dmc_remove_menus() {
	$roles = array( 'contributor', 'author' );
	$user  = wp_get_current_user();
	foreach ( $roles as $role ) {
		if ( in_array( $role, (array) $user->roles, true ) ) {
			remove_menu_page( 'index.php' );                  //Dashboard
			// remove_menu_page( 'jetpack' );                    //Jetpack*
			remove_menu_page( 'edit.php' );                   //Posts
			// remove_menu_page( 'upload.php' );                 //Media
			// remove_menu_page( 'edit.php?post_type=page' );    //Pages
			remove_menu_page( 'edit-comments.php' );          //Comments
			// remove_menu_page( 'themes.php' );                 //Appearance
			// remove_menu_page( 'plugins.php' );                //Plugins
			// remove_menu_page( 'users.php' );                  //Users
			remove_menu_page( 'tools.php' );                  //Tools
			// remove_menu_page( 'options-general.php' );        //Settings

			remove_menu_page( 'acf-options-site-global-settings' );
		}
	}
}
add_action( 'admin_init', 'dmc_remove_menus' );


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
	);

	foreach ( $capabilities as $cap ) {
		$role->add_cap( $cap );
	}
}
add_action( 'admin_init', 'dmc_modify_editor_role' );


// Give editor role access to the Redirection plugin
add_filter( 'redirection_role', 'dmc_redirection_editor_access' );
function dmc_redirection_editor_access() {
	return 'edit_pages';
}

// Gravity Forms Custom Addresses (Australia)
add_filter( 'gform_address_types', 'dmc_australian_address', 10, 2 );
function dmc_australian_address( $address_types, $form_id ) {
	$address_types['australian'] = array(
		'label'       => 'Australia',
		'country'     => 'Australia',
		'state_label' => 'State',
		'zip_label'   => 'Postcode',
		'states'      => array(
			'Please select ...',
			'Australian Capital Territory',
			'New South Wales',
			'Northern Territory',
			'Queensland',
			'South Australia',
			'Tasmania',
			'Victoria',
			'Western Australia',
		),
	);
	return $address_types;
}
add_filter( 'gform_address_street', 'dmc_change_address_street', 10, 2 );
function dmc_change_address_street( $label, $form_id ) {
	return 'Address line 1';
}
add_filter( 'gform_address_street2', 'dmc_change_address_street3', 10, 2 );
function dmc_change_address_street3( $label, $form_id ) {
	return 'Address line 2';
}
add_filter( 'gform_address_city', 'dmc_change_address_city', 10, 2 );
function dmc_change_address_city( $label, $form_id ) {
	return 'Town/suburb';
}
add_filter( 'gform_address_state', 'dmc_change_address_state', 10, 2 );
function dmc_change_address_state( $label, $form_id ) {
	return 'State';
}
add_filter( 'gform_address_zip', 'dmc_change_address_zip', 10, 2 );
function dmc_change_address_zip( $label, $form_id ) {
	return 'Postcode';
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
add_filter( 'login_headertitle', 'dmc_custom_login_logo_url_title' );

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
			background-image: url('<?php echo esc_url( get_bloginfo( 'template_directory' ) ); ?>/img/logo-med.png');
			background-size: 320px 129px;
			padding-bottom: 30px;
		}
	</style>
<?php
}
add_action( 'login_enqueue_scripts', 'dmc_custom_login_logo' );
