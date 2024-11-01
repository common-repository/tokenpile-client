<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       tokenpile.com
 * @since      0.9.0b
 *
 * @package    Tokenpile_client
 * @subpackage Tokenpile_client/admin/partials
 */
?>

<!-- This file should primarily consist of HTML with a little bit of PHP. -->
<div class="wrap">
	<h2><?php echo esc_html( get_admin_page_title() ); ?></h2>
	    <?php
	    $user = get_option($this->option_name . '_user');
    // if no user in settings
    if ($user == null) {
        echo '<button onclick="window.open(\'https://www.tokenpile.com/vendor-registration/\')">Click to Create a TokenPile Account</button>';
    }
    ?>
	<form action="options.php" method="post">
	        <?php
        settings_fields($this->tokenpile_plugin_name);
        do_settings_sections($this->tokenpile_plugin_name);
        submit_button();
        ?>
	    </form>
	<form action="<?php echo admin_url( 'admin-post.php' ); ?>">
            		<input type="hidden" name="action" value="tokenpile_sync_all_posts">
<?php submit_button( 'Click to resync posts' ); ?>
</form>
</div>