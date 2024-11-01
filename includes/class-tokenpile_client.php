<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       tokenpile.com
 * @since      0.9.0b
 *
 * @package    Tokenpile_client
 * @subpackage Tokenpile_client/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      0.9.0b
 * @package    Tokenpile_client
 * @subpackage Tokenpile_client/includes
 * @author     Jonathan Stewart <jonathan@tokenpile.com>
 */
class Tokenpile_client {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    0.9.0b
	 * @access   protected
	 * @var      Tokenpile_client_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    0.9.0b
	 * @access   protected
	 * @var      string    $tokenpile_plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $tokenpile_plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    0.9.0b
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	protected $url;
	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    0.9.0b
	 */
	public function __construct() {
		if ( defined( 'TOKENPILE_PLUGIN_NAME_VERSION' ) ) {
		    $this->version = TOKENPILE_PLUGIN_NAME_VERSION;
		} else {
			$this->version = '0.9.0b';
		}
		$this->tokenpile_plugin_name = 'tokenpile_client';

		$this->url = 'https://www.tokenpile.com';
		//$this->url = 'https://localhost';
		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Tokenpile_client_Loader. Orchestrates the hooks of the plugin.
	 * - Tokenpile_client_i18n. Defines internationalization functionality.
	 * - Tokenpile_client_Admin. Defines all hooks for the admin area.
	 * - Tokenpile_client_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    0.9.0b
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-tokenpile_client-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-tokenpile_client-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-tokenpile_client-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-tokenpile_client-public.php';

		$this->loader = new Tokenpile_client_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Tokenpile_client_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    0.9.0b
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Tokenpile_client_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    0.9.0b
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Tokenpile_client_Admin( $this->get_plugin_name(), $this->get_version(), $this->url );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );

		$this->loader->add_action ( 'admin_menu', $plugin_admin, 'add_options_page' );
		$this->loader->add_action ( 'admin_init', $plugin_admin, 'register_setting' );
		
		$this->loader->add_action ('add_meta_boxes', $plugin_admin, 'tokenpile_add_custom_box');
		//$this->loader->add_action('save_post', $plugin_admin, 'tokenpile_save_postdata');
		$this->loader->add_action( 'admin_post_tokenpile_sync_all_posts', $plugin_admin, 'tokenpile_sync_all_posts' );
		$this->loader->add_action( 'wp_ajax_tokenpile_debug_sync_post', $plugin_admin, 'tokenpile_debug_sync_post' );
		$this->loader->add_action( 'wp_ajax_tokenpile_debug_update_post', $plugin_admin, 'tokenpile_debug_update_post' );
		$this->loader->add_action( 'wp_ajax_tokenpile_debug_delete_post', $plugin_admin, 'tokenpile_debug_delete_post' );

		$this->loader->add_action( 'wp_ajax_tokenpile_test_ajax', $plugin_admin, 'tokenpile_test_ajax' );
		
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    0.9.0b
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Tokenpile_client_Public( $this->get_plugin_name(), $this->get_version(), $this->url );

		$this->loader->add_action( 'init', $plugin_public, 'tokenpile_register_shortcodes' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );
		$this->loader->add_filter( 'the_content', $plugin_public, 'tokenpile_display_div' );
		
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    0.9.0b
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     0.9.0b
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->tokenpile_plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     0.9.0b
	 * @return    Tokenpile_client_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     0.9.0b
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}
}
