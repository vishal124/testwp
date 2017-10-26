<?php
/**
 * @package Cryptocurrency All-in-One
 */
/*
Plugin Name: Cryptocurrency All-in-One
Plugin URI: https://creditstocks.com/
Description: Provides multiple cryptocurrency features: accepting payments, displaying prices and exchange rates, cryptocurrency calculator, accepting donations, counterparty asset explorer.
Version: 2.4.2
Author: Boyan Yankov
Author URI: http://byankov.com/
License: GPL2 or later
*/

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

//define plugin url global
define('CP_URL', plugin_dir_url( __FILE__ ));

//include source files
require_once( dirname( __FILE__ ) . '/includes/currencyprice.php' );
require_once( dirname( __FILE__ ) . '/includes/currencygraph.php' );
require_once( dirname( __FILE__ ) . '/includes/cryptodonation.php' );
require_once( dirname( __FILE__ ) . '/includes/cryptopayment.php' );
require_once( dirname( __FILE__ ) . '/includes/allcurrencies.php' );
require_once( dirname( __FILE__ ) . '/includes/xcp.php' );
require_once( dirname( __FILE__ ) . '/includes/common.php' );
require_once( dirname( __FILE__ ) . '/includes/widget.php' );

if ( is_admin() || ( defined( 'WP_CLI' ) && WP_CLI ) ) {
	require_once( dirname( __FILE__ ) . '/includes/admin.class.php' );
	add_action( 'init', array( 'CPAdmin', 'init' ) );
}

//define suported shortcodes
add_shortcode( 'currencyprice', 'cp_currencyprice_shortcode' );
add_shortcode( 'currencygraph', 'cp_currencygraph_shortcode' );
add_shortcode( 'cryptodonation', 'cp_cryptodonation_shortcode' );
add_shortcode( 'donation', 'cp_cryptodonation_shortcode' ); //deprecated!!!
add_shortcode( 'cryptopayment', 'cp_cryptopayment_shortcode' );
add_shortcode( 'allcurrencies', 'cp_all_currencies_shortcode' );
add_shortcode( 'xcpasset', 'cp_xcpasset_shortcode' );

//this plugin requires jquery library
function cp_load_jquery() {
    wp_enqueue_script( 'jquery' );
}
add_action( 'wp_enqueue_script', 'cp_load_jquery' );

//handle plugin activation
register_activation_hook( __FILE__, 'cp_plugin_activate' );

//add widget support
function cp_shortcode_widget_init(){
	register_widget('CP_Shortcode_Widget');
}
add_action('widgets_init','cp_shortcode_widget_init');

//add custom stylesheet
add_action('wp_head', 'cryptocurrency_prices_custom_styles', 100);