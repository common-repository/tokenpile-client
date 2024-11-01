<?php

/**
 * Fired during plugin deactivation
 *
 * @link       tokenpile.com
 * @since      0.9.0b
 *
 * @package    Tokenpile_client
 * @subpackage Tokenpile_client/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      0.9.0b
 * @package    Tokenpile_client
 * @subpackage Tokenpile_client/includes
 * @author     Jonathan Stewart <jonathan@tokenpile.com>
 */
class Tokenpile_client_Deactivator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    0.9.0b
	 */
	public static function deactivate() {

	    delete_option('tokenpile_user');	
	    delete_option('tokenpile_key');
	    delete_option('tokenpile_secret');
	    delete_option('tokenpile_auto');
	    delete_option('tokenpile_secret');

	    unregister_setting('tokenpile_client', 'tokenpile_auto', 'boolval');
	    unregister_setting('tokenpile_client', 'tokenpile_user', 'strval');
	    unregister_setting('tokenpile_client', 'tokenpile_key', 'strval');
	    unregister_setting('tokenpile_client', 'tokenpile_secret', 'strval');
	}

}
