<?php
/**
 * Plugin Name:     Clovio Pretty Link
 * Plugin URI:      github.com/chigozieorunta/clovio-pretty-link
 * Description:     Display list of Pretty links and prevents user with Author roles from gaining access to main Pretty links.
 * Author:          Chigozie Orunta
 * Author URI:      github.com/chigozieorunta
 * Text Domain:     clovio-pretty-link
 * Domain Path:     /languages
 * Version:         0.1.0
 *
 * @package         Clovio_Pretty_Link
 */

// Autoload Composer libraries/packages here...
require_once( 'vendor/autoload.php' );

/*
 * @Carbon Fields
 * To render theme options page easily
 */
use Carbon_Fields\Container;
use Carbon_Fields\Field;

//Initialize Carbon Fields
add_action( 'after_setup_theme', 'crb_load' );
function crb_load() {
    \Carbon_Fields\Carbon_Fields::boot();
}

//Register Fields
add_action( 'carbon_fields_register_fields', 'crb_attach_theme_options' );
function crb_attach_theme_options() {
    /*
    * @global $wpdb
    * Get results from pretty links database...
    * 
    */
    global $wpdb;
    $links = $wpdb->get_results("SELECT * FROM wp_prli_links ORDER BY created_at ASC");
    $fields = array(); $value = 0;

    /*
    * @$links as $link
    * Loop through and create pretty and target urls.
    * 
    */
    foreach($links as $link) {
        $value++;

        $fieldpretty = Field::make( 'text', 'fieldpretty'.$value, __( 'Pretty URL' ) )
        ->set_attribute( 'readOnly', true )
        ->set_attribute( 'placeholder', home_url().'/'.$link->slug )
        ->set_width(50);

        $fieldtarget = Field::make( 'text', 'fieldtarget'.$value, __( 'Target URL' ) )
        ->set_attribute( 'readOnly', true )
        ->set_attribute( 'placeholder', $link->url )
        ->set_width(50);

        array_push($fields, $fieldpretty, $fieldtarget);
    }

    // Default options page
    $basic_options_container = Container::make( 'theme_options', __( 'Clovio Pretty URLs' ) )
    ->add_fields( $fields );
}

add_filter( 'carbon_fields_theme_options_container_admin_only_access', '__return_false' );

//Disable Pretty links menu for Author
add_action( 'admin_menu', 'disable_pretty_links_for_author_role' );
function disable_pretty_links_for_author_role() {
    $user = wp_get_current_user();
    if ( in_array( 'author', (array) $user->roles ) ) { 
        remove_menu_page( 'edit.php?post_type=pretty-link' ); 
    }
}

//Enable manage options capabilities for Author
add_action( 'admin_init', 'add_author_caps' );
function add_author_caps() {
    global $wp_roles;
    $wp_roles->add_cap( 'author', 'manage_options' );
    $wp_roles->remove_cap( 'author', 'edit_theme_options' );
    $wp_roles->remove_cap( 'author', 'install_plugins' );
    $wp_roles->remove_cap( 'author', 'activate_plugins' );
    $wp_roles->remove_cap( 'author', 'edit_plugins' );
}

//Load JS scripts
add_action( 'admin_enqueue_scripts', 'load_scripts' );
function load_scripts() {
    wp_enqueue_script( 'clovio', plugin_dir_url( __FILE__ ).'js/scripts.js', array('jquery'), 1.0, true );
}