<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       tokenpile.com
 * @since      0.9.0b
 *
 * @package    Tokenpile_client
 * @subpackage Tokenpile_client/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package Tokenpile_client
 * @subpackage Tokenpile_client/admin
 * @author Jonathan Stewart <jonathan@tokenpile.com>
 */
class Tokenpile_client_Admin
{

    /**
     * The options name to be used in this plugin
     *
     * @since 0.9.0b
     * @access private
     * @var string $option_name Option name of this plugin
     */
    private $option_name = 'tokenpile_client';

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
     *            The name of this plugin.
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
     * Register the stylesheets for the admin area.
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
        wp_enqueue_style($this->tokenpile_plugin_name, plugin_dir_url(__FILE__) . 'css/tokenpile_client-admin.css', array(), $this->version, 'all');
    }

    /**
     * Register the JavaScript for the admin area.
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
        
        wp_enqueue_script($this->tokenpile_plugin_name, plugin_dir_url(__FILE__) . 'js/tokenpile_client-admin.js', array(
            'jquery'
        ), $this->version, false);
    }

    /**
     * new
     */
    /**
     * Add an options page under the Settings submenu
     *
     * @since 0.9.0b
     */
    public function add_options_page()
    {
        $this->plugin_screen_hook_suffix = add_options_page(__('TokenPile Settings', 'tokenpile_client'), __('TokenPile', 'tokenpile_client'), 'manage_options', $this->tokenpile_plugin_name, array(
            $this,
            'display_options_page'
        ));
    }

    /**
     * Render the options page for plugin
     *
     * @since 0.9.0b
     */
    public function display_options_page()
    {
        include_once 'partials/tokenpile_client-admin-display.php';
    }

    public function tokenpile_sync_all_posts()
    {
        $posts = get_posts();
        foreach ($posts as $post) {
            echo 'saving ' . $post->ID;
            // var_dump($post);
            $this->tokenpile_save_postdata($post);
        }
        $url = wp_get_referer();
        echo 'url: ' . $url;
        // wp_safe_redirect( $url );
    }

    public function tokenpile_save_postdata($post)
    {
        $post_id = $post->ID;
        $postTypes = get_option($this->option_name . '_post_types');
        echo "post type in post types: " . in_array($post->post_type, get_option($this->option_name . '_post_types'));
        if (in_array($post->post_type, get_option($this->option_name . '_post_types'))) {
            echo 'saving post data';
            if (array_key_exists('tokenpile_should_sync', $_POST)) {
                update_post_meta($post_id, '_tokenpile_should_sync_key', $_POST['tokenpile_should_sync']);
            } elseif (array_key_exists('tokenpile_should_sync_default', $_POST)) {
                update_post_meta($post_id, '_tokenpile_should_sync_key', $_POST['tokenpile_should_sync_default']);
            }

            $productKey = get_post_meta($post_id, '_tokenpile_product_key', true);
            $shouldSync = get_post_meta($post_id, '_tokenpile_should_sync_key', true);

            if ($productKey && ! $shouldSync) {
                // delete
                echo 'removing post';
                $this->tokenpile_debug_delete_post($post_id);
            } elseif (! $productKey || $shouldSync) {
                // sync
                echo 'sending post';
                $this->tokenpile_sync_post($post_id);
            } elseif ( $productKey || $shouldSync) {
                echo 'updating post';
                $this->tokenpile_debug_update_post($post_id);
            }
        }
    }

    public function tokenpile_debug_sync_post()
    {
        $postId = $_POST['post_id'];
        $this->tokenpile_sync_post($postId);
    }

    public function tokenpile_sync_post($postId)
    {
        $result = array();
        
        $post = get_post($postId);
        $ch = curl_init($this->url . "/wp-json/wc/v2/products");
        $postUrl = '<a href="' . esc_url(get_permalink($postId)) . '">Read ' . $post->post_title . '</a>';
        //echo 'post link: ' . $postUrl;
        $array = array(
            "name" => $post->post_title,
            "type" => "simple",
            "sku" => $post->slug,
            "shipping_required" => false,
            "virtual" => true,
            "description" => $postUrl,
            "regular_price" => '$' . get_option($this->option_name . '_price'),
            "vendor" => get_option($this->option_name . '_user')
        );
        // Setup request to send json via POST.
        $payload = json_encode($array);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_USERPWD, get_option($this->option_name . '_key') . ':' . get_option($this->option_name . '_secret'));
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type:application/json'
        ));
        // Return response instead of printing.
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        // Send request.
        $response = curl_exec($ch);
        // echo 'returned: ' . print_r($response);
        $error = curl_error($ch);
        $status = curl_getinfo($ch);
        //echo 'status: ' . print_r($status);
        $status_code = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        if ($status_code == '201') {
            $oldKey = get_post_meta($postId, "_tokenpile_product_key");
            $result = json_decode($response, true);
            $pId = $result['id'];
            //echo 'setting pid for ' . $postId . ' to ' . $pId;
            update_post_meta($postId, '_tokenpile_product_key', $pId, $oldKey);
            $result['type'] = "success";
            $result['message'] = $pId;
        }else{
            $result['type'] = "error";
            $result['message'] = $error;            
        }

        curl_close($ch);
        if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            $result = json_encode($result);
            echo $result;
        }
        else {
            header("Location: ".$_SERVER["HTTP_REFERER"]);
        }
        
        die();
    }

    public function tokenpile_debug_update_post()
    {
        $result = array();
        
        $postId = $_POST['post_id'];
        $post = get_post($postId);
        $pId = get_post_meta($postId, '_tokenpile_product_key', true);
        $ch = curl_init($this->url . "/wp-json/wc/v2/products/" . $pId);
        $postUrl = '<a href="' . esc_url(get_permalink($postId)) . '">Read ' . $post->post_title . '</a>';
        //echo 'post link: ' . $postUrl;
        $array = array(
            "name" => $post->post_title,
            "type" => "simple",
            "sku" => $post->slug,
            "shipping_required" => false,
            "virtual" => true,
            "description" => $postUrl,
            "regular_price" => '$' . get_option($this->option_name . '_price'),
            "vendor" => get_option($this->option_name . '_user')
        );
        // Setup request to send json via POST.
        $payload = json_encode($array);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_USERPWD, get_option($this->option_name . '_key') . ':' . get_option($this->option_name . '_secret'));
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type:application/json'
        ));
        // Return response instead of printing.
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        // Send request.
        $response = curl_exec($ch);
        //echo 'returned: ' . print_r($response);
        $error = curl_error($ch);
        $status = curl_getinfo($ch);
        //echo 'status: ' . print_r($status);
        $status_code = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        if ($status_code == '200') {
            $result['type'] = "success";
            $result['message'] = "Post updated";
        }else{
            $result['type'] = "error";
            $result['message'] = $error;
        }
        
        curl_close($ch);
        if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            $result = json_encode($result);
            echo $result;
        }
        else {
            header("Location: ".$_SERVER["HTTP_REFERER"]);
        }
        
        die();
    }
    
    public function tokenpile_debug_delete_post()
    {
        $result = array();
        
        $post_id = $_POST['post_id'];
        $pId = get_post_meta($post_id, '_tokenpile_product_key', true);
        $ch = curl_init($this->url . "/wp-json/wc/v2/products/" . $pId);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_USERPWD, get_option($this->option_name . '_key') . ':' . get_option($this->option_name . '_secret'));
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
        curl_exec($ch);
        $response = curl_exec($ch);
        //echo 'returned: ' . print_r($response);
        $error = curl_error($ch);
        $status = curl_getinfo($ch);
        //echo 'status: ' . print_r($status);
        $status_code = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        if ($status_code == '201') {
            delete_post_meta($post_id, '_tokenpile_product_key', true);
            $result['type'] = "success";
            $result['message'] = "Post deleted";
        }else{
            $result['type'] = "error";
            $result['message'] = $error;
        }
        curl_close($ch);
        if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            $result = json_encode($result);
            echo $result;
        }
        else {
            header("Location: ".$_SERVER["HTTP_REFERER"]);
        }
        
        die();
    }
    
    public function tokenpile_test_ajax(){
        $post_id = $_GET['post_id'];
        //         if ( !wp_verify_nonce( $_REQUEST['nonce'], "tokenpile_nonce")) {
//             exit("No naughty business please");
//         }
        $result = array();
        $result['type'] = "success";
        $result['message'] = $post_id;
        if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            $result = json_encode($result);
            echo $result;
        }
        else {
            header("Location: ".$_SERVER["HTTP_REFERER"]);
        }
        
        die();
    }

    public function tokenpile_custom_box_html($post)
    {
       $selected_post_types = get_option($this->option_name . '_post_types');
       if (! empty($selected_post_types) && in_array($post->post_type, $selected_post_types)) {
            
//            error_log('in tokenpile_custom_box_html, ');
            $globalSyncPosts = get_option($this->option_name . '_auto');
            $globalSyncValue = $globalSyncPosts ? 'True' : 'False';
            $productKey = get_post_meta($post->ID, '_tokenpile_product_key', true);
            $shouldSync = get_post_meta($post->ID, '_tokenpile_should_sync_key', true);        
            #$nonce = wp_create_nonce("tokenpile_nonce");
            #$link = admin_url('admin-ajax.php?action=tokenpile_test_ajax&post_id='.$post->ID.'&nonce='.$nonce);
            echo '<div id="tokenpile_product_key"><label>Product Key: ' . $productKey . '</label></div>';
            echo '<div id="tokenpile_global_sync"><label>Global Sync Enabled: ' . $globalSyncValue . '</label></div>';
            ?>
    <div id='test_message'></div><br>
    		<div>
    		<input type="hidden" name="tokenpile_should_sync_default" id="tokenpile_should_sync_default" value="0" /> 
    		<input type="checkbox" name="tokenpile_should_sync" id="tokenpile_should_sync"
    		<?php
    
            if ($shouldSync && ! $productKey) {
                echo " checked='1' value='1'";
            } elseif ($shouldSync == "" && $globalSyncPosts) {
                echo " checked='1' value='1'";
            }
            ?>>
            <label for="tokenpile_should_sync">Should Sync</label> 
            </div>
    	<script type="text/javascript">
    	jQuery(document).ready(function($) {
    // 		jQuery('#tokenpile_test_ajax_button').click(function(){
    // 		    $.ajax({
    // 		        url: ajaxurl,
    // 		        dataType: 'json',
    // 		        data: {
    // 		            action     : 'tokenpile_test_ajax', // load function hooked to: "wp_ajax_*" action hook
    //					'post_id': <?php echo $post->ID; ?>
    // 		        },
    // 		        success: function(response) {
    // 		            if(response.type == "success") {
    // 		               jQuery("#test_message").text(response.message)
    // 		            }
    // 		            else {
    // 		               alert(response)
    // 		            }
    // 		         },
    // 		        error: function(response) {
    // 		               alert(response)
    // 		         }
    // 		    });
    //		});
    		jQuery('#tokenpile_sync_post_button').click(function() { //start function when any update link is clicked
    				jQuery.ajax({
    					url: ajaxurl,
    			        dataType: 'json',
    					data: {
    							'action': 'tokenpile_debug_sync_post',
    							'post_id': <?php echo $post->ID; ?>
    						}, 
    					success: function(response) {
        					jQuery("#tokenpile_product_key").text('<label>Product Key: ' + response.message + '</label>');
        					//alert('Got this from the server: ' + response);
    					},
    			        error: function(response) {
    			               alert(response)
    			         }
    				});
    		});
    		jQuery('#tokenpile_update_post_button').click(function() { //start function when any update link is clicked
    				jQuery.ajax({
    					url: ajaxurl,
    			        dataType: 'json',
    					data: {
    							'action': 'tokenpile_debug_update_post',
    							'post_id': <?php echo $post->ID; ?>
    						}, 
    					success: function(response) {
    			               jQuery("#test_message").text(response.message)
        					//alert('Got this from the server: ' + response);
    					},
    			        error: function(response) {
    			               alert(response)
    			         }
    				});
    		});
    		jQuery('#tokenpile_delete_post_button').click(function() { //start function when any update link is clicked
    				jQuery.ajax({
    					url: ajaxurl,
    			        dataType: 'json',
    					data: {
    						'action': 'tokenpile_debug_delete_post',
    						'post_id': <?php echo $post->ID; ?>
    					}, 
    					success: function(response) {
    			               jQuery("#test_message").text(response.message)
        					//alert('Got this from the server: ' + response);
    					},
    			        error: function(response) {
    			               alert(response)
    			         }
    				});
    		});
    	});
    	</script>
    		<?php
            if ($productKey) {
                ?>
                <div><button id="tokenpile_sync_post_button" style="display: none;width: 90%;">Publish Post</button></div>
                <div><button id="tokenpile_update_post_button" style="display: block;width: 90%;">Update Post</button></div>
                <div><button id="tokenpile_delete_post_button" style="display: block;width: 90%;">Unpublish Post</button></div><?php
            } else {
                ?>
                <div><button id="tokenpile_sync_post_button" style="display: block;width: 90%;">Publish Post</button></div>
                <div><button id="tokenpile_update_post_button" style="display: none;width: 90%;">Update Post</button></div>
                <div><button id="tokenpile_delete_post_button" style="display: none;width: 90%;">Unpublish Post</button></div><?php
            }
       }else{
           echo '<div><label>Tokenpile sync disabled for this content type.</div>';
           
       }
    }

    public function tokenpile_add_custom_box()
    {
        $names = get_post_types();
        foreach ($names as $name) {
            add_meta_box('tokenpile_box_id',
                __( 'TokenPile Post Settings', 'textdomain' ),
                array( $this, 'tokenpile_custom_box_html' ),
                $name,
                'side',
                'default'
                );
//             add_meta_box('tokenpile_box_id', // Unique ID
//             'TokenPile Post Settings', // Box title
//             array(
//                 $this,
//                 'tokenpile_custom_box_html'
//             ), // Content callback, must be of type callable
//             $name // Post type
//             );
        }
    }

    public function register_setting()
    {
        // Add a General section
        add_settings_section($this->option_name . '_general', __('General', 'tokenpile_client'), array(
            $this,
            $this->option_name . '_general_cb'
        ), $this->tokenpile_plugin_name);

        add_settings_field($this->option_name . '_auto', __('Submit new posts automatically', 'tokenpile_client'), array(
            $this,
            $this->option_name . '_submit_cb'
        ), $this->tokenpile_plugin_name, $this->option_name . '_general', array(
            'label_for' => $this->option_name . '_auto'
        ));

        add_settings_field($this->option_name . '_user', __('TokenPile User ID', 'tokenpile_client'), array(
            $this,
            $this->option_name . '_user_cb'
        ), $this->tokenpile_plugin_name, $this->option_name . '_general', array(
            'label_for' => $this->option_name . '_user'
        ));

        add_settings_field($this->option_name . '_key', __('TokenPile User Key', 'tokenpile_client'), array(
            $this,
            $this->option_name . '_key_cb'
        ), $this->tokenpile_plugin_name, $this->option_name . '_general', array(
            'label_for' => $this->option_name . '_key'
        ));

        add_settings_field($this->option_name . '_secret', __('TokenPile Secret Key', 'tokenpile_client'), array(
            $this,
            $this->option_name . '_secret_cb'
        ), $this->tokenpile_plugin_name, $this->option_name . '_general', array(
            'label_for' => $this->option_name . '_secret'
        ));

        add_settings_field($this->option_name . '_price', __('Price Per Post $', 'tokenpile_client'), array(
            $this,
            $this->option_name . '_price_cb'
        ), $this->tokenpile_plugin_name, $this->option_name . '_general', array(
            'label_for' => $this->option_name . '_price'
        ));

        add_settings_field($this->option_name . '_post_types', __('Show on post types', 'tokenpile_client'), array(
            $this,
            $this->option_name . '_post_types_cb'
        ), $this->tokenpile_plugin_name, $this->option_name . '_general', array(
            'label_for' => $this->option_name . '_post_types'
        ));

        add_settings_field($this->option_name . '_auto', __('Show icon', 'tokenpile_client'), array(
            $this,
            $this->option_name . '_icon_cb'
        ), $this->tokenpile_plugin_name, $this->option_name . '_general', array(
            'label_for' => $this->option_name . '_auto'
        ));
        
        $args = array(
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default' => NULL
        );
        register_setting($this->tokenpile_plugin_name, $this->option_name . '_auto', 'boolval');
        register_setting($this->tokenpile_plugin_name, $this->option_name . '_user', 'strval', $args);
        register_setting($this->tokenpile_plugin_name, $this->option_name . '_key', 'strval', $args);
        register_setting($this->tokenpile_plugin_name, $this->option_name . '_secret', 'strval', $args);
        register_setting($this->tokenpile_plugin_name, $this->option_name . '_price', 'strval', $args);
        register_setting($this->tokenpile_plugin_name, $this->option_name . '_post_types');
        register_setting($this->tokenpile_plugin_name, $this->option_name . '_icon', 'boolval');
    }

    /**
     * Render the text for the general section
     *
     * @since 0.9.0b
     */
    public function tokenpile_client_general_cb()
    {
        echo '<p>' . __('Please change the settings accordingly.', 'tokenpile_client') . '</p>';
    }

    /**
     * Render the radio input field for position option
     *
     * @since 0.9.0b
     */
    public function tokenpile_client_submit_cb()
    {
        $auto = get_option($this->option_name . '_auto');
        ?>
<input type="checkbox" name="<?php echo $this->option_name . '_auto' ?>"
	id="<?php echo $this->option_name . '_auto' ?>"
	<?php if ($auto == '1') echo "checked='checked'"; ?>>
<?php
    }

    /**
     * Render the treshold day input for this plugin
     *
     * @since 0.9.0b
     */
    public function tokenpile_client_user_cb()
    {
        echo '<input type="text" name="' . $this->option_name . '_user' . '" id="' . $this->option_name . '_user' . '" value="' . get_option($this->option_name . '_user') . '"> ';
    }

    public function tokenpile_client_secret_cb()
    {
        echo '<input type="text" name="' . $this->option_name . '_secret' . '" id="' . $this->option_name . '_secret' . '" value="' . get_option($this->option_name . '_secret') . '"> ';
    }

    public function tokenpile_client_key_cb()
    {
        echo '<input type="text" name="' . $this->option_name . '_key' . '" id="' . $this->option_name . '_key' . '" value="' . get_option($this->option_name . '_key') . '"> ';
    }

    public function tokenpile_client_price_cb()
    {
        echo '<input type="text" name="' . $this->option_name . '_price' . '" id="' . $this->option_name . '_price' . '" value="' . get_option($this->option_name . '_price') . '"> ';
    }

    public function tokenpile_client_post_types_cb()
    {
        $names = get_post_types();
        $selected_post_types = get_option($this->option_name . '_post_types');
        echo '<select name=' . $this->option_name . '_post_types' . '[] multiple>';
        foreach ($names as $name) {
            if (! empty($selected_post_types) && in_array($name, $selected_post_types)) {
                echo '<option value="' . $name . '" selected>' . $name . '</option>';
            } else {
                echo '<option value="' . $name . '" >' . $name . '</option>';
            }
        }
        echo '</select>';
    }
    
    public function tokenpile_client_icon_cb()
    {
        $icon = get_option($this->option_name . '_icon');
        ?>
<input type="checkbox" name="<?php echo $this->option_name . '_icon' ?>"
	id="<?php echo $this->option_name . '_icon' ?>"
	<?php if ($icon == '1') echo "checked='checked'"; ?>>
<?php
    }
    
}
