<?php 
/*
Plugin Name: DMC Web Helpers
Plugin URI: http://www.dmcweb.com.au
Description: Common helper functions used on most projects using customised Reverie theme
Version: 1.0
Author: David McDonald
Author URI: http://www.davidmcodnald.org
License: GPLv2
Copyright 2013  David McDonald (email : info@davidmcdonald.org, twitter : @davemac)
*/

// Slider

add_action('init', 'register_dmc_slider' , 0 );

function register_dmc_slider() {
 
    $labels = array(
        'name' => _x( 'Homepage Slider', 'post type general name' ),
        'singular_name' => _x( 'Homepage Slider', 'post type singular name' ),
        'add_new' => _x( 'Add New', 'Homepage Slider' ),
        'add_new_item' => __( 'Add New Homepage Slider' ),
        'edit_item' => __( 'Edit Homepage Slider' ),
        'new_item' => __( 'New Homepage Slider' ),
        'all_items' => __( 'All Homepage Sliders' ),
        'view_item' => __( 'View Homepage Slider' ),
        'search_items' => __( 'Search Homepage Sliders' ),
        'not_found' =>  __( 'No Homepage Sliders found' ),
        'not_found_in_trash' => __( 'No Homepage Sliders found in Trash' ),
        'parent_item_colon' => '',
        'menu_name' => 'Homepage Slider'
    
      );
      $args = array(
        'labels' => $labels,
        'public' => true,
        'query_var' => true,
        'rewrite' => true,
        'capability_type' => 'post',
        // 'capabilities' => array(
//          'publish_posts' => 'manage_options',
//          'edit_posts' => 'manage_options',
//          'edit_others_posts' => 'manage_options',
//          'delete_posts' => 'manage_options',
//          'delete_others_posts' => 'manage_options',
//          'read_private_posts' => 'manage_options',
//          'edit_post' => 'manage_options',
//          'delete_post' => 'manage_options',
//          'read_post' => 'manage_options',
//      ),
        'has_archive' => true,
        'hierarchical' => false,
        'menu_position' => 5,
        'rewrite' => array( 'slug' => 'slider', 'with_front' => false ),
        'supports' => array( 'title', 'editor', 'thumbnail' )
      );
     
    register_post_type( 'dmc_slider' , $args );
}

function display_slider_content() {

    $args = array( 'post_type' => 'dmc_slider', 'order' => 'ASC', 'posts_per_page' => 6 );
    $dmcslider = new WP_Query( $args );
    
    while ( $dmcslider->have_posts() ) : $dmcslider->the_post(); ?>

        <?php 
            $slider_link = get_field('dmc_slider_image_links_to');
            if( $slider_link ) : 
                foreach( $slider_link as $p ) :
        ?>
            <div>
                <a href="<?php echo get_permalink( $p->ID ); ?>">
                    <div class="row collapse">
                        <div class="small-12 columns">
                            <div class="slider-meta">
                                <h1><?php the_title(); ?></h1>
                                <?php the_content(); ?>
                                <span class="button radius large">Find Out More</span>
                            </div>
                        </div>
                    </div>
                
                    <?php if ( has_post_thumbnail() ) { 

                        $attachment_id = get_post_thumbnail_id();
                        $attachment_data = wp_prepare_attachment_for_js( $attachment_id );

                        the_post_thumbnail( 
                            'dmc-slider', 
                            array( 
                                'class' => 'hero',
                                'alt' => trim(strip_tags( $attachment_data['alt'] ) ),
                                'title'   => trim(strip_tags( $attachment_data['title'] )),
                            )
                        ); 
                    }; ?>
                </a>
            </div>

            <?php endforeach; 
        endif; 

    endwhile; wp_reset_postdata();
}

// disable default dashboard widgets
function disable_default_dashboard_widgets() {
    // remove_meta_box('dashboard_right_now', 'dashboard', 'core');
    //QuickPress
    remove_meta_box('dashboard_quick_press', 'dashboard', 'core');
    //Wordpress Development Blog Feed
    remove_meta_box('dashboard_primary', 'dashboard', 'core');
    //Other Wordpress News Feed
    remove_meta_box('dashboard_secondary', 'dashboard', 'core');
    //Plugins - Popular, New and Recently updated Wordpress Plugins
    remove_meta_box('dashboard_plugins', 'dashboard', 'core');
    // Recent Comments
    remove_meta_box('dashboard_recent_comments', 'dashboard', 'core');
    // remove_meta_box('dashboard_incoming_links', 'dashboard', 'core');
    // remove_meta_box('dashboard_recent_drafts', 'dashboard', 'core'); 
}
add_action('admin_menu', 'disable_default_dashboard_widgets');


// remove certain admin menu items for specific user roles
function dmc_remove_menus(){
    $roles = array( 'contributor', 'author' );
    $user = wp_get_current_user();
    foreach ($roles as $role){
        if ( in_array( $role, (array) $user->roles ) ) {
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
            remove_menu_page( 'edit.php?post_type=dmc_slider' );
            remove_menu_page( 'edit.php?post_type=dmc-sponsors' );
            remove_menu_page( 'edit.php?post_type=dmc-attractions' );
            remove_menu_page( 'edit.php?post_type=dmc-presenters' );

            remove_menu_page( 'acf-options-site-global-settings' );
        }
    }
}
add_action( 'admin_init', 'dmc_remove_menus' );


// Give editor role custom capabilities, access to certain plugins
    function dmc_modify_editor_role(){
        $role = get_role('editor');
        // allow editors to manage gravity forms
        $role->add_cap('gform_full_access');
        // allow editors to use Appearance menu
        $role->add_cap( 'edit_theme_options' );
        // allow editors to manage co-authors plus plugin, create guest authors
        $role->add_cap( 'coauthors_guest_author_manage_cap' );
    }
add_action('admin_init','dmc_modify_editor_role');


// Gravity Forms Custom Addresses (Australia)
add_filter( 'gform_address_types', 'dmc_australian_address', 10, 2 );
function dmc_australian_address( $address_types, $form_id ){
    $address_types['australian'] = array(
        'label' => 'Australia',
        'country' => 'Australia',
        'state_label' => 'State',
        'zip_label' => 'Postcode',
        'states' => array(
            'Please select ...',
            'Australian Capital Territory',
            'New South Wales',
            'Northern Territory',
            'Queensland',
            'South Australia',
            'Tasmania',
            'Victoria',
            'Western Australia'
            )
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
add_filter( 'gform_default_address_type', 'dmc_set_default_country',  10, 2 );
function dmc_set_default_country( $default_address_type, $form_id ) {
    return 'australian';
}


// Check if page is a child
function is_tree( $pid ) {   
    if ( !is_404() ) {   
        global $post;   
        if ( !is_search() ) :             
            if ( is_page($pid) )
                return true;            
            $anc = get_post_ancestors( $post->ID );
            foreach ( $anc as $ancestor ) {
                if( is_page() && $ancestor == $pid ) {
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

function dmc_custom_login_logo() { ?>
    <style type="text/css">
    	body.login div#login{
    		padding-top: 70px;
    	}
        body.login div#login h1 a {
        	width: 320px;
        	height: 129px;
        	margin-left: 4px;
            background-image: url(<?php echo get_bloginfo( 'template_directory' ) ?>/img/logo-med.png);
            background-size: 320px 129px;
            padding-bottom: 30px;
        }
    </style>
<?php }
add_action( 'login_enqueue_scripts', 'dmc_custom_login_logo' );