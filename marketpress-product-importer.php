<?php
/*
  Plugin Name: MarketPress Product Importer
  Version: 1.1.1
  Plugin URI: http://bluenetpublishing.com/plugins/marketpress-product-importer/
  Description: Enables users of the WPMU DEV plugin MarketPress, to quickly and easily import products to their online store, or product listing site.  Currently supports basic product data, featured images, categories, tags, skus, price, sale price, and external product links.
  
  Author: Mark Hill
  Author URI: http://bluenetpublishing.com
  Copyright 2012 BlueNet Publishing
  
  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License (Version 2 - GPLv2)
  as published by the Free Software Foundation.
  
  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
  GNU General Public License for more details.
  
  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA
 */


if (!class_exists("MP_Product_Importer")) {

	class MP_Product_Importer {
	
		var $version = '1.0';
		var $importer_name = 'MarketPress Product Importer';
		
		const DB_VERSION = 2;
		
		function MP_Product_Importer() {
			$this->__construct();
		}
		
		function __construct() {
			// activate plugin
			register_activation_hook(__FILE__, array($this, 'mpimporter_activate'));
			
			// deactivate plugin
			register_deactivation_hook(__FILE__, array($this, 'mpimporter_deactivate'));
			
			// Add hooks and actions
			add_action('init', array($this, 'mpimporter_update'), 1);
			add_action('admin_init', array($this, 'mpimporter_register_settings'));
			add_action('admin_menu', array($this, 'mpimporter_options_page'));
			
			// add settings link on plugin page
			add_filter('plugin_action_links_' . plugin_basename(__FILE__), array($this, 'mpimporter_settings_link'), 10, 2);
			
			$this->errors = new WP_Error();
		}
		
		function mpimporter_activate() {
			global $wpdb;
	
			// create db table for importing products
			$mpi_importer = $wpdb->prefix . 'mpimporter';
	
			/* Here we double check to see if the table we are going to create already exists
			 * If it does then we'll check to see if it needs upgrading with the function below
			 * If the table doesn't exist then execute the query to create it for the first time. */
			if ($wpdb->get_var('SHOW TABLES LIKE \'' . $mpi_importer . '\';') != $mpi_importer) {
			
				$create_mpimporter_sql = 'CREATE TABLE ' . $mpi_importer . ' (
				`ID` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				`post_content` longtext NOT NULL,
				`post_title` text NOT NULL,
				`post_excerpt` text NOT NULL,
				`post_status` varchar(20) NOT NULL DEFAULT "publish",
				`comment_status` varchar(20) NOT NULL DEFAULT "open",
				`ping_status` varchar(20) NOT NULL DEFAULT "open",
				`comment_count` bigint(20) NOT NULL DEFAULT "0",
				`prod_link` longtext NOT NULL,
				`prod_tags` longtext NOT NULL,
				`prod_category` text NOT NULL,
				`prod_image` text NOT NULL,
				`prod_sku` varchar(255) NOT NULL,
				`prod_sale_price` varchar(255) NOT NULL,
				`prod_price` varchar(255) NOT NULL,
				`prod_var_name` varchar(50) NOT NULL,
				PRIMARY KEY (`ID`)
				) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=10';
			
				$wpdb->query($create_mpimporter_sql);
			}	
			
			/* Now we store an option in Wordpress that tells us what version of the plugin's table
			 * structure is installed. This way, in the future we can easily run the SQL code needed
			 * to upgrade older versions of the plugin to the most recent. The add_option function
			 * let's us quickly insert a value into the WordPress options table. */
			add_option('mpimporter_db_version', self::DB_VERSION);
		}
		
		function mpimporter_deactivate() {
			global $wpdb;
	
			// delete the database tables
			$mpi_importer = $wpdb->prefix . 'mpimporter';
			
			//$db = get_db();
			$drop_mpimporter_sql = "DROP TABLE IF EXISTS ".$mpi_importer;
			$wpdb->query($drop_mpimporter_sql);
		}	
		
		function mpimporter_update() {
			global $wpdb;

			/* In this line we check what version of the tables are current installed.
			 * The get_option function lets us quickly retreive a value that has stored in the
			 * WordPress options table. */
			$installed_ver = get_option('mpimporter_db_version');
	
			// If the installed db version and the current db version are the same,
			// then theres no need to upgrade.
			if ($installed_ver == self::DB_VERSION)
				return false;
	
			$mpi_importer = $wpdb->prefix . 'mpimporter';
	
			/* Now that these queries have been run, we know we have the most recent version of
			 * the database installed, so let's update our version number. The update_option
			 * function lets us quickly update an option that exists in the WordPress options
			 * table. If the option doesn't already exist, it will be created. */
			update_option('mpimporter_db_version', self::DB_VERSION);
		}
		
		// Add options page to settings menu
		function mpimporter_options_page() {
			$this->pagehook = add_options_page('MarketPress Product Importer Settings', 'MP Importer', 'administrator', 'mpimporter', array($this, 'mpimporter_display_options'));
			add_action('admin_print_styles-'.$this->pagehook, array($this, 'mpimporter_custom_css'));
			add_action('load-'.$this->pagehook, array(&$this, 'on_load_page'));
		}
		
		function on_load_page() {
			//ensure, that the needed javascripts been loaded to allow drag/drop, expand/collapse and hide/show of boxes
			wp_enqueue_script('common');
			wp_enqueue_script('wp-lists');
			wp_enqueue_script('postbox');
		}
		
		function mpimporter_custom_css() {
			wp_register_style('mpimporter-admin-css', plugins_url('css/mpimporter.css', __FILE__), array(), '1.1.1', 'all');
			wp_enqueue_style('mpimporter-admin-css');
		}
		
		// Register the settings. Add the settings section and settings fields
		function mpimporter_register_settings(){
			register_setting('mpimporter_options', 'mpimporter_options');
			//add_settings_section('main_section', '', array($this, 'mpimporter_main_section'), __FILE__);			
		}
		
		// Add settings link to plugins page
		function mpimporter_settings_link($links) {
			$settings_link = '<a href="options-general.php?page=mpimporter">' . __('Settings', 'MP Importer') . '</a>';
			array_unshift($links, $settings_link);
			return $links;
		}
		
		// Display the admin options page
		function mpimporter_display_options() {
			add_meta_box('mpimporter-sidebox-1', 'Found a Bug?', array($this, 'on_sidebox_1_content'), $this->pagehook, 'side', 'core');
			add_meta_box('mpimporter-sidebox-2', 'Sponsors', array($this, 'on_sidebox_2_content'), $this->pagehook, 'side', 'core');
			add_meta_box('mpimporter-contentbox-1', 'Plugin Overview', array($this, 'on_contentbox_1_content'), $this->pagehook, 'normal', 'core');
			add_meta_box('mpimporter-contentbox-2', 'Importer Settings', array($this, 'on_contentbox_2_content'), $this->pagehook, 'normal', 'core');
			?>
			
			<div id="mpimporter-metaboxes-general" class="wrap">
				<?php screen_icon(); ?>
				<h2>MarketPress Product Importer</h2>
				<form id="mpimporter-form" method="post" action="" enctype="multipart/form-data">
					<?php wp_nonce_field('mpimporter-metaboxes-general'); ?>
					<?php wp_nonce_field('closedpostboxes', 'closedpostboxesnonce', false ); ?>
					<?php wp_nonce_field('meta-box-order', 'meta-box-order-nonce', false ); ?>
					
					<div id="poststuff" class="metabox-holder has-right-sidebar">
						<div id="side-info-column" class="inner-sidebar">
							<?php do_meta_boxes($this->pagehook, 'side', null); ?>
						</div>
						<div id="post-body" class="has-sidebar">
							<div id="post-body-content" class="has-sidebar-content">
								<?php if (isset($_POST['mpimporter-' . sanitize_title($this->importer_name)])) {
									$this->process();
								} ?>
								<?php do_meta_boxes($this->pagehook, 'normal', null); ?>
							</div>
						</div>
						<br class="clear"/>
						
						<p>For more information about MarketPress Product Importer, please visit <a href="http://bluenetpublishing.com/plugins/marketpress-product-importer/" target="_blank">BlueNet Publishing</a>.</p>
										
					</div>
				</form>
			</div>
			
			<script type="text/javascript">
			//<![CDATA[
			jQuery(document).ready( function($) {
				// close postboxes that should be closed
				$('.if-js-closed').removeClass('if-js-closed').addClass('closed');
				// postboxes setup
				postboxes.add_postbox_toggles('<?php echo $this->pagehook; ?>');
			});
			//]]>
			</script>
			<?php
		}
		
		
		/******************** callback functions ********************/
		function on_contentbox_1_content() {
		?>
		<p>Take MarketPress to the next level. As the name implies, MarketPress Product Importer enables users of the <a href="http://premium.wpmudev.org?ref=bluenet-05291" target="_blank">WPMU DEV MarketPress plugin</a>, to quickly and easily import products to their online store, or product listing site. If you manage affiliate sites, you need this plugin.</p>
							
		<p><strong>Currently Supports:</strong></p>
		
		<ul class="mpimporter-lists">
			<li>Product Images</li>
			<li>Product Categories</li>
			<li>Product Tags</li>
			<li>Product Price</li>
			<li>Product Sale Price</li>
			<li>Product Links</li>
		</ul>
		
		<p>For complete setup and usage instructions with screenshots, visit: <a href="http://bluenetpublishing.com/plugins/marketpress-product-importer/" target="_blank">MarketPress Importer Development</a>.</p>
		<?php
		}
		
		function on_contentbox_2_content() {
			$this->display();
		}
		
		function on_sidebox_1_content() {
		?>
		<p>If you've found a bug in this plugin, please submit it: <a href="http://bluenetpublishing.com/plugins/marketpress-product-importer/" target="_blank">Plugin Support</a>.</p>
		<?php
		}
		
		function on_sidebox_2_content() {
		?>
		<a "http://premium.wpmudev.org?ref=bluenet-05291" target="_blank">
<img src="http://wpmu.org/wp-content/uploads/2010/05/250box.png" alt="WPMU DEV - The WordPress Experts" title="Check out WPMU DEV - The WordPress Experts" /></a>
		<?php
		}
	
		
		function display() {
			global $wpdb;
			
			$mpimporter_table = $wpdb->prefix . 'mpimporter';
	
			if ($this->results) {
				?>
				<p><?php printf(__('Successfully imported %s products. Data in the plugin table was not deleted, so running the importer again will just create copies of the products in MarketPress.', 'mpimporter'), number_format_i18n($this->results)); ?></p>
				<?php
			} else {
				$num_products = $wpdb->get_var("SELECT count(*) FROM " . $mpimporter_table);
				
				if ($num_products) {
					?>					
					
					<p><?php printf(__('There are currently <strong>%s</strong> products available for importing. Click the "<strong>Import Now</strong>" button, to begin your import!', 'mpimporter'), number_format_i18n($num_products)); ?></p>
					<?php
					$this->import_button();
				} else { //no products
					?>
										
					<div class="mpimporter-steps">
						<p class="noprod">There are currently no products to import.</p>
						
						<h4>Step #1</h4>
						
							<p>When this plugin was activated, a new database table was created (<strong><?php printf(__($mpimporter_table)); ?></strong>).  In order to run the importer, we need to populate this table with your product information.  While not required, I'd recommend using <a href="http://bluenetpublishing.com/wp-content/uploads/2012/01/csvisual.zip" target="_blank">CSVisual</a>, an edited version of <a href="http://i1t2b3.com/2009/01/14/quick-csv-import-with-mapping/" target="_blank">Quick CSV import with Visual Mapping</a> by Alexander Skakunov.  The CSV files are not WordPress plugins. Simply download the files, define database settings in config.inc, then load index.php in your browser.</p>
						
						<h4>Step #2</h4>
						
							<ul class="mpimporter-lists">
								<li>Select your "Source CSV file to import"</li>
								<li>Select "Export table".  Be sure to select (<strong><?php printf(__($mpimporter_table)); ?></strong>)</li>
								<li>Click the "Upload the file" button</li>
								<li>Select the fields you wish to import from your file, and complete CSV upload</li>
							</ul>
						
						<h4>Step #3</h4>
						
							<p>Now that you have data to work with, let's import some products into MarketPress.  Refresh the MarketPress Product Importer settings page, and you should see the "number of products" available, as well as, an "<strong>Import Now</strong>" button.</p>
							
						<h4>Step #4</h4>
						
							<p>Hit the "<strong>Import Now</strong>" button, and the plugin will import your new products. NOTE: Data in the table (<strong><?php printf(__($mpimporter_table)); ?></strong>) is not deleted, so running the import again will just create copies of the products in MarketPress.  I'll be adding an update to handle this, so users will have the ability to update existing MarketPress products, without having a duplicate record created.</p>
						
					</div>
					
					<?php
				}
			}
		}
		
		function import_button($label = '') {
			$label = !empty($label) ? $label : __('Import Now &raquo;', 'mpimporter');
			?>
			<p class="submit">
				<input type="submit" class="button-primary" name="mpimporter-<?php echo sanitize_title($this->importer_name); ?>" id="mpimporter-submit" value="<?php echo $label; ?>" />
			</p>
			<?php
		}
		
		
		function process() {
			global $wpdb;
	
			set_time_limit(0); //this can take a while
			$this->results = 0;
			
			$delay = 2;
			
			$mpimporter_table = $wpdb->prefix . 'mpimporter';
			$products = $wpdb->get_results("SELECT * FROM " . $mpimporter_table, ARRAY_A);
			
			?>
			<div id="mpimporter-results-1" class="postbox">
			<div class="handlediv" title="Click to toggle"><br></div><h3 class="hndle"><span>Importer Results</span></h3>
				<div class="inside">
				<table class="widefat">
					<thead>
						<tr>
							<th>ID</th>
							<th>Product Name</th>
							<th>Product Sku</th>
							<th>Product Price</th>
							<th>Result</th>
						</tr>
					</thead>
					<tfoot>
						<tr>
							<th>ID</th>
							<th>Product Name</th>
							<th>Product Sku</th>
							<th>Product Price</th>
							<th>Result</th>
						</tr>
					</tfoot>
					<tbody>
			<?php
			
			foreach ($products as $product) {
				//import product
				$old_id = $product['ID'];
				unset($product['ID']); //clear id so it inserts as a new product
				$product['post_type'] = 'product';
				$product['comment_status'] = 'closed';
				$product['comment_count'] = 0;
				
				// insert product tags
				$product['tax_input']['product_tag'] = $product['prod_tags'];
				
				//create the post
				$new_id = wp_insert_post($product);
				
				// insert product categories
				$product_cats = array_map('trim',explode(",",$product['prod_category']));
				wp_set_object_terms($new_id, $product_cats, 'product_category');
				
				//price function
				$func_curr = '$price = round($price, 2);return ($price) ? $price : 0;';
				
				//sku function
				$func_sku = 'return preg_replace("/[^a-zA-Z0-9_-]/", "", $value);';
				
				update_post_meta($new_id, 'mp_var_name', array($product['prod_var_name']));
				update_post_meta($new_id, 'mp_sku', array_map(create_function('$value', $func_sku), array($product['prod_sku'])));
				update_post_meta($new_id, 'mp_price', array_map(create_function('$price', $func_curr), array($product['prod_price'])));				
				
				//add sale price only if set and different than reg price
				if (isset($product['prod_sale_price']) && $product['prod_sale_price'] != $product['prod_price']) {
					update_post_meta($new_id, 'mp_is_sale', 1);
					update_post_meta($new_id, 'mp_sale_price', array_map(create_function('$price', $func_curr), array($product['prod_sale_price'])));
				}
				
				//add stock count				
				
				//add external link
				if (!empty($product['prod_link'])) {
					update_post_meta($new_id, 'mp_product_link', esc_url_raw($product['prod_link']));
				}
				
				//add extra shipping
				
				// add image attachment
				if (isset($product['prod_image']) && (!empty($product['prod_image']))) {
					//$img = get_post_meta($post->ID, "Image URL", true);
					$img = $product['prod_image'];
					$ext = substr(strrchr($img,'.'),1);
					
					// WordPress Upload Directory to Copy to (must be CHMOD to 777)
					$uploads = wp_upload_dir();
					$copydir = $uploads['path']."/";
					
					// Code to Copy Image to WordPress Upload Directory (Server Must Support file_get_content/fopen/fputs)
					$data = file_get_contents($img);
					$filetitle = strtolower(str_replace(array(' ', '-', '.', '(', ')', '!', '@', '#', '$', '%', '^', '&', '*', '_', '=', '+'), "-", $product['post_title']));
					$file = fopen($copydir . $filetitle."-".$new_id.".".$ext, "w+");
					fputs($file, $data);
					fclose($file);
					
					// Insert Image to WordPress Media Libarby
					$filepath = $uploads['path']."/".$filetitle."-".$new_id.".".$ext;
					
					$wp_filetype = wp_check_filetype(basename($filepath), null);
					$attachment = array(
						'post_mime_type' => $wp_filetype['type'],
						'post_title' => $product['prod_sku'],
						'post_content' => '',
						'post_type' => 'attachment',
						'post_parent' => $new_id,
						'post_status' => 'inherit'
					);
					
					// Get Attachment ID for Post
					$attach_id = wp_insert_attachment($attachment, $filepath, $new_id);
					require_once(ABSPATH . 'wp-admin/includes/image.php');
					$attach_data = wp_generate_attachment_metadata($attach_id, $filepath);
					wp_update_attachment_metadata($attach_id, $attach_data);
	
					// Attached Image as Featured Thumbnail in Post
					update_post_meta($new_id, '_thumbnail_id', $attach_id);
				}
				
				//update mpimporter as products are added to MarketPress
				echo "<tr>\n";
					echo "<td>".$old_id."</td>\n";
					echo "<td>".$product['post_title']."</td>\n";
					echo "<td>".$product['prod_sku']."</td>\n";
					echo "<td>".$product['prod_price']."</td>\n";
					echo "<td>Completed</td>\n";
				echo "</tr>\n";
				
				ob_flush();
				flush();
				sleep($delay);

				// increment count
				$this->results++;
			}
			?>

					</tbody>
				</table>
				</div>
			</div>
			
			<?php

		} // end process()
	
	} // end class

} // end !class_exists

if (class_exists("MP_Product_Importer")) {
	$MPImportClass = new MP_Product_Importer();
}

?>