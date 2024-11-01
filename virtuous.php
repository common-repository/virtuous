<?php
/*
Plugin Name: Virtuous
Plugin URI: http://www.virtuoussoftware.com
Description: Integrate Virtuous software into your site!
Version: 1.0.0.0
Author: Virtuous Software
Author URI: https://virtuoussoftware.com
License: A "Slug" license name e.g. GPL2
Last Update: March 16, 2016
*/
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

define( 'VIRTUOUS_PLUGIN_VERSION', '1.0' );
define( 'VIRTUOUS_PLUGIN_DIR', plugin_dir_url( __FILE__ ) );
define( 'VIRTUOUS_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );

if ( ! class_exists( 'VIRTUOUS' ) ) :

class VIRTUOUS {
    public static function get_base_path() {
        return dirname( __FILE__ );
    }
}

endif;

$core_filename = dirname( __FILE__ ) . '/lib/core/class-virtuous-core.php';

if ( file_exists( $core_filename ) ) {
    require_once( $core_filename );
    $VirtuousCore = new VirtuousCore();
}

include( dirname( __FILE__ ) . '/lib/conf/config.php' );

// Options Menu and Page
add_action( 'admin_menu', 'vdrm_add_virtuous_menu' );

function vdrm_add_virtuous_menu() {

    $icon = VIRTUOUS_PLUGIN_DIR . '/assets/icons/virtuous-icon.png';
    $options_file = 'virtuous/admin/api-settings.php';
    $projects_file = 'virtuous/admin/project-settings.php';

    add_menu_page ( 
        'Virtuous',
        'Virtuous',
        'manage_options',
        $options_file,
        '',
        $icon
     );

    add_submenu_page ( $options_file, 'Virtuous API Settings', 'API Settings', 'manage_options', $options_file );
    add_submenu_page ( $options_file, 'Virtuous Project Settings', 'Project Settings', 'manage_options', $projects_file );
    
    add_action( 'admin_init', 'vdrm_register_virtuous_settings' );
}

function vdrm_register_virtuous_settings() {

    register_setting( 'virtuous-settings-group', 'virtuous_api_environment' );
    register_setting( 'virtuous-settings-group', 'virtuous_api_login_email' );
    register_setting( 'virtuous-settings-group', 'virtuous_api_include_support_button' );
    register_setting( 'virtuous-settings-group', 'virtuous_api_support_button_text' );
    register_setting( 'virtuous-settings-group', 'virtuous_api_support_form_base_url' );
    register_setting( 'virtuous-settings-group', 'virtuous_api_project_types' );
}

function vdrm_virtuous_plugin_menu() {

    add_options_page( 'Virtuous Options', 'Virtuous', 'manage_options', 'api-settings', 'virtuous_options' );
    add_options_page( 'Virtuous Projects', 'Virtuous', 'manage_projects', 'project-settings', 'virtuous_projects' );
}

/**
 * javascript includes
 */
function vdrm_uikit_script_func() {

    wp_enqueue_script( 'uikit', plugins_url( '/assets/js/uikit.js', __FILE__ ), array( 'jquery' ) );
}

function vdrm_virtuous_script_func() {

    wp_enqueue_script( 'virtuous', plugins_url( '/assets/js/virtuous.js', __FILE__ ), array( 'jquery' ) );
    wp_localize_script( 'virtuous', 'virtuous_ajax_script', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) );        

}

/**
 * css stylesheet includes
 */
function vdrm_uikit_style_func() {

    wp_register_style( 'uikitstyle', plugins_url( '/assets/css/uikit.css', __FILE__ ) );
    wp_enqueue_style( 'uikitstyle' );
}

add_action( 'admin_enqueue_scripts', 'vdrm_admin_register_head' );
function vdrm_admin_register_head() {  
    wp_enqueue_script( 'virtuous', plugins_url( '/assets/js/virtuous.js', __FILE__ ), array( 'jquery' ) );
    wp_register_style( 'adminstyle', plugins_url( '/assets/css/admin.css', __FILE__ ) );
    wp_enqueue_style( 'adminstyle' );
}

add_action( 'wp_enqueue_scripts', 'vdrm_uikit_style_func' );
add_action( 'wp_enqueue_scripts', 'vdrm_uikit_script_func' );
add_action( 'wp_enqueue_scripts', 'vdrm_virtuous_script_func' );



