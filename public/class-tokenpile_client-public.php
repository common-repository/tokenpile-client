<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       tokenpile.com
 * @since      0.9.0b
 *
 * @package    Tokenpile_client
 * @subpackage Tokenpile_client/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package Tokenpile_client
 * @subpackage Tokenpile_client/public
 * @author Jonathan Stewart <jonathan@tokenpile.com>
 */
class Tokenpile_client_Public
{
    
    /**
     * The ID of this plugin.
     *
     * @since 0.9.0b
     * @access private
     * @var string $tokenpile_plugin_name The ID of this plugin.
     */
    private $tokenpile_plugin_name;
    
    /**
     * The version of this plugin.
     *
     * @since 0.9.0b
     * @access private
     * @var string $version The current version of this plugin.
     */
    private $version;
    
    private $url;
    
    /**
     * Initialize the class and set its properties.
     *
     * @since 0.9.0b
     * @param string $tokenpile_plugin_name
     *            The name of the plugin.
     * @param string $version
     *            The version of this plugin.
     */
    public function __construct($tokenpile_plugin_name, $version, $url)
    {
        $this->tokenpile_plugin_name = $tokenpile_plugin_name;
        $this->version = $version;
        $this->url = $url;
    }
    
    /**
     * Register the stylesheets for the public-facing side of the site.
     *
     * @since 0.9.0b
     */
    public function enqueue_styles()
    {
        
        /**
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the run() function
         * defined in Tokenpile_client_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The Tokenpile_client_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */
        wp_enqueue_style($this->tokenpile_plugin_name, plugin_dir_url(__FILE__) . 'css/tokenpile_client-public.css', array(), $this->version, 'all');
    }
    
    /**
     * Register the JavaScript for the public-facing side of the site.
     *
     * @since 0.9.0b
     */
    public function enqueue_scripts()
    {
        
        /**
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the run() function
         * defined in Tokenpile_client_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The Tokenpile_client_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */
        wp_enqueue_script($this->tokenpile_plugin_name, plugin_dir_url(__FILE__) . 'js/tokenpile_client-public.js', array(
            'jquery'
        ), $this->version, false);
        wp_enqueue_script("tokenpile_main", "//www.tokenpile.com/tokenpile-public/tokenpile.js", array(
            'jquery'
        ), $this->version, false);
    }
    
    public function tokenpile_display_div($content)
    {
        global $post;
        $display = get_option($this->tokenpile_plugin_name . '_display');
        $types = get_option($this->tokenpile_plugin_name . '_post_types');
        if (in_array($post->post_type, $types) && $display) {
            // Add image to the beginning of each page
            $pId = get_post_meta($post->ID, '_tokenpile_product_key', true);
            if ($pId) {
                $content .= '<div id="tokenpile_div" pId=' . $pId . '></div>';
            }
        }
        return $content;
    }
    
    public function tokenpile_register_shortcodes()
    {
        add_shortcode('tokenpile_link', array(
            $this,
            'tokenpile_div_shortcode_func'
        ));
    }
    
    public function tokenpile_div_shortcode_func($atts = [], $content = null)
    {
        // normalize attribute keys, lowercase
        $atts = array_change_key_case((array) $atts, CASE_LOWER);
        $pId = $atts['pid'];
        if ($pId) {
            $content .= '<div id="tokenpile_div" pId=' . $pId . '></div>';
        }
        return $content;
    }
}
