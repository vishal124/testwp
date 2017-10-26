<?php
/**
 * Exit if accessed directly
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if (! class_exists('Ced_ship_per_product'))
{
	/**
	 * This is main class of plugin
	 *
	 * @name    Ced_ship_per_product
	 * @category Class
	 * @author   CedCommerce <plugins@cedcommerce.com>
	 */
	class Ced_ship_per_product 
	{
		var $id='ced_pps';
		
		/**
		 * This function is a constructor of class
		 * 
		 * @name __construct()
		 * @author CedCommerce <plugins@cedcommerce.com>
		 */
		function __construct()
		{
			add_action( 'woocommerce_cart_calculate_fees',array($this, 'ced_spp_woocommerce_custom_surcharge' ));
			add_filter ( 'woocommerce_shipping_methods', array(&$this, 'ced_spp_shipping_method' ));
			add_action ( 'woocommerce_shipping_init',  array(&$this, 'ced_spp_product_based_shipping_method_init' ));
			
			// Display Fields
			add_action( 'woocommerce_product_options_shipping',  array(&$this,  'ced_spp_add_custom_general_fields' ),10,2);
			// Save Fields
			add_action( 'woocommerce_process_product_meta',  array(&$this,  'ced_spp_add_custom_general_fields_save' ));
			add_action ( 'admin_enqueue_scripts', array (&$this, 'ced_spp_ptp_add_theme_scripts') );
			add_action ( 'wp_ajax_nopriv_extractcsv_file', array (&$this, 'ced_spp_extract_csv') );
			add_action ( 'wp_ajax_extractcsv_file', array (	&$this, 'ced_spp_extract_csv') );
			add_action ( 'wp_ajax_nopriv_ced_pbs_delete_rows', array (&$this, 'ced_pbs_delete_rows') );
			add_action ( 'wp_ajax_ced_pbs_delete_rows', array (	&$this, 'ced_pbs_delete_rows') );
			
			add_action ( 'wp_ajax_nopriv_ced_pbs_showing_codes', array (&$this, 'ced_pbs_showing_codes') );
			add_action ( 'wp_ajax_ced_pbs_showing_codes', array (	&$this, 'ced_pbs_showing_codes') );
			
			
			add_action ( 'wp_ajax_ced_pbs_datable_process_edit', array (	&$this, 'ced_pbs_datable_process_edit') );
			add_action ( 'wp_ajax_nopriv_ced_pbs_datable_process_edit', array (&$this, 'ced_pbs_datable_process_edit') );
			add_action ( 'admin_init', array ( &$this, 'ced_spp_csv_export') );
			add_action ( 'plugins_loaded', array ($this, 'ced_spp_load_textdomain') );
			add_action ( 'init', array ($this, 'ced_spp_check_database_version') );

		}
		
		
		
		function ced_pbs_datable_process_edit()
		{
			/*
			 * DataTables example server-side processing script.
			*
			* Please note that this script is intentionally extremely simply to show how
			* server-side processing can be implemented, and probably shouldn't be used as
			* the basis for a large complex system. It is suitable for simple use cases as
			* for learning.
			*
			* See http://datatables.net/usage/server-side for full details on the server-
			* side processing requirements of DataTables.
			*
			* @license MIT - http://datatables.net/license_mit
			*/
				
			/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
			 * Easy set variables
			*/
			global $wpdb;
			// DB table to use
			$table = $wpdb->prefix . 'pbs';
				
			// Table's primary key
			$primaryKey = 'unique_id';
				
			// Array of database columns which should be read and sent back to DataTables.
			// The `db` parameter represents the column name in the database, while the `dt`
			// parameter represents the DataTables column identifier. In this case simple
			// indexes
			$columns = array(
					array( 'db' => 'country_code', 'dt' => 0 ),
					array( 'db' => 'country_code', 'dt' => 1 ),
					array( 'db' => 'state_code',  'dt' => 2),
					array( 'db' => 'city',   	  'dt' => 3 ),
					array( 'db' => 'zip_code',    'dt' => 4 ),
					array( 'db' => 'product_id',  'dt' => 5 ),
					array( 'db' => 'line_cost',   'dt' => 6),
					array( 'db' => 'item_cost',   'dt' => 7 ),
					array( 'db' => 'unique_id',   'dt' => 8 ),
			);
				
			// SQL server connection information
			$sql_details = array(
					'user' => DB_USER,
					'pass' => DB_PASSWORD,
					'db'   => DB_NAME,
					'host' => DB_HOST
			);
				
			if(!empty($_POST['varids']))
			{
				
			}	
			/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
			 * If you just want to use the basic configuration for DataTables with PHP
			* server-side, there is no need to edit below this line.
			*/
				
			require( 'ssp.class.php' );
			$whereall= "type = 'product' and product_id='".$_POST['postID']."'";
			$data = SSP::complex( $_POST, $sql_details, $table, $primaryKey, $columns, $whereall,$whereall );
			
			$outputHtml = "";
			$all_data = $data['data'];
		
			$size =1;
			foreach($all_data as $key => $value)
			{
				$html1 = '<input type="checkbox" data-uid="'.$value[8].'" name="select">';
				$data['data'][$key][0] = $html1;
				$html2 = '<input type="text" value="'.$value[1].'" name="'.$this->id.'_country['. $size.']" class="country_n" placeholder="'. __('Country Code','ship-per-product').'">';
				$data['data'][$key][1] = $html2;
				$html3 = '<input type="text" value="'.$value[2].'" name="'.$this->id.'_state['. $size.']" class="state_n" placeholder="'. __('State Code','ship-per-product').'">';
				$data['data'][$key][2] = $html3;
				$html4 = '<input type="text" value="'.$value[3].'" name="'.$this->id.'_city['. $size.']" class="city_n" placeholder="'. __('City','ship-per-product').'">';
				$data['data'][$key][3] = $html4;
				$html5 = '<input type="text" value="'.$value[4].'" name="'.$this->id.'_zip['. $size.']" class="zip_n" placeholder="'. __('Zip Code','ship-per-product').'">';
				$data['data'][$key][4] = $html5;
				$html7 = '<input type="text" value="'.$value[6].'" name="'.$this->id.'_line['. $size.']" class="line_n" placeholder="'. __('Line cost','ship-per-product').'">';;
				$data['data'][$key][5] = $html7;
				$html8 = '<input type="text" value="'.$value[7].'" name="'.$this->id.'_item['. $size.']" class="country_n" placeholder="'. __('Item Code','ship-per-product').'"><input type="hidden" value="'.$value[8].'" name="'.$this->id.'_uid['. $size.']" class="country_n">';
				$data['data'][$key][6] = $html8;
				$size++;
			}
			echo json_encode($data);
			die;
		}
		
		
		/**
		 * This function is for showing codes of different states
		 * 
		 * @name ced_pbs_showing_codes
		 * @author   CedCommerce <plugins@cedcommerce.com>
		 * 
		 */
		function ced_pbs_showing_codes()
		{
			$countryCode = $_POST['countryCode'];
			global $woocommerce;
			$countries_obj   = new WC_Countries();
			$countries   = $countries_obj->__get('countries');
			$countyStates = $countries_obj->get_states($countryCode);
			echo json_encode($countyStates);die;
		}
		
		
		/** 
		 * This function is for deleting shipping rates
		 * 
		 * @name ced_pbs_delete_rows
		 * @author   CedCommerce <plugins@cedcommerce.com>
		 */
		function ced_pbs_delete_rows()
		{
			
			global $wpdb;
			$table_name = $wpdb->prefix . 'pbs';
			$cleanData = ($_POST['arrTodelte']);
			$tempData = str_replace("\\", "",$cleanData);
			$finalArrData = json_decode($tempData);
			
			foreach ($finalArrData as $key => $value)
			{
				$wpdb->delete( $table_name, array( 'unique_id' => $value,'type'=>'product' ) );
			}
		}
		
		/**
		 * This function is to Calculates Handling fee for Ship Per Product
		 * 
		 * @name ced_spp_woocommerce_custom_surcharge
		 * @param unknown $cartref
		 * @author   CedCommerce <plugins@cedcommerce.com>
		 */
		function ced_spp_woocommerce_custom_surcharge($cartref)
		{
			$ship = new ship_per_product();
			$this->enabled                = $ship->get_option('enabled');
			$this->handling_cal_per_prod  = $ship->get_option(0) ;
			$this->handling_cal_per_order = $ship->get_option(2) ;
			$this->handling_fee_per_prod  = $ship->get_option(1);
			$this->handling_fee_per_order = $ship->get_option(3);
			$this->handling_fee_per_prod  = $ship->ced_spp_tofloat($this->handling_fee_per_prod);
			$this->handling_fee_per_order = $ship->ced_spp_tofloat($this->handling_fee_per_order);
			if('no' != $this->enabled)
			{
				global $woocommerce;
				$surcharge = '';
				if ( is_admin() && ! defined( 'DOING_AJAX' ) )
					return;
					
				if(!empty($this->handling_fee_per_order))
				{
					if($this->handling_cal_per_order =='percent')
					{
						$surcharge = ($woocommerce->cart->cart_contents_total + $woocommerce->cart->shipping_total ) * ($this->handling_fee_per_order / 100);
					}
					else
					{
						$surcharge = $this->handling_fee_per_order;
					}
				}
				if(!empty($this->handling_fee_per_prod))
				{
					if($this->handling_cal_per_prod =='percent' && is_array($woocommerce->cart->cart_contents))
					{
						foreach ($woocommerce->cart->cart_contents as $item_id=>$contents)
						{
							$surcharge += ($contents['data']->price * ($this->handling_fee_per_prod / 100)) * $contents['quantity'];
						}
					}
					else if(is_array($woocommerce->cart->cart_contents))
					{
						foreach ($woocommerce->cart->cart_contents as $item_id=>$contents)
						{
							$surcharge += $this->handling_fee_per_prod * $contents['quantity'];
						}
					}
			
				}
				if(!empty($surcharge))
					$cartref->add_fee( 'Handling Fee', $surcharge, true);
			} 
		}
		/**
		 * 
		 * Saves old option table database values to new database table
		 * 
		 * @author   CedCommerce <plugins@cedcommerce.com>
		 * @name ced_spp_check_database_version
		 *
		 */

		function ced_spp_check_database_version(){
			
			$get_current_db = get_option('CEDSPPM'.CEDSPPM_DATABASE);
			
			if($get_current_db == null){
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
				
				$prev_data = get_option('ship_per_product_details');
				
				$arr = array();
				if(!empty($prev_data) && is_array($prev_data)) {
					foreach ($prev_data as $sku=>$country_arr) {
						if(is_array($country_arr)){
							foreach ($country_arr as $country=>$state_arr) {
								if(is_array($state_arr)) {
									foreach ($state_arr as $state=>$city_arr) {
										if(is_array($city_arr)) {
											foreach ($city_arr as $city=>$zip_arr) {
												if(is_array($zip_arr)) {
													foreach ($zip_arr as $zip=>$val) {
														if(is_array($val)) {
															$id = $sku;
															$product = wc_get_product( $sku );
															if($city == ""){
																$city="*";
															}
															if($zip == ""){
																$zip="*";
															}
															if($state == ""){
																$state="*";
															}
															
															$arr[]= array ($country, $state, $city, $zip, $id, $val[0], $val[1]);
														}
													}
												}
											}
										}
									}
								}
							}
						}
					}
				}
				
				$args = array(
						'post_type'  => 'product',
						'meta_key'	=> 'prod_per_ship_rate 	'

				);
				$loop = new WP_Query( $args );
				
				
				$arr1 = array();
				if ( $loop->have_posts() ) {
					while ( $loop->have_posts() ) : $loop->the_post();
					
					$get_prod_value = get_post_meta($loop->post->ID, 'prod_per_ship_rate');
					
					if(!empty($get_prod_value) && is_array($get_prod_value)) {
						foreach ($get_prod_value as $sku=>$country_arr) {
							if(is_array($country_arr)){
								foreach ($country_arr as $country=>$state_arr) {
									if(is_array($state_arr)) {
										foreach ($state_arr as $state=>$city_arr) {
											if(is_array($city_arr)) {
												foreach ($city_arr as $city=>$zip_arr) {
													if(is_array($zip_arr)) {
														foreach ($zip_arr as $zip=>$val) {
															if(is_array($val)) {
																$product = wc_get_product( $loop->post->ID );
																if($city == ""){
																	$city="*";
																}
																if($zip == ""){
																	$zip="*";
																}
																if($state == ""){
																	$state="*";
																}
																	
																$arr1[]=array($country, $state, $city, $zip, $loop->post->ID, $val[0], $val[1]);
															}
														}
													}
												}
											}
										}
									}
								}
							}
						}
					}
						
					endwhile;
				}
				
				

				$arr=array_merge($arr,$arr1);
				if(!empty($arr)){

				
					foreach ($arr as $key => $value) {
						
						global $wpdb;
						$unique_id = $value[4].$value[0].$value[1].$value[2].$value[3].$value[5].$value[6];
						$table_name = $wpdb->prefix . 'pbs';
						
						$retrieve_data = $wpdb->get_results( "SELECT * FROM $table_name where `unique_id` ='".$unique_id."'" );
						
						if($value[5] == 0){
							$value[5] = "0";
						}
						if($value[6] == 0){
							$value[6] = "0";
						}
						if(empty($retrieve_data)){
							if($value[4] != "" && $value[4] != null && $value[0] != "" && $value[0] != null && $value[1] != "" && $value[1] != null && $value[5] != "" && $value[5] != null && $value[6] != "" && $value[6] != null)
							{
								$value[5] = (int)$value[5];
								$value[6] = (int)$value[6];
								$wpdb->insert(
										$table_name,
										array(
												'product_id'   => $value[4],
												'country_code' => $value[0],
												'state_code'   => $value[1],
												'city'         => $value[2],
												'zip_code'     => $value[3],
												'line_cost'    => $value[5],
												'item_cost'    => $value[6],
												'unique_id'    => $unique_id,
												'type'         => 'product' 
										)
								);
							}
						}
					}

				}
					
				update_option('CEDSPPM'.CEDSPPM_DATABASE, CEDSPPM_DATABASE);	

			}
			
		}
		
		/**
		 * This function is to loads text Domain
		 * 
		 * @name ced_spp_load_textdomain
		 * @author   CedCommerce <plugins@cedcommerce.com>
		 */
		function ced_spp_load_textdomain()
		{
			
			$domain = "ship-per-product";
			$locale = apply_filters ( 'plugin_locale', get_locale (), $domain );
			load_textdomain ( $domain, PLUGIN_DIR_PATH . 'languages/' . $domain . '-' . $locale . '.mo' );
			load_plugin_textdomain ( 'ship-per-product', false, plugin_basename ( dirname ( __FILE__ ) ) . '/languages' );
				
		}
			
		/**
		 * Download CSV file of Shipping Details
		 * Extract CSV for both Product Shipping Details as well as Global Shipping Detail
		 * @name ced_spp_csv_export
		 * @author   CedCommerce <plugins@cedcommerce.com>
		 */
		function ced_spp_csv_export() 
		{
			
			if ('csv' == (isset ( $_GET ['format'] ) ? $_GET ['format'] : null)) 
			{
				global $pagenow;
				if($pagenow == 'admin.php') 
				{
					global $wpdb;
					$table_name = $wpdb->prefix . 'pbs';
					$retrieve_data = $wpdb->get_results( "SELECT * FROM $table_name");
					if(!empty($retrieve_data) && is_array($retrieve_data))
					{
						foreach ($retrieve_data as $key=>$value)
						{
							$id = $value->product_id;
							$product = new WC_Product($id);
							$sku = $product->get_sku();
							$arr[]= array ($value->country_code, $value->state_code, $value->city, $value->zip_code, $sku, $id, $value->line_cost, $value->item_cost);
						}
					}
					$this->ced_spp_array_to_csv_download($arr,
							"pbs_rates.csv"
					);
					die;
				} 
				else 
				{
					if($pagenow == 'post.php') 
					{
						
						$postId = $_GET ['post'];
						global $wpdb;
						$table_name = $wpdb->prefix . 'pbs';
						$retrieve_data = $wpdb->get_results("SELECT * FROM $table_name where `product_id` = '".$postId."'");
						if(!empty($retrieve_data) && is_array($retrieve_data))
						{
							foreach ($retrieve_data as $key=>$value)
							{
								$id = $value->product_id;
								$product = new WC_Product($id);
								$sku = $product->get_sku();
								$arr[]= array ($value->country_code, $value->state_code, $value->city, $value->zip_code, $sku, $id, $value->line_cost, $value->item_cost);
							}
						}
						$this->ced_spp_array_to_csv_download($arr,
								"pbs_rates.csv"
						);
						die;
					}
				}
			}
				
		}
		
		/**
		 * Adds a custom fields to Shipping Tab in Product Edit screen 
		 * 
		 * @name ced_spp_add_custom_general_fields
		 * @author   CedCommerce <plugins@cedcommerce.com>
		 */
		function ced_spp_add_custom_general_fields() 
		{
			global $woocommerce, $post;
			// Checkbox
			woocommerce_wp_checkbox(
				array(
						'id' => 'enable_ced_product_based_shipping',
						'wrapper_class' => 'options_group',
						'label' => __('Ship Per Product', 'ship-per-product' ),
						'description' => __( 'Check to enable.', 'ship-per-product' )
					)
			); 
		}	
					
		/**
		 * Saves shipping details of a product
		 * @name ced_spp_add_custom_general_fields_save
		 * @author   CedCommerce <plugins@cedcommerce.com>
		 * @param int $post_id
		 */
		function ced_spp_add_custom_general_fields_save($post_id) 
		{
			
			$woocommerce_checkbox = isset ( $_POST ['enable_ced_product_based_shipping'] ) ? 'yes' : 'no';
			update_post_meta ( $post_id, 'enable_ced_product_based_shipping', $woocommerce_checkbox );
				
		}
		/**
		 * To store the shipping details array
		 * @author   CedCommerce <plugins@cedcommerce.com>
		 * @name ced_pbs_insert_product_edit_shipping
		 */
		public function ced_pbs_insert_product_edit_shipping($arrayTosave)
		{ 
			
			global $wpdb;
			$toInsertarray = $arrayTosave;

			foreach ($toInsertarray as $key => $value)
			{
				
				if($value['unique_id'] == "" || $value['unique_id'] == null)
				{
					
					$unique_id = $value['product_id'].$value['country_code'].$value['state_code'].$value['city'].$value['zip_code'].$value['line'].$value['item'];
					$table_name = $wpdb->prefix . 'pbs';
					$retrieve_data = $wpdb->get_results( "SELECT * FROM $table_name where `unique_id` ='".$unique_id."'" );
					
					if(empty($retrieve_data))
					{
						if($value['city'] == "")
						{
							$value['city'] = "*";
						}
						if($value['zip_code'] == "")
						{
							$value['zip_code'] = "*";
						}
						if($value['state_code'] == "")
						{
							$value['state_code'] = "*";
						}
						if($value['product_id'] != "" && $value['product_id'] != null && $value['country_code'] != "" && $value['country_code'] != null && $value['line'] != "" && $value['line'] != null && $value['item'] != "" && $value['item'] != null)
						{
							
							$wpdb->insert(
									$table_name,
									array(
											'product_id'   => $value['product_id'],
											'country_code' => $value['country_code'],
											'state_code'   => $value['state_code'],
											'city'         => $value['city'],
											'zip_code'     => $value['zip_code'],
											'line_cost'    => $value['line'],
											'item_cost'    => $value['item'],
											'unique_id'    => $unique_id,
											'type'         => 'product' 
									)
							);
						}
					}
				}
				else{
					$unique_id = $value['unique_id'];
					$new_unique_id = $value['product_id'].$value['country_code'].$value['state_code'].$value['city'].$value['zip_code'].$value['line'].$value['item'];
					global $wpdb;
					$table_name = $wpdb->prefix . 'pbs';
					if($value['city'] == "")
					{
						$value['city'] = "*";
					}
					if($value['zip_code'] == "")
					{
						$value['zip_code'] = "*";
					}
					if($value['state_code'] == "")
					{
						$value['state_code'] = "*";
					}
					if($value['product_id'] != "" && $value['product_id'] != null && $value['country_code'] != "" && $value['country_code'] != null && $value['line'] != "" && $value['line'] != null && $value['item'] != "" && $value['item'] != null)
					{
						$dataToupdate = array(
												'product_id'   => $value['product_id'],
												'country_code' => $value['country_code'],
												'state_code'   => $value['state_code'],
												'city'         => $value['city'],
												'zip_code'     => $value['zip_code'],
												'line_cost'    => $value['line'],
												'item_cost'    => $value['item'],
												'unique_id'    => $new_unique_id
										);
						$where = array('unique_id' => $unique_id,'type' =>'product');
						$update_data = $wpdb->update( $table_name, $dataToupdate, $where );
					}
				}
			}
		}	
		/**
		 * Adds a new Shipping Method
		 * @name ced_spp_shipping_method
		 * @author   CedCommerce <plugins@cedcommerce.com>
		 * @param array $methods
		 * @return array
		 */
		function ced_spp_shipping_method($methods) 
		{
			$methods [] = 'ship_per_product';
			return $methods;
		}
		
		/**
		 * This function is to initialize Shipping Method
		 * 
		 * @name ced_spp_product_based_shipping_method_init
		 * @author   CedCommerce <plugins@cedcommerce.com>
		 */
		function ced_spp_product_based_shipping_method_init() 
		{
			require_once CEDSPPM_DIR. 'includes/ship-per-product-options.php';
		}
				
		/**
		 * This function is to enqueue Theme Scripts
		 * 
		 * @name ced_spp_ptp_add_theme_scripts
		 * @author   CedCommerce <plugins@cedcommerce.com>
		 */
		function ced_spp_ptp_add_theme_scripts() 
		{
			
			$ifEdit = "";
			$varIDs =array();
			$postId = "";
			if(isset($_GET['action']))
			{
				if($_GET['action'] == 'edit')
				{
					$ifEdit  = $_GET['action'];
					$postId  = $_GET['post'];
					$product = get_product($postId);
				}
			}
			$current_setting = "";
			if(isset($_GET['section']))
			{
				$current_setting  = $_GET['section'];
			}
			$currentSubsection = "";
			if(isset($_GET['sub-section']))
			{
				$currentSubsection = $_GET['sub-section'];
			}
			wp_enqueue_script ( 'extractcsv', WP_PLUGIN_URL. '/ship-per-product/assets/js/ship-per-product-extractcsv.js', array (
					'jquery'
			), CED_SPPP_VER,true);
			
			wp_enqueue_style( 'ced_pbs_admin_datatable_style',WP_PLUGIN_URL . '/ship-per-product/assets/css/jquery.dataTables.min.css', array(), CED_SPPP_VER );
			wp_enqueue_script( 'ced_pbs_admin_datatable_script',WP_PLUGIN_URL . '/ship-per-product/assets/js/jquery.dataTables.min.js',array('jquery'), CED_SPPP_VER,true );
			wp_localize_script ( 'extractcsv', 'extractcsv_obj', array (
								'ajax_url'           => admin_url ( 'admin-ajax.php' ),
								'ID'                 => $this->id,
								'del_conf'	         => __('Delete the selected rows', 'ship-per-product'),
								'current_url'        => $current_setting,
								'current_subsection' => $currentSubsection,
								'csv_error'          => __('Please choose a CSV file.','ship-per-product'),
								'country_code'       => __('Country Code','ship-per-product'),
								'state_code'         => __('State Code','ship-per-product'),
								'city_code'          => __('City Code','ship-per-product'),
								'zip_code'           => __('Zip Code','ship-per-product'),
								'line_cost'          => __('Line Cost','ship-per-product'),
								'item_cost'          => __('Item Cost','ship-per-product'),
								'required'           => __('Please fill all required fields','ship-per-product'),
								'no_states'          => __('No states found','ship-per-product'),
								'postID'             => $postId,
								'ifEdit'             => $ifEdit,
								'varIDs'             => $varIDs
			) );
			
			wp_enqueue_style( 'popupdfcss',WP_PLUGIN_URL.'/ship-per-product/assets/css/ship-per-product-admin.css', array(), CED_SPPP_VER );
				
		}
			
		/**
		 * Extract Product Shipping Detail csv file to array 
		 * 
		 * @name ced_spp_extract_csv
		 * @author   CedCommerce <plugins@cedcommerce.com>
		 */
		function ced_spp_extract_csv() 
		{
			
			$productId = $_POST['productId'];
			if(current_user_can('manage_options')) 
			{
				$data = array();
				$error = false;
				$files = array();
				$uploaddir = CEDSPPM_DIR.'uploads/';
				if(isset($_FILES))
				{
					$file = $_FILES[0]['tmp_name'];
					
					if(file_exists($file))
					{
						$arr_result = array();
						$skipped = 0;
						$imported = 0;
						$comments = 0;
						ini_set('auto_detect_line_endings',true);
						$handle = fopen($file, 'r');
						if($handle) 
						{
							$rows = 0;
							while (($data = fgetcsv($handle, 1000))!== false) 
							{
								if($rows >0)
								{
									if($data[3]!='') 
									{
										//$new_unique_id = $productId.$data[0].$data[1].$data[2].$data[3].$data[4].$data[5];
										$arrTosave[] = array(
															'product_id'=>$productId,
															'country_code'=>$data[0],
															'state_code'=>$data[1],
															'city'=>$data[2],
															'zip_code'=>$data[3],
															'line'=>$data[4],
															'item'=>$data[5],
															'unique_id'    => "",
													);
									}
								}
								$rows++;
							}
						
							$this->ced_pbs_insert_product_edit_shipping($arrTosave);
							fclose($handle);
						}
					}
				}
				else 
				{
					$error = true;
				}

				$data = ($error) ? array('error' => 'There was an error uploading your files') : array('success' => "sucess");
				
				echo json_encode($data);
				wp_die();
			} 
			else 
			{
				wp_send_json_error('You do not have sufficient permissions to access this page');
			}
				
		}
			
		/**
		 * Force download of csv file
		 * 
		 * @name ced_spp_array_to_csv_download
		 * @author CedCommerce <plugins@cedcommerce.com>
		 * @param array $array
		 * @param string $filename
		 * @param string $delimiter
		 */
		function ced_spp_array_to_csv_download($array, $filename = "export.csv", $delimiter=";") 
		{
			
			// open raw memory as file so no temp files needed, you might run out of memory though
			$f = fopen('php://memory', 'w');
			if(is_array($array))
			{
				fputcsv($f, array('Country','State','City','Zip code','SKU','ID','Line cost','Item cost'));
				foreach ($array as $line)
				{
					fputcsv($f, $line, $delimiter);
				}
			}
			fseek($f, 0);
			// tell the browser it's going to be a csv file
			header('Content-Type: application/csv');
			// tell the browser we want to save it instead of displaying it
			header('Content-Disposition: attachment; filename="'.$filename.'";');
			fpassthru($f);
			
		}
	}
	$ced_ship_per_product = new Ced_ship_per_product();
}
?>