<?php
/**
 * Plugin Name: Ship Per Product
 * Plugin URI: http://cedcommerce.com
 * Description: Per product shipping allows to add shipping costs for individual, based on customer location.
 * Version: 2.0.4
 * Text Domain: ship-per-product
 * Author: CedCommerce
 * Author URI: http://cedcommerce.com
 * Domain Path: /languages
 * License: GPLv3 or later
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 */
/**
 * Exit if accessed directly
 */
if ( ! defined( 'ABSPATH' ) ) 
{
	exit;
}
define('CEDSPP', 'ced_spp_');


define('PLUGIN_DIR_PATH', plugin_dir_path( __FILE__ ));
define('PLUGIN_DIR_URL', plugin_dir_url( __FILE__ ));
define('CED_SPPP_VER', '2.0.3');
$activated = true;
if (function_exists('is_multisite') && is_multisite())
{
	include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
	if ( !is_plugin_active( 'woocommerce/woocommerce.php' ) )
	{
		$activated = false;
	}
}
else
{
	if (!in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins'))))
	{
		$activated = false;
	}
}
if ($activated)
{
	
	define('CEDSPPM_DIR', plugin_dir_path(__FILE__));
	define('CEDSPPM_URL', plugin_dir_url(__FILE__));
	define('CEDSPPM_DATABASE', "1.0.0.1");

	global $ced_pbs_db_version;
	$ced_pbs_db_version = '1.0';
	register_activation_hook( __FILE__, 'ced_pbs_create_table' );
	function ced_pbs_create_table()
	{
		global $wpdb;
		$table_name = $wpdb->prefix . 'pbs';
		$charset_collate = $wpdb->get_charset_collate();
		$sql = "CREATE TABLE $table_name (
		product_id mediumint(9) NOT NULL ,
		country_code text NOT NULL,
		state_code text NOT NULL,
		city text,
		zip_code text NOT NULL,
		line_cost int(9) NOT NULL,
		item_cost int(9) NOT NULL,
		type text NOT NULL,
		unique_id text NOT NULL
		) $charset_collate;";
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );
	}
	add_filter( 'plugin_action_links','ced_spp_doc_settings', 10, 5 );
	/**
	 * This function is to add setting and docs link on plugin list page.
	 * 
	 * @name ced_spp_doc_settings()
	 * @author CedCommerce
	 * 
	*/
	function ced_spp_doc_settings( $actions, $plugin_file )
	{
		static $plugin;
		if (!isset($plugin))
		{
			$plugin = plugin_basename(__FILE__);
		}
		if ($plugin == $plugin_file)
		{
			$settings = array('settings' => '<a href="'.home_url('/wp-admin/admin.php?page=wc-settings&tab=shipping&section=ced_pps').'">' . __('Settings', 'ship-per-product') . '</a>');
			$actions = array_merge($settings, $actions);
		}
		return $actions;
	}
	
	include_once( CEDSPPM_DIR . 'includes/class-ship-per-product.php' );
	
}
else
{
	/**
	 * Show error notice if WooCommerce is not activated.
	 * 
	 * @name ced_spp_plugin_error_notice()
	 * @author CedCommerce
	 * 
	 */
	function ced_spp_plugin_error_notice() 
	{

		?>
		<div class="error notice is-dismissible">
			<p><?php _e( 'WooCommerce is not activated. Please install WooCommerce to use the Product Based Shipping !!!', 'ship-per-product' ); ?></p>
		</div>
		<?php
			
	}
	
	add_action( 'admin_init', 'ced_spp_plugin_deactivate' );
	
	/**
	 * Deactivate extension if WooCommerce is not activated.
	 * 
	 * @name ced_spp_plugin_deactivate()
	 * @author CedCommerce
	 * 
	 */
	function ced_spp_plugin_deactivate() 
	{
		deactivate_plugins( plugin_basename( __FILE__ ) );
		add_action( 'admin_notices', 'ced_spp_plugin_error_notice' );
	}
}
?>