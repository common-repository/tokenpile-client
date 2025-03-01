<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              tokenpile.com
 * @since             0.9.0b
 * @package           Tokenpile_client
 *
 * @wordpress-plugin
 * Plugin Name:       TokenPile-Client
 * Plugin URI:        https://www.tokenpile.com/wordpress-integration
 * Description:       Create revenue for your site without ads. Tokenpile.com, the content marketplace.
 * Version:           0.9.0b
 * Author:            Jonathan Stewart
 * Author URI:        tokenpile.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       tokenpile_client
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if (! defined('WPINC')) {
    die();
}

/**
 * Currently plugin version.
 * Start at version 0.9.0b and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define('TOKENPILE_PLUGIN_NAME_VERSION', '0.9.0b');

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-tokenpile_client-activator.php
 */
function activate_tokenpile_client()
{
    require_once plugin_dir_path(__FILE__) . 'includes/class-tokenpile_client-activator.php';
    Tokenpile_client_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-tokenpile_client-deactivator.php
 */
function deactivate_tokenpile_client()
{
    require_once plugin_dir_path(__FILE__) . 'includes/class-tokenpile_client-deactivator.php';
    Tokenpile_client_Deactivator::deactivate();
}

register_activation_hook(__FILE__, 'activate_tokenpile_client');
register_deactivation_hook(__FILE__, 'deactivate_tokenpile_client');

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path(__FILE__) . 'includes/class-tokenpile_client.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since 0.9.0b
 */
function run_tokenpile_client()
{
    $plugin = new Tokenpile_client();
    $plugin->run();
}
run_tokenpile_client();
