<?php
/**
 * Exit if accessed directly
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if (! class_exists('ship_per_product'))
{
	/**
	 * This is class for adding shipping method
	 *
	 * @name    ship_per_product
	 * @category Class
	 * @author CedCommerce <plugins@cedcommerce.com>
	 */
	class ship_per_product extends WC_Shipping_Method 
	{
		public function __construct() 
		{
			$this->ced_spp_init();
			$this->ced_spp_save_per_prod_shipping();
			// Load the settings.
			$this->ced_spp_init_form_fields();
			$this->init_settings();
			add_action ( 'woocommerce_update_options_shipping_' . $this->id, array ($this,'process_admin_options') );
		}
		
		/**
		 * Initialize setting for Ship per product
		 * 
		 * @name ced_spp_init
		 * @author CedCommerce <plugins@cedcommerce.com>
		 */
		function ced_spp_init() 
		{
			$this->id = "ced_pps";
			$this->method_title = __( 'Ship Per Product', 'ship-per-product' );
			$this->shipping_descr = __('This Shipping Method charges shipping cost based on product shipping location', 'ship-per-product');
			$this->enabled = $this->get_option('enabled');
			$this->title = $this->get_option('title');
			$this->include_tax = 'yes' === $this->get_option('include_tax') ? true : false;
			$this->default_shipping_Cost = $this->get_option('default_shipping_Cost');
			$this->ced_countries = $this->get_option('ced_countries') ? $this->get_option('ced_countries') : array();
			$this->skip_free_shipping = $this->get_option('skip_free_shipping');
			$this->handling_cal_per_prod=$this->get_option(0) ;
			$this->handling_cal_per_order=$this->get_option(2) ;
			$this->handling_fee_per_prod = $this->get_option(1);
			$this->handling_fee_per_order = $this->get_option(3);
			$this->handling_fee_per_prod = $this->ced_spp_tofloat($this->handling_fee_per_prod);
			$this->handling_fee_per_order = $this->ced_spp_tofloat($this->handling_fee_per_order);
			
		}
		
		/**
		 * Add fields to shipping method Ship per product
		 * 
		 * @name ced_spp_init_form_fields
		 * @author CedCommerce <plugins@cedcommerce.com>
		 */
		function ced_spp_init_form_fields() 
		{
			$countries_list = WC ()->countries->get_shipping_countries ();
			$this->form_fields = array (
						'enabled' => array (
							'title' => __ ( 'Enable/Disable', 'ship-per-product' ),
							'type' => 'checkbox',
							'label' => __ ( 'Enable Ship Per Product', 'ship-per-product' ),
							'default' => 'no'
						),
						'title' => array (
							'title' => __( 'Method Title', 'ship-per-product' ),
							'type' => 'text',
							'description' => __( 'This controls the title which user sees during checkout.', 'ship-per-product' ),
							'default' => __( 'Ship Per Product', 'ship-per-product' ),
							'desc_tip'        =>  true,
						)
						,
						'include_tax' => array (
							'title' => __ ( 'Include Tax', 'ship-per-product' ),
							'type' => 'checkbox',
							'default' => 'yes',
							'label' => __ ( 'check to enable tax to this method', 'ship-per-product' ),
						),
										array(
											'title'         => __( 'Handling charge per product', 'ship-per-product' ),
											'desc'          => __( 'Select the mode in which you want to enter amount', 'ship-per-product' ),
											'id'            => 'handling_fee_per_prod',
											'type'          => 'select',
											'checkboxgroup' => 'start',
											'desc_tip'      =>  __('Select in which mode you want ot enter amount.', 'ship-per-product'),
											'options'       =>array('percent'=>__('Percent', 'ship-per-product'),
																	'fixed'=>__('Fixed', 'ship-per-product'))
									),
									
										array(
											'desc'          => __( 'Enter value of handling charge per product', 'ship-per-product' ),
											'id'            => 'handling_fee_per_prod',
											'default'       => 'no',
											'type'          => 'text',
											'class'         =>  'ced-spp-input',
											'desc_tip'      =>  __( 'This fee is added to each and every product in the cart.', 'ship-per-product'  ),
											'checkboxgroup' => 'end',
											'autoload'      => false,
									),
										array(
												'title'         => __( 'Handling charge per order', 'ship-per-product' ),
												'desc'          => __( 'Enable it to enter value in numeric', 'ship-per-product' ),
												'id'            => 'handling_fee_per_order_type',
												'type'          => 'select',
												'checkboxgroup' => 'start',
												'desc_tip'      =>  __('Select the mode in which you want to enter amount.', 'ship-per-product'),
												'options'       =>array('percent'=>"Percent",
																		'fixed'=>"Fixed")
										),
										array(
												'desc'          => __( 'Enter value of handling charge per order', 'ship-per-product' ),
												'id'            => 'handling_fee_per_order',
												'default'       => 'no',
												'type'          => 'text',
												'class'         =>  'ced-spp-input',
												'desc_tip'      =>  __('This fee is added per order.', 'ship-per-product' ),
												'checkboxgroup' => 'end',
												'autoload'      => false,
										),
					
						'default_shipping_Cost' => array (
							'title' => __ ( 'Default shipping charge', 'ship-per-product' ),
							'type' => 'text' ,
							'desc_tip'        =>  true,
							'description' => __ ( 'Default Shipping Price , applicable if product shipping price is not defined.', 'ship-per-product' ),
						),
						'ced_countries' => array (
							'title' => __ ( 'Specific Countries', 'ship-per-product' ),
							'type' => 'multiselect',
							'class' => 'wc-enhanced-select',
							'css' => 'width: 450px;',
							'options' => $countries_list,
							'custom_attributes' => array (
									'data-placeholder' => __ ( 'Select some countries', 'ship-per-product' )
							)
						),
						'skip_free_shipping' => array (
							'title' => __ ( 'Skip free shipping product for calculation', 'ship-per-product' ),
							'type' => 'checkbox',
							'label' => __ ( 'If this field is set, then shipping cost will not be calculated for free shipping products.', 'ship-per-product' ),
							'default' => 'no'
						)
					
					);
			
		}
		
		/**
		 * Adds custom html to shipping page
		 * 
		 * @name admin_options
		 * @author CedCommerce <plugins@cedcommerce.com>
		 * @see WC_Settings_API::admin_options()
		 */			
		public function admin_options() 
		{
			
					$wp_active_sub_section = isset($_GET['sub-section']) ? $_GET['sub-section'] : 'ship-per-product-setting';
				?>
					<h3><?php echo $this->title ?></h3>
					<?php if(!session_id())
						{
							session_start();
						}
					?>
					<?php if(isset($_SESSION['ced_pbs_not_csv'])){
							if($_SESSION['ced_pbs_not_csv'] == "not_csv"){ ?>
							 <div class="error notice is-dismissible">
						        <p><?php _e( 'Seleceted File is not a valid CSV file.', 'ship-per-product' ); ?></p>
						    </div>
					<?php unset($_SESSION['ced_pbs_not_csv']);
						}
					}?>
						<?php if(isset($_SESSION['ced-pbs-import-error'])){
							if($_SESSION['ced-pbs-import-error'] == "import_error"){ ?>
							 <div class="error notice is-dismissible">
						        <p><?php _e( 'CSV file has some rows which has product id which do not exist.', 'ship-per-product' ); ?></p>
						    </div>
					<?php unset($_SESSION['ced-pbs-import-error']);
						}
					}?>
					<div class="wrap product-based-shipping-welcome">
						<p><?php echo $this->shipping_descr?></p>
						<h2 class="nav-tab-wrapper">
							<a href="?page=wc-settings&amp;tab=shipping&amp;section=ced_pps&amp;sub-section=ship-per-product-setting"	class="nav-tab <?php echo $wp_active_sub_section=='ship-per-product-setting' ? 'nav-tab-active' : '';  ?>"><?php _e('Settings', 'ship-per-product'); ?></a>
							<a href="?page=wc-settings&amp;tab=shipping&amp;section=ced_pps&amp;sub-section=ship-per-product-table" class="nav-tab <?php  echo $wp_active_sub_section=='ship-per-product-table' ? 'nav-tab-active' : ''; ?>"><?php _e('Product Based','ship-per-product');?></a>
						</h2>
						<br>
						<?php 
						if($wp_active_sub_section=='ship-per-product-setting') 
						{?>
							<table class="form-table" id="shipping_setting">
								<?php 	$this->generate_settings_html();?>
							</table>
						<?php  
						} 
						else 
						{
							if($wp_active_sub_section=='ship-per-product-table') 
							{
								$GLOBALS['hide_save_button'] = true;
								?>
								<div class="ced_spp_setting">
									<?php $this->generate_settings_html();?>
								</div>
								<?php 
								$products = array();
								$args = array( 'post_type' => 'product', 'posts_per_page' =>-1 );
								$loop = new WP_Query( $args );
								while ( $loop->have_posts() ) : $loop->the_post();
								$product = get_product(get_the_ID());
								//global $product;
									if( $product->is_type( 'variable' )){
										$variation = $product->get_available_variations();
										if(!empty($variation)){
										foreach ($variation as $k=>$v)
										{
											$text = "";
											foreach ($v['attributes'] as $name){
												$text .= "#".$name;
											}
											$products[$v['variation_id']] = get_the_title()." ".$text;
										}
										}
									}
									$products[get_the_ID()] = get_the_title();
								endwhile;
								wp_reset_query();
								global $wpdb;
								$table_name = $wpdb->prefix . 'pbs';
								$final_rate = get_option('product_based_shipping_details');
								$final_rate = $wpdb->get_results( "SELECT * FROM $table_name where type = 'product'");
								?>
								<table class="form-table" id="shipping_rates">
									<tr valign="top" id="ship-per-product">
										<td class="forminput" id="<?php echo $this->id?>_shipping_rate">
											<div class="ced_pbs_panel">
												<h3><?php _e('Import Rates', 'ship-per-product' )?></h3>
											</div>
											<div class="ced_pbs_content_sec" id='ced_pbs_import_upload_csv'>
												<p> <?php _e( 'Upload product wise shipping rates to import rates into your shop. You need to choose a CSV file and click Upload.', 'ship-per-product' )?></p>
												<p><?php _e('CSV for shipping rate must be with 8 columns ( Country Code, Region/State Code, City, Zip/Postal Code, Sku, ID, Line cost, Item cost )', 'ship-per-product' )?> </p>
												<table class="ced_pbs_shippingrows widefat">
													<tbody>
														<tr>
															<th><?php echo _e('Choose a CSV file:','ship-per-product');?>
															</th>
															<td>
																<input class="ced_pbs_csv_custom_import" name="csv_import" id="csv_import" type="file" size="25" value="" aria-required="true" /> 
																<input type="hidden" value="save" name="ship-per-product-tab">
																<input type="hidden" value="134217728" name="max_file_size">
																<small><?php echo _e('Maximum size:128 MB','ship-per-product');?></small>
															</td>
															<td>
																<a href="<?php echo PLUGIN_DIR_URL.'uploads/pbs_sample.csv'?>"><?php _e('Export Demo CSV','ship-per-product')?>
																	<span class="ced_pbs_sample_export"><img src="<?php echo PLUGIN_DIR_URL.'assets/images/download.png'?>"></img></span>
																</a>
															</td>
														</tr>
														<tr>
															<th><?php echo _e('Override Previous Shipping Detail','ship-per-product');?>
															</th>
															<td>
																<label>
																	<input type="checkbox" name="to_override_ship" class="spp_to_override_ship" id="to_override_ship" value="1"><?php echo _e('Check to replace with previous Shipping Details','ship-per-product');?>
																</label>
																<br> 
																<?php echo _e('( By Default Shipping Detail will be added with previous Shipping Detail )','ship-per-product');?>
															</td>
														</tr>
														<tr>
															<td>
																<p><input name="save" id = "ced_pbs_import_button" class="button-primary woocommerce-save-button ced_bar_import_button" type="submit" value="<?php esc_attr_e( 'Import', 'woocommerce' ); ?>" /></p>
															</td>
															
														</tr>
													</tbody>
												</table>
											</div>
											<div class="ced_pbs_panel ced_pbs_heading_rates">
												<h3><?php _e('Add Rates', 'ship-per-product' )?></h3>
											</div>
											<div class="ced_pbs_content_sec">
												<table class="ced_pbs_shippingrows widefat">
													<thead>
														<tr>
															<td colspan="9">
																<?php echo _e('You can get country and state code from ','ship-per-product');?><?php _e('Here','ship-per-product')?>
																<?php 
																	global $woocommerce;
																	$countries_obj   = new WC_Countries();
																	$countries   = $countries_obj->__get('countries'); ?>
																	<div><h2><?php _e('Countries') ?> </h2>
																		
																		 <select class="ced-bar-select2" name = "ced_pbs_woo_countries" id="ced_pbs_woo_countries">
																	    	<?php foreach ($countries as $k=>$v){?>
																	    			<option  value="<?php echo $k;?>"><?php echo $v;?></option>
																	    	<?php }?>
																	    </select>
																	    <div id="ced_pbs_selected_country_code"></div>
																    </div>
																	    <?php
																	    $default_country = $countries_obj->get_base_country();
																	    $default_county_states = $countries_obj->get_states($default_country); ?>
																	   <div id = "ced_pbs_no_state_found"></div>
																	   <div><h2><?php _e('States') ?> </h2>
																		 <select class="ced-bar-select2" name = "ced_pbs_woo_countries" id="ced_pbs_woo_states">
																	    	<?php foreach ($default_county_states as $k=>$v){?>
																	    			<option  value="<?php echo $k;?>"><?php echo $v;?></option>
																	    	<?php }?>
																	    </select>
																		    <div id="ced_pbs_selected_state_code"></div>
																	    </div>
																<?php 
																?>
															</td>
														</tr>
														<tr>
															<td colspan="9">
																<?php echo _e('Data would not be saved if you left any field blank except state code, city, zip code','ship-per-product');?>
															</td>
														</tr>
													</thead>
													<tbody id="add-rows">
														<tr id="ced_pps_wrapper_tr">
															<td>
																<input type="text" value="" name="ced_pps_country[1]" id = "ced_pps_country" class="country_n"  placeholder="<?php _e('Country Code','ship-per-product');?>">
															</td>
															<td>
																<input type="text" value=""	name="ced_pps_state[1]" id = "ced_pps_state"	class="state_n" placeholder="<?php _e('State Code','ship-per-product');?>">
															</td>
															<td>
															<input type="text" value="" name="ced_pps_city[1]" id = "ced_pps_city" class="city_n" placeholder="<?php _e('City','ship-per-product')?>">
															</td>
															<td>
																<input type="text" value="" name="ced_pps_zip[1]" id = "ced_pps_zip"	class="zip_n" placeholder="<?php _e('Zip Code','ship-per-product')?>">
															</td>
															<td>
																<select class="ced-bar-select2" name = "<?php echo $this->id; ?>_sku[1]" id = "ced_pps_sku">
															    	<?php foreach ($products as $k=>$v){?>
															    			<option value="<?php echo $k;?>"><?php echo $v;?></option>
															    	<?php }?>
															    </select>
															</td>
															<td>
																<input type="text" value="" name="ced_pps_line[1]" id = "ced_pps_line"	class="line_n wc_input_price" placeholder="<?php _e('Line Cost','ship-per-product')?>(0.00)" >
															</td>
															<td>
																<input type="text" value="" name="ced_pps_item[1]" id = "ced_pps_item"	class="item_n wc_input_price" placeholder="<?php _e('Item Cost','ship-per-product')?>(0.00)" >
															</td>
														</tr>
														<tr>
															<td>
																<p><input name="save" id="ced_pbs_prod_save" class="button-primary woocommerce-save-button" type="submit" value="<?php esc_attr_e( 'Add Rates', 'woocommerce' ); ?>" /></p>
															</td>
														</tr>
													</tbody>
												</table>
											</div>
											<div  class="ced_pbs_panel ced_pbs_heading_rates">
												<h3><?php _e('List Rates', 'ship-per-product' )?></h3>
											</div>
											<div class="ced_pbs_content_sec">
												<table id="ced_pbs_datatable_id" class="ced_pbs_shippingrows ced_pbs_datatable_id widefat">
													<thead>
														<tr>
															<td colspan="9">
																<a class="ced_pbs_left" href="?page=wc-settings&tab=shipping&section=ced_pps&sub-section=ship-per-product-table&format=csv" class="button" target="_blank" ><?php echo _e('Export Rates','ship-per-product');?></a>
															</td>
														</tr>
														<tr>
															<th class="check-column ced-spp-check-style"><input type="checkbox"></th>
															<th><?php echo __('Country', 'ship-per-product')?></th>
															<th><?php echo __('Region/State', 'ship-per-product')?></th>
															<th><?php echo __('City', 'ship-per-product')?></th>
															<th><?php echo __('Zip/Postal Code', 'ship-per-product')?></th>
															<th><?php echo __('Product', 'ship-per-product')?></th>
															<th><?php echo __('Line cost', 'ship-per-product')?></th>
															<th><?php echo __('Item cost', 'ship-per-product')?></th>
															<th></th>
														</tr>
													</thead>
													<tfoot>
														<tr>
															<th colspan="4">
																<div class="ced_spp_left_button">
																	<a href="#"	class="remove button"><?php echo _e('Delete Rates','ship-per-product');?></a>
																</div>
															</th>
															<th colspan="4">
																
															</th>
														</tr>
													</tfoot>
													<tbody>
														<?php
														
														if(!empty($final_rate) && is_array($final_rate)) 
														{
															$size = 0;
															foreach ($final_rate as $key=>$value) 
															{
																$title = "";
																$product = new WC_Product($value->product_id);
																
																if($product->post->ID==$value->product_id){
																	$title = get_the_title($value->product_id);
																}
																
																?>
																
																<tr data-uid = "<?php echo $value->unique_id;?>">
																	<td class="check-column spp-global-table-row">
																		<input type="checkbox" name="select">
																	</td>
																	<td>
																		<label ><?php echo $value->country_code;?></label>
																	</td>
																	<td>
																		<label ><?php echo $value->state_code;?></label>
																	</td>
																	<td>
																		<label ><?php echo $value->city;?></label>
																	</td>
																	<td>
																		<label ><?php echo $value->zip_code;?></label>
																	</td>
																	<td>
																		<label ><?php echo $title;?></label>
																	</td>
																	<td>
																		<label ><?php echo $value->line_cost;?></label>
																	</td>
																	<td>
																		<label ><?php echo $value->item_cost;?></label>
																	</td>
																	<td>
																		<button type="button" data-uid="<?php echo $value->unique_id?>" data-country = "<?php echo $value->country_code;?>" data-state = <?php echo $value->state_code;?> data-city = <?php echo $value->city;?> data-zip = <?php echo $value->zip_code;?> data-sku = <?php echo $value->product_id;?> data-line = <?php echo $value->line_cost;?> data-item = <?php echo $value->item_cost;?> class="ced_pbs_edit">Edit</button>
																	</td>
																</tr>
																<?php 
																$size++;
															}
														}
														?>
													</tbody>
												</table>
											</div>
										</td>
									</tr>
								</table>
							<?php 
							}
						}?>
					</div>
			<?php	
		}
		/**
		 * Saves admin Setting for Ship per Product
		 * 
		 * @name ced_spp_save_per_prod_shipping
		 * @author CedCommerce <plugins@cedcommerce.com>
		 */
		function ced_spp_save_per_prod_shipping() 
		{
			if(!isset($_POST['ship-per-product-tab'])) 
			{
				return;
			}
			
			
			$time_start = microtime(true);
			
			// Initialize blank arrays & save variables
			$ced_pps_country = $ced_pps_state = $ced_pps_city = $ced_pps_zip = $ced_pps_sku = $ced_pps_line = $ced_pps_item = array();
			$saveNames = array('_country', '_state', '_city', '_zip', '_sku', '_line', '_item');
			
			// Clean table rate data
			if(is_array($saveNames))
			{
				$postedValues =array();
				foreach ($saveNames as $sn) 
				{
					$save_name = 'ced_pps' . $sn;
					if ( isset( $_POST[ $this->id . $sn] ) )  
					{
						$save_name = array_map( 'sanitize_text_field', $_POST[ $this->id . $sn] );
						$postedValues[$sn] = $_POST[ $this->id . $sn][1];
					}	
				}
			}	
			
			if(isset($_POST['ced_pps_hidden_unique_id']))
			{
				$uniqueID = $_POST['ced_pps_hidden_unique_id'];
				$this->ced_pbs_update_product_shipping($postedValues,$uniqueID,'product');
			}
			else
			{
				$arr_result = array();
				$skipped = 0;
				$imported = 0;
				$comments = 0;
				$file = '';
				
				if (!empty($_FILES['csv_import']['tmp_name']))
				{
					if(!session_id())
					{
						session_start();
					}
					$csv_mimetypes = array('text/csv',
											'application/csv',
											'text/comma-separated-values',
											'application/excel',
											'application/vnd.ms-excel',
											'application/vnd.msexcel',
											'application/octet-stream',
											);
					
					if(!in_array($_FILES['csv_import']['type'], $csv_mimetypes))
					{
						$_SESSION['ced_pbs_not_csv'] = "not_csv";
						return ;
					}
					global $wpdb;
					$file = $_FILES['csv_import']['tmp_name'];
					
					
					if(file_exists($file)) 
					{
						unset($_SESSION['ced-pbs-import-error']);
						
						$row = 1;
						ini_set('auto_detect_line_endings',true);
						$handle = fopen($file, 'r');
						if($handle) 
						{
							if(isset($_POST['to_override_ship'])) 
							{
								$option = array();
								global $wpdb;
								$table_name = $wpdb->prefix . 'pbs';
								$where = array('type' => 'product');
								$wpdb->delete( $table_name, $where);
							}
							$count = 0;
							$postedValuescat =array();
							
							while (($data = fgetcsv($handle, 1000))!== false) 
							{
								if($row == 1)
								{ 
									$row++; 
									continue; 
								}
								if(isset($data)&&!empty($data)&&count($data)==8){

								
									$data[4] = $data[5];
									
									if($data[4]!='') 
									{
										$product = new WC_Product($data[4]);
										
										if(isset($product->id))
										{	
											if($product->id == $data[4]){
												$postedValuescat[$count]['_sku'] =  $data[4];
												$postedValuescat[$count]['_country'] =  $data[0];
												$postedValuescat[$count]['_state'] =  $data[1];
												$postedValuescat[$count]['_city'] =  $data[2];
												$postedValuescat[$count]['_zip'] =  $data[3];
												$postedValuescat[$count]['_line'] =  $data[6];
												$postedValuescat[$count]['_item'] =  $data[7];
											}
											else{
												$_SESSION['ced-pbs-import-error'] = "import_error";
											}
										}
										else{
											$_SESSION['ced-pbs-import-error'] = "import_error";
										}
										$count++;
									}
								}
								else{
									return;
								}
							}
							
							
							
							fclose($handle);
						}
						$this->ced_pbs_insert_product_shipping_upload($postedValuescat,'product');
					}
				}
				$this->ced_pbs_insert_product_shipping($postedValues,'product');
				global $post;
				$params = array('post_type' => 'product','posts_per_page' => -1);
				$wc_query = new WP_Query($params);
				if ($wc_query->have_posts()) :
					while ($wc_query->have_posts()) :
						$wc_query->the_post();
						$productSku = $this->ced_spp_get_product_sku($post->ID);
						delete_post_meta($post->ID, 'prod_per_ship_rate');
					endwhile;
					wp_reset_postdata();
				endif;
				
				global $post;
				$params = array('post_type' => 'product_variation','posts_per_page' => -1);
				$wc_query = new WP_Query($params);
				if ($wc_query->have_posts()) :
					while ($wc_query->have_posts()) :
						$wc_query->the_post();
						$productSku = $this->ced_spp_get_product_sku($post->ID); 
						delete_post_meta($post->ID, 'prod_per_ship_rate');
					endwhile;
					wp_reset_postdata();
				endif;
				
				if (file_exists($file)) 
				{
					@unlink($file);
				}
				
				$exec_time = microtime(true) - $time_start;
				if ($skipped) 
				{
						$this->log['error'][] = "<b>".__('Skipped','ship-per-product')."{$skipped} ".__('Products shipping rate (most likely due to non existing sku, or empty sku)','ship-per-product')."</b>";
				}
				$this->log ['notice'] [] = sprintf ( "<b>Imported {$imported} products shipping rate in %.2f seconds.</b>", $exec_time);
				$_POST['ship-per-product-tab'] = null;
			}
		
		}
		/**
		 * This function is to update values in the table
		 * @name ced_pbs_update_product_shipping
		 * @author CedCommerce <plugins@cedcommerce.com>
		 */
		public function ced_pbs_update_product_shipping($toUpdatearray,$uniqueId,$type)
		{
			$unique_id = $uniqueId;
			
			if($type == 'product')
			{
				if($toUpdatearray['_sku'] == "" || $toUpdatearray['_sku'] == null || $toUpdatearray['_country'] == "" || $toUpdatearray['_country'] == null || $toUpdatearray['_line'] == "" || $toUpdatearray['_line'] == null || $toUpdatearray['_item'] == "" || $toUpdatearray['_item'] == null)
				{
					return;
				}
				if($toUpdatearray['_city'] == "")
				{
					$toUpdatearray['_city'] = "*";
				}
				if($toUpdatearray['_zip'] == "")
				{
					$toUpdatearray['_zip'] = "*";
				}
				if($toUpdatearray['_state'] == "")
				{
					$toUpdatearray['_state'] = "*";
				}
				$dataToupdate = array(
							'product_id'   => $toUpdatearray['_sku'],
							'country_code' => $toUpdatearray['_country'],
							'state_code'   => $toUpdatearray['_state'],
							'city'         => $toUpdatearray['_city'],
							'zip_code'     => $toUpdatearray['_zip'],
							'line_cost'    => $toUpdatearray['_line'],
							'item_cost'    => $toUpdatearray['_item']
					);
				$where = array('unique_id' => $unique_id,'type' => 'product');
				
			}
			
			global $wpdb;
			$table_name = $wpdb->prefix . 'pbs';
			$update_data = $wpdb->update( $table_name, $dataToupdate, $where );
		}
		/**
		 * This function is to insert values in the table
		 * @name ced_pbs_insert_int_table
		 * @author CedCommerce <plugins@cedcommerce.com>
		 */
		public function ced_pbs_insert_product_shipping_upload($toInsertarrayupload,$type)
		{
			
			global $wpdb;
			if($type == 'product')
			{
				foreach($toInsertarrayupload as $key => $toInsertarray)
				{
					if($toInsertarray['_sku'] == "" || $toInsertarray['_sku'] == null || $toInsertarray['_country'] == "" || $toInsertarray['_country'] == null || $toInsertarray['_line'] == "" || $toInsertarray['_line'] == null || $toInsertarray['_item'] == "" || $toInsertarray['_item'] == null)
					{
						return;
					}
					$pid = $toInsertarray['_sku'];
					$countryCode = $toInsertarray['_country'];
					$stateCode   = $toInsertarray['_state'];
					$city        = $toInsertarray['_city'];
					$zipCode     = $toInsertarray['_zip'];
					$lineCost    = $toInsertarray['_line'];
					$itemCost    = $toInsertarray['_item'];
					$unique_id = $toInsertarray['_sku'].$toInsertarray['_country'].$toInsertarray['_state'].$toInsertarray['_city'].$toInsertarray['_zip'].$toInsertarray['_line'].$toInsertarray['_item'];
					$table_name = $wpdb->prefix . 'pbs';
					$retrieve_data = $wpdb->get_results( "SELECT * FROM $table_name where `type` = 'product' AND `unique_id` ='".$unique_id."'" );
			
					if($toInsertarray['_city'] == "")
					{
						$city = "*";
					}
					if($toInsertarray['_zip'] == "")
					{
						$zipCode = "*";
					}
					if($toInsertarray['_state'] == "")
					{
						$stateCode = "*";
					}
					if(!empty($retrieve_data))
					{
						return ;
					}
					$wpdb->insert(
							$table_name,
							array(
									'product_id'   => $pid,
									'country_code' => $countryCode,
									'state_code'   => $stateCode,
									'city'         => $city,
									'zip_code'     => $zipCode,
									'line_cost'    => $lineCost,
									'item_cost'    => $itemCost,
									'unique_id'    => $unique_id,
									'type'         => $type
							)
					);
				}
			}
		}
		/**
		 * This function is to insert values in the table
		 * @name ced_pbs_insert_int_table
		 * @author CedCommerce <plugins@cedcommerce.com>
		 */
		public function ced_pbs_insert_product_shipping($toInsertarray,$type)
		{
			global $wpdb;
			if($type == 'product')
			{
				if($toInsertarray['_sku'] == "" || $toInsertarray['_sku'] == null || $toInsertarray['_country'] == "" || $toInsertarray['_country'] == null || $toInsertarray['_line'] == "" || $toInsertarray['_line'] == null || $toInsertarray['_item'] == "" || $toInsertarray['_item'] == null)
				{
					return;
				}
				$pid = $toInsertarray['_sku'];
				$countryCode = $toInsertarray['_country'];
				$stateCode   = $toInsertarray['_state'];
				$city        = $toInsertarray['_city'];
				$zipCode     = $toInsertarray['_zip'];
				$lineCost    = $toInsertarray['_line'];
				$itemCost    = $toInsertarray['_item'];
				$unique_id = $toInsertarray['_sku'].$toInsertarray['_country'].$toInsertarray['_state'].$toInsertarray['_city'].$toInsertarray['_zip'].$toInsertarray['_line'].$toInsertarray['_item'];
				$table_name = $wpdb->prefix . 'pbs';
				$retrieve_data = $wpdb->get_results( "SELECT * FROM $table_name where `type` = 'product' AND `unique_id` ='".$unique_id."'" );
				
				if($toInsertarray['_city'] == "")
				{
					$city = "*";
				}
				if($toInsertarray['_zip'] == "")
				{
					$zipCode = "*";
				}
				if($toInsertarray['_state'] == "")
				{
					$stateCode = "*";
				}
			}
			
			if(!empty($retrieve_data))
			{
				return ;
			}
			$wpdb->insert(
					$table_name,
					array(
							'product_id'   => $pid,
							'country_code' => $countryCode,
							'state_code'   => $stateCode,
							'city'         => $city,
							'zip_code'     => $zipCode,
							'line_cost'    => $lineCost,
							'item_cost'    => $itemCost,
							'unique_id'    => $unique_id,
							'type'         => $type
					)
			);
		}
		/**
		 * Fetch product meta SKU by product ID 
		 * 
		 * @name ced_spp_get_product_sku
		 * @author CedCommerce <plugins@cedcommerce.com>
		 * @param int $Product_id
		 * @return array <product meta>
		 */
		function ced_spp_get_product_sku($id) 
		{
			global $wpdb;
			$product_id = $wpdb->get_var( $wpdb->prepare( "SELECT meta_value FROM $wpdb->postmeta WHERE meta_key='_sku' AND post_id='%s' LIMIT 1", $id ) );
			return $product_id;
		}
		
		/**
		 * Defines if Ship Per Product Method is available or not
		 * 
		 * @name is_available
		 * @author CedCommerce <plugins@cedcommerce.com>
		 * @see WC_Shipping_Method::is_available()
		 */
		public function is_available( $package ) 
		{
			global $woocommerce;
			$isavail = true;
			if('no' === $this->enabled) 
			{
				$isavail = false;
			}
			if(!empty($this->ced_countries )) 
			{
				if($package['destination']['country']!='' && in_array($package['destination']['country'],$this->ced_countries) && $isavail==true) 
				{
					$isavail = true;
				} 
				else 
				{
					$isavail = false;
				}
			}
			if($isavail) 
			{
				add_filter( 'woocommerce_package_rates', array($this,'ced_spp_hide_shipping_when_free_is_available'), 10, 2 );
			}
			return apply_filters( 'woocommerce_shipping_' . $this->id . '_is_available', $isavail, $package );
		}
		
		/**
		 * Calculates Shipping Cost of cart
		 * 
		 * @name calculate_shipping
		 * @author CedCommerce <plugins@cedcommerce.com>
		 * @param array $package
		 */
		public function calculate_shipping($package = array()) 
		{
			$shiprate = 0;
			$postcode = trim($package['destination']['postcode']);
			$city = trim($package['destination']['city']);
			$state = trim($package['destination']['state']);
			$country = trim($package['destination']['country']);
			
			if (is_array($package['contents']))
			foreach ($package['contents'] as $item_id=>$contents) 
			{
				if ( $contents['quantity'] > 0 && $contents['data']->needs_shipping() ) 
				{
					if($postcode!='' || $city!='' || $state!='' || $country!='' ) 
					{
						$flag = false;
						if($contents['data']->product_type == 'simple') 
						{
							if('yes'== get_post_meta( $contents['product_id'], 'enable_ced_product_based_shipping', true ) ) 
							{
								if($flag==false)
								{
									
									$productId= ($contents['product_id']);
									global $wpdb;
									//$unique_id = $toInsertarray['_sku'].$toInsertarray['_country'].$toInsertarray['_state'].$toInsertarray['_city'].$toInsertarray['_zip'].$toInsertarray['_line'].$toInsertarray['_item'];
									$table_name = $wpdb->prefix . 'pbs';
									
									if(!empty($country)&&!empty($state)){

										if(empty($city)&&!empty($postcode)){
											$retrieve_data = $wpdb->get_results( "SELECT * FROM $table_name where `type` = 'product' and `product_id` = '".$productId."' and `country_code` = '".$country."' and `state_code` = '".$state."' and `city` = '*' and `zip_code` = '".$postcode."'" );
											if(empty($retrieve_data)){
												$retrieve_data = $wpdb->get_results( "SELECT * FROM $table_name where `type` = 'product' and `product_id` = '".$productId."' and `country_code` = '".$country."' and `state_code` = '".$state."' and `city` = '*' and `zip_code` = '*'" );
											}
											if(empty($retrieve_data)){
												$retrieve_data = $wpdb->get_results( "SELECT * FROM $table_name where `type` = 'product' and `product_id` = '".$productId."' and `country_code` = '".$country."' and `state_code` = '*' and `city` = '*' and `zip_code` = '".$postcode."'" );
											}
											if(empty($retrieve_data)){
												$retrieve_data = $wpdb->get_results( "SELECT * FROM $table_name where `type` = 'product' and `product_id` = '".$productId."' and `country_code` = '".$country."' and `state_code` = '*' and `city` = '*' and `zip_code` = '*'" );
											}
										}
										elseif (empty($postcode)&&!empty($city)) 
										{
											$retrieve_data = $wpdb->get_results( "SELECT * FROM $table_name where `type` = 'product' and `product_id` = '".$productId."' and `country_code` = '".$country."' and `state_code` = '".$state."' and `city` = '".$city."' and `zip_code` = '*'" );
											if(empty($retrieve_data)){
												$retrieve_data = $wpdb->get_results( "SELECT * FROM $table_name where `type` = 'product' and `product_id` = '".$productId."' and `country_code` = '".$country."' and `state_code` = '".$state."' and `city` = '*' and `zip_code` = '*'" );
											}
											if(empty($retrieve_data)){
												$retrieve_data = $wpdb->get_results( "SELECT * FROM $table_name where `type` = 'product' and `product_id` = '".$productId."' and `country_code` = '".$country."' and `state_code` = '*' and `city` = '".$city."' and `zip_code` = '*'" );
											}
											if(empty($retrieve_data)){
												$retrieve_data = $wpdb->get_results( "SELECT * FROM $table_name where `type` = 'product' and `product_id` = '".$productId."' and `country_code` = '".$country."' and `state_code` = '*' and `city` = '*' and `zip_code` = '*'" );
											}
										}
										elseif(empty($postcode)&&empty($city))
										{
											
											$retrieve_data = $wpdb->get_results( "SELECT * FROM $table_name where `type` = 'product' and `product_id` = '".$productId."' and `country_code` = '".$country."' and `state_code` = '".$state."' and `city` = '*' and `zip_code` = '*'" );
											if(empty($retrieve_data)){
												$retrieve_data = $wpdb->get_results( "SELECT * FROM $table_name where `type` = 'product' and `product_id` = '".$productId."' and `country_code` = '".$country."' and `state_code` = '*' and `city` = '*' and `zip_code` = '*'" );
											}
										}
										elseif(!empty($postcode)&&!empty($city))
										{
											
											$retrieve_data = $wpdb->get_results( "SELECT * FROM $table_name where `type` = 'product' and `product_id` = '".$productId."' and `country_code` = '".$country."' and `state_code` = '".$state."' and `city` = '".$city."' and `zip_code` = '".$postcode."'" );
											if(empty($retrieve_data)){
												
												$retrieve_data = $wpdb->get_results( "SELECT * FROM $table_name where `type` = 'product' and `product_id` = '".$productId."' and `country_code` = '".$country."' and `state_code` = '".$state."' and `city` = '".$city."' and `zip_code` = '*'" );
											}
											if(empty($retrieve_data)){
												
												$retrieve_data = $wpdb->get_results( "SELECT * FROM $table_name where `type` = 'product' and `product_id` = '".$productId."' and `country_code` = '".$country."' and `state_code` = '".$state."' and `city` = '*' and `zip_code` = '".$postcode."'" );

											}
											if(empty($retrieve_data)){
												
												$retrieve_data = $wpdb->get_results( "SELECT * FROM $table_name where `type` = 'product' and `product_id` = '".$productId."' and `country_code` = '".$country."' and `state_code` = '".$state."' and `city` = '*' and `zip_code` = '*'" );
											}
											if(empty($retrieve_data)){
												
												$retrieve_data = $wpdb->get_results( "SELECT * FROM $table_name where `type` = 'product' and `product_id` = '".$productId."' and `country_code` = '".$country."' and `state_code` = '*' and `city` = '".$city."' and `zip_code` = '".$postcode."'" );
											}
											if(empty($retrieve_data)){
												
												$retrieve_data = $wpdb->get_results( "SELECT * FROM $table_name where `type` = 'product' and `product_id` = '".$productId."' and `country_code` = '".$country."' and `state_code` = '*' and `city` = '".$city."' and `zip_code` = '*'" );
											}
											if(empty($retrieve_data)){
												
												$retrieve_data = $wpdb->get_results( "SELECT * FROM $table_name where `type` = 'product' and `product_id` = '".$productId."' and `country_code` = '".$country."' and `state_code` = '*' and `city` = '*' and `zip_code` = '".$postcode."'" );
											}
											if(empty($retrieve_data)){
												
												$retrieve_data = $wpdb->get_results( "SELECT * FROM $table_name where `type` = 'product' and `product_id` = '".$productId."' and `country_code` = '".$country."' and `state_code` = '*' and `city` = '*' and `zip_code` = '*'" );
											}

										}
									}elseif(!empty($country)&&empty($state)){

										if(empty($postcode)&&empty($city)){
											
											$retrieve_data = $wpdb->get_results( "SELECT * FROM $table_name where `type` = 'product' and `product_id` = '".$productId."' and `country_code` = '".$country."' and `state_code` = '*' and `city` = '*' and `zip_code` = '*'" );
										}
										elseif(empty($city)&&!empty($postcode)){
											$retrieve_data = $wpdb->get_results( "SELECT * FROM $table_name where `type` = 'product' and `product_id` = '".$productId."' and `country_code` = '".$country."' and `state_code` = '*' and `city` = '*' and `zip_code` = '".$postcode."'" );
											if(empty($retrieve_data)){
												$retrieve_data = $wpdb->get_results( "SELECT * FROM $table_name where `type` = 'product' and `product_id` = '".$productId."' and `country_code` = '".$country."' and `state_code` = '*' and `city` = '*' and `zip_code` = '*'" );
											}
										}
										elseif (empty($postcode)&&!empty($city)) 
										{
											$retrieve_data = $wpdb->get_results( "SELECT * FROM $table_name where `type` = 'product' and `product_id` = '".$productId."' and `country_code` = '".$country."' and `state_code` = '*' and `city` = '".$city."' and `zip_code` = '*'" );
											if(empty($retrieve_data)){
												$retrieve_data = $wpdb->get_results( "SELECT * FROM $table_name where `type` = 'product' and `product_id` = '".$productId."' and `country_code` = '".$country."' and `state_code` = '*' and `city` = '*' and `zip_code` = '*'" );
											}
										}
										elseif(!empty($postcode)&&!empty($city))
										{
											
											$retrieve_data = $wpdb->get_results( "SELECT * FROM $table_name where `type` = 'product' and `product_id` = '".$productId."' and `country_code` = '".$country."' and `state_code` = '*' and `city` = '".$city."' and `zip_code` = '".$postcode."'" );
											if(empty($retrieve_data)){
												$retrieve_data = $wpdb->get_results( "SELECT * FROM $table_name where `type` = 'product' and `product_id` = '".$productId."' and `country_code` = '".$country."' and `state_code` = '*' and `city` = '".$city."' and `zip_code` = '*'" );
											}
											if(empty($retrieve_data)){
												$retrieve_data = $wpdb->get_results( "SELECT * FROM $table_name where `type` = 'product' and `product_id` = '".$productId."' and `country_code` = '".$country."' and `state_code` = '*' and `city` = '*' and `zip_code` = '".$postcode."'" );

											}
											if(empty($retrieve_data)){
												
												$retrieve_data = $wpdb->get_results( "SELECT * FROM $table_name where `type` = 'product' and `product_id` = '".$productId."' and `country_code` = '".$country."' and `state_code` = '*' and `city` = '*' and `zip_code` = '*'" );
											}

										}


									}
									
									if(!empty($retrieve_data))
									{
										
										$flag = true;
										$shiprate = $shiprate + ($contents['quantity'] * $retrieve_data[0]->item_cost);
										$shiprate = $shiprate +  $retrieve_data[0]->line_cost;
									}
									
								}
							}
						}
						else if($contents['data']->product_type == 'variation')
						{
							if('yes'== get_post_meta( $contents['product_id'], 'enable_ced_product_based_shipping', true ) ) 
							{
								if($flag==false)
								{
									$productId= ($contents['data']->variation_id);
									global $wpdb;
									
									$table_name = $wpdb->prefix . 'pbs';
									if(!empty($country)&&!empty($state)){

										if(empty($city)&&!empty($postcode)){
											$retrieve_data = $wpdb->get_results( "SELECT * FROM $table_name where `type` = 'product' and `product_id` = '".$productId."' and `country_code` = '".$country."' and `state_code` = '".$state."' and `city` = '*' and `zip_code` = '".$postcode."'" );
											if(empty($retrieve_data)){
												$retrieve_data = $wpdb->get_results( "SELECT * FROM $table_name where `type` = 'product' and `product_id` = '".$productId."' and `country_code` = '".$country."' and `state_code` = '".$state."' and `city` = '*' and `zip_code` = '*'" );
											}
											if(empty($retrieve_data)){
												$retrieve_data = $wpdb->get_results( "SELECT * FROM $table_name where `type` = 'product' and `product_id` = '".$productId."' and `country_code` = '".$country."' and `state_code` = '*' and `city` = '*' and `zip_code` = '".$postcode."'" );
											}
											if(empty($retrieve_data)){
												$retrieve_data = $wpdb->get_results( "SELECT * FROM $table_name where `type` = 'product' and `product_id` = '".$productId."' and `country_code` = '".$country."' and `state_code` = '*' and `city` = '*' and `zip_code` = '*'" );
											}
										}
										elseif (empty($postcode)&&!empty($city)) 
										{
											$retrieve_data = $wpdb->get_results( "SELECT * FROM $table_name where `type` = 'product' and `product_id` = '".$productId."' and `country_code` = '".$country."' and `state_code` = '".$state."' and `city` = '".$city."' and `zip_code` = '*'" );
											if(empty($retrieve_data)){
												$retrieve_data = $wpdb->get_results( "SELECT * FROM $table_name where `type` = 'product' and `product_id` = '".$productId."' and `country_code` = '".$country."' and `state_code` = '".$state."' and `city` = '*' and `zip_code` = '*'" );
											}
											if(empty($retrieve_data)){
												$retrieve_data = $wpdb->get_results( "SELECT * FROM $table_name where `type` = 'product' and `product_id` = '".$productId."' and `country_code` = '".$country."' and `state_code` = '*' and `city` = '".$city."' and `zip_code` = '*'" );
											}
											if(empty($retrieve_data)){
												$retrieve_data = $wpdb->get_results( "SELECT * FROM $table_name where `type` = 'product' and `product_id` = '".$productId."' and `country_code` = '".$country."' and `state_code` = '*' and `city` = '*' and `zip_code` = '*'" );
											}
										}
										elseif(empty($postcode)&&empty($city))
										{
											
											$retrieve_data = $wpdb->get_results( "SELECT * FROM $table_name where `type` = 'product' and `product_id` = '".$productId."' and `country_code` = '".$country."' and `state_code` = '".$state."' and `city` = '*' and `zip_code` = '*'" );
											if(empty($retrieve_data)){
												$retrieve_data = $wpdb->get_results( "SELECT * FROM $table_name where `type` = 'product' and `product_id` = '".$productId."' and `country_code` = '".$country."' and `state_code` = '*' and `city` = '*' and `zip_code` = '*'" );
											}
										}
										elseif(!empty($postcode)&&!empty($city))
										{
											
											$retrieve_data = $wpdb->get_results( "SELECT * FROM $table_name where `type` = 'product' and `product_id` = '".$productId."' and `country_code` = '".$country."' and `state_code` = '".$state."' and `city` = '".$city."' and `zip_code` = '".$postcode."'" );
											if(empty($retrieve_data)){
												
												$retrieve_data = $wpdb->get_results( "SELECT * FROM $table_name where `type` = 'product' and `product_id` = '".$productId."' and `country_code` = '".$country."' and `state_code` = '".$state."' and `city` = '".$city."' and `zip_code` = '*'" );
											}
											if(empty($retrieve_data)){
												
												$retrieve_data = $wpdb->get_results( "SELECT * FROM $table_name where `type` = 'product' and `product_id` = '".$productId."' and `country_code` = '".$country."' and `state_code` = '".$state."' and `city` = '*' and `zip_code` = '".$postcode."'" );

											}
											if(empty($retrieve_data)){
												
												$retrieve_data = $wpdb->get_results( "SELECT * FROM $table_name where `type` = 'product' and `product_id` = '".$productId."' and `country_code` = '".$country."' and `state_code` = '".$state."' and `city` = '*' and `zip_code` = '*'" );
											}
											if(empty($retrieve_data)){
												
												$retrieve_data = $wpdb->get_results( "SELECT * FROM $table_name where `type` = 'product' and `product_id` = '".$productId."' and `country_code` = '".$country."' and `state_code` = '*' and `city` = '".$city."' and `zip_code` = '".$postcode."'" );
											}
											if(empty($retrieve_data)){
												
												$retrieve_data = $wpdb->get_results( "SELECT * FROM $table_name where `type` = 'product' and `product_id` = '".$productId."' and `country_code` = '".$country."' and `state_code` = '*' and `city` = '".$city."' and `zip_code` = '*'" );
											}
											if(empty($retrieve_data)){
												
												$retrieve_data = $wpdb->get_results( "SELECT * FROM $table_name where `type` = 'product' and `product_id` = '".$productId."' and `country_code` = '".$country."' and `state_code` = '*' and `city` = '*' and `zip_code` = '".$postcode."'" );
											}
											if(empty($retrieve_data)){
												
												$retrieve_data = $wpdb->get_results( "SELECT * FROM $table_name where `type` = 'product' and `product_id` = '".$productId."' and `country_code` = '".$country."' and `state_code` = '*' and `city` = '*' and `zip_code` = '*'" );
											}

										}
									}
									elseif(!empty($country)&&empty($state)){

										if(empty($postcode)&&empty($city)){
											
											$retrieve_data = $wpdb->get_results( "SELECT * FROM $table_name where `type` = 'product' and `product_id` = '".$productId."' and `country_code` = '".$country."' and `state_code` = '*' and `city` = '*' and `zip_code` = '*'" );
										}
										elseif(empty($city)&&!empty($postcode)){
											$retrieve_data = $wpdb->get_results( "SELECT * FROM $table_name where `type` = 'product' and `product_id` = '".$productId."' and `country_code` = '".$country."' and `state_code` = '*' and `city` = '*' and `zip_code` = '".$postcode."'" );
											if(empty($retrieve_data)){
												$retrieve_data = $wpdb->get_results( "SELECT * FROM $table_name where `type` = 'product' and `product_id` = '".$productId."' and `country_code` = '".$country."' and `state_code` = '*' and `city` = '*' and `zip_code` = '*'" );
											}
										}
										elseif (empty($postcode)&&!empty($city)) 
										{
											$retrieve_data = $wpdb->get_results( "SELECT * FROM $table_name where `type` = 'product' and `product_id` = '".$productId."' and `country_code` = '".$country."' and `state_code` = '*' and `city` = '".$city."' and `zip_code` = '*'" );
											if(empty($retrieve_data)){
												$retrieve_data = $wpdb->get_results( "SELECT * FROM $table_name where `type` = 'product' and `product_id` = '".$productId."' and `country_code` = '".$country."' and `state_code` = '*' and `city` = '*' and `zip_code` = '*'" );
											}
										}
										elseif(!empty($postcode)&&!empty($city))
										{
											
											$retrieve_data = $wpdb->get_results( "SELECT * FROM $table_name where `type` = 'product' and `product_id` = '".$productId."' and `country_code` = '".$country."' and `state_code` = '*' and `city` = '".$city."' and `zip_code` = '".$postcode."'" );
											if(empty($retrieve_data)){
												$retrieve_data = $wpdb->get_results( "SELECT * FROM $table_name where `type` = 'product' and `product_id` = '".$productId."' and `country_code` = '".$country."' and `state_code` = '*' and `city` = '".$city."' and `zip_code` = '*'" );
											}
											if(empty($retrieve_data)){
												$retrieve_data = $wpdb->get_results( "SELECT * FROM $table_name where `type` = 'product' and `product_id` = '".$productId."' and `country_code` = '".$country."' and `state_code` = '*' and `city` = '*' and `zip_code` = '".$postcode."'" );

											}
											if(empty($retrieve_data)){
												
												$retrieve_data = $wpdb->get_results( "SELECT * FROM $table_name where `type` = 'product' and `product_id` = '".$productId."' and `country_code` = '".$country."' and `state_code` = '*' and `city` = '*' and `zip_code` = '*'" );
											}

										}


									}

									if(!empty($retrieve_data))
									{
										$flag = true;
										$shiprate = $shiprate + ($contents['quantity'] * $retrieve_data[0]->item_cost);
										$shiprate = $shiprate +  $retrieve_data[0]->line_cost;
									}									
								}
							}
						}
						
						
						if($flag==false) 
						{
							if(isset($this->default_shipping_Cost)) 
							{
								$shiprate = $shiprate + ($contents['quantity'] * $this->default_shipping_Cost);
							}
						}
					} 
					else 
					{
						if($contents['data']->product_type == 'simple') 
						{
							if('yes'== get_post_meta( $contents['product_id'], 'enable_ced_product_based_shipping', true ) ) 
							{
								if(isset($this->default_shipping_Cost)) 
								{
									$shiprate = $shiprate + ($contents['quantity'] * $this->default_shipping_Cost);
								}
							}
						} 
						else if($contents['data']->product_type == 'variation') 
						{
							if('yes' == get_post_meta( $contents['data']->variation_id, 'enable_ship_pr_variable', true )) 
							{
								if(isset($this->default_shipping_Cost)) 
								{
									$shiprate = $shiprate + ($contents['quantity'] * $this->default_shipping_Cost);
								}
							}
						}
					}
				}
			}
			if($this->include_tax == true)
			{
				$this->include_tax = "";
			}
			
			if(isset($shiprate) && !empty($shiprate))
			{
				$this->add_rate( array(
						'id' 		=> $this->id,
						'label' 	=> $this->title,
						'cost' 		=> $shiprate,
						'taxes' 	=> $this->include_tax,
				));
			}
		}
		
		/**
		 * Fetchs inner most array to get Shipping cost of available for all location (State, city, zipcode)
		 * 
		 * @name ced_spp_return_innermost_array
		 * @author CedCommerce <plugins@cedcommerce.com>
		 * @param array $source
		 * @return array
		 */
		function ced_spp_return_innermost_array($source=array()) 
		{
			 foreach ($source as $key1=>$val1) 
			 {
			 	if (!is_array($val1)) 
				{
			 		return $source;
			 	}
			 	foreach($val1 as $key2=>$val2) 
				{
			 		if(!is_array($val2)) 
					{
			 			return $val1;
			 		}
			 		foreach ($val2 as $key3=>$val3) 
					{
			 			if(!is_array($val3))
						{
			 				return $val2;
			 			}
			 			foreach ($val3 as $key4=>$val4) 
						{
			 				if(!is_array($val4)) 
							{
			 					return $val3;
			 				}
			 				foreach($val4 as $key5=>$val5) 
							{
			 					if(!is_array($val5)) 
								{
			 						return $val4;
			 					}
			 					foreach($val5 as $key6=>$val6) 
								{
			 						if(!is_array($val6)) 
									{
			 							return $val5;
			 						}
			 					}
			 				}
			 			}
			 		}
			 	}
			}
		}
		
		/**
		 * Hide shipping rates when free shipping is available
		 *
		 * @name ced_spp_hide_shipping_when_free_is_available
		 * @author CedCommerce <plugins@cedcommerce.com>
		 * @param array $rates Array of rates found for the package
		 * @param array $package The package array/object being shipped
		 * @return array of modified rates
		 */
		function ced_spp_hide_shipping_when_free_is_available( $rates, $package ) 
		{
			// Only modify rates if free_shipping is present
			if ( isset( $rates['free_shipping'] ) ) 
			{
				if($this->skip_free_shipping == 'yes') 
				{
					
				} 
				else 
				{
					unset( $rates['free_shipping'] );
				}
			}
			return $rates;
		}
		
		/**
		 * Converts string to float
		 * 
		 * @name ced_spp_tofloat
		 * @author CedCommerce <plugins@cedcommerce.com>
		 * @param string $num
		 * @return number
		 */
		function ced_spp_tofloat($num) 
		{
			$dotPos = strrpos($num, '.');
			$commaPos = strrpos($num, ',');
			$sep = (($dotPos > $commaPos) && $dotPos) ? $dotPos :
			((($commaPos > $dotPos) && $commaPos) ? $commaPos : false);
		
			if (!$sep) 
			{
				return floatval(preg_replace("/[^0-9]/", "", $num));
			}
			return floatval(
					preg_replace("/[^0-9]/", "", substr($num, 0, $sep)) . '.' .
					preg_replace("/[^0-9]/", "", substr($num, $sep+1, strlen($num)))
			);
		}
	}
}
?>