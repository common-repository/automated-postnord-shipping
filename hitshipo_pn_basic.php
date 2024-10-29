<?php

/**
 * Plugin Name: Automated PostNord Shipping
 * Plugin URI: https://myshipi.com/
 * Description: Shipping label and commercial invoice automation included.
 * Version: 1.2.2
 * Author: Shipi
 * Author URI: https://myshipi.com/
 * Developer: aarsiv
 * Developer URI: https://myshipi.com/
 * Text Domain: hitshipo_pn
 * Domain Path: /i18n/languages/
 *
 * WC requires at least: 2.6
 * WC tested up to: 6.4
 *
 *
 * @package WooCommerce
 */

use Automattic\WooCommerce\Admin\Features\OnboardingTasks\Tasks\Shipping;

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly.
}

// Define WC_PLUGIN_FILE.
if (!defined('HITSHIPO_POSTNORD_PLUGIN_FILE')) {
	define('HITSHIPO_POSTNORD_PLUGIN_FILE', __FILE__);
}


// set HPOS feature compatible by plugin
add_action(
    'before_woocommerce_init',
    function () {
        if ( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
            \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
        }
    }
);

// Include the main WooCommerce class.
if (in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {

	if (!class_exists('hitshipo_pn_parent')) {
		class hitshipo_pn_parent
		{
			private $errror = '';
			private $hpos_enabled = false;
			private $new_prod_editor_enabled = false;
			public function __construct()
			{
				if (get_option("woocommerce_custom_orders_table_enabled") === "yes") {
 		            $this->hpos_enabled = true;
 		        }
 		        if (get_option("woocommerce_feature_product_block_editor_enabled") === "yes") {
 		            $this->new_prod_editor_enabled = true;
 		        }
				add_action('woocommerce_shipping_init', array($this, 'hitshipo_pn_init'));
				add_action('init', array($this, 'hit_order_status_update'));
				add_filter('woocommerce_shipping_methods', array($this, 'hitshipo_pn_method'));
				add_filter('plugin_action_links_' . plugin_basename(__FILE__), array($this, 'hitshipo_pn_plugin_action_links'));
				add_action('add_meta_boxes', array($this, 'create_pn_shipping_meta_box'));
				if ($this->hpos_enabled) {
					add_action('woocommerce_process_shop_order_meta', array($this, 'hit_create_pn_shipping'), 10, 1);
				} else {
					add_action('save_post', array($this, 'hit_create_pn_shipping'), 10, 1);
				}
				if ($this->hpos_enabled) {
					add_filter('bulk_actions-woocommerce_page_wc-orders', array($this, 'hit_bulk_order_menu'), 10, 1);
					add_filter('handle_bulk_actions-woocommerce_page_wc-orders', array($this, 'hit_bulk_create_order'), 10, 3);
				} else {
					add_filter('bulk_actions-edit-shop_order', array($this, 'hit_bulk_order_menu'), 10, 1);
					add_filter('handle_bulk_actions-edit-shop_order', array($this, 'hit_bulk_create_order'), 10, 3);
				}
				add_action('admin_notices', array($this, 'shipo_bulk_label_action_admin_notice'));
				add_filter('woocommerce_product_data_tabs', array($this, 'hit_product_data_tab'));
				add_action('woocommerce_process_product_meta', array($this, 'hit_save_product_options'));
				add_filter('woocommerce_product_data_panels', array($this, 'hit_product_option_view'));
				add_action('admin_menu', array($this, 'hit_pn_menu_page'));
				
				// add_action( 'woocommerce_checkout_order_processed', array( $this, 'hit_wc_checkout_order_processed' ) );
				// add_action('woocommerce_thankyou', array($this, 'hit_wc_checkout_order_processed'));
				add_action( 'woocommerce_order_status_processing', array( $this, 'hit_wc_checkout_order_processed' ) );
				add_action('woocommerce_order_details_after_order_table', array($this, 'pn_track'));
				if ($this->hpos_enabled) {
					add_filter('manage_woocommerce_page_wc-orders_columns', array($this, 'hitshipo_wc_new_order_column'));
					add_action('manage_woocommerce_page_wc-orders_custom_column', array($this, 'show_buttons_to_downlaod_shipping_label'), 10, 2);
				} else {
					add_filter('manage_edit-shop_order_columns', array($this, 'hitshipo_wc_new_order_column'));
					add_action('manage_shop_order_posts_custom_column', array($this, 'show_buttons_to_downlaod_shipping_label'), 10, 2);
				}
				add_action('admin_print_styles', array($this, 'hits_admin_scripts'));

				$general_settings = get_option('hitshipo_pn_main_settings');
				$general_settings = empty($general_settings) ? array() : $general_settings;

				if (isset($general_settings['hitshipo_pn_v_enable']) && $general_settings['hitshipo_pn_v_enable'] == 'yes') {
					add_action('woocommerce_product_options_shipping', array($this, 'hit_choose_vendor_address'));
					add_action('woocommerce_process_product_meta', array($this, 'hit_save_product_meta'));

					// Edit User Hooks
					add_action('edit_user_profile', array($this, 'hit_define_pn_credentails'));
					add_action('edit_user_profile_update', array($this, 'save_user_fields'));
				}
			}
			public function hits_admin_scripts()
			{
				global $wp_scripts;
				wp_enqueue_script('wc-enhanced-select');
				wp_enqueue_script('chosen');
				wp_enqueue_style('woocommerce_admin_styles', WC()->plugin_url() . '/assets/css/admin.css');
			}

			function hitshipo_wc_new_order_column($columns)
			{
				$columns['hit_pn'] = 'PostNord';
				return $columns;
			}

			function show_buttons_to_downlaod_shipping_label($column,  $post)
			{
				if ('hit_pn' === $column) {

					$order = ($this->hpos_enabled) ? $post : wc_get_order( $post );
					$order_id = $order->get_id();
					$json_data = get_option('hit_pn_values_' . $order_id);

					if (!empty($json_data)) {
						$array_data = json_decode($json_data, true);
						// echo '<pre>';print_r($array_data);die();
						if (isset($array_data[0])) {
							foreach ($array_data as $key => $value) {
								echo '<a href="' . $value['label'] . '" target="_blank" class="button button-secondary"><span class="dashicons dashicons-printer" style="vertical-align:sub;"></span></a> ';
								// echo ' <a href="'.$value['invoice'].'" target="_blank" class="button button-secondary"><span class="dashicons dashicons-pdf" style="vertical-align:sub;"></span></a><br/>';
							}
						} else {
							echo '<a href="' . $array_data['label'] . '" target="_blank" class="button button-secondary"><span class="dashicons dashicons-printer" style="vertical-align:sub;"></span></a> ';
							// echo ' <a href="'.$array_data['invoice'].'" target="_blank" class="button button-secondary"><span class="dashicons dashicons-pdf" style="vertical-align:sub;"></span></a>';
						}
					} else {
						echo '-';
					}
				}
			}

			function hit_pn_menu_page()
			{
				$general_settings = get_option('hitshipo_pn_main_settings');
				if (isset($general_settings['hitshipo_pn_integration_key']) && !empty($general_settings['hitshipo_pn_integration_key'])) {
					add_menu_page(__( 'PostNord Labels', 'hitshipo_pn' ), 'PostNord Labels', 'manage_options', 'hit-PostNord-labels', array($this,'my_label_page_contents'), '', 6);
				}
				add_submenu_page('options-general.php', 'PostNord Config', 'PostNord Config', 'manage_options', 'hit-pn-configuration', array($this, 'my_admin_page_contents'));
			}
			function my_label_page_contents(){
				$general_settings = get_option('hitshipo_pn_main_settings');
				$url = site_url();
				if (isset($general_settings['hitshipo_pn_integration_key']) && !empty($general_settings['hitshipo_pn_integration_key'])) {
					echo "<iframe style='width: 100%;height: 100vh;' src='https://app.myshipi.com/embed/label.php?shop=".$url."&key=".$general_settings['hitshipo_pn_integration_key']."&show=ship'></iframe>";
				}
            }

			function my_admin_page_contents()
			{
				include_once('controllors/views/hitshipo_pn_settings_view.php');
			}

			public function hit_product_data_tab($tabs)
			{

				$tabs['hits_pn_product_options'] = array(
					'label'		=> __('Shipi - PostNord Options', 'hitshipo_pn'),
					'target'	=> 'hit_pn_product_options',
					// 'class'		=> array( 'show_if_simple', 'show_if_variable' ),
				);

				return $tabs;
			}

			public function hit_save_product_options($post_id)
			{
				if (isset($_POST['hits_pn_cc'])) {
					$cc = sanitize_text_field($_POST['hits_pn_cc']);
					if ($this->hpos_enabled && $this->new_prod_editor_enabled) {
 	                    $hpos_prod_data = wc_get_product($post_id);
 	                    $hpos_prod_data->update_meta_data("hits_pn_cc", (string) esc_html( $cc ));
 	                } else {
						update_post_meta($post_id, 'hits_pn_cc', (string) esc_html($cc));
					}
					// print_r($post_id);die();
				}
			}

			public function hit_product_option_view()
			{
				global $woocommerce, $post;
				if ($this->hpos_enabled) {
					$hpos_prod_data = wc_get_product($post->ID);
					$hits_pn_saved_cc = $hpos_prod_data->get_meta("hits_pn_cc");
				} else {
					$hits_pn_saved_cc = get_post_meta($post->ID, 'hits_pn_cc', true);
				}
?>
				<div id='hit_pn_product_options' class='panel woocommerce_options_panel'>
					<div class='options_group'>
						<p class="form-field">
							<label for="hits_pn_cc"><?php _e('Enter Commodity code', 'hitshipo_pn'); ?></label>
							<span class='woocommerce-help-tip' data-tip="<?php _e('Enter commodity code for product (20 charcters max).', 'hitshipo_pn') ?>"></span>
							<input type='text' id='hits_pn_cc' name='hits_pn_cc' maxlength="20" <?php echo (!empty($hits_pn_saved_cc) ? 'value="' . $hits_pn_saved_cc . '"' : ''); ?> style="width: 30%;">
						</p>
					</div>
				</div>
			<?php
			}

			public function hit_bulk_order_menu($actions)
			{
				$actions['create_label_shipo'] = __('Create PostNord Labels - Shipi', 'hitshipo_pn');
				return $actions;
			}

			public function hit_bulk_create_order($redirect_to, $action, $order_ids)
			{
				$success = 0;
				$failed = 0;
				$failed_ids = [];
				if ($action == "create_label_shipo") {

					if (!empty($order_ids)) {
						$create_shipment_for = "default";
						$ship_content = 'Shipment Content';
						$pickup_mode = 'manual';

						foreach ($order_ids as $key => $order_id) {
							$order = wc_get_order($order_id);
							if ($order) {

								$order_data = $order->get_data();
								$order_id = $order_data['id'];
								$order_currency = $order_data['currency'];

								// $order_shipping_first_name = $order_data['shipping']['first_name'];
								// $order_shipping_last_name = $order_data['shipping']['last_name'];
								// $order_shipping_company = empty($order_data['shipping']['company']) ? $order_data['shipping']['first_name'] :  $order_data['shipping']['company'];
								// $order_shipping_address_1 = $order_data['shipping']['address_1'];
								// $order_shipping_address_2 = $order_data['shipping']['address_2'];
								// $order_shipping_city = $order_data['shipping']['city'];
								// $order_shipping_state = $order_data['shipping']['state'];
								// $order_shipping_postcode = $order_data['shipping']['postcode'];
								// $order_shipping_country = $order_data['shipping']['country'];
								// $order_shipping_phone = $order_data['billing']['phone'];
								// $order_shipping_email = $order_data['billing']['email'];

								$shipping_arr = (isset($order_data['shipping']['first_name']) && $order_data['shipping']['first_name'] != "") ? $order_data['shipping'] : $order_data['billing'];
								$order_shipping_first_name = $shipping_arr['first_name'];
								$order_shipping_last_name = $shipping_arr['last_name'];
								$order_shipping_company = empty($shipping_arr['company']) ? $shipping_arr['first_name'] :  $shipping_arr['company'];
								$order_shipping_address_1 = $shipping_arr['address_1'];
								$order_shipping_address_2 = $shipping_arr['address_2'];
								$order_shipping_city = $shipping_arr['city'];
								$order_shipping_state = $shipping_arr['state'];
								$order_shipping_postcode = $shipping_arr['postcode'];
								$order_shipping_country = $shipping_arr['country'];
								$order_shipping_phone = $order_data['billing']['phone'];
								$order_shipping_email = $order_data['billing']['email'];
								$shipping_charge = $order_data['shipping_total'];

								$items = $order->get_items();
								$pack_products = array();
								$general_settings = get_option('hitshipo_pn_main_settings', array());

								$service_code = apply_filters('hitstacks_pn_bulk_service', '18', $order_shipping_country, $general_settings['hitshipo_pn_country']);

								foreach ($items as $item) {
									$product_data = $item->get_data();

									$product = array();
									$product['product_name'] = str_replace('"', '', $product_data['name']);
									$product['product_quantity'] = $product_data['quantity'];
									$product['product_id'] = $product_data['product_id'];

									if ($this->hpos_enabled) {
										$hpos_prod_data = wc_get_product($product_data['product_id']);
										$saved_cc = $hpos_prod_data->get_meta("hits_pn_cc");
									} else {
										$saved_cc = get_post_meta($product_data['product_id'], 'hits_pn_cc', true);
									}
									if (!empty($saved_cc)) {
										$product['commodity_code'] = $saved_cc;
									}

									$product_variation_id = $item->get_variation_id();
									if (empty($product_variation_id)) {
										$getproduct = wc_get_product($product_data['product_id']);
									} else {
										$getproduct = wc_get_product($product_variation_id);
									}

									$woo_weight_unit = get_option('woocommerce_weight_unit');
									$woo_dimension_unit = get_option('woocommerce_dimension_unit');

									$pn_mod_weight_unit = $pn_mod_dim_unit = '';

									if (!empty($general_settings['hitshipo_pn_weight_unit']) && $general_settings['hitshipo_pn_weight_unit'] == 'KG_CM') {
										$pn_mod_weight_unit = 'kg';
										$pn_mod_dim_unit = 'cm';
									} elseif (!empty($general_settings['hitshipo_pn_weight_unit']) && $general_settings['hitshipo_pn_weight_unit'] == 'LB_IN') {
										$pn_mod_weight_unit = 'lbs';
										$pn_mod_dim_unit = 'in';
									} elseif (!empty($general_settings['hitshipo_pn_weight_unit']) && $general_settings['hitshipo_pn_weight_unit'] == 'G_CM') {
										$pn_mod_weight_unit = 'g';
										$pn_mod_dim_unit = 'cm';
									} else {
										$pn_mod_weight_unit = 'kg';
										$pn_mod_dim_unit = 'cm';
									}

									$product['price'] = $getproduct->get_price();

									if (!$product['price']) {
										$product['price'] = (isset($product_data['total']) && isset($product_data['quantity'])) ? number_format(($product_data['total'] / $product_data['quantity']), 2) : 0;
									}

									if ($woo_dimension_unit != $pn_mod_dim_unit) {
										$prod_width = $getproduct->get_width();
										$prod_height = $getproduct->get_height();
										$prod_depth = $getproduct->get_length();

										//wc_get_dimension( $dimension, $to_unit, $from_unit );
										$product['width'] = (!empty($prod_width) && $prod_width > 0) ? round(wc_get_dimension($prod_width, $pn_mod_dim_unit, $woo_dimension_unit), 2) : 0.1 ;
										$product['height'] = (!empty($prod_height) && $prod_height > 0) ? round(wc_get_dimension($prod_height, $pn_mod_dim_unit, $woo_dimension_unit), 2) : 0.1 ;
										$product['depth'] = (!empty($prod_depth) && $prod_depth > 0) ? round(wc_get_dimension($prod_depth, $pn_mod_dim_unit, $woo_dimension_unit), 2) : 0.1 ;
									} else {
										$product['width'] = !empty($getproduct->get_width()) ? round($getproduct->get_width(), 3) : 0.1;
										$product['height'] = !empty($getproduct->get_height()) ? round($getproduct->get_height(), 3) : 0.1;
										$product['depth'] = !empty($getproduct->get_length()) ? round($getproduct->get_length(), 3) : 0.1;
									}

									if ($woo_weight_unit != $pn_mod_weight_unit) {
										$prod_weight = $getproduct->get_weight();
										$product['weight'] = (!empty($prod_weight) && $prod_weight > 0) ? round(wc_get_weight($prod_weight, $pn_mod_weight_unit, $woo_weight_unit), 2) : 0.1 ;
									} else {
										$product['weight'] = !empty($getproduct->get_weight()) ? round($getproduct->get_weight(), 3) : 0.1;
									}

									$pack_products[] = $product;
								}

								$custom_settings = array();
								$custom_settings['default'] = array(
									'hitshipo_pn_site_id' => $general_settings['hitshipo_pn_site_id'],
									'hitshipo_pn_site_pwd' => $general_settings['hitshipo_pn_site_pwd'],
									'hitshipo_pn_part_type' => $general_settings['hitshipo_pn_part_type'],
									'hitshipo_pn_issue_c' => $general_settings['hitshipo_pn_issue_c'],
									'hitshipo_pn_api_key' => $general_settings['hitshipo_pn_api_key'],
									'hitshipo_pn_shipper_name' => $general_settings['hitshipo_pn_shipper_name'],
									'hitshipo_pn_company' => $general_settings['hitshipo_pn_company'],
									'hitshipo_pn_mob_num' => $general_settings['hitshipo_pn_mob_num'],
									'hitshipo_pn_email' => $general_settings['hitshipo_pn_email'],
									'hitshipo_pn_address1' => $general_settings['hitshipo_pn_address1'],
									'hitshipo_pn_address2' => $general_settings['hitshipo_pn_address2'],
									'hitshipo_pn_city' => $general_settings['hitshipo_pn_city'],
									'hitshipo_pn_state' => $general_settings['hitshipo_pn_state'],
									'hitshipo_pn_zip' => $general_settings['hitshipo_pn_zip'],
									'hitshipo_pn_country' => $general_settings['hitshipo_pn_country'],
									'hitshipo_pn_gstin' => $general_settings['hitshipo_pn_gstin'],
									'hitshipo_pn_con_rate' => $general_settings['hitshipo_pn_con_rate'],
									'service_code' => $service_code,
									'hitshipo_pn_label_email' => $general_settings['hitshipo_pn_label_email'],
								);
								$vendor_settings = array();
								// 	if(isset($general_settings['hitshipo_pn_v_enable']) && $general_settings['hitshipo_pn_v_enable'] == 'yes' && isset($general_settings['hitshipo_pn_v_labels']) && $general_settings['hitshipo_pn_v_labels'] == 'yes'){
								// 	// Multi Vendor Enabled
								// 	foreach ($pack_products as $key => $value) {
								// 		$product_id = $value['product_id'];
								// 		$pn_account = get_post_meta($product_id,'pn_address', true);
								// 		if(empty($pn_account) || $pn_account == 'default'){
								// 			$pn_account = 'default';
								// 			if (!isset($vendor_settings[$pn_account])) {
								// 				$vendor_settings[$pn_account] = $custom_settings['default'];
								// 			}

								// 			$vendor_settings[$pn_account]['products'][] = $value;
								// 		}

								// 		if($pn_account != 'default'){
								// 			$user_account = get_post_meta($pn_account,'hitshipo_pn_vendor_settings', true);
								// 			$user_account = empty($user_account) ? array() : $user_account;
								// 			if(!empty($user_account)){
								// 				if(!isset($vendor_settings[$pn_account])){

								// 					$vendor_settings[$pn_account] = $custom_settings['default'];

								// 				if($user_account['hitshipo_pn_site_id'] != '' && $user_account['hitshipo_pn_site_pwd'] != ''){

								// 					$vendor_settings[$pn_account]['hitshipo_pn_site_id'] = $user_account['hitshipo_pn_site_id'];

								// 					if($user_account['hitshipo_pn_site_pwd'] != ''){
								// 						$vendor_settings[$pn_account]['hitshipo_pn_site_pwd'] = $user_account['hitshipo_pn_site_pwd'];
								// 					}

								// 				}

								// 				if ($user_account['hitshipo_pn_address1'] != '' && $user_account['hitshipo_pn_city'] != '' && $user_account['hitshipo_pn_state'] != '' && $user_account['hitshipo_pn_zip'] != '' && $user_account['hitshipo_pn_country'] != '' && $user_account['hitshipo_pn_shipper_name'] != '') {

								// 					if($user_account['hitshipo_pn_shipper_name'] != ''){
								// 						$vendor_settings[$pn_account]['hitshipo_pn_shipper_name'] = $user_account['hitshipo_pn_shipper_name'];
								// 					}

								// 					if($user_account['hitshipo_pn_company'] != ''){
								// 						$vendor_settings[$pn_account]['hitshipo_pn_company'] = $user_account['hitshipo_pn_company'];
								// 					}

								// 					if($user_account['hitshipo_pn_mob_num'] != ''){
								// 						$vendor_settings[$pn_account]['hitshipo_pn_mob_num'] = $user_account['hitshipo_pn_mob_num'];
								// 					}

								// 					if($user_account['hitshipo_pn_email'] != ''){
								// 						$vendor_settings[$pn_account]['hitshipo_pn_email'] = $user_account['hitshipo_pn_email'];
								// 					}

								// 					if ($user_account['hitshipo_pn_address1'] != '') {
								// 						$vendor_settings[$pn_account]['hitshipo_pn_address1'] = $user_account['hitshipo_pn_address1'];
								// 					}

								// 					$vendor_settings[$pn_account]['hitshipo_pn_address2'] = $user_account['hitshipo_pn_address2'];

								// 					if($user_account['hitshipo_pn_city'] != ''){
								// 						$vendor_settings[$pn_account]['hitshipo_pn_city'] = $user_account['hitshipo_pn_city'];
								// 					}

								// 					if($user_account['hitshipo_pn_state'] != ''){
								// 						$vendor_settings[$pn_account]['hitshipo_pn_state'] = $user_account['hitshipo_pn_state'];
								// 					}

								// 					if($user_account['hitshipo_pn_zip'] != ''){
								// 						$vendor_settings[$pn_account]['hitshipo_pn_zip'] = $user_account['hitshipo_pn_zip'];
								// 					}

								// 					if($user_account['hitshipo_pn_country'] != ''){
								// 						$vendor_settings[$pn_account]['hitshipo_pn_country'] = $user_account['hitshipo_pn_country'];
								// 					}

								// 					$vendor_settings[$pn_account]['hitshipo_pn_gstin'] = $user_account['hitshipo_pn_gstin'];
								// 					$vendor_settings[$pn_account]['hitshipo_pn_con_rate'] = $user_account['hitshipo_pn_con_rate'];

								// 				}

								// 					if(isset($general_settings['hitshipo_pn_v_email']) && $general_settings['hitshipo_pn_v_email'] == 'yes'){
								// 						$user_dat = get_userdata($pn_account);
								// 						$vendor_settings[$pn_account]['hitshipo_pn_label_email'] = $user_dat->data->user_email;
								// 					}


								// 					if($order_data['shipping']['country'] != $vendor_settings[$pn_account]['hitshipo_pn_country']){
								// 						$vendor_settings[$pn_account]['service_code'] = empty($service_code) ? $user_account['hitshipo_pn_def_inter'] : $service_code;
								// 					}else{
								// 						$vendor_settings[$pn_account]['service_code'] = empty($service_code) ? $user_account['hitshipo_pn_def_dom'] : $service_code;
								// 					}
								// 				}
								// 				$vendor_settings[$pn_account]['products'][] = $value;
								// 			}
								// 		}

								// 	}

								// }

								if (empty($vendor_settings)) {
									$custom_settings['default']['products'] = $pack_products;
								} else {
									$custom_settings = $vendor_settings;
								}

								if (!empty($general_settings) && isset($general_settings['hitshipo_pn_integration_key']) && isset($custom_settings[$create_shipment_for])) {
									$mode = 'live';
									if (isset($general_settings['hitshipo_pn_test']) && $general_settings['hitshipo_pn_test'] == 'yes') {
										$mode = 'test';
									}

									$execution = 'manual';

									$boxes_to_shipo = array();
									if (isset($general_settings['hitshipo_pn_packing_type']) && $general_settings['hitshipo_pn_packing_type'] == "box") {
										if (isset($general_settings['hitshipo_pn_boxes']) && !empty($general_settings['hitshipo_pn_boxes'])) {
											foreach ($general_settings['hitshipo_pn_boxes'] as $box) {
												if ($box['enabled'] != 1) {
													continue;
												} else {
													$boxes_to_shipo[] = $box;
												}
											}
										}
									}

									global $pn_core;
									$frm_curr = get_option('woocommerce_currency');
									$to_curr = isset($pn_core[$custom_settings[$create_shipment_for]['hitshipo_pn_country']]) ? $pn_core[$custom_settings[$create_shipment_for]['hitshipo_pn_country']]['currency'] : '';
									$curr_con_rate = (isset($custom_settings[$create_shipment_for]['hitshipo_pn_con_rate']) && !empty($custom_settings[$create_shipment_for]['hitshipo_pn_con_rate'])) ? $custom_settings[$create_shipment_for]['hitshipo_pn_con_rate'] : 0;

									if (!empty($frm_curr) && !empty($to_curr) && ($frm_curr != $to_curr)) {
										if (isset($general_settings['hitshipo_pn_auto_con_rate']) && $general_settings['hitshipo_pn_auto_con_rate'] == "yes") {
											$current_date = date('m-d-Y', time());
											$ex_rate_data = get_option('hitshipo_pn_ex_rate' . $create_shipment_for);
											$ex_rate_data = !empty($ex_rate_data) ? $ex_rate_data : array();
											if (empty($ex_rate_data) || (isset($ex_rate_data['date']) && $ex_rate_data['date'] != $current_date)) {
												if (isset($custom_settings[$create_shipment_for]['hitshipo_pn_country']) && !empty($custom_settings[$create_shipment_for]['hitshipo_pn_country']) && isset($general_settings['hitshipo_pn_integration_key']) && !empty($general_settings['hitshipo_pn_integration_key'])) {

													$ex_rate_Request = json_encode(array(
														'integrated_key' => $general_settings['hitshipo_pn_integration_key'],
														'from_curr' => $frm_curr,
														'to_curr' => $to_curr
													));

													$ex_rate_url = "https://app.myshipi.com/get_exchange_rate.php";
													// $ex_rate_url = "http://localhost/hitshipo/get_exchange_rate.php";
													$ex_rate_response = wp_remote_post(
														$ex_rate_url,
														array(
															'method'      => 'POST',
															'timeout'     => 45,
															'redirection' => 5,
															'httpversion' => '1.0',
															'blocking'    => true,
															'headers'     => array('Content-Type' => 'application/json; charset=utf-8'),
															'body'        => $ex_rate_Request,
															'sslverify'   => FALSE
														)
													);

													$ex_rate_result = (is_array($ex_rate_response) && isset($ex_rate_response['body'])) ? json_decode($ex_rate_response['body'], true) : array();

													if (!empty($ex_rate_result) && isset($ex_rate_result['ex_rate']) && $ex_rate_result['ex_rate'] != "Not Found") {
														$ex_rate_result['date'] = $current_date;
														update_option('hitshipo_pn_ex_rate' . $create_shipment_for, $ex_rate_result);
													} else {
														if (!empty($ex_rate_data)) {
															$ex_rate_data['date'] = $current_date;
															update_option('hitshipo_pn_ex_rate' . $create_shipment_for, $ex_rate_data);
														}
													}
												}
											}
											$get_ex_rate = get_option('hitshipo_pn_ex_rate' . $create_shipment_for, '');
											$get_ex_rate = !empty($get_ex_rate) ? $get_ex_rate : array();
											$curr_con_rate = (!empty($get_ex_rate) && isset($get_ex_rate['ex_rate'])) ? $get_ex_rate['ex_rate'] : 0;
										}
									}

									foreach ($custom_settings[$create_shipment_for]['products'] as $prod_to_shipo_key => $prod_to_shipo) {
										if (!empty($frm_curr) && !empty($to_curr) && ($frm_curr != $to_curr)) {
											if ($curr_con_rate > 0) {
												$custom_settings[$create_shipment_for]['products'][$prod_to_shipo_key]['price'] = $prod_to_shipo['price'] * $curr_con_rate;
											}
										}
									}

									$data = array();
									$data['integrated_key'] = $general_settings['hitshipo_pn_integration_key'];
									$data['order_id'] = $order_id;
									$data['exec_type'] = $execution;
									$data['mode'] = $mode;
									$data['carrier_type'] = 'pn';
									$data['meta'] = array(
										"site_id" => $custom_settings[$create_shipment_for]['hitshipo_pn_site_id'],
										"password"  => $custom_settings[$create_shipment_for]['hitshipo_pn_site_pwd'],
										"party_type" => $custom_settings[$create_shipment_for]['hitshipo_pn_part_type'],
										"issue_c" => $custom_settings[$create_shipment_for]['hitshipo_pn_issue_c'],
										"api_key" => $custom_settings[$create_shipment_for]['hitshipo_pn_api_key'],
										"t_company" => $order_shipping_company,
										"t_address1" => str_replace('"', '', $order_shipping_address_1),
										"t_address2" => str_replace('"', '', $order_shipping_address_2),
										"t_city" => $order_shipping_city,
										"t_state" => $order_shipping_state,
										"t_postal" => $order_shipping_postcode,
										"t_country" => $order_shipping_country,
										"t_name" => $order_shipping_first_name . ' ' . $order_shipping_last_name,
										"t_phone" => $order_shipping_phone,
										"t_email" => $order_shipping_email,
										"insurance" => $general_settings['hitshipo_pn_insure'],
										"pack_this" => "Y",
										"shipping_charge" => $shipping_charge,
										"products" => $custom_settings[$create_shipment_for]['products'],
										"pack_algorithm" => $general_settings['hitshipo_pn_packing_type'],
										"boxes" => $boxes_to_shipo,
										"max_weight" => $general_settings['hitshipo_pn_max_weight'],
										"weight_dim_unit" => $general_settings['hitshipo_pn_weight_unit'],
										"cod" => ($general_settings['hitshipo_pn_cod'] == 'yes') ? "Y" : "N",
										"service_code" => $custom_settings[$create_shipment_for]['service_code'],
										"shipment_content" => $ship_content,
										"email_alert" => (isset($general_settings['hitshipo_pn_email_alert']) && ($general_settings['hitshipo_pn_email_alert'] == 'yes')) ? "Y" : "N",
										"sms_alert" => (isset($general_settings['hitshipo_pn_sms_alert']) && ($general_settings['hitshipo_pn_sms_alert'] == 'yes')) ? "Y" : "N",
										"s_company" => $custom_settings[$create_shipment_for]['hitshipo_pn_company'],
										"s_address1" => $custom_settings[$create_shipment_for]['hitshipo_pn_address1'],
										"s_address2" => $custom_settings[$create_shipment_for]['hitshipo_pn_address2'],
										"s_city" => $custom_settings[$create_shipment_for]['hitshipo_pn_city'],
										"s_state" => $custom_settings[$create_shipment_for]['hitshipo_pn_state'],
										"s_postal" => $custom_settings[$create_shipment_for]['hitshipo_pn_zip'],
										"s_country" => $custom_settings[$create_shipment_for]['hitshipo_pn_country'],
										"gstin" => $custom_settings[$create_shipment_for]['hitshipo_pn_gstin'],
										"s_name" => $custom_settings[$create_shipment_for]['hitshipo_pn_shipper_name'],
										"s_phone" => $custom_settings[$create_shipment_for]['hitshipo_pn_mob_num'],
										"s_email" => $custom_settings[$create_shipment_for]['hitshipo_pn_email'],
										"label_size" => $general_settings['hitshipo_pn_print_size'],
										"label_paper_size" => $general_settings['hitshipo_pn_paper_size'],
										"eori" => $general_settings['hitshipo_pn_eori'],
										"hsn" => $general_settings['hitshipo_pn_hsn'],
										"pac_type" => $general_settings['hitshipo_pn_pac_type'],
										"tos" => $general_settings['hitshipo_pn_tos'],
										"tod_cc" => $general_settings['hitshipo_pn_tod_cc'],
										"tod_ccl" => $general_settings['hitshipo_pn_tod_ccl'],
										"sent_email_to" => $custom_settings[$create_shipment_for]['hitshipo_pn_label_email'],
										"pic_exec_type" => $pickup_mode,
										"pic_open_time" => '',
										"pic_close_time" => '',
										"translation" => ((isset($general_settings['hitshipo_pn_translation']) && $general_settings['hitshipo_pn_translation'] == "yes") ? 'Y' : 'N'),
										"translation_key" => (isset($general_settings['hitshipo_pn_translation_key']) ? $general_settings['hitshipo_pn_translation_key'] : ''),
										"label" => $create_shipment_for
									);
									
									//Bulk shipment
									$bulk_shipment_url = "https://app.myshipi.com/label_api/create_shipment.php";
									// $bulk_shipment_url = "http://localhost/hitshipo/label_api/create_shipment.php";
									$response = wp_remote_post(
										$bulk_shipment_url,
										array(
											'method'      => 'POST',
											'timeout'     => 45,
											'redirection' => 5,
											'httpversion' => '1.0',
											'blocking'    => true,
											'headers'     => array('Content-Type' => 'application/json; charset=utf-8'),
											'body'        => json_encode($data),
											'sslverify'   => FALSE
										)
									);

									$output = (is_array($response) && isset($response['body'])) ? json_decode($response['body'],true) : [];

									if ($output) {
										if (isset($output['status']) || isset($output['pickup_status'])) {

											if (isset($output['status']) && is_array($output['status']) && $output['status'] != 'success') {
												// update_option('hit_pn_status_'.$order_id, $output['status'][0]);
												$failed += 1;
												$failed_ids[] = $order_id;
											} else if (isset($output['status']) && $output['status'] == 'success') {
												$output['user_id'] = $create_shipment_for;
												$result_arr = get_option('hit_pn_values_' . $order_id, array());
												if (!empty($result_arr)) {
													$result_arr = json_decode($result_arr, true);
												}
												$result_arr[] = $output;

												update_option('hit_pn_values_' . $order_id, json_encode($result_arr));

												$success += 1;
											}
										} else {
											$failed += 1;
											$failed_ids[] = $order_id;
										}
									} else {
										$failed += 1;
										$failed_ids[] = $order_id;
									}
								}
							} else {
								$failed += 1;
							}
						}
						return $redirect_to = add_query_arg(array(
							'success_lbl' => $success,
							'failed_lbl' => $failed,
							// 'failed_lbl_ids' => implode( ',', rtrim($failed_ids, ",") ),
						), $redirect_to);
					}
				}
			}

			function shipo_bulk_label_action_admin_notice()
			{
				if (isset($_GET['success_lbl']) && isset($_GET['failed_lbl'])) {
					printf('<div id="message" class="updated fade"><p>
						Generated labels: ' . esc_html($_GET['success_lbl']) . ' Failed Label: ' . esc_html($_GET['failed_lbl']) . ' </p></div>');
				}
			}

			public function pn_track($order)
			{
			}
			public function save_user_fields($user_id)
			{
				if (isset($_POST['hitshipo_pn_country'])) {
					$general_settings['hitshipo_pn_site_id'] = sanitize_text_field(isset($_POST['hitshipo_pn_site_id']) ? $_POST['hitshipo_pn_site_id'] : '');
					$general_settings['hitshipo_pn_site_pwd'] = sanitize_text_field(isset($_POST['hitshipo_pn_site_pwd']) ? $_POST['hitshipo_pn_site_pwd'] : '');
					$general_settings['hitshipo_pn_part_type'] = sanitize_text_field(isset($_POST['hitshipo_pn_part_type']) ? $_POST['hitshipo_pn_part_type'] : '');
					$general_settings['hitshipo_pn_issue_c'] = sanitize_text_field(isset($_POST['hitshipo_pn_issue_c']) ? $_POST['hitshipo_pn_issue_c'] : '');
					$general_settings['hitshipo_pn_api_key'] = sanitize_text_field(isset($_POST['hitshipo_pn_api_key']) ? $_POST['hitshipo_pn_api_key'] : '');
					$general_settings['hitshipo_pn_shipper_name'] = sanitize_text_field(isset($_POST['hitshipo_pn_shipper_name']) ? $_POST['hitshipo_pn_shipper_name'] : '');
					$general_settings['hitshipo_pn_company'] = sanitize_text_field(isset($_POST['hitshipo_pn_company']) ? $_POST['hitshipo_pn_company'] : '');
					$general_settings['hitshipo_pn_mob_num'] = sanitize_text_field(isset($_POST['hitshipo_pn_mob_num']) ? $_POST['hitshipo_pn_mob_num'] : '');
					$general_settings['hitshipo_pn_email'] = sanitize_text_field(isset($_POST['hitshipo_pn_email']) ? $_POST['hitshipo_pn_email'] : '');
					$general_settings['hitshipo_pn_address1'] = sanitize_text_field(isset($_POST['hitshipo_pn_address1']) ? $_POST['hitshipo_pn_address1'] : '');
					$general_settings['hitshipo_pn_address2'] = sanitize_text_field(isset($_POST['hitshipo_pn_address2']) ? $_POST['hitshipo_pn_address2'] : '');
					$general_settings['hitshipo_pn_city'] = sanitize_text_field(isset($_POST['hitshipo_pn_city']) ? $_POST['hitshipo_pn_city'] : '');
					$general_settings['hitshipo_pn_state'] = sanitize_text_field(isset($_POST['hitshipo_pn_state']) ? $_POST['hitshipo_pn_state'] : '');
					$general_settings['hitshipo_pn_zip'] = sanitize_text_field(isset($_POST['hitshipo_pn_zip']) ? $_POST['hitshipo_pn_zip'] : '');
					$general_settings['hitshipo_pn_country'] = sanitize_text_field(isset($_POST['hitshipo_pn_country']) ? $_POST['hitshipo_pn_country'] : '');
					$general_settings['hitshipo_pn_gstin'] = sanitize_text_field(isset($_POST['hitshipo_pn_gstin']) ? $_POST['hitshipo_pn_gstin'] : '');
					$general_settings['hitshipo_pn_con_rate'] = sanitize_text_field(isset($_POST['hitshipo_pn_con_rate']) ? $_POST['hitshipo_pn_con_rate'] : '');
					$general_settings['hitshipo_pn_def_dom'] = sanitize_text_field(isset($_POST['hitshipo_pn_def_dom']) ? $_POST['hitshipo_pn_def_dom'] : '');

					$general_settings['hitshipo_pn_def_inter'] = sanitize_text_field(isset($_POST['hitshipo_pn_def_inter']) ? $_POST['hitshipo_pn_def_inter'] : '');

					update_post_meta($user_id, 'hitshipo_pn_vendor_settings', $general_settings);
				}
			}

			public function hit_define_pn_credentails($user)
			{
				global $pn_core;
				$main_settings = get_option('hitshipo_pn_main_settings');
				$main_settings = empty($main_settings) ? array() : $main_settings;
				$allow = false;

				if (!isset($main_settings['hitshipo_pn_v_roles'])) {
					return;
				} else {
					foreach ($user->roles as $value) {
						if (in_array($value, $main_settings['hitshipo_pn_v_roles'])) {
							$allow = true;
						}
					}
				}

				if (!$allow) {
					return;
				}

				$general_settings = get_post_meta($user->ID, 'hitshipo_pn_vendor_settings', true);
				$general_settings = empty($general_settings) ? array() : $general_settings;
				$countires =  array(
					'AF' => 'Afghanistan',
					'AL' => 'Albania',
					'DZ' => 'Algeria',
					'AS' => 'American Samoa',
					'AD' => 'Andorra',
					'AO' => 'Angola',
					'AI' => 'Anguilla',
					'AG' => 'Antigua and Barbuda',
					'AR' => 'Argentina',
					'AM' => 'Armenia',
					'AW' => 'Aruba',
					'AU' => 'Australia',
					'AT' => 'Austria',
					'AZ' => 'Azerbaijan',
					'BS' => 'Bahamas',
					'BH' => 'Bahrain',
					'BD' => 'Bangladesh',
					'BB' => 'Barbados',
					'BY' => 'Belarus',
					'BE' => 'Belgium',
					'BZ' => 'Belize',
					'BJ' => 'Benin',
					'BM' => 'Bermuda',
					'BT' => 'Bhutan',
					'BO' => 'Bolivia',
					'BA' => 'Bosnia and Herzegovina',
					'BW' => 'Botswana',
					'BR' => 'Brazil',
					'VG' => 'British Virgin Islands',
					'BN' => 'Brunei',
					'BG' => 'Bulgaria',
					'BF' => 'Burkina Faso',
					'BI' => 'Burundi',
					'KH' => 'Cambodia',
					'CM' => 'Cameroon',
					'CA' => 'Canada',
					'CV' => 'Cape Verde',
					'KY' => 'Cayman Islands',
					'CF' => 'Central African Republic',
					'TD' => 'Chad',
					'CL' => 'Chile',
					'CN' => 'China',
					'CO' => 'Colombia',
					'KM' => 'Comoros',
					'CK' => 'Cook Islands',
					'CR' => 'Costa Rica',
					'HR' => 'Croatia',
					'CU' => 'Cuba',
					'CY' => 'Cyprus',
					'CZ' => 'Czech Republic',
					'DK' => 'Denmark',
					'DJ' => 'Djibouti',
					'DM' => 'Dominica',
					'DO' => 'Dominican Republic',
					'TL' => 'East Timor',
					'EC' => 'Ecuador',
					'EG' => 'Egypt',
					'SV' => 'El Salvador',
					'GQ' => 'Equatorial Guinea',
					'ER' => 'Eritrea',
					'EE' => 'Estonia',
					'ET' => 'Ethiopia',
					'FK' => 'Falkland Islands',
					'FO' => 'Faroe Islands',
					'FJ' => 'Fiji',
					'FI' => 'Finland',
					'FR' => 'France',
					'GF' => 'French Guiana',
					'PF' => 'French Polynesia',
					'GA' => 'Gabon',
					'GM' => 'Gambia',
					'GE' => 'Georgia',
					'DE' => 'Germany',
					'GH' => 'Ghana',
					'GI' => 'Gibraltar',
					'GR' => 'Greece',
					'GL' => 'Greenland',
					'GD' => 'Grenada',
					'GP' => 'Guadeloupe',
					'GU' => 'Guam',
					'GT' => 'Guatemala',
					'GG' => 'Guernsey',
					'GN' => 'Guinea',
					'GW' => 'Guinea-Bissau',
					'GY' => 'Guyana',
					'HT' => 'Haiti',
					'HN' => 'Honduras',
					'HK' => 'Hong Kong',
					'HU' => 'Hungary',
					'IS' => 'Iceland',
					'IN' => 'India',
					'ID' => 'Indonesia',
					'IR' => 'Iran',
					'IQ' => 'Iraq',
					'IE' => 'Ireland',
					'IL' => 'Israel',
					'IT' => 'Italy',
					'CI' => 'Ivory Coast',
					'JM' => 'Jamaica',
					'JP' => 'Japan',
					'JE' => 'Jersey',
					'JO' => 'Jordan',
					'KZ' => 'Kazakhstan',
					'KE' => 'Kenya',
					'KI' => 'Kiribati',
					'KW' => 'Kuwait',
					'KG' => 'Kyrgyzstan',
					'LA' => 'Laos',
					'LV' => 'Latvia',
					'LB' => 'Lebanon',
					'LS' => 'Lesotho',
					'LR' => 'Liberia',
					'LY' => 'Libya',
					'LI' => 'Liechtenstein',
					'LT' => 'Lithuania',
					'LU' => 'Luxembourg',
					'MO' => 'Macao',
					'MK' => 'Macedonia',
					'MG' => 'Madagascar',
					'MW' => 'Malawi',
					'MY' => 'Malaysia',
					'MV' => 'Maldives',
					'ML' => 'Mali',
					'MT' => 'Malta',
					'MH' => 'Marshall Islands',
					'MQ' => 'Martinique',
					'MR' => 'Mauritania',
					'MU' => 'Mauritius',
					'YT' => 'Mayotte',
					'MX' => 'Mexico',
					'FM' => 'Micronesia',
					'MD' => 'Moldova',
					'MC' => 'Monaco',
					'MN' => 'Mongolia',
					'ME' => 'Montenegro',
					'MS' => 'Montserrat',
					'MA' => 'Morocco',
					'MZ' => 'Mozambique',
					'MM' => 'Myanmar',
					'NA' => 'Namibia',
					'NR' => 'Nauru',
					'NP' => 'Nepal',
					'NL' => 'Netherlands',
					'NC' => 'New Caledonia',
					'NZ' => 'New Zealand',
					'NI' => 'Nicaragua',
					'NE' => 'Niger',
					'NG' => 'Nigeria',
					'NU' => 'Niue',
					'KP' => 'North Korea',
					'MP' => 'Northern Mariana Islands',
					'NO' => 'Norway',
					'OM' => 'Oman',
					'PK' => 'Pakistan',
					'PW' => 'Palau',
					'PA' => 'Panama',
					'PG' => 'Papua New Guinea',
					'PY' => 'Paraguay',
					'PE' => 'Peru',
					'PH' => 'Philippines',
					'PL' => 'Poland',
					'PT' => 'Portugal',
					'PR' => 'Puerto Rico',
					'QA' => 'Qatar',
					'CG' => 'Republic of the Congo',
					'RE' => 'Reunion',
					'RO' => 'Romania',
					'RU' => 'Russia',
					'RW' => 'Rwanda',
					'SH' => 'Saint Helena',
					'KN' => 'Saint Kitts and Nevis',
					'LC' => 'Saint Lucia',
					'VC' => 'Saint Vincent and the Grenadines',
					'WS' => 'Samoa',
					'SM' => 'San Marino',
					'ST' => 'Sao Tome and Principe',
					'SA' => 'Saudi Arabia',
					'SN' => 'Senegal',
					'RS' => 'Serbia',
					'SC' => 'Seychelles',
					'SL' => 'Sierra Leone',
					'SG' => 'Singapore',
					'SK' => 'Slovakia',
					'SI' => 'Slovenia',
					'SB' => 'Solomon Islands',
					'SO' => 'Somalia',
					'ZA' => 'South Africa',
					'KR' => 'South Korea',
					'SS' => 'South Sudan',
					'ES' => 'Spain',
					'LK' => 'Sri Lanka',
					'SD' => 'Sudan',
					'SR' => 'Suriname',
					'SZ' => 'Swaziland',
					'SE' => 'Sweden',
					'CH' => 'Switzerland',
					'SY' => 'Syria',
					'TW' => 'Taiwan',
					'TJ' => 'Tajikistan',
					'TZ' => 'Tanzania',
					'TH' => 'Thailand',
					'TG' => 'Togo',
					'TO' => 'Tonga',
					'TT' => 'Trinidad and Tobago',
					'TN' => 'Tunisia',
					'TR' => 'Turkey',
					'TC' => 'Turks and Caicos Islands',
					'TV' => 'Tuvalu',
					'VI' => 'U.S. Virgin Islands',
					'UG' => 'Uganda',
					'UA' => 'Ukraine',
					'AE' => 'United Arab Emirates',
					'GB' => 'United Kingdom',
					'US' => 'United States',
					'UY' => 'Uruguay',
					'UZ' => 'Uzbekistan',
					'VU' => 'Vanuatu',
					'VE' => 'Venezuela',
					'VN' => 'Vietnam',
					'YE' => 'Yemen',
					'ZM' => 'Zambia',
					'ZW' => 'Zimbabwe',
				);
				$_pn_carriers = array(
					//"Public carrier name" => "technical name",
					"7" => "DPD",
					"11" => "Posten Varubrev Ekonomi",
					"17" => "MyPack Home",
					"18" => "Parcel",
					"19" => "MyPack Collect",
					"20" => "Return Pickup",
					"21" => "Företagspaket Ekonomi Förbet",
					"22" => "PostNord Return Pickup",
					"24" => "Return Drop Off",
					"25" => "Postpaket",
					"27" => "Postpaket Kontant",
					"28" => "SverigePaket",
					"30" => "MyPack Home Small (Parcel Letter)",
					"32" => "MyPack Home",
					"34" => "International tracked letter",
					"35" => "Företagspaket 09.00, (Förbet.)",
					"37" => "Tompallsdistribution",
					"38" => "Kartong med porto",
					"42" => "Express Next Day",
					"45" => "Brevpostförskott Inrikes",
					"47" => "EMS International Express",
					"48" => "InNight",
					"49" => "InNight Reverse",
					"51" => "Företagspaket Comeback",
					"52" => "Pallet",
					"53" => "PALL.ETT Special",
					"54" => "PALL.ETT+",
					"58" => "InNight Forwarding",
					"59" => "Retail Delivery",
					"69" => "InNight Systemtransporter",
					"75" => "Posten Varubrev 1:a klass",
					"78" => "Posten Varubrev Klimatek",
					"79" => "Posten Varubrev Ekonomi",
					"80" => "DPD MAX",
					"81" => "Lokal Åkeritjänst - Pall",
					"82" => "Lokal Åkeritjänst - Paket",
					"83" => "Groupage",
					"84" => "Road Freight Europe",
					"85" => "Part- /Full Loads",
					"86" => "Posten Varubrev 1:a klass",
					"87" => "Posten Varubrev Retur",
					"88" => "DPD",
					"91" => "International Parcel",
					"92" => "Import-Ekonomipaket",
					"93" => "eCIP Collect",
					"94" => "eCIP Home",
					"95" => "Postpaket Utrikes",
					"97" => "Parcel Post Collect",
					"98" => "Import-EPG",
					"AF" => "Brevpostförskott",
					"RR" => "Rek",
					"VV" => "Värde",
					"RP" => "Rek",
					"RL" => "RL",
					"RK" => "RK",
					"AJ" => "Skicka Hem",
					"LX" => "Expressbrev",
					"EE" => "EMS",
					"AP" => "Skicka Lätt",
					"ED" => "EMS",
					"EM" => "EMS",
					"VS" => "Värde skjutvapen RPS",
					"LY" => "Spårbart brev",
					"UX" => "Export Letter Sweden",
					"AK" => "Postal Distributed Newspaper",
				);

				$part_type = array("160" => "160 - customer number", "167" => "167 - VAT customer number", "156" => "156 - Service point ID", "229" => "229 - Geographic location");
				$issue_c = array("Z11-DK" => "Z11-Denmark", "Z12-SE" => "Z12-Sweden", "Z13-NO" => "Z13-Norway", "Z14-FI" => "Z14-Finland");

				echo '<hr><h3 class="heading">PostNord - <a href="https://myshipi.com/" target="_blank">Shipi</a></h3>';
			?>

				<table class="form-table">
					<tr>
						<td style=" width: 50%; padding: 5px; ">
							<h4> <span class="woocommerce-help-tip" data-tip="<?php _e('PostNord Integration Team will give this details to you.', 'hitshipo_pn') ?>"></span> <?php _e('PostNord API Application ID', 'hitshipo_pn') ?></h4>
							<p> <?php _e('Leave this field as empty to use default account.', 'hitshipo_pn') ?> </p>
						</td>
						<td>
							<input type="text" name="hitshipo_pn_site_id" value="<?php echo (isset($general_settings['hitshipo_pn_site_id'])) ? $general_settings['hitshipo_pn_site_id'] : ''; ?>">
						</td>

					</tr>
					<tr>
						<td style=" width: 50%; padding: 5px; ">
							<h4> <span class="woocommerce-help-tip" data-tip="<?php _e('PostNord Integration Team will give this details to you.', 'hitshipo_pn') ?>"></span> <?php _e('PostNord Party ID', 'hitshipo_pn') ?></h4>
							<p> <?php _e('Leave this field as empty to use default account.', 'hitshipo_pn') ?> </p>
						</td>
						<td>
							<input type="text" name="hitshipo_pn_site_pwd" value="<?php echo (isset($general_settings['hitshipo_pn_site_pwd'])) ? $general_settings['hitshipo_pn_site_pwd'] : ''; ?>">
						</td>
					</tr>
					<tr>
						<td style=" width: 50%; padding: 5px; ">
							<h4> <span class="woocommerce-help-tip" data-tip="<?php _e('PostNord Integration Team will give this details to you.', 'hitshipo_pn') ?>"></span> <?php _e('PostNord API key', 'hitshipo_pn') ?></h4>
							<p> <?php _e('Leave this field as empty to use default account.', 'hitshipo_pn') ?> </p>
						</td>
						<td>
							<input type="text" name="hitshipo_pn_api_key" value="<?php echo (isset($general_settings['hitshipo_pn_api_key'])) ? $general_settings['hitshipo_pn_api_key'] : ''; ?>">
						</td>
					</tr>
					<tr>
						<td style=" width: 50%; padding: 5px; ">
							<h4> <span class="woocommerce-help-tip" data-tip="<?php _e('Choose PostNord Party ID type.', 'hitshipo_pn') ?>"></span> <?php _e('PostNord Party ID type', 'hitshipo_pn') ?></h4>
						</td>
						<td>
							<select name="hitshipo_pn_part_type" class="wc-enhanced-select" style="width:200px;">
								<?php foreach ($part_type as $key => $value) {
									if (isset($general_settings['hitshipo_pn_part_type']) && ($general_settings['hitshipo_pn_part_type'] == $key)) {
										echo "<option value=" . $key . " selected='true'>" . $value . "</option>";
									} else {
										echo "<option value=" . $key . ">" . $value . "</option>";
									}
								} ?>
							</select>
						</td>
					</tr>
					<tr>
						<td style=" width: 50%; padding: 5px; ">
							<h4> <span class="woocommerce-help-tip" data-tip="<?php _e('Choose Issuer Code and Country.', 'hitshipo_pn') ?>"></span> <?php _e('Issuer Code and Country', 'hitshipo_pn') ?></h4>
						</td>
						<td>
							<select name="hitshipo_pn_issue_c" class="wc-enhanced-select" style="width:200px;">
								<?php foreach ($issue_c as $key => $value) {
									if (isset($general_settings['hitshipo_pn_issue_c']) && ($general_settings['hitshipo_pn_issue_c'] == $key)) {
										echo "<option value=" . $key . " selected='true'>" . $value . "</option>";
									} else {
										echo "<option value=" . $key . ">" . $value . "</option>";
									}
								} ?>
							</select>
						</td>
					</tr>
					<tr>
						<td style=" width: 50%; padding: 5px; ">
							<h4> <span class="woocommerce-help-tip" data-tip="<?php _e('Shipping Person Name', 'hitshipo_pn') ?>"></span> <?php _e('Shipper Name', 'hitshipo_pn') ?></h4>
						</td>
						<td>
							<input type="text" name="hitshipo_pn_shipper_name" value="<?php echo (isset($general_settings['hitshipo_pn_shipper_name'])) ? $general_settings['hitshipo_pn_shipper_name'] : ''; ?>">
						</td>
					</tr>
					<tr>
						<td style=" width: 50%; padding: 5px; ">
							<h4> <span class="woocommerce-help-tip" data-tip="<?php _e('Shipper Company Name.', 'hitshipo_pn') ?>"></span> <?php _e('Company Name', 'hitshipo_pn') ?></h4>
						</td>
						<td>
							<input type="text" name="hitshipo_pn_company" value="<?php echo (isset($general_settings['hitshipo_pn_company'])) ? $general_settings['hitshipo_pn_company'] : ''; ?>">
						</td>
					</tr>
					<tr>
						<td style=" width: 50%; padding: 5px; ">
							<h4> <span class="woocommerce-help-tip" data-tip="<?php _e('Shipper Mobile / Contact Number.', 'hitshipo_pn') ?>"></span> <?php _e('Contact Number', 'hitshipo_pn') ?></h4>
						</td>
						<td>
							<input type="text" name="hitshipo_pn_mob_num" value="<?php echo (isset($general_settings['hitshipo_pn_mob_num'])) ? $general_settings['hitshipo_pn_mob_num'] : ''; ?>">
						</td>
					</tr>
					<tr>
						<td style=" width: 50%; padding: 5px; ">
							<h4> <span class="woocommerce-help-tip" data-tip="<?php _e('Email Address of the Shipper.', 'hitshipo_pn') ?>"></span> <?php _e('Email Address', 'hitshipo_pn') ?></h4>
						</td>
						<td>
							<input type="text" name="hitshipo_pn_email" value="<?php echo (isset($general_settings['hitshipo_pn_email'])) ? $general_settings['hitshipo_pn_email'] : ''; ?>">
						</td>
					</tr>
					<tr>
						<td style=" width: 50%; padding: 5px; ">
							<h4> <span class="woocommerce-help-tip" data-tip="<?php _e('Address Line 1 of the Shipper from Address.', 'hitshipo_pn') ?>"></span> <?php _e('Address Line 1', 'hitshipo_pn') ?></h4>
						</td>
						<td>
							<input type="text" name="hitshipo_pn_address1" value="<?php echo (isset($general_settings['hitshipo_pn_address1'])) ? $general_settings['hitshipo_pn_address1'] : ''; ?>">
						</td>
					</tr>
					<tr>
						<td style=" width: 50%; padding: 5px; ">
							<h4> <span class="woocommerce-help-tip" data-tip="<?php _e('Address Line 2 of the Shipper from Address.', 'hitshipo_pn') ?>"></span> <?php _e('Address Line 2', 'hitshipo_pn') ?></h4>
						</td>
						<td>
							<input type="text" name="hitshipo_pn_address2" value="<?php echo (isset($general_settings['hitshipo_pn_address2'])) ? $general_settings['hitshipo_pn_address2'] : ''; ?>">
						</td>
					</tr>
					<tr>
						<td style=" width: 50%;padding: 5px; ">
							<h4> <span class="woocommerce-help-tip" data-tip="<?php _e('City of the Shipper from address.', 'hitshipo_pn') ?>"></span> <?php _e('City', 'hitshipo_pn') ?></h4>
						</td>
						<td>
							<input type="text" name="hitshipo_pn_city" value="<?php echo (isset($general_settings['hitshipo_pn_city'])) ? $general_settings['hitshipo_pn_city'] : ''; ?>">
						</td>
					</tr>
					<tr>
						<td style=" width: 50%; padding: 5px; ">
							<h4> <span class="woocommerce-help-tip" data-tip="<?php _e('State of the Shipper from address.', 'hitshipo_pn') ?>"></span> <?php _e('State (Two Letter String)', 'hitshipo_pn') ?></h4>
						</td>
						<td>
							<input type="text" name="hitshipo_pn_state" value="<?php echo (isset($general_settings['hitshipo_pn_state'])) ? $general_settings['hitshipo_pn_state'] : ''; ?>">
						</td>
					</tr>
					<tr>
						<td style=" width: 50%; padding: 5px; ">
							<h4> <span class="woocommerce-help-tip" data-tip="<?php _e('Postal/Zip Code.', 'hitshipo_pn') ?>"></span> <?php _e('Postal/Zip Code', 'hitshipo_pn') ?></h4>
						</td>
						<td>
							<input type="text" name="hitshipo_pn_zip" value="<?php echo (isset($general_settings['hitshipo_pn_zip'])) ? $general_settings['hitshipo_pn_zip'] : ''; ?>">
						</td>
					</tr>
					<tr>
						<td style=" width: 50%; padding: 5px; ">
							<h4> <span class="woocommerce-help-tip" data-tip="<?php _e('Country of the Shipper from Address.', 'hitshipo_pn') ?>"></span> <?php _e('Country', 'hitshipo_pn') ?></h4>
						</td>
						<td>
							<select name="hitshipo_pn_country" class="wc-enhanced-select" style="width:210px;">
								<?php foreach ($countires as $key => $value) {

									if (isset($general_settings['hitshipo_pn_country']) && ($general_settings['hitshipo_pn_country'] == $key)) {
										echo "<option value=" . $key . " selected='true'>" . $value . " [" . $pn_core[$key]['currency'] . "]</option>";
									} else {
										echo "<option value=" . $key . ">" . $value . " [" . $pn_core[$key]['currency'] . "]</option>";
									}
								} ?>
							</select>
						</td>
					</tr>
					<tr>
						<td style=" width: 50%; padding: 5px; ">
							<h4> <span class="woocommerce-help-tip" data-tip="<?php _e('GSTIN/VAT No.', 'hitshipo_pn') ?>"></span> <?php _e('GSTIN/VAT No', 'hitshipo_pn') ?></h4>
						</td>
						<td>
							<input type="text" name="hitshipo_pn_gstin" value="<?php echo (isset($general_settings['hitshipo_pn_gstin'])) ? $general_settings['hitshipo_pn_gstin'] : ''; ?>">
						</td>
					</tr>
					<tr>
						<td style=" width: 50%; padding: 5px; ">
							<h4> <span class="woocommerce-help-tip" data-tip="<?php _e('Conversion Rate from Site Currency to PostNord Currency.', 'hitshipo_pn') ?>"></span> <?php _e('Conversion Rate from Site Currency to PostNord Currency ( Ignore if auto conversion is Enabled )', 'hitshipo_pn') ?></h4>
						</td>
						<td>
							<input type="text" name="hitshipo_pn_con_rate" value="<?php echo (isset($general_settings['hitshipo_pn_con_rate'])) ? $general_settings['hitshipo_pn_con_rate'] : ''; ?>">
						</td>
					</tr>
					<tr>
						<td style=" width: 50%; padding: 5px; ">
							<h4> <span class="woocommerce-help-tip" data-tip="<?php _e('Default Domestic Shipping.', 'hitshipo_pn') ?>"></span> <?php _e('Default Domestic Service', 'hitshipo_pn') ?></h4>
							<p><?php _e('This will be used while shipping label generation.', 'hitshipo_pn') ?></p>
						</td>
						<td>
							<select name="hitshipo_pn_def_dom" class="wc-enhanced-select" style="width:210px;">
								<?php foreach ($_pn_carriers as $key => $value) {
									if (isset($general_settings['hitshipo_pn_def_dom']) && ($general_settings['hitshipo_pn_def_dom'] == $key)) {
										echo "<option value=" . $key . " selected='true'>[" . $key . "] " . $value . "</option>";
									} else {
										echo "<option value=" . $key . ">[" . $key . "] " . $value . "</option>";
									}
								} ?>
							</select>
						</td>
					</tr>
					<tr>
						<td style=" width: 50%; padding: 5px; ">
							<h4> <span class="woocommerce-help-tip" data-tip="<?php _e('Default International Shipping.', 'hitshipo_pn') ?>"></span> <?php _e('Default International Service', 'hitshipo_pn') ?></h4>
							<p><?php _e('This will be used while shipping label generation.', 'hitshipo_pn') ?></p>
						</td>
						<td>
							<select name="hitshipo_pn_def_inter" class="wc-enhanced-select" style="width:210px;">
								<?php foreach ($_pn_carriers as $key => $value) {
									if (isset($general_settings['hitshipo_pn_def_inter']) && ($general_settings['hitshipo_pn_def_inter'] == $key)) {
										echo "<option value=" . $key . " selected='true'>[" . $key . "] " . $value . "</option>";
									} else {
										echo "<option value=" . $key . ">[" . $key . "] " . $value . "</option>";
									}
								} ?>
							</select>
						</td>
					</tr>
				</table>
				<hr>
			<?php
			}
			public function hit_save_product_meta($post_id)
			{
				if (isset($_POST['pn_shipment'])) {
					$pn_shipment = sanitize_text_field($_POST['pn_shipment']);
					if (!empty($pn_shipment)){
						if ($this->hpos_enabled && $this->new_prod_editor_enabled) {
	 	                    $hpos_prod_data = wc_get_product($post_id);
	 	                    $hpos_prod_data->update_meta_data("pn_address", (string) esc_html( $pn_shipment ));
	 	                } else {
							update_post_meta($post_id, 'pn_address', (string) esc_html($pn_shipment));
						}
					}
				}
			}
			public function hit_choose_vendor_address()
			{
				global $woocommerce, $post;
				$hit_multi_vendor = get_option('hit_multi_vendor');
				$hit_multi_vendor = empty($hit_multi_vendor) ? array() : $hit_multi_vendor;
				if ($this->hpos_enabled) {
					$hpos_prod_data = wc_get_product($post->ID);
					$selected_addr = $hpos_prod_data->get_meta("pn_address");
				} else {
					$selected_addr = get_post_meta($post->ID, 'pn_address', true);
				}

				$main_settings = get_option('hitshipo_pn_main_settings');
				$main_settings = empty($main_settings) ? array() : $main_settings;
				if (!isset($main_settings['hitshipo_pn_v_roles']) || empty($main_settings['hitshipo_pn_v_roles'])) {
					return;
				}
				$v_users = get_users(['role__in' => $main_settings['hitshipo_pn_v_roles']]);

			?>
				<div class="options_group">
					<p class="form-field pn_shipment">
						<label for="pn_shipment"><?php _e('PostNord Account', 'woocommerce'); ?></label>
						<select id="pn_shipment" style="width:240px;" name="pn_shipment" class="wc-enhanced-select" data-placeholder="<?php _e('Search for a product&hellip;', 'woocommerce'); ?>">
							<option value="default">Default Account</option>
							<?php
							if ($v_users) {
								foreach ($v_users as $value) {
									echo '<option value="' .  $value->data->ID  . '" ' . ($selected_addr == $value->data->ID ? 'selected="true"' : '') . '>' . $value->data->display_name . '</option>';
								}
							}
							?>
						</select>
					</p>
				</div>
<?php
			}

			public function hitshipo_pn_init()
			{
				include_once("controllors/hitshipo_pn_init.php");
			}
			public function hit_order_status_update()
			{
				global $woocommerce;
				if (isset($_GET['shipi_key'])) {
					$hitshipo_key = sanitize_text_field($_GET['shipi_key']);
					if ($hitshipo_key == 'fetch') {
						echo json_encode(array(get_transient('hitshipo_pn_nonce_temp')));
						die();
					}
				}

				if (isset($_GET['hitshipo_integration_key']) && isset($_GET['hitshipo_action'])) {
					$integration_key = sanitize_text_field($_GET['hitshipo_integration_key']);
					$hitshipo_action = sanitize_text_field($_GET['hitshipo_action']);
					$general_settings = get_option('hitshipo_pn_main_settings');
					$general_settings = empty($general_settings) ? array() : $general_settings;
					if (isset($general_settings['hitshipo_pn_integration_key']) && $integration_key == $general_settings['hitshipo_pn_integration_key']) {
						if ($hitshipo_action == 'stop_working') {
							update_option('hitshipo_pn_working_status', 'stop_working');
						} else if ($hitshipo_action = 'start_working') {
							update_option('hitshipo_pn_working_status', 'start_working');
						}
					}
				}

				if (isset($_GET['h1t_updat3_0rd3r']) && isset($_GET['key']) && isset($_GET['action'])) {
					$order_id = sanitize_text_field($_GET['h1t_updat3_0rd3r']);
					$key = sanitize_text_field($_GET['key']);
					$action = sanitize_text_field($_GET['action']);
					$order_ids = explode(",", $order_id);
					$general_settings = get_option('hitshipo_pn_main_settings', array());

					if (isset($general_settings['hitshipo_pn_integration_key']) && $general_settings['hitshipo_pn_integration_key'] == $key) {
						if ($action == 'processing') {
							foreach ($order_ids as $order_id) {
								$order = wc_get_order($order_id);
								$order->update_status('processing');
							}
						} else if ($action == 'completed') {
							foreach ($order_ids as $order_id) {
								$order = wc_get_order($order_id);
								$order->update_status('completed');
							}
						}
					}
					die();
				}

				if (isset($_GET['h1t_updat3_sh1pp1ng']) && isset($_GET['key']) && isset($_GET['user_id']) && isset($_GET['carrier']) && isset($_GET['track']) && $_GET['carrier'] == "pn") {
					$order_id = sanitize_text_field($_GET['h1t_updat3_sh1pp1ng']);
					$key = sanitize_text_field($_GET['key']);
					$general_settings = get_option('hitshipo_pn_main_settings', array());
					$user_id = sanitize_text_field($_GET['user_id']);
					$carrier = sanitize_text_field($_GET['carrier']);
					$track = sanitize_text_field($_GET['track']);
					$output['status'] = 'success';
					$output['tracking_num'] = $track;
					$output['label'] = "https://app.myshipi.com/api/shipping_labels/" . $user_id . "/" . $carrier . "/order_" . $order_id . "_track_" . $track . "_label.pdf";
					$output['invoice'] = "";
					$result_arr = array();
					if (isset($general_settings['hitshipo_pn_integration_key']) && $general_settings['hitshipo_pn_integration_key'] == $key) {

						if (isset($_GET['label'])) {
							$output['user_id'] = sanitize_text_field($_GET['label']);
							if (isset($general_settings['hitshipo_pn_v_enable']) && $general_settings['hitshipo_pn_v_enable'] == 'yes') {
								$result_arr = get_option('hit_pn_values_' . $order_id, array());
								if (!empty($result_arr)) {
									$result_arr = json_decode($result_arr, true);
								}
							}

							$result_arr[] = $output;
						} else {
							$result_arr[] = $output;
						}

						update_option('hit_pn_values_' . $order_id, json_encode($result_arr));
					}

					die();
				}
			}
			public function hitshipo_pn_method($methods)
			{
				if (is_admin() && !is_ajax() || apply_filters('hitshipo_shipping_method_enabled', true)) {
					$methods['hitshipo_pn'] = 'hitshipo_pn';
				}

				return $methods;
			}

			public function hitshipo_pn_plugin_action_links($links)
			{
				$setting_value = version_compare(WC()->version, '2.1', '>=') ? "wc-settings" : "woocommerce_settings";
				$plugin_links = array(
					'<a href="' . admin_url('admin.php?page=' . $setting_value  . '&tab=shipping&section=hitshipo_pn') . '" style="color:green;">' . __('Configure', 'hitshipo_pn') . '</a>',
					'<a href="https://app.myshipi.com/support" target="_blank" >' . __('Support', 'hitshipo_pn') . '</a>'
				);
				return array_merge($plugin_links, $links);
			}
			public function create_pn_shipping_meta_box()
			{
				$meta_scrn = $this->hpos_enabled ? wc_get_page_screen_id( 'shop-order' ) : 'shop_order';
				add_meta_box('hit_create_pn_shipping', __('PostNord Shipping Label', 'hitshipo_pn'), array($this, 'create_pn_shipping_label_genetation'), $meta_scrn, 'side', 'core');
			}
			public function create_pn_shipping_label_genetation($post)
			{
				// print_r('');
				// die();		    	
				if(!$this->hpos_enabled && $post->post_type !='shop_order' ){
		    		return;
		    	}
				$order = (!$this->hpos_enabled) ? wc_get_order( $post->ID ) : $post;
				$order_id = $order->get_id();
				$_pn_carriers = array(
					//"Public carrier name" => "technical name",
					"7" => "DPD",
					"11" => "Posten Varubrev Ekonomi",
					"17" => "MyPack Home",
					"18" => "Parcel",
					"19" => "MyPack Collect",
					"20" => "Return Pickup",
					"21" => "Företagspaket Ekonomi Förbet",
					"22" => "PostNord Return Pickup",
					"24" => "Return Drop Off",
					"25" => "Postpaket",
					"27" => "Postpaket Kontant",
					"28" => "SverigePaket",
					"30" => "MyPack Home Small (Parcel Letter)",
					"32" => "MyPack Home",
					"34" => "International tracked letter",
					"35" => "Företagspaket 09.00, (Förbet.)",
					"37" => "Tompallsdistribution",
					"38" => "Kartong med porto",
					"42" => "Express Next Day",
					"45" => "Brevpostförskott Inrikes",
					"47" => "EMS International Express",
					"48" => "InNight",
					"49" => "InNight Reverse",
					"51" => "Företagspaket Comeback",
					"52" => "Pallet",
					"53" => "PALL.ETT Special",
					"54" => "PALL.ETT+",
					"58" => "InNight Forwarding",
					"59" => "Retail Delivery",
					"69" => "InNight Systemtransporter",
					"75" => "Posten Varubrev 1:a klass",
					"78" => "Posten Varubrev Klimatek",
					"79" => "Posten Varubrev Ekonomi",
					"80" => "DPD MAX",
					"81" => "Lokal Åkeritjänst - Pall",
					"82" => "Lokal Åkeritjänst - Paket",
					"83" => "Groupage",
					"84" => "Road Freight Europe",
					"85" => "Part- /Full Loads",
					"86" => "Posten Varubrev 1:a klass",
					"87" => "Posten Varubrev Retur",
					"88" => "DPD",
					"91" => "International Parcel",
					"92" => "Import-Ekonomipaket",
					"93" => "eCIP Collect",
					"94" => "eCIP Home",
					"95" => "Postpaket Utrikes",
					"97" => "Parcel Post Collect",
					"98" => "Import-EPG",
					"AF" => "Brevpostförskott",
					"RR" => "Rek",
					"VV" => "Värde",
					"RP" => "Rek",
					"RL" => "RL",
					"RK" => "RK",
					"AJ" => "Skicka Hem",
					"LX" => "Expressbrev",
					"EE" => "EMS",
					"AP" => "Skicka Lätt",
					"ED" => "EMS",
					"EM" => "EMS",
					"VS" => "Värde skjutvapen RPS",
					"LY" => "Spårbart brev",
					"UX" => "Export Letter Sweden",
					"AK" => "Postal Distributed Newspaper",
				);

				$general_settings = get_option('hitshipo_pn_main_settings', array());

				$items = $order->get_items();

				$custom_settings = array();
				$custom_settings['default'] =  array();
				$vendor_settings = array();

				$pack_products = array();

				foreach ($items as $item) {
					$product_data = $item->get_data();
					$product = array();
					$product['product_name'] = $product_data['name'];
					$product['product_quantity'] = $product_data['quantity'];
					$product['product_id'] = $product_data['product_id'];

					$pack_products[] = $product;
				}

				if (isset($general_settings['hitshipo_pn_v_enable']) && $general_settings['hitshipo_pn_v_enable'] == 'yes' && isset($general_settings['hitshipo_pn_v_labels']) && $general_settings['hitshipo_pn_v_labels'] == 'yes') {
					// Multi Vendor Enabled
					foreach ($pack_products as $key => $value) {

						$product_id = $value['product_id'];
						if ($this->hpos_enabled) {
							$hpos_prod_data = wc_get_product($product_id);
							$pn_account = $hpos_prod_data->get_meta("pn_address");
						} else {
							$pn_account = get_post_meta($product_id, 'pn_address', true);
						}
						if (empty($pn_account) || $pn_account == 'default') {
							$pn_account = 'default';
							if (!isset($vendor_settings[$pn_account])) {
								$vendor_settings[$pn_account] = $custom_settings['default'];
							}

							$vendor_settings[$pn_account]['products'][] = $value;
						}

						if ($pn_account != 'default') {
							$user_account = get_post_meta($pn_account, 'hitshipo_pn_vendor_settings', true);
							$user_account = empty($user_account) ? array() : $user_account;
							if (!empty($user_account)) {
								if (!isset($vendor_settings[$pn_account])) {

									$vendor_settings[$pn_account] = $custom_settings['default'];
									unset($value['product_id']);
									$vendor_settings[$pn_account]['products'][] = $value;
								}
							} else {
								$pn_account = 'default';
								$vendor_settings[$pn_account] = $custom_settings['default'];
								$vendor_settings[$pn_account]['products'][] = $value;
							}
						}
					}
				}

				if (empty($vendor_settings)) {
					$custom_settings['default']['products'] = $pack_products;
				} else {
					$custom_settings = $vendor_settings;
				}

				$json_data = get_option('hit_pn_values_' . $order_id);

				$notice = get_option('hit_pn_status_' . $order_id, null);
				// echo '<pre>';print_r($notice);die();
				if ($notice && $notice != 'success') {
					echo "<p style='color:red'>" . $notice . "</p>";
					delete_option('hit_pn_status_' . $order_id);
				}
				if (!empty($json_data)) {
					$array_data = json_decode($json_data, true);
					// echo '<pre>';print_r($array_data);die();
					if (isset($array_data[0])) {
						foreach ($array_data as $key => $value) {
							if (isset($value['user_id'])) {
								unset($custom_settings[$value['user_id']]);
							}
							if (isset($value['user_id']) && $value['user_id'] == 'default') {
								echo '<br/><b>Default Account</b><br/>';
							} else {
								$user = get_user_by('id', $value['user_id']);
								echo '<br/><b>Account:</b> <small>' . $user->display_name . '</small><br/>';
							}
							echo '<a href="' . $value['label'] . '" target="_blank" style="background:#00a0d6; color: #fff;border-color: #00a0d6;box-shadow: 0px 1px 0px #00a0d6;text-shadow: 0px 1px 0px #fff;margin-top:3px;" class="button button-primary"> Shipping Label</a> ';
						}
					} else {
						$custom_settings = array();
						echo '<a href="' . $array_data['label'] . '" target="_blank" style="background:#00a0d6; color: #fff;border-color: #00a0d6;box-shadow: 0px 1px 0px #00a0d6;text-shadow: 0px 1px 0px #fff;" class="button button-primary"> Shipping Label</a> ';
					}
				}
				foreach ($custom_settings as $ukey => $value) {
					if ($ukey == 'default') {
						echo '<br/><b>Default Account</b>';
						echo '<br/><select name="hit_pn_service_code_default">';
						if (!empty($general_settings['hitshipo_pn_carrier'])) {
							foreach ($general_settings['hitshipo_pn_carrier'] as $key => $value) {
								echo "<option value='" . $key . "'>" . $key . ' - ' . $_pn_carriers[$key] . "</option>";
							}
						}
						echo '</select>';
						echo '<br/><b>Shipment Content</b>';

						echo '<br/><input type="text" style="width:250px;margin-bottom:10px;"  name="hit_pn_shipment_content_default" placeholder="Shipment content" value="' . (($general_settings['hitshipo_pn_ship_content']) ? $general_settings['hitshipo_pn_ship_content'] : "") . '" >';

						echo '<button name="hit_pn_create_label" value="default" style="background:#00a0d6; color: #fff;border-color: #00a0d6;box-shadow: 0px 1px 0px #00a0d6;text-shadow: 0px 1px 0px #fff;" class="button button-primary">Create Shipment</button>';
					} else {

						$user = get_user_by('id', $ukey);
						echo '<br/><b>Account:</b> <small>' . $user->display_name . '</small>';
						echo '<br/><select name="hit_pn_service_code_' . $ukey . '">';
						if (!empty($general_settings['hitshipo_pn_carrier'])) {
							foreach ($general_settings['hitshipo_pn_carrier'] as $key => $value) {
								echo "<option value='" . $key . "'>" . $key . ' - ' . $_pn_carriers[$key] . "</option>";
							}
						}
						echo '</select>';
						echo '<br/><b>Shipment Content</b>';

						echo '<br/><input type="text" style="width:250px;margin-bottom:10px;"  name="hit_pn_shipment_content_' . $ukey . '" placeholder="Shipment content" value="' . (($general_settings['hitshipo_pn_ship_content']) ? $general_settings['hitshipo_pn_ship_content'] : "") . '" >';

						echo '<button name="hit_pn_create_label" value="' . $ukey . '" style="background:#00a0d6; color: #fff;border-color: #00a0d6;box-shadow: 0px 1px 0px #00a0d6;text-shadow: 0px 1px 0px #fff;" class="button button-primary">Create Shipment</button><br/>';
					}
				}

				if (!empty($json_data)) {

					echo '<br/><button name="hit_pn_reset" class="button button-secondary" style="margin-top:3px;"> Reset Shipments</button>';
				}
			}

			public function hit_wc_checkout_order_processed($order_id)
			{
				if ($this->hpos_enabled) {
	 		        if ('shop_order' !== Automattic\WooCommerce\Utilities\OrderUtil::get_order_type($order_id)) {
	 		            return;
	 		        }
	 		    } else {
					$post = get_post($order_id);
					
			    	if($post->post_type !='shop_order' ){
			    		return;
			    	}
			    }
				$ship_content = !empty($_POST['hit_pn_shipment_content']) ? sanitize_text_field($_POST['hit_pn_shipment_content']) : 'Shipment Content';
				$order = wc_get_order($order_id);

				$service_code = $multi_ven = '';
				foreach ($order->get_shipping_methods() as $item_id => $item) {
					$service_code = $item->get_meta('hitshipo_pn_service');
					$multi_ven = $item->get_meta('hitshipo_multi_ven');
				}
				$order_data = $order->get_data();
				$items = $order->get_items();
				$general_settings = get_option('hitshipo_pn_main_settings', array());
				
				$order_shipping_country = isset($order_data['shipping']['country']) ? ($order_data['shipping']['country']) : '';
				
				$service_code = apply_filters('hitstacks_pn_auto_service', '18', $order_shipping_country, $general_settings['hitshipo_pn_country']);
				if (empty($service_code)) {
					if($order_shipping_country == $general_settings['hitshipo_pn_country']){
						$service_code = '30';
					}else{
						$service_code = 'UX';
					}
				}
				if (!isset($general_settings['hitshipo_pn_label_automation']) || $general_settings['hitshipo_pn_label_automation'] != 'yes') {
					return;
				}
				$custom_settings = array();
				$custom_settings['default'] = array(
					'hitshipo_pn_site_id' => $general_settings['hitshipo_pn_site_id'],
					'hitshipo_pn_site_pwd' => $general_settings['hitshipo_pn_site_pwd'],
					'hitshipo_pn_part_type' => $general_settings['hitshipo_pn_part_type'],
					'hitshipo_pn_issue_c' => $general_settings['hitshipo_pn_issue_c'],
					'hitshipo_pn_api_key' => $general_settings['hitshipo_pn_api_key'],
					'hitshipo_pn_shipper_name' => $general_settings['hitshipo_pn_shipper_name'],
					'hitshipo_pn_company' => $general_settings['hitshipo_pn_company'],
					'hitshipo_pn_mob_num' => $general_settings['hitshipo_pn_mob_num'],
					'hitshipo_pn_email' => $general_settings['hitshipo_pn_email'],
					'hitshipo_pn_address1' => $general_settings['hitshipo_pn_address1'],
					'hitshipo_pn_address2' => $general_settings['hitshipo_pn_address2'],
					'hitshipo_pn_city' => $general_settings['hitshipo_pn_city'],
					'hitshipo_pn_state' => $general_settings['hitshipo_pn_state'],
					'hitshipo_pn_zip' => $general_settings['hitshipo_pn_zip'],
					'hitshipo_pn_country' => $general_settings['hitshipo_pn_country'],
					'hitshipo_pn_gstin' => $general_settings['hitshipo_pn_gstin'],
					'hitshipo_pn_con_rate' => $general_settings['hitshipo_pn_con_rate'],
					'service_code' => $service_code,
					'hitshipo_pn_label_email' => $general_settings['hitshipo_pn_label_email'],
				);
				$vendor_settings = array();

				$pn_mod_weight_unit = $pn_mod_dim_unit = '';

				if (!empty($general_settings['hitshipo_pn_weight_unit']) && $general_settings['hitshipo_pn_weight_unit'] == 'KG_CM') {
					$pn_mod_weight_unit = 'kg';
					$pn_mod_dim_unit = 'cm';
				} elseif (!empty($general_settings['hitshipo_pn_weight_unit']) && $general_settings['hitshipo_pn_weight_unit'] == 'LB_IN') {
					$pn_mod_weight_unit = 'lbs';
					$pn_mod_dim_unit = 'in';
				} elseif (!empty($general_settings['hitshipo_pn_weight_unit']) && $general_settings['hitshipo_pn_weight_unit'] == 'G_CM') {
					$pn_mod_weight_unit = 'g';
					$pn_mod_dim_unit = 'cm';
				} else {
					$pn_mod_weight_unit = 'kg';
					$pn_mod_dim_unit = 'cm';
				}


				$pack_products = array();

				foreach ($items as $item) {
					$product_data = $item->get_data();

					$product = array();
					$product['product_name'] = str_replace('"', '', $product_data['name']);
					$product['product_quantity'] = $product_data['quantity'];
					$product['product_id'] = $product_data['product_id'];

					if ($this->hpos_enabled) {
						$hpos_prod_data = wc_get_product($product_data['product_id']);
						$saved_cc = $hpos_prod_data->get_meta("hits_pn_cc");
					} else {
						$saved_cc = get_post_meta($product_data['product_id'], 'hits_pn_cc', true);
					}
					if (!empty($saved_cc)) {
						$product['commodity_code'] = $saved_cc;
					}

					$product_variation_id = $item->get_variation_id();
					if (empty($product_variation_id) || $product_variation_id == 0) {
						$getproduct = wc_get_product($product_data['product_id']);
					} else {
						$getproduct = wc_get_product($product_variation_id);
					}
					$woo_weight_unit = get_option('woocommerce_weight_unit');
					$woo_dimension_unit = get_option('woocommerce_dimension_unit');

					$product['price'] = $getproduct->get_price();

					if (!$product['price']) {
						$product['price'] = (isset($product_data['total']) && isset($product_data['quantity'])) ? number_format(($product_data['total'] / $product_data['quantity']), 2) : 0;
					}

					if ($woo_dimension_unit != $pn_mod_dim_unit) {
						$prod_width = $getproduct->get_width();
						$prod_height = $getproduct->get_height();
						$prod_depth = $getproduct->get_length();

						//wc_get_dimension( $dimension, $to_unit, $from_unit );
						$product['width'] = (!empty($prod_width) && $prod_width > 0) ? round(wc_get_dimension($prod_width, $pn_mod_dim_unit, $woo_dimension_unit), 3) : 0.1 ;
						$product['height'] = (!empty($prod_height) && $prod_height > 0) ? round(wc_get_dimension($prod_height, $pn_mod_dim_unit, $woo_dimension_unit), 3) : 0.1 ;
						$product['depth'] = (!empty($prod_depth) && $prod_depth > 0) ? round(wc_get_dimension($prod_depth, $pn_mod_dim_unit, $woo_dimension_unit), 3) : 0.1 ;
					} else {
						$product['width'] = !empty($getproduct->get_width()) ? round($getproduct->get_width(), 3) : 0.1;
						$product['height'] = !empty($getproduct->get_height()) ? round($getproduct->get_height(), 3) : 0.1;
						$product['depth'] = !empty($getproduct->get_length()) ? round($getproduct->get_length(), 3) : 0.1;
					}

					if ($woo_weight_unit != $pn_mod_weight_unit) {
						$prod_weight = $getproduct->get_weight();
						$product['weight'] = (!empty($prod_weight) && $prod_weight > 0) ? round(wc_get_weight($prod_weight, $pn_mod_weight_unit, $woo_weight_unit), 3) : 0.1 ;
					} else {
						$product['weight'] = !empty($getproduct->get_weight()) ? round($getproduct->get_weight(), 3) : 0.1;
					}
					$pack_products[] = $product;
				}

				if (isset($general_settings['hitshipo_pn_v_enable']) && $general_settings['hitshipo_pn_v_enable'] == 'yes' && isset($general_settings['hitshipo_pn_v_labels']) && $general_settings['hitshipo_pn_v_labels'] == 'yes') {
					// Multi Vendor Enabled
					foreach ($pack_products as $key => $value) {

						$product_id = $value['product_id'];
						if ($this->hpos_enabled) {
							$hpos_prod_data = wc_get_product($product_id);
							$pn_account = $hpos_prod_data->get_meta("pn_address");
						} else {
							$pn_account = get_post_meta($product_id, 'pn_address', true);
						}
						if (empty($pn_account) || $pn_account == 'default') {
							$pn_account = 'default';
							if (!isset($vendor_settings[$pn_account])) {
								$vendor_settings[$pn_account] = $custom_settings['default'];
							}

							$vendor_settings[$pn_account]['products'][] = $value;
						}

						if ($pn_account != 'default') {
							$user_account = get_post_meta($pn_account, 'hitshipo_pn_vendor_settings', true);
							$user_account = empty($user_account) ? array() : $user_account;
							if (!empty($user_account)) {
								if (!isset($vendor_settings[$pn_account])) {

									$vendor_settings[$pn_account] = $custom_settings['default'];

									if ($user_account['hitshipo_pn_site_id'] != '' && $user_account['hitshipo_pn_site_pwd'] != '') {

										$vendor_settings[$pn_account]['hitshipo_pn_site_id'] = $user_account['hitshipo_pn_site_id'];

										if ($user_account['hitshipo_pn_site_pwd'] != '') {
											$vendor_settings[$pn_account]['hitshipo_pn_site_pwd'] = $user_account['hitshipo_pn_site_pwd'];
										}

										if ($user_account['hitshipo_pn_part_type'] != '') {
											$vendor_settings[$pn_account]['hitshipo_pn_part_type'] = $user_account['hitshipo_pn_part_type'];
										}

										if ($user_account['hitshipo_pn_issue_c'] != '') {
											$vendor_settings[$pn_account]['hitshipo_pn_issue_c'] = $user_account['hitshipo_pn_issue_c'];
										}

										if ($user_account['hitshipo_pn_api_key'] != '') {
											$vendor_settings[$pn_account]['hitshipo_pn_api_key'] = $user_account['hitshipo_pn_api_key'];
										}
									}

									if ($user_account['hitshipo_pn_address1'] != '' && $user_account['hitshipo_pn_city'] != '' && $user_account['hitshipo_pn_state'] != '' && $user_account['hitshipo_pn_zip'] != '' && $user_account['hitshipo_pn_country'] != '' && $user_account['hitshipo_pn_shipper_name'] != '') {

										if ($user_account['hitshipo_pn_shipper_name'] != '') {
											$vendor_settings[$pn_account]['hitshipo_pn_shipper_name'] = $user_account['hitshipo_pn_shipper_name'];
										}

										if ($user_account['hitshipo_pn_company'] != '') {
											$vendor_settings[$pn_account]['hitshipo_pn_company'] = $user_account['hitshipo_pn_company'];
										}

										if ($user_account['hitshipo_pn_mob_num'] != '') {
											$vendor_settings[$pn_account]['hitshipo_pn_mob_num'] = $user_account['hitshipo_pn_mob_num'];
										}

										if ($user_account['hitshipo_pn_email'] != '') {
											$vendor_settings[$pn_account]['hitshipo_pn_email'] = $user_account['hitshipo_pn_email'];
										}

										if ($user_account['hitshipo_pn_address1'] != '') {
											$vendor_settings[$pn_account]['hitshipo_pn_address1'] = $user_account['hitshipo_pn_address1'];
										}

										$vendor_settings[$pn_account]['hitshipo_pn_address2'] = $user_account['hitshipo_pn_address2'];

										if ($user_account['hitshipo_pn_city'] != '') {
											$vendor_settings[$pn_account]['hitshipo_pn_city'] = $user_account['hitshipo_pn_city'];
										}

										if ($user_account['hitshipo_pn_state'] != '') {
											$vendor_settings[$pn_account]['hitshipo_pn_state'] = $user_account['hitshipo_pn_state'];
										}

										if ($user_account['hitshipo_pn_zip'] != '') {
											$vendor_settings[$pn_account]['hitshipo_pn_zip'] = $user_account['hitshipo_pn_zip'];
										}

										if ($user_account['hitshipo_pn_country'] != '') {
											$vendor_settings[$pn_account]['hitshipo_pn_country'] = $user_account['hitshipo_pn_country'];
										}

										$vendor_settings[$pn_account]['hitshipo_pn_gstin'] = $user_account['hitshipo_pn_gstin'];
										$vendor_settings[$pn_account]['hitshipo_pn_con_rate'] = $user_account['hitshipo_pn_con_rate'];
									}

									if (isset($general_settings['hitshipo_pn_v_email']) && $general_settings['hitshipo_pn_v_email'] == 'yes') {
										$user_dat = get_userdata($pn_account);
										$vendor_settings[$pn_account]['hitshipo_pn_label_email'] = $user_dat->data->user_email;
									}

									if ($multi_ven != '') {
										$array_ven = explode('|', $multi_ven);
										$scode = '';
										foreach ($array_ven as $key => $svalue) {
											$ex_service = explode("_", $svalue);
											if ($ex_service[0] == $pn_account) {
												$vendor_settings[$pn_account]['service_code'] = $ex_service[1];
											}
										}

										if ($scode == '') {
											if ($order_data['shipping']['country'] != $vendor_settings[$pn_account]['hitshipo_pn_country']) {
												$vendor_settings[$pn_account]['service_code'] = $user_account['hitshipo_pn_def_inter'];
											} else {
												$vendor_settings[$pn_account]['service_code'] = $user_account['hitshipo_pn_def_dom'];
											}
										}
									} else {
										if ($order_data['shipping']['country'] != $vendor_settings[$pn_account]['hitshipo_pn_country']) {
											$vendor_settings[$pn_account]['service_code'] = $user_account['hitshipo_pn_def_inter'];
										} else {
											$vendor_settings[$pn_account]['service_code'] = $user_account['hitshipo_pn_def_dom'];
										}
									}
								}
								$vendor_settings[$pn_account]['products'][] = $value;
							}
						}
					}
				}
				if (empty($vendor_settings)) {
					$custom_settings['default']['products'] = $pack_products;
				} else {
					$custom_settings = $vendor_settings;
				}

				$order_id = $order_data['id'];
				$order_currency = $order_data['currency'];

				// $order_shipping_first_name = $order_data['shipping']['first_name'];
				// $order_shipping_last_name = $order_data['shipping']['last_name'];
				// $order_shipping_company = empty($order_data['shipping']['company']) ? $order_data['shipping']['first_name'] :  $order_data['shipping']['company'];
				// $order_shipping_address_1 = $order_data['shipping']['address_1'];
				// $order_shipping_address_2 = $order_data['shipping']['address_2'];
				// $order_shipping_city = $order_data['shipping']['city'];
				// $order_shipping_state = $order_data['shipping']['state'];
				// $order_shipping_postcode = $order_data['shipping']['postcode'];
				// $order_shipping_country = $order_data['shipping']['country'];
				// $order_shipping_phone = $order_data['billing']['phone'];
				// $order_shipping_email = $order_data['billing']['email'];

				$shipping_arr = (isset($order_data['shipping']['first_name']) && $order_data['shipping']['first_name'] != "") ? $order_data['shipping'] : $order_data['billing'];
                $order_shipping_first_name = $shipping_arr['first_name'];
                $order_shipping_last_name = $shipping_arr['last_name'];
                $order_shipping_company = empty($shipping_arr['company']) ? $shipping_arr['first_name'] :  $shipping_arr['company'];
                $order_shipping_address_1 = $shipping_arr['address_1'];
                $order_shipping_address_2 = $shipping_arr['address_2'];
                $order_shipping_city = $shipping_arr['city'];
                $order_shipping_state = $shipping_arr['state'];
                $order_shipping_postcode = $shipping_arr['postcode'];
                $order_shipping_country = $shipping_arr['country'];
                $order_shipping_phone = $order_data['billing']['phone'];
                $order_shipping_email = $order_data['billing']['email'];
				if (!empty($general_settings) && isset($general_settings['hitshipo_pn_integration_key'])) {
					$mode = 'live';
					if (isset($general_settings['hitshipo_pn_test']) && $general_settings['hitshipo_pn_test'] == 'yes') {
						$mode = 'test';
					}
					$execution = 'manual';
					if (isset($general_settings['hitshipo_pn_label_automation']) && $general_settings['hitshipo_pn_label_automation'] == 'yes') {
						$execution = 'auto';
					}

					$boxes_to_shipo = array();
					if (isset($general_settings['hitshipo_pn_packing_type']) && $general_settings['hitshipo_pn_packing_type'] == "box") {
						if (isset($general_settings['hitshipo_pn_boxes']) && !empty($general_settings['hitshipo_pn_boxes'])) {
							foreach ($general_settings['hitshipo_pn_boxes'] as $box) {
								if ($box['enabled'] != 1) {
									continue;
								} else {
									$boxes_to_shipo[] = $box;
								}
							}
						}
					}


					foreach ($custom_settings as $key => $cvalue) {
						global $pn_core;
						$frm_curr = get_option('woocommerce_currency');
						$to_curr = isset($pn_core[$cvalue['hitshipo_pn_country']]) ? $pn_core[$cvalue['hitshipo_pn_country']]['currency'] : '';
						$curr_con_rate = (isset($cvalue['hitshipo_pn_con_rate']) && !empty($cvalue['hitshipo_pn_con_rate'])) ? $cvalue['hitshipo_pn_con_rate'] : 0;

						if (!empty($frm_curr) && !empty($to_curr) && ($frm_curr != $to_curr)) {
							if (isset($general_settings['hitshipo_pn_auto_con_rate']) && $general_settings['hitshipo_pn_auto_con_rate'] == "yes") {
								$current_date = date('m-d-Y', time());
								$ex_rate_data = get_option('hitshipo_pn_ex_rate' . $key);
								$ex_rate_data = !empty($ex_rate_data) ? $ex_rate_data : array();
								if (empty($ex_rate_data) || (isset($ex_rate_data['date']) && $ex_rate_data['date'] != $current_date)) {
									if (isset($cvalue['hitshipo_pn_country']) && !empty($cvalue['hitshipo_pn_country']) && isset($general_settings['hitshipo_pn_integration_key']) && !empty($general_settings['hitshipo_pn_integration_key'])) {

										$ex_rate_Request = json_encode(array(
											'integrated_key' => $general_settings['hitshipo_pn_integration_key'],
											'from_curr' => $frm_curr,
											'to_curr' => $to_curr
										));

										$ex_rate_url = "https://app.myshipi.com/get_exchange_rate.php";
										// $ex_rate_url = "http://localhost/hitshipo/get_exchange_rate.php";
										$ex_rate_response = wp_remote_post(
											$ex_rate_url,
											array(
												'method'      => 'POST',
												'timeout'     => 45,
												'redirection' => 5,
												'httpversion' => '1.0',
												'blocking'    => true,
												'headers'     => array('Content-Type' => 'application/json; charset=utf-8'),
												'body'        => $ex_rate_Request,
												'sslverify'   => FALSE
											)
										);

										$ex_rate_result = (is_array($ex_rate_response) && isset($ex_rate_response['body'])) ? json_decode($ex_rate_response['body'], true) : array();

										if (!empty($ex_rate_result) && isset($ex_rate_result['ex_rate']) && $ex_rate_result['ex_rate'] != "Not Found") {
											$ex_rate_result['date'] = $current_date;
											update_option('hitshipo_pn_ex_rate' . $key, $ex_rate_result);
										} else {
											if (!empty($ex_rate_data)) {
												$ex_rate_data['date'] = $current_date;
												update_option('hitshipo_pn_ex_rate' . $key, $ex_rate_data);
											}
										}
									}
								}
								$get_ex_rate = get_option('hitshipo_pn_ex_rate' . $key, '');
								$get_ex_rate = !empty($get_ex_rate) ? $get_ex_rate : array();
								$curr_con_rate = (!empty($get_ex_rate) && isset($get_ex_rate['ex_rate'])) ? $get_ex_rate['ex_rate'] : 0;
							}
						}

						foreach ($cvalue['products'] as $prod_to_shipo_key => $prod_to_shipo) {
							if (!empty($frm_curr) && !empty($to_curr) && ($frm_curr != $to_curr)) {
								if ($curr_con_rate > 0) {
									$cvalue['products'][$prod_to_shipo_key]['price'] = $prod_to_shipo['price'] * $curr_con_rate;
								}
							}
						}

						$pic_open = date("c");
						$pic_close = date("c");

						if (isset($general_settings['hitshipo_pn_pickup_open_date']) && isset($general_settings['hitshipo_pn_pickup_open_time'])) {
							if (!empty($general_settings['hitshipo_pn_pickup_open_date']) && !empty($general_settings['hitshipo_pn_pickup_open_time'])) {
								if ($general_settings['hitshipo_pn_pickup_open_date'] > 0) {
									$pic_open = date("Y-m-d", strtotime("+" . $general_settings['hitshipo_pn_pickup_open_date'] . " weekday")) . $general_settings['hitshipo_pn_pickup_open_time'];
								} else {
									$pic_open = date("Y-m-d") . $general_settings['hitshipo_pn_pickup_open_time'];
								}
							}
						}

						if (isset($general_settings['hitshipo_pn_pickup_close_date']) && isset($general_settings['hitshipo_pn_pickup_close_time'])) {
							if (!empty($general_settings['hitshipo_pn_pickup_close_date']) && !empty($general_settings['hitshipo_pn_pickup_close_time'])) {
								if ($general_settings['hitshipo_pn_pickup_close_date'] > 0) {
									$pic_close = date("Y-m-d", strtotime("+" . $general_settings['hitshipo_pn_pickup_close_date'] . " weekday")) . $general_settings['hitshipo_pn_pickup_close_time'];
								} else {
									$pic_close = date("Y-m-d") . $general_settings['hitshipo_pn_pickup_close_time'];
								}
							}
						}

						//For Automatic Label Generation						
					
						$data = array();
						$data['integrated_key'] = $general_settings['hitshipo_pn_integration_key'];
						$data['order_id'] = $order_id;
						$data['exec_type'] = $execution;
						$data['mode'] = $mode;
						$data['carrier_type'] = 'pn';
						$data['ship_price'] = $order_data['shipping_total'];
						$data['meta'] = array(
							"site_id" => $cvalue['hitshipo_pn_site_id'],
							"password"  => $cvalue['hitshipo_pn_site_pwd'],
							"party_type" => $cvalue['hitshipo_pn_part_type'],
							"issue_c" => $cvalue['hitshipo_pn_issue_c'],
							"api_key" => $cvalue['hitshipo_pn_api_key'],
							"t_company" => $order_shipping_company,
							"t_address1" => str_replace('"', '', $order_shipping_address_1),
							"t_address2" => str_replace('"', '', $order_shipping_address_2),
							"t_city" => $order_shipping_city,
							"t_state" => $order_shipping_state,
							"t_postal" => $order_shipping_postcode,
							"t_country" => $order_shipping_country,
							"t_name" => $order_shipping_first_name . ' ' . $order_shipping_last_name,
							"t_phone" => $order_shipping_phone,
							"t_email" => $order_shipping_email,
							"insurance" => $general_settings['hitshipo_pn_insure'],
							"pack_this" => "Y",
							"shipping_charge" => $order_data['shipping_total'],
							"products" => $cvalue['products'],
							"pack_algorithm" => $general_settings['hitshipo_pn_packing_type'],
							"boxes" => $boxes_to_shipo,
							"max_weight" => $general_settings['hitshipo_pn_max_weight'],
							"weight_dim_unit" => $general_settings['hitshipo_pn_weight_unit'],
							"cod" => ($general_settings['hitshipo_pn_cod'] == 'yes') ? "Y" : "N",
							"service_code" => $service_code,
							"shipment_content" => $ship_content,
							"email_alert" => (isset($general_settings['hitshipo_pn_email_alert']) && ($general_settings['hitshipo_pn_email_alert'] == 'yes')) ? "Y" : "N",
							"sms_alert" => (isset($general_settings['hitshipo_pn_sms_alert']) && ($general_settings['hitshipo_pn_sms_alert'] == 'yes')) ? "Y" : "N",
							"s_company" => $cvalue['hitshipo_pn_company'],
							"s_address1" => $cvalue['hitshipo_pn_address1'],
							"s_address2" => $cvalue['hitshipo_pn_address2'],
							"s_city" => $cvalue['hitshipo_pn_city'],
							"s_state" => $cvalue['hitshipo_pn_state'],
							"s_postal" => $cvalue['hitshipo_pn_zip'],
							"s_country" => $cvalue['hitshipo_pn_country'],
							"gstin" => $cvalue['hitshipo_pn_gstin'],
							"s_name" => $cvalue['hitshipo_pn_shipper_name'],
							"s_phone" => $cvalue['hitshipo_pn_mob_num'],
							"s_email" => $cvalue['hitshipo_pn_email'],
							"label_size" => $general_settings['hitshipo_pn_print_size'],
							"label_paper_size" => $general_settings['hitshipo_pn_paper_size'],
							"eori" => $general_settings['hitshipo_pn_eori'],
							"hsn" => $general_settings['hitshipo_pn_hsn'],
							"pac_type" => $general_settings['hitshipo_pn_pac_type'],
							"tos" => $general_settings['hitshipo_pn_tos'],
							"tod_cc" => $general_settings['hitshipo_pn_tod_cc'],
							"tod_ccl" => $general_settings['hitshipo_pn_tod_ccl'],
							"sent_email_to" => $cvalue['hitshipo_pn_label_email'],
							"pic_exec_type" => (isset($general_settings['hitshipo_pn_pickup_automation']) && $general_settings['hitshipo_pn_pickup_automation'] == 'yes') ? "auto" : "manual",
							"pic_open_time" => $pic_open,
							"pic_close_time" => $pic_close,
							"label" => $key,
							"translation" => ((isset($general_settings['hitshipo_pn_translation']) && $general_settings['hitshipo_pn_translation'] == "yes") ? 'Y' : 'N'),
							"translation_key" => (isset($general_settings['hitshipo_pn_translation_key']) ? $general_settings['hitshipo_pn_translation_key'] : ''),
						);
						//Auto Shipment
						$auto_ship_url = "https://app.myshipi.com/label_api/create_shipment.php";
						// $auto_ship_url = "http://localhost/hitshipo/label_api/create_shipment.php";
						wp_remote_post(
							$auto_ship_url,
							array(
								'method'      => 'POST',
								'timeout'     => 45,
								'redirection' => 5,
								'httpversion' => '1.0',
								'blocking'    => false,
								'headers'     => array('Content-Type' => 'application/json; charset=utf-8'),
								'body'        => json_encode($data),
								'sslverify'   => FALSE
							)
						);
					}
				}
			}

			// Save the data of the Meta field
			public function hit_create_pn_shipping($order_id)
			{
				if ($this->hpos_enabled) {
	 		        if ('shop_order' !== Automattic\WooCommerce\Utilities\OrderUtil::get_order_type($order_id)) {
	 		            return;
	 		        }
	 		    } else {
			    	$post = get_post($order_id);
			    	if($post->post_type != 'shop_order' ){
			    		return;
			    	}
			    }

				if (isset($_POST['hit_pn_reset'])) {
					delete_option('hit_pn_values_' . $order_id);
				}

				if (isset($_POST['hit_pn_create_label'])) {
					$create_shipment_for = sanitize_text_field($_POST['hit_pn_create_label']);
					$service_code = sanitize_text_field($_POST['hit_pn_service_code_' . $create_shipment_for]);
					$ship_content = !empty($_POST['hit_pn_shipment_content_' . $create_shipment_for]) ? sanitize_text_field($_POST['hit_pn_shipment_content_' . $create_shipment_for]) : 'Shipment Content';
					$pickup_mode = 'manual';
					$order = wc_get_order($order_id);
					if ($order) {
						$order_data = $order->get_data();
						$order_id = $order_data['id'];
						$order_currency = $order_data['currency'];

						// $order_shipping_first_name = $order_data['shipping']['first_name'];
						// $order_shipping_last_name = $order_data['shipping']['last_name'];
						// $order_shipping_company = empty($order_data['shipping']['company']) ? $order_data['shipping']['first_name'] :  $order_data['shipping']['company'];
						// $order_shipping_address_1 = $order_data['shipping']['address_1'];
						// $order_shipping_address_2 = $order_data['shipping']['address_2'];
						// $order_shipping_city = $order_data['shipping']['city'];
						// $order_shipping_state = $order_data['shipping']['state'];
						// $order_shipping_postcode = $order_data['shipping']['postcode'];
						// $order_shipping_country = $order_data['shipping']['country'];
						// $order_shipping_phone = $order_data['billing']['phone'];
						// $order_shipping_email = $order_data['billing']['email'];

						$shipping_arr = (isset($order_data['shipping']['first_name']) && $order_data['shipping']['first_name'] != "") ? $order_data['shipping'] : $order_data['billing'];
						$order_shipping_first_name = $shipping_arr['first_name'];
						$order_shipping_last_name = $shipping_arr['last_name'];
						$order_shipping_company = empty($shipping_arr['company']) ? $shipping_arr['first_name'] :  $shipping_arr['company'];
						$order_shipping_address_1 = $shipping_arr['address_1'];
						$order_shipping_address_2 = $shipping_arr['address_2'];
						$order_shipping_city = $shipping_arr['city'];
						$order_shipping_state = $shipping_arr['state'];
						$order_shipping_postcode = $shipping_arr['postcode'];
						$order_shipping_country = $shipping_arr['country'];
						$order_shipping_phone = $order_data['billing']['phone'];
						$order_shipping_email = $order_data['billing']['email'];
						$shipping_charge = $order_data['shipping_total'];

						$items = $order->get_items();
						$pack_products = array();
						$general_settings = get_option('hitshipo_pn_main_settings', array());

						foreach ($items as $item) {
							$product_data = $item->get_data();
							$product = array();
							$product['product_name'] = str_replace('"', '', $product_data['name']);
							$product['product_quantity'] = $product_data['quantity'];
							$product['product_id'] = $product_data['product_id'];

							if ($this->hpos_enabled) {
								$hpos_prod_data = wc_get_product($product_data['product_id']);
								$saved_cc = $hpos_prod_data->get_meta("hits_pn_cc");
							} else {
								$saved_cc = get_post_meta($product_data['product_id'], 'hits_pn_cc', true);
							}
							if (!empty($saved_cc)) {
								$product['commodity_code'] = $saved_cc;
							}

							$product_variation_id = $item->get_variation_id();
							if (empty($product_variation_id)) {
								$getproduct = wc_get_product($product_data['product_id']);
							} else {
								$getproduct = wc_get_product($product_variation_id);
							}

							$woo_weight_unit = get_option('woocommerce_weight_unit');
							$woo_dimension_unit = get_option('woocommerce_dimension_unit');

							$pn_mod_weight_unit = $pn_mod_dim_unit = '';

							if (!empty($general_settings['hitshipo_pn_weight_unit']) && $general_settings['hitshipo_pn_weight_unit'] == 'KG_CM') {
								$pn_mod_weight_unit = 'kg';
								$pn_mod_dim_unit = 'cm';
							} elseif (!empty($general_settings['hitshipo_pn_weight_unit']) && $general_settings['hitshipo_pn_weight_unit'] == 'LB_IN') {
								$pn_mod_weight_unit = 'lbs';
								$pn_mod_dim_unit = 'in';
							} elseif (!empty($general_settings['hitshipo_pn_weight_unit']) && $general_settings['hitshipo_pn_weight_unit'] == 'G_CM') {
								$pn_mod_weight_unit = 'g';
								$pn_mod_dim_unit = 'cm';
							} else {
								$pn_mod_weight_unit = 'kg';
								$pn_mod_dim_unit = 'cm';
							}

							$product['price'] = $getproduct->get_price();

							if (!$product['price']) {
								$product['price'] = (isset($product_data['total']) && isset($product_data['quantity'])) ? number_format(($product_data['total'] / $product_data['quantity']), 2) : 0;
							}

							if ($woo_dimension_unit != $pn_mod_dim_unit) {
								$prod_width = $getproduct->get_width();
								$prod_height = $getproduct->get_height();
								$prod_depth = $getproduct->get_length();

								//wc_get_dimension( $dimension, $to_unit, $from_unit );
								$product['width'] = (!empty($prod_width) && $prod_width > 0) ? round(wc_get_dimension($prod_width, $pn_mod_dim_unit, $woo_dimension_unit), 2) : 0.1 ;
								$product['height'] = (!empty($prod_height) && $prod_height > 0) ? round(wc_get_dimension($prod_height, $pn_mod_dim_unit, $woo_dimension_unit), 2) : 0.1 ;
								$product['depth'] = (!empty($prod_depth) && $prod_depth > 0) ? round(wc_get_dimension($prod_depth, $pn_mod_dim_unit, $woo_dimension_unit), 2) : 0.1 ;
							} else {
								$product['width'] = !empty($getproduct->get_width()) ? round($getproduct->get_width(), 3) : 0.1;
								$product['height'] = !empty($getproduct->get_height()) ? round($getproduct->get_height(), 3) : 0.1;
								$product['depth'] = !empty($getproduct->get_length()) ? round($getproduct->get_length(), 3) : 0.1;
							}

							if ($woo_weight_unit != $pn_mod_weight_unit) {
								$prod_weight = $getproduct->get_weight();
								$product['weight'] = (!empty($prod_weight) && $prod_weight > 0) ? round(wc_get_weight($prod_weight, $pn_mod_weight_unit, $woo_weight_unit), 2) : 0.1 ;
							} else {
								$product['weight'] = !empty($getproduct->get_weight()) ? round($getproduct->get_weight(), 3) : 0.1;
							}

							$pack_products[] = $product;
						}

						$custom_settings = array();
						$custom_settings['default'] = array(
							'hitshipo_pn_site_id' => $general_settings['hitshipo_pn_site_id'],
							'hitshipo_pn_site_pwd' => $general_settings['hitshipo_pn_site_pwd'],
							'hitshipo_pn_part_type' => $general_settings['hitshipo_pn_part_type'],
							'hitshipo_pn_issue_c' => $general_settings['hitshipo_pn_issue_c'],
							'hitshipo_pn_api_key' => $general_settings['hitshipo_pn_api_key'],
							'hitshipo_pn_shipper_name' => $general_settings['hitshipo_pn_shipper_name'],
							'hitshipo_pn_company' => $general_settings['hitshipo_pn_company'],
							'hitshipo_pn_mob_num' => $general_settings['hitshipo_pn_mob_num'],
							'hitshipo_pn_email' => $general_settings['hitshipo_pn_email'],
							'hitshipo_pn_address1' => $general_settings['hitshipo_pn_address1'],
							'hitshipo_pn_address2' => $general_settings['hitshipo_pn_address2'],
							'hitshipo_pn_city' => $general_settings['hitshipo_pn_city'],
							'hitshipo_pn_state' => $general_settings['hitshipo_pn_state'],
							'hitshipo_pn_zip' => $general_settings['hitshipo_pn_zip'],
							'hitshipo_pn_country' => $general_settings['hitshipo_pn_country'],
							'hitshipo_pn_gstin' => $general_settings['hitshipo_pn_gstin'],
							'hitshipo_pn_con_rate' => $general_settings['hitshipo_pn_con_rate'],
							'service_code' => $service_code,
							'hitshipo_pn_label_email' => $general_settings['hitshipo_pn_label_email'],
						);
						$vendor_settings = array();
						if (isset($general_settings['hitshipo_pn_v_enable']) && $general_settings['hitshipo_pn_v_enable'] == 'yes' && isset($general_settings['hitshipo_pn_v_labels']) && $general_settings['hitshipo_pn_v_labels'] == 'yes') {
							// Multi Vendor Enabled
							foreach ($pack_products as $key => $value) {
								$product_id = $value['product_id'];
								if ($this->hpos_enabled) {
									$hpos_prod_data = wc_get_product($product_id);
									$pn_account = $hpos_prod_data->get_meta("pn_address");
								} else {
									$pn_account = get_post_meta($product_id, 'pn_address', true);
								}
								if (empty($pn_account) || $pn_account == 'default') {
									$pn_account = 'default';
									if (!isset($vendor_settings[$pn_account])) {
										$vendor_settings[$pn_account] = $custom_settings['default'];
									}

									$vendor_settings[$pn_account]['products'][] = $value;
								}

								if ($pn_account != 'default') {
									$user_account = get_post_meta($pn_account, 'hitshipo_pn_vendor_settings', true);
									$user_account = empty($user_account) ? array() : $user_account;
									if (!empty($user_account)) {
										if (!isset($vendor_settings[$pn_account])) {

											$vendor_settings[$pn_account] = $custom_settings['default'];

											if ($user_account['hitshipo_pn_site_id'] != '' && $user_account['hitshipo_pn_site_pwd'] != '') {

												$vendor_settings[$pn_account]['hitshipo_pn_site_id'] = $user_account['hitshipo_pn_site_id'];

												if ($user_account['hitshipo_pn_site_pwd'] != '') {
													$vendor_settings[$pn_account]['hitshipo_pn_site_pwd'] = $user_account['hitshipo_pn_site_pwd'];
												}

												if ($user_account['hitshipo_pn_part_type'] != '') {
													$vendor_settings[$pn_account]['hitshipo_pn_part_type'] = $user_account['hitshipo_pn_part_type'];
												}

												if ($user_account['hitshipo_pn_issue_c'] != '') {
													$vendor_settings[$pn_account]['hitshipo_pn_issue_c'] = $user_account['hitshipo_pn_issue_c'];
												}

												if ($user_account['hitshipo_pn_api_key'] != '') {
													$vendor_settings[$pn_account]['hitshipo_pn_api_key'] = $user_account['hitshipo_pn_api_key'];
												}
											}

											if ($user_account['hitshipo_pn_address1'] != '' && $user_account['hitshipo_pn_city'] != '' && $user_account['hitshipo_pn_state'] != '' && $user_account['hitshipo_pn_zip'] != '' && $user_account['hitshipo_pn_country'] != '' && $user_account['hitshipo_pn_shipper_name'] != '') {

												if ($user_account['hitshipo_pn_shipper_name'] != '') {
													$vendor_settings[$pn_account]['hitshipo_pn_shipper_name'] = $user_account['hitshipo_pn_shipper_name'];
												}

												if ($user_account['hitshipo_pn_company'] != '') {
													$vendor_settings[$pn_account]['hitshipo_pn_company'] = $user_account['hitshipo_pn_company'];
												}

												if ($user_account['hitshipo_pn_mob_num'] != '') {
													$vendor_settings[$pn_account]['hitshipo_pn_mob_num'] = $user_account['hitshipo_pn_mob_num'];
												}

												if ($user_account['hitshipo_pn_email'] != '') {
													$vendor_settings[$pn_account]['hitshipo_pn_email'] = $user_account['hitshipo_pn_email'];
												}

												if ($user_account['hitshipo_pn_address1'] != '') {
													$vendor_settings[$pn_account]['hitshipo_pn_address1'] = $user_account['hitshipo_pn_address1'];
												}

												$vendor_settings[$pn_account]['hitshipo_pn_address2'] = $user_account['hitshipo_pn_address2'];

												if ($user_account['hitshipo_pn_city'] != '') {
													$vendor_settings[$pn_account]['hitshipo_pn_city'] = $user_account['hitshipo_pn_city'];
												}

												if ($user_account['hitshipo_pn_state'] != '') {
													$vendor_settings[$pn_account]['hitshipo_pn_state'] = $user_account['hitshipo_pn_state'];
												}

												if ($user_account['hitshipo_pn_zip'] != '') {
													$vendor_settings[$pn_account]['hitshipo_pn_zip'] = $user_account['hitshipo_pn_zip'];
												}

												if ($user_account['hitshipo_pn_country'] != '') {
													$vendor_settings[$pn_account]['hitshipo_pn_country'] = $user_account['hitshipo_pn_country'];
												}

												$vendor_settings[$pn_account]['hitshipo_pn_gstin'] = $user_account['hitshipo_pn_gstin'];
												$vendor_settings[$pn_account]['hitshipo_pn_con_rate'] = $user_account['hitshipo_pn_con_rate'];
											}

											if (isset($general_settings['hitshipo_pn_v_email']) && $general_settings['hitshipo_pn_v_email'] == 'yes') {
												$user_dat = get_userdata($pn_account);
												$vendor_settings[$pn_account]['hitshipo_pn_label_email'] = $user_dat->data->user_email;
											}


											if ($order_data['shipping']['country'] != $vendor_settings[$pn_account]['hitshipo_pn_country']) {
												$vendor_settings[$pn_account]['service_code'] = empty($service_code) ? $user_account['hitshipo_pn_def_inter'] : $service_code;
											} else {
												$vendor_settings[$pn_account]['service_code'] = empty($service_code) ? $user_account['hitshipo_pn_def_dom'] : $service_code;
											}
										}
										$vendor_settings[$pn_account]['products'][] = $value;
									}
								}
							}
						}

						if (empty($vendor_settings)) {
							$custom_settings['default']['products'] = $pack_products;
						} else {
							$custom_settings = $vendor_settings;
						}

						if (!empty($general_settings) && isset($general_settings['hitshipo_pn_integration_key']) && isset($custom_settings[$create_shipment_for])) {
							$mode = 'live';
							if (isset($general_settings['hitshipo_pn_test']) && $general_settings['hitshipo_pn_test'] == 'yes') {
								$mode = 'test';
							}

							$execution = 'manual';

							$boxes_to_shipo = array();
							if (isset($general_settings['hitshipo_pn_packing_type']) && $general_settings['hitshipo_pn_packing_type'] == "box") {
								if (isset($general_settings['hitshipo_pn_boxes']) && !empty($general_settings['hitshipo_pn_boxes'])) {
									foreach ($general_settings['hitshipo_pn_boxes'] as $box) {
										if ($box['enabled'] != 1) {
											continue;
										} else {
											$boxes_to_shipo[] = $box;
										}
									}
								}
							}

							global $pn_core;
							$frm_curr = get_option('woocommerce_currency');
							$to_curr = isset($pn_core[$custom_settings[$create_shipment_for]['hitshipo_pn_country']]) ? $pn_core[$custom_settings[$create_shipment_for]['hitshipo_pn_country']]['currency'] : '';
							$curr_con_rate = (isset($custom_settings[$create_shipment_for]['hitshipo_pn_con_rate']) && !empty($custom_settings[$create_shipment_for]['hitshipo_pn_con_rate'])) ? $custom_settings[$create_shipment_for]['hitshipo_pn_con_rate'] : 0;

							if (!empty($frm_curr) && !empty($to_curr) && ($frm_curr != $to_curr)) {
								if (isset($general_settings['hitshipo_pn_auto_con_rate']) && $general_settings['hitshipo_pn_auto_con_rate'] == "yes") {
									$current_date = date('m-d-Y', time());
									$ex_rate_data = get_option('hitshipo_pn_ex_rate' . $create_shipment_for);
									$ex_rate_data = !empty($ex_rate_data) ? $ex_rate_data : array();
									if (empty($ex_rate_data) || (isset($ex_rate_data['date']) && $ex_rate_data['date'] != $current_date)) {
										if (isset($custom_settings[$create_shipment_for]['hitshipo_pn_country']) && !empty($custom_settings[$create_shipment_for]['hitshipo_pn_country']) && isset($general_settings['hitshipo_pn_integration_key']) && !empty($general_settings['hitshipo_pn_integration_key'])) {

											$ex_rate_Request = json_encode(array(
												'integrated_key' => $general_settings['hitshipo_pn_integration_key'],
												'from_curr' => $frm_curr,
												'to_curr' => $to_curr
											));

											$ex_rate_url = "https://app.myshipi.com/get_exchange_rate.php";
											// $ex_rate_url = "http://localhost/hitshipo/get_exchange_rate.php";
											$ex_rate_response = wp_remote_post(
												$ex_rate_url,
												array(
													'method'      => 'POST',
													'timeout'     => 45,
													'redirection' => 5,
													'httpversion' => '1.0',
													'blocking'    => true,
													'headers'     => array('Content-Type' => 'application/json; charset=utf-8'),
													'body'        => $ex_rate_Request,
													'sslverify'   => FALSE
												)
											);

											$ex_rate_result = (is_array($ex_rate_response) && isset($ex_rate_response['body'])) ? json_decode($ex_rate_response['body'], true) : array();

											if (!empty($ex_rate_result) && isset($ex_rate_result['ex_rate']) && $ex_rate_result['ex_rate'] != "Not Found") {
												$ex_rate_result['date'] = $current_date;
												update_option('hitshipo_pn_ex_rate' . $create_shipment_for, $ex_rate_result);
											} else {
												if (!empty($ex_rate_data)) {
													$ex_rate_data['date'] = $current_date;
													update_option('hitshipo_pn_ex_rate' . $create_shipment_for, $ex_rate_data);
												}
											}
										}
									}
									$get_ex_rate = get_option('hitshipo_pn_ex_rate' . $create_shipment_for, '');
									$get_ex_rate = !empty($get_ex_rate) ? $get_ex_rate : array();
									$curr_con_rate = (!empty($get_ex_rate) && isset($get_ex_rate['ex_rate'])) ? $get_ex_rate['ex_rate'] : 0;
								}
							}

							foreach ($custom_settings[$create_shipment_for]['products'] as $prod_to_shipo_key => $prod_to_shipo) {
								if (!empty($frm_curr) && !empty($to_curr) && ($frm_curr != $to_curr)) {
									if ($curr_con_rate > 0) {
										$custom_settings[$create_shipment_for]['products'][$prod_to_shipo_key]['price'] = $prod_to_shipo['price'] * $curr_con_rate;
									}
								}
							}

							$data = array();
							$data['integrated_key'] = $general_settings['hitshipo_pn_integration_key'];
							$data['order_id'] = $order_id;
							$data['exec_type'] = $execution;
							$data['ship_price'] = $shipping_charge;
							$data['mode'] = $mode;
							$data['carrier_type'] = 'pn';
							$data['meta'] = array(
								"site_id" => $custom_settings[$create_shipment_for]['hitshipo_pn_site_id'],
								"password"  => $custom_settings[$create_shipment_for]['hitshipo_pn_site_pwd'],
								"party_type" => $custom_settings[$create_shipment_for]['hitshipo_pn_part_type'],
								"issue_c" => $custom_settings[$create_shipment_for]['hitshipo_pn_issue_c'],
								"api_key" => $custom_settings[$create_shipment_for]['hitshipo_pn_api_key'],
								"t_company" => $order_shipping_company,
								"t_address1" => str_replace('"', '', $order_shipping_address_1),
								"t_address2" => str_replace('"', '', $order_shipping_address_2),
								"t_city" => $order_shipping_city,
								"t_state" => $order_shipping_state,
								"t_postal" => $order_shipping_postcode,
								"t_country" => $order_shipping_country,
								"t_name" => $order_shipping_first_name . ' ' . $order_shipping_last_name,
								"t_phone" => $order_shipping_phone,
								"t_email" => $order_shipping_email,
								"insurance" => ($general_settings['hitshipo_pn_insure'] == "yes") ? "Y" : "N",
								"pack_this" => "Y",
								"shipping_charge" => $shipping_charge,
								"products" => $custom_settings[$create_shipment_for]['products'],
								"pack_algorithm" => $general_settings['hitshipo_pn_packing_type'],
								"boxes" => $boxes_to_shipo,
								"max_weight" => $general_settings['hitshipo_pn_max_weight'],
								"weight_dim_unit" => $general_settings['hitshipo_pn_weight_unit'],
								"cod" => ($general_settings['hitshipo_pn_cod'] == 'yes') ? "Y" : "N",
								"service_code" => $custom_settings[$create_shipment_for]['service_code'],
								"shipment_content" => $ship_content,
								"email_alert" => (isset($general_settings['hitshipo_pn_email_alert']) && ($general_settings['hitshipo_pn_email_alert'] == 'yes')) ? "Y" : "N",
								"sms_alert" => (isset($general_settings['hitshipo_pn_sms_alert']) && ($general_settings['hitshipo_pn_sms_alert'] == 'yes')) ? "Y" : "N",
								"s_company" => $custom_settings[$create_shipment_for]['hitshipo_pn_company'],
								"s_address1" => $custom_settings[$create_shipment_for]['hitshipo_pn_address1'],
								"s_address2" => $custom_settings[$create_shipment_for]['hitshipo_pn_address2'],
								"s_city" => $custom_settings[$create_shipment_for]['hitshipo_pn_city'],
								"s_state" => $custom_settings[$create_shipment_for]['hitshipo_pn_state'],
								"s_postal" => $custom_settings[$create_shipment_for]['hitshipo_pn_zip'],
								"s_country" => $custom_settings[$create_shipment_for]['hitshipo_pn_country'],
								"gstin" => $custom_settings[$create_shipment_for]['hitshipo_pn_gstin'],
								"s_name" => $custom_settings[$create_shipment_for]['hitshipo_pn_shipper_name'],
								"s_phone" => $custom_settings[$create_shipment_for]['hitshipo_pn_mob_num'],
								"s_email" => $custom_settings[$create_shipment_for]['hitshipo_pn_email'],
								"label_size" => $general_settings['hitshipo_pn_print_size'],
								"label_paper_size" => $general_settings['hitshipo_pn_paper_size'],
								"eori" => $general_settings['hitshipo_pn_eori'],
								"hsn" => $general_settings['hitshipo_pn_hsn'],
								"pac_type" => $general_settings['hitshipo_pn_pac_type'],
								"tos" => $general_settings['hitshipo_pn_tos'],
								"tod_cc" => $general_settings['hitshipo_pn_tod_cc'],
								"tod_ccl" => $general_settings['hitshipo_pn_tod_ccl'],
								"sent_email_to" => $custom_settings[$create_shipment_for]['hitshipo_pn_label_email'],
								"pic_exec_type" => $pickup_mode,
								"pic_open_time" => '',
								"pic_close_time" => '',
								"translation" => ((isset($general_settings['hitshipo_pn_translation']) && $general_settings['hitshipo_pn_translation'] == "yes") ? 'Y' : 'N'),
								"translation_key" => (isset($general_settings['hitshipo_pn_translation_key']) ? $general_settings['hitshipo_pn_translation_key'] : ''),
								"label" => $create_shipment_for
							);
							//Manual Shipment
							$manual_ship_url = "https://app.myshipi.com/label_api/create_shipment.php";
							// $manual_ship_url = "http://localhost/hitshipo/label_api/create_shipment.php";
							$response = wp_remote_post(
								$manual_ship_url,
								array(
									'method'      => 'POST',
									'timeout'     => 45,
									'redirection' => 5,
									'httpversion' => '1.0',
									'blocking'    => true,
									'headers'     => array('Content-Type' => 'application/json; charset=utf-8'),
									'body'        => json_encode($data),
									'sslverify'   => FALSE
								)
							);

							$output = (is_array($response) && isset($response['body'])) ? json_decode($response['body'],true) : [];

							if ($output) {
								if (isset($output['status']) || isset($output['pickup_status'])) {

									if (isset($output['status']) && $output['status'] != 'success') {
										update_option('hit_pn_status_' . $order_id, $output['status']);
									} else if (isset($output['status']) && $output['status'] == 'success') {
										$output['user_id'] = $create_shipment_for;
										$val = get_option('hit_pn_values_' . $order_id, []);
										$result_arr = array();
										if (!empty($val)) {
											$result_arr = json_decode($val, true);
										}
										$result_arr[] = $output;

										update_option('hit_pn_values_' . $order_id, json_encode($result_arr));
									}
								} else {
									update_option('hit_pn_status_' . $order_id, 'Site not Connected with Shipi. Contact Shipi Team.');
								}
							} else {
								update_option('hit_pn_status_' . $order_id, 'Site not Connected with Shipi. Contact Shipi Team.');
							}
						}
					}
				}
			}

			// Save the data of the Meta field

		}

		$pn_core = array();
		$pn_core['AD'] = array('region' => 'EU', 'currency' => 'EUR', 'weight' => 'KG_CM');
		$pn_core['AE'] = array('region' => 'AP', 'currency' => 'AED', 'weight' => 'KG_CM');
		$pn_core['AF'] = array('region' => 'AP', 'currency' => 'AFN', 'weight' => 'KG_CM');
		$pn_core['AG'] = array('region' => 'AM', 'currency' => 'XCD', 'weight' => 'LB_IN');
		$pn_core['AI'] = array('region' => 'AM', 'currency' => 'XCD', 'weight' => 'LB_IN');
		$pn_core['AL'] = array('region' => 'AP', 'currency' => 'EUR', 'weight' => 'KG_CM');
		$pn_core['AM'] = array('region' => 'AP', 'currency' => 'AMD', 'weight' => 'KG_CM');
		$pn_core['AN'] = array('region' => 'AM', 'currency' => 'ANG', 'weight' => 'KG_CM');
		$pn_core['AO'] = array('region' => 'AP', 'currency' => 'AOA', 'weight' => 'KG_CM');
		$pn_core['AR'] = array('region' => 'AM', 'currency' => 'ARS', 'weight' => 'KG_CM');
		$pn_core['AS'] = array('region' => 'AM', 'currency' => 'USD', 'weight' => 'LB_IN');
		$pn_core['AT'] = array('region' => 'EU', 'currency' => 'EUR', 'weight' => 'KG_CM');
		$pn_core['AU'] = array('region' => 'AP', 'currency' => 'AUD', 'weight' => 'KG_CM');
		$pn_core['AW'] = array('region' => 'AM', 'currency' => 'AWG', 'weight' => 'LB_IN');
		$pn_core['AZ'] = array('region' => 'AM', 'currency' => 'AZN', 'weight' => 'KG_CM');
		$pn_core['AZ'] = array('region' => 'AM', 'currency' => 'AZN', 'weight' => 'KG_CM');
		$pn_core['GB'] = array('region' => 'EU', 'currency' => 'GBP', 'weight' => 'KG_CM');
		$pn_core['BA'] = array('region' => 'AP', 'currency' => 'BAM', 'weight' => 'KG_CM');
		$pn_core['BB'] = array('region' => 'AM', 'currency' => 'BBD', 'weight' => 'LB_IN');
		$pn_core['BD'] = array('region' => 'AP', 'currency' => 'BDT', 'weight' => 'KG_CM');
		$pn_core['BE'] = array('region' => 'EU', 'currency' => 'EUR', 'weight' => 'KG_CM');
		$pn_core['BF'] = array('region' => 'AP', 'currency' => 'XOF', 'weight' => 'KG_CM');
		$pn_core['BG'] = array('region' => 'EU', 'currency' => 'BGN', 'weight' => 'KG_CM');
		$pn_core['BH'] = array('region' => 'AP', 'currency' => 'BHD', 'weight' => 'KG_CM');
		$pn_core['BI'] = array('region' => 'AP', 'currency' => 'BIF', 'weight' => 'KG_CM');
		$pn_core['BJ'] = array('region' => 'AP', 'currency' => 'XOF', 'weight' => 'KG_CM');
		$pn_core['BM'] = array('region' => 'AM', 'currency' => 'BMD', 'weight' => 'LB_IN');
		$pn_core['BN'] = array('region' => 'AP', 'currency' => 'BND', 'weight' => 'KG_CM');
		$pn_core['BO'] = array('region' => 'AM', 'currency' => 'BOB', 'weight' => 'KG_CM');
		$pn_core['BR'] = array('region' => 'AM', 'currency' => 'BRL', 'weight' => 'KG_CM');
		$pn_core['BS'] = array('region' => 'AM', 'currency' => 'BSD', 'weight' => 'LB_IN');
		$pn_core['BT'] = array('region' => 'AP', 'currency' => 'BTN', 'weight' => 'KG_CM');
		$pn_core['BW'] = array('region' => 'AP', 'currency' => 'BWP', 'weight' => 'KG_CM');
		$pn_core['BY'] = array('region' => 'AP', 'currency' => 'BYR', 'weight' => 'KG_CM');
		$pn_core['BZ'] = array('region' => 'AM', 'currency' => 'BZD', 'weight' => 'KG_CM');
		$pn_core['CA'] = array('region' => 'AM', 'currency' => 'CAD', 'weight' => 'LB_IN');
		$pn_core['CF'] = array('region' => 'AP', 'currency' => 'XAF', 'weight' => 'KG_CM');
		$pn_core['CG'] = array('region' => 'AP', 'currency' => 'XAF', 'weight' => 'KG_CM');
		$pn_core['CH'] = array('region' => 'EU', 'currency' => 'CHF', 'weight' => 'KG_CM');
		$pn_core['CI'] = array('region' => 'AP', 'currency' => 'XOF', 'weight' => 'KG_CM');
		$pn_core['CK'] = array('region' => 'AP', 'currency' => 'NZD', 'weight' => 'KG_CM');
		$pn_core['CL'] = array('region' => 'AM', 'currency' => 'CLP', 'weight' => 'KG_CM');
		$pn_core['CM'] = array('region' => 'AP', 'currency' => 'XAF', 'weight' => 'KG_CM');
		$pn_core['CN'] = array('region' => 'AP', 'currency' => 'CNY', 'weight' => 'KG_CM');
		$pn_core['CO'] = array('region' => 'AM', 'currency' => 'COP', 'weight' => 'KG_CM');
		$pn_core['CR'] = array('region' => 'AM', 'currency' => 'CRC', 'weight' => 'KG_CM');
		$pn_core['CU'] = array('region' => 'AM', 'currency' => 'CUC', 'weight' => 'KG_CM');
		$pn_core['CV'] = array('region' => 'AP', 'currency' => 'CVE', 'weight' => 'KG_CM');
		$pn_core['CY'] = array('region' => 'AP', 'currency' => 'EUR', 'weight' => 'KG_CM');
		$pn_core['CZ'] = array('region' => 'EU', 'currency' => 'CZK', 'weight' => 'KG_CM');
		$pn_core['DE'] = array('region' => 'AP', 'currency' => 'EUR', 'weight' => 'KG_CM');
		$pn_core['DJ'] = array('region' => 'EU', 'currency' => 'DJF', 'weight' => 'KG_CM');
		$pn_core['DK'] = array('region' => 'AM', 'currency' => 'DKK', 'weight' => 'KG_CM');
		$pn_core['DM'] = array('region' => 'AM', 'currency' => 'XCD', 'weight' => 'LB_IN');
		$pn_core['DO'] = array('region' => 'AP', 'currency' => 'DOP', 'weight' => 'LB_IN');
		$pn_core['DZ'] = array('region' => 'AM', 'currency' => 'DZD', 'weight' => 'KG_CM');
		$pn_core['EC'] = array('region' => 'EU', 'currency' => 'USD', 'weight' => 'KG_CM');
		$pn_core['EE'] = array('region' => 'AP', 'currency' => 'EUR', 'weight' => 'KG_CM');
		$pn_core['EG'] = array('region' => 'AP', 'currency' => 'EGP', 'weight' => 'KG_CM');
		$pn_core['ER'] = array('region' => 'EU', 'currency' => 'ERN', 'weight' => 'KG_CM');
		$pn_core['ES'] = array('region' => 'AP', 'currency' => 'EUR', 'weight' => 'KG_CM');
		$pn_core['ET'] = array('region' => 'AU', 'currency' => 'ETB', 'weight' => 'KG_CM');
		$pn_core['FI'] = array('region' => 'AP', 'currency' => 'EUR', 'weight' => 'KG_CM');
		$pn_core['FJ'] = array('region' => 'AP', 'currency' => 'FJD', 'weight' => 'KG_CM');
		$pn_core['FK'] = array('region' => 'AM', 'currency' => 'GBP', 'weight' => 'KG_CM');
		$pn_core['FM'] = array('region' => 'AM', 'currency' => 'USD', 'weight' => 'LB_IN');
		$pn_core['FO'] = array('region' => 'AM', 'currency' => 'DKK', 'weight' => 'KG_CM');
		$pn_core['FR'] = array('region' => 'EU', 'currency' => 'EUR', 'weight' => 'KG_CM');
		$pn_core['GA'] = array('region' => 'AP', 'currency' => 'XAF', 'weight' => 'KG_CM');
		$pn_core['GB'] = array('region' => 'EU', 'currency' => 'GBP', 'weight' => 'KG_CM');
		$pn_core['GD'] = array('region' => 'AM', 'currency' => 'XCD', 'weight' => 'LB_IN');
		$pn_core['GE'] = array('region' => 'AM', 'currency' => 'GEL', 'weight' => 'KG_CM');
		$pn_core['GF'] = array('region' => 'AM', 'currency' => 'EUR', 'weight' => 'KG_CM');
		$pn_core['GG'] = array('region' => 'AM', 'currency' => 'GBP', 'weight' => 'KG_CM');
		$pn_core['GH'] = array('region' => 'AP', 'currency' => 'GHS', 'weight' => 'KG_CM');
		$pn_core['GI'] = array('region' => 'AM', 'currency' => 'GBP', 'weight' => 'KG_CM');
		$pn_core['GL'] = array('region' => 'AM', 'currency' => 'DKK', 'weight' => 'KG_CM');
		$pn_core['GM'] = array('region' => 'AP', 'currency' => 'GMD', 'weight' => 'KG_CM');
		$pn_core['GN'] = array('region' => 'AP', 'currency' => 'GNF', 'weight' => 'KG_CM');
		$pn_core['GP'] = array('region' => 'AM', 'currency' => 'EUR', 'weight' => 'KG_CM');
		$pn_core['GQ'] = array('region' => 'AP', 'currency' => 'XAF', 'weight' => 'KG_CM');
		$pn_core['GR'] = array('region' => 'EU', 'currency' => 'EUR', 'weight' => 'KG_CM');
		$pn_core['GT'] = array('region' => 'AM', 'currency' => 'GTQ', 'weight' => 'KG_CM');
		$pn_core['GU'] = array('region' => 'AM', 'currency' => 'USD', 'weight' => 'LB_IN');
		$pn_core['GW'] = array('region' => 'AP', 'currency' => 'XOF', 'weight' => 'KG_CM');
		$pn_core['GY'] = array('region' => 'AP', 'currency' => 'GYD', 'weight' => 'LB_IN');
		$pn_core['HK'] = array('region' => 'AM', 'currency' => 'HKD', 'weight' => 'KG_CM');
		$pn_core['HN'] = array('region' => 'AM', 'currency' => 'HNL', 'weight' => 'KG_CM');
		$pn_core['HR'] = array('region' => 'AP', 'currency' => 'HRK', 'weight' => 'KG_CM');
		$pn_core['HT'] = array('region' => 'AM', 'currency' => 'HTG', 'weight' => 'LB_IN');
		$pn_core['HU'] = array('region' => 'EU', 'currency' => 'HUF', 'weight' => 'KG_CM');
		$pn_core['IC'] = array('region' => 'EU', 'currency' => 'EUR', 'weight' => 'KG_CM');
		$pn_core['ID'] = array('region' => 'AP', 'currency' => 'IDR', 'weight' => 'KG_CM');
		$pn_core['IE'] = array('region' => 'EU', 'currency' => 'EUR', 'weight' => 'KG_CM');
		$pn_core['IL'] = array('region' => 'AP', 'currency' => 'ILS', 'weight' => 'KG_CM');
		$pn_core['IN'] = array('region' => 'AP', 'currency' => 'INR', 'weight' => 'KG_CM');
		$pn_core['IQ'] = array('region' => 'AP', 'currency' => 'IQD', 'weight' => 'KG_CM');
		$pn_core['IR'] = array('region' => 'AP', 'currency' => 'IRR', 'weight' => 'KG_CM');
		$pn_core['IS'] = array('region' => 'EU', 'currency' => 'ISK', 'weight' => 'KG_CM');
		$pn_core['IT'] = array('region' => 'EU', 'currency' => 'EUR', 'weight' => 'KG_CM');
		$pn_core['JE'] = array('region' => 'AM', 'currency' => 'GBP', 'weight' => 'KG_CM');
		$pn_core['JM'] = array('region' => 'AM', 'currency' => 'JMD', 'weight' => 'KG_CM');
		$pn_core['JO'] = array('region' => 'AP', 'currency' => 'JOD', 'weight' => 'KG_CM');
		$pn_core['JP'] = array('region' => 'AP', 'currency' => 'JPY', 'weight' => 'KG_CM');
		$pn_core['KE'] = array('region' => 'AP', 'currency' => 'KES', 'weight' => 'KG_CM');
		$pn_core['KG'] = array('region' => 'AP', 'currency' => 'KGS', 'weight' => 'KG_CM');
		$pn_core['KH'] = array('region' => 'AP', 'currency' => 'KHR', 'weight' => 'KG_CM');
		$pn_core['KI'] = array('region' => 'AP', 'currency' => 'AUD', 'weight' => 'KG_CM');
		$pn_core['KM'] = array('region' => 'AP', 'currency' => 'KMF', 'weight' => 'KG_CM');
		$pn_core['KN'] = array('region' => 'AM', 'currency' => 'XCD', 'weight' => 'LB_IN');
		$pn_core['KP'] = array('region' => 'AP', 'currency' => 'KPW', 'weight' => 'LB_IN');
		$pn_core['KR'] = array('region' => 'AP', 'currency' => 'KRW', 'weight' => 'KG_CM');
		$pn_core['KV'] = array('region' => 'AM', 'currency' => 'EUR', 'weight' => 'KG_CM');
		$pn_core['KW'] = array('region' => 'AP', 'currency' => 'KWD', 'weight' => 'KG_CM');
		$pn_core['KY'] = array('region' => 'AM', 'currency' => 'KYD', 'weight' => 'KG_CM');
		$pn_core['KZ'] = array('region' => 'AP', 'currency' => 'KZF', 'weight' => 'LB_IN');
		$pn_core['LA'] = array('region' => 'AP', 'currency' => 'LAK', 'weight' => 'KG_CM');
		$pn_core['LB'] = array('region' => 'AP', 'currency' => 'USD', 'weight' => 'KG_CM');
		$pn_core['LC'] = array('region' => 'AM', 'currency' => 'XCD', 'weight' => 'KG_CM');
		$pn_core['LI'] = array('region' => 'AM', 'currency' => 'CHF', 'weight' => 'LB_IN');
		$pn_core['LK'] = array('region' => 'AP', 'currency' => 'LKR', 'weight' => 'KG_CM');
		$pn_core['LR'] = array('region' => 'AP', 'currency' => 'LRD', 'weight' => 'KG_CM');
		$pn_core['LS'] = array('region' => 'AP', 'currency' => 'LSL', 'weight' => 'KG_CM');
		$pn_core['LT'] = array('region' => 'EU', 'currency' => 'EUR', 'weight' => 'KG_CM');
		$pn_core['LU'] = array('region' => 'EU', 'currency' => 'EUR', 'weight' => 'KG_CM');
		$pn_core['LV'] = array('region' => 'EU', 'currency' => 'EUR', 'weight' => 'KG_CM');
		$pn_core['LY'] = array('region' => 'AP', 'currency' => 'LYD', 'weight' => 'KG_CM');
		$pn_core['MA'] = array('region' => 'AP', 'currency' => 'MAD', 'weight' => 'KG_CM');
		$pn_core['MC'] = array('region' => 'AM', 'currency' => 'EUR', 'weight' => 'KG_CM');
		$pn_core['MD'] = array('region' => 'AP', 'currency' => 'MDL', 'weight' => 'KG_CM');
		$pn_core['ME'] = array('region' => 'AM', 'currency' => 'EUR', 'weight' => 'KG_CM');
		$pn_core['MG'] = array('region' => 'AP', 'currency' => 'MGA', 'weight' => 'KG_CM');
		$pn_core['MH'] = array('region' => 'AM', 'currency' => 'USD', 'weight' => 'LB_IN');
		$pn_core['MK'] = array('region' => 'AP', 'currency' => 'MKD', 'weight' => 'KG_CM');
		$pn_core['ML'] = array('region' => 'AP', 'currency' => 'COF', 'weight' => 'KG_CM');
		$pn_core['MM'] = array('region' => 'AP', 'currency' => 'USD', 'weight' => 'KG_CM');
		$pn_core['MN'] = array('region' => 'AP', 'currency' => 'MNT', 'weight' => 'KG_CM');
		$pn_core['MO'] = array('region' => 'AP', 'currency' => 'MOP', 'weight' => 'KG_CM');
		$pn_core['MP'] = array('region' => 'AM', 'currency' => 'USD', 'weight' => 'LB_IN');
		$pn_core['MQ'] = array('region' => 'AM', 'currency' => 'EUR', 'weight' => 'KG_CM');
		$pn_core['MR'] = array('region' => 'AP', 'currency' => 'MRO', 'weight' => 'KG_CM');
		$pn_core['MS'] = array('region' => 'AM', 'currency' => 'XCD', 'weight' => 'LB_IN');
		$pn_core['MT'] = array('region' => 'AP', 'currency' => 'EUR', 'weight' => 'KG_CM');
		$pn_core['MU'] = array('region' => 'AP', 'currency' => 'MUR', 'weight' => 'KG_CM');
		$pn_core['MV'] = array('region' => 'AP', 'currency' => 'MVR', 'weight' => 'KG_CM');
		$pn_core['MW'] = array('region' => 'AP', 'currency' => 'MWK', 'weight' => 'KG_CM');
		$pn_core['MX'] = array('region' => 'AM', 'currency' => 'MXN', 'weight' => 'KG_CM');
		$pn_core['MY'] = array('region' => 'AP', 'currency' => 'MYR', 'weight' => 'KG_CM');
		$pn_core['MZ'] = array('region' => 'AP', 'currency' => 'MZN', 'weight' => 'KG_CM');
		$pn_core['NA'] = array('region' => 'AP', 'currency' => 'NAD', 'weight' => 'KG_CM');
		$pn_core['NC'] = array('region' => 'AP', 'currency' => 'XPF', 'weight' => 'KG_CM');
		$pn_core['NE'] = array('region' => 'AP', 'currency' => 'XOF', 'weight' => 'KG_CM');
		$pn_core['NG'] = array('region' => 'AP', 'currency' => 'NGN', 'weight' => 'KG_CM');
		$pn_core['NI'] = array('region' => 'AM', 'currency' => 'NIO', 'weight' => 'KG_CM');
		$pn_core['NL'] = array('region' => 'EU', 'currency' => 'EUR', 'weight' => 'KG_CM');
		$pn_core['NO'] = array('region' => 'EU', 'currency' => 'NOK', 'weight' => 'KG_CM');
		$pn_core['NP'] = array('region' => 'AP', 'currency' => 'NPR', 'weight' => 'KG_CM');
		$pn_core['NR'] = array('region' => 'AP', 'currency' => 'AUD', 'weight' => 'KG_CM');
		$pn_core['NU'] = array('region' => 'AP', 'currency' => 'NZD', 'weight' => 'KG_CM');
		$pn_core['NZ'] = array('region' => 'AP', 'currency' => 'NZD', 'weight' => 'KG_CM');
		$pn_core['OM'] = array('region' => 'AP', 'currency' => 'OMR', 'weight' => 'KG_CM');
		$pn_core['PA'] = array('region' => 'AM', 'currency' => 'USD', 'weight' => 'KG_CM');
		$pn_core['PE'] = array('region' => 'AM', 'currency' => 'PEN', 'weight' => 'KG_CM');
		$pn_core['PF'] = array('region' => 'AP', 'currency' => 'XPF', 'weight' => 'KG_CM');
		$pn_core['PG'] = array('region' => 'AP', 'currency' => 'PGK', 'weight' => 'KG_CM');
		$pn_core['PH'] = array('region' => 'AP', 'currency' => 'PHP', 'weight' => 'KG_CM');
		$pn_core['PK'] = array('region' => 'AP', 'currency' => 'PKR', 'weight' => 'KG_CM');
		$pn_core['PL'] = array('region' => 'EU', 'currency' => 'PLN', 'weight' => 'KG_CM');
		$pn_core['PR'] = array('region' => 'AM', 'currency' => 'USD', 'weight' => 'LB_IN');
		$pn_core['PT'] = array('region' => 'EU', 'currency' => 'EUR', 'weight' => 'KG_CM');
		$pn_core['PW'] = array('region' => 'AM', 'currency' => 'USD', 'weight' => 'KG_CM');
		$pn_core['PY'] = array('region' => 'AM', 'currency' => 'PYG', 'weight' => 'KG_CM');
		$pn_core['QA'] = array('region' => 'AP', 'currency' => 'QAR', 'weight' => 'KG_CM');
		$pn_core['RE'] = array('region' => 'AP', 'currency' => 'EUR', 'weight' => 'KG_CM');
		$pn_core['RO'] = array('region' => 'EU', 'currency' => 'RON', 'weight' => 'KG_CM');
		$pn_core['RS'] = array('region' => 'AP', 'currency' => 'RSD', 'weight' => 'KG_CM');
		$pn_core['RU'] = array('region' => 'AP', 'currency' => 'RUB', 'weight' => 'KG_CM');
		$pn_core['RW'] = array('region' => 'AP', 'currency' => 'RWF', 'weight' => 'KG_CM');
		$pn_core['SA'] = array('region' => 'AP', 'currency' => 'SAR', 'weight' => 'KG_CM');
		$pn_core['SB'] = array('region' => 'AP', 'currency' => 'SBD', 'weight' => 'KG_CM');
		$pn_core['SC'] = array('region' => 'AP', 'currency' => 'SCR', 'weight' => 'KG_CM');
		$pn_core['SD'] = array('region' => 'AP', 'currency' => 'SDG', 'weight' => 'KG_CM');
		$pn_core['SE'] = array('region' => 'EU', 'currency' => 'SEK', 'weight' => 'KG_CM');
		$pn_core['SG'] = array('region' => 'AP', 'currency' => 'SGD', 'weight' => 'KG_CM');
		$pn_core['SH'] = array('region' => 'AP', 'currency' => 'SHP', 'weight' => 'KG_CM');
		$pn_core['SI'] = array('region' => 'EU', 'currency' => 'EUR', 'weight' => 'KG_CM');
		$pn_core['SK'] = array('region' => 'EU', 'currency' => 'EUR', 'weight' => 'KG_CM');
		$pn_core['SL'] = array('region' => 'AP', 'currency' => 'SLL', 'weight' => 'KG_CM');
		$pn_core['SM'] = array('region' => 'EU', 'currency' => 'EUR', 'weight' => 'KG_CM');
		$pn_core['SN'] = array('region' => 'AP', 'currency' => 'XOF', 'weight' => 'KG_CM');
		$pn_core['SO'] = array('region' => 'AM', 'currency' => 'SOS', 'weight' => 'KG_CM');
		$pn_core['SR'] = array('region' => 'AM', 'currency' => 'SRD', 'weight' => 'KG_CM');
		$pn_core['SS'] = array('region' => 'AP', 'currency' => 'SSP', 'weight' => 'KG_CM');
		$pn_core['ST'] = array('region' => 'AP', 'currency' => 'STD', 'weight' => 'KG_CM');
		$pn_core['SV'] = array('region' => 'AM', 'currency' => 'USD', 'weight' => 'KG_CM');
		$pn_core['SY'] = array('region' => 'AP', 'currency' => 'SYP', 'weight' => 'KG_CM');
		$pn_core['SZ'] = array('region' => 'AP', 'currency' => 'SZL', 'weight' => 'KG_CM');
		$pn_core['TC'] = array('region' => 'AM', 'currency' => 'USD', 'weight' => 'LB_IN');
		$pn_core['TD'] = array('region' => 'AP', 'currency' => 'XAF', 'weight' => 'KG_CM');
		$pn_core['TG'] = array('region' => 'AP', 'currency' => 'XOF', 'weight' => 'KG_CM');
		$pn_core['TH'] = array('region' => 'AP', 'currency' => 'THB', 'weight' => 'KG_CM');
		$pn_core['TJ'] = array('region' => 'AP', 'currency' => 'TJS', 'weight' => 'KG_CM');
		$pn_core['TL'] = array('region' => 'AP', 'currency' => 'USD', 'weight' => 'KG_CM');
		$pn_core['TN'] = array('region' => 'AP', 'currency' => 'TND', 'weight' => 'KG_CM');
		$pn_core['TO'] = array('region' => 'AP', 'currency' => 'TOP', 'weight' => 'KG_CM');
		$pn_core['TR'] = array('region' => 'AP', 'currency' => 'TRY', 'weight' => 'KG_CM');
		$pn_core['TT'] = array('region' => 'AM', 'currency' => 'TTD', 'weight' => 'LB_IN');
		$pn_core['TV'] = array('region' => 'AP', 'currency' => 'AUD', 'weight' => 'KG_CM');
		$pn_core['TW'] = array('region' => 'AP', 'currency' => 'TWD', 'weight' => 'KG_CM');
		$pn_core['TZ'] = array('region' => 'AP', 'currency' => 'TZS', 'weight' => 'KG_CM');
		$pn_core['UA'] = array('region' => 'AP', 'currency' => 'UAH', 'weight' => 'KG_CM');
		$pn_core['UG'] = array('region' => 'AP', 'currency' => 'USD', 'weight' => 'KG_CM');
		$pn_core['US'] = array('region' => 'AM', 'currency' => 'USD', 'weight' => 'LB_IN');
		$pn_core['UY'] = array('region' => 'AM', 'currency' => 'UYU', 'weight' => 'KG_CM');
		$pn_core['UZ'] = array('region' => 'AP', 'currency' => 'UZS', 'weight' => 'KG_CM');
		$pn_core['VC'] = array('region' => 'AM', 'currency' => 'XCD', 'weight' => 'LB_IN');
		$pn_core['VE'] = array('region' => 'AM', 'currency' => 'VEF', 'weight' => 'KG_CM');
		$pn_core['VG'] = array('region' => 'AM', 'currency' => 'USD', 'weight' => 'LB_IN');
		$pn_core['VI'] = array('region' => 'AM', 'currency' => 'USD', 'weight' => 'LB_IN');
		$pn_core['VN'] = array('region' => 'AP', 'currency' => 'VND', 'weight' => 'KG_CM');
		$pn_core['VU'] = array('region' => 'AP', 'currency' => 'VUV', 'weight' => 'KG_CM');
		$pn_core['WS'] = array('region' => 'AP', 'currency' => 'WST', 'weight' => 'KG_CM');
		$pn_core['XB'] = array('region' => 'AM', 'currency' => 'EUR', 'weight' => 'LB_IN');
		$pn_core['XC'] = array('region' => 'AM', 'currency' => 'EUR', 'weight' => 'LB_IN');
		$pn_core['XE'] = array('region' => 'AM', 'currency' => 'ANG', 'weight' => 'LB_IN');
		$pn_core['XM'] = array('region' => 'AM', 'currency' => 'EUR', 'weight' => 'LB_IN');
		$pn_core['XN'] = array('region' => 'AM', 'currency' => 'XCD', 'weight' => 'LB_IN');
		$pn_core['XS'] = array('region' => 'AP', 'currency' => 'SIS', 'weight' => 'KG_CM');
		$pn_core['XY'] = array('region' => 'AM', 'currency' => 'ANG', 'weight' => 'LB_IN');
		$pn_core['YE'] = array('region' => 'AP', 'currency' => 'YER', 'weight' => 'KG_CM');
		$pn_core['YT'] = array('region' => 'AP', 'currency' => 'EUR', 'weight' => 'KG_CM');
		$pn_core['ZA'] = array('region' => 'AP', 'currency' => 'ZAR', 'weight' => 'KG_CM');
		$pn_core['ZM'] = array('region' => 'AP', 'currency' => 'ZMW', 'weight' => 'KG_CM');
		$pn_core['ZW'] = array('region' => 'AP', 'currency' => 'USD', 'weight' => 'KG_CM');
	}
	$hitshipo_pn = new hitshipo_pn_parent();
}
