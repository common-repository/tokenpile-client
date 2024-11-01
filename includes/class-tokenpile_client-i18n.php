<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       tokenpile.com
 * @since      0.9.0b
 *
 * @package    Tokenpile_client
 * @subpackage Tokenpile_client/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      0.9.0b
 * @package    Tokenpile_client
 * @subpackage Tokenpile_client/includes
 * @author     Jonathan Stewart <jonathan@tokenpile.com>
 */
class Tokenpile_client_i18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    0.9.0b
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'tokenpile_client',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}



}
