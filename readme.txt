=== MarketPress Product Importer ===
Contributors: bluenet
Donate link: http://bluenetpublishing.com/plugins/marketpress-product-importer/
Tags: marketpress, product importer, product uploader, ecommerce, csv product upload, featured images, product skus, csv upload, 
Requires at least: 3.1
Tested up to: 3.3.1
Stable tag: 1.1.1

MarketPress Product Importer enables users to import products directly into MarketPress.  The best, free eCommerce plugin available for WordPress.

== Description ==

Take MarketPress to the next level.  As the name implies, MarketPress Product Importer enables users of the WPMU DEV plugin MarketPress, to quickly and easily import products to their online store, or product listing site.  If you manage affiliate sites, you need this plugin.

When this plugin is activated, a new database table is created for storing the product data temporarily. In order to run the importer, that table needs to be populated with the users product information. I'm planning to integrate the CSV import directly into the plugin, but for now, I'm using [CSVisual](http://bluenetpublishing.com/wp-content/uploads/2012/01/csvisual.zip "http://bluenetpublishing.com/wp-content/uploads/2012/01/csvisual.zip"), an edited version of [Quick CSV import with Visual Mapping](http://i1t2b3.com/2009/01/14/quick-csv-import-with-mapping/ "http://i1t2b3.com/2009/01/14/quick-csv-import-with-mapping/") by Alexander Skakunov.

Once the product data has been added to the (_mpimporter) table, the plugin will recognize it, and show the "number of products" available, as well as, an "Import Now" button.

Simply click the "Import Now" button, and the plugin will import your new products. NOTE: Data in the (_mpimporter) table is not deleted, so running the import again will just create copies of the products in MarketPress. I'm working on an update to handle this, so users will have the ability to update existing MarketPress products, without having a duplicate record created.

= Currently Supported Features =

* Basic Product Data
* Featured Images
* Product Categories
* Product Tags
* Product SKU's
* Product Price
* Product Sale Price
* External Product Links

For complete setup and usage instructions with screenshots, visit [MarketPress Product Importer Development](http://bluenetpublishing.com/plugins/marketpress-product-importer/ "http://bluenetpublishing.com/plugins/marketpress-product-importer/").

== Installation ==

1. Download the MarketPress Product Importer plugin file
2. Unzip the file into a folder on your hard drive
3. Upload the '/marketpress-product-importer/' folder to the '/wp-content/plugins/' folder on your site
4. Activate the plugin through the 'Plugins' menu in WordPress

== Frequently Asked Questions ==

= After activating the plugin, what should I do first? =

For complete setup and usage instructions with screenshots, visit [MarketPress Product Importer Development](http://bluenetpublishing.com/plugins/marketpress-product-importer/ "http://bluenetpublishing.com/plugins/marketpress-product-importer/").

== Screenshots ==

1. Plugin Activation & Settings
2. Plugin Settings Page
3. CSVisual Interface
4. Sample CSV
5. CSV upload with Visual Field Mapping
6. Import products to MarketPress

== Changelog ==

= 1.0 =
* Initial Release

= 1.1 =
* Updated admin meta boxes
* Moved importer results div to top of page

= 1.1.1 =
* Added banner for plugin repo directory listing
* Updated css styles

== Upgrade Notice ==

There are currently no upgrades for the Marketpress Product Importer