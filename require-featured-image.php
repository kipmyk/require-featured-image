<?php
/*
Plugin Name: Require Featured Image
Plugin URI: https://github.com/kipmyk/require-featured-image
Description: A straightforward WordPress plugin that enforces the addition of a featured image to a post before it can be published.
Author: Mike Kipruto
Version: 1.0.0
Author URI: https://kipmyk.co.ke/
Text Domain: require-featured-image
*/

class RequireFeaturedImage {

    private $default_minimum_size = ['width' => 800, 'height' => 600];

    public function __construct() {
        require_once('admin-options.php');

        // Hook actions
        add_action('transition_post_status', [$this, 'guard'], 10, 3);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_edit_screen_js']);
        add_action('plugins_loaded', [$this, 'load_textdomain']);

        // Activation hook
        register_activation_hook(__FILE__, [$this, 'set_default_on_activation']);
    }

    /**
     * Prevents publishing if the featured image is missing or too small.
     */
    public function guard($new_status, $old_status, $post) {
        if (isset($_GET['_locale']) && $_GET['_locale'] === 'user') {
            return;
        }

        if ($new_status === 'publish' && $this->should_stop_post_publishing($post)) {
            if ($old_status === 'publish') {
                $old_status = 'draft';
            }
            $post->post_status = $old_status;
            wp_update_post($post);
            wp_die($this->get_warning_message());
        }
    }

    /**
     * Enqueues JavaScript on the edit screen for post types that require featured images.
     */
    public function enqueue_edit_screen_js($hook) {
        global $post;
        if ($hook !== 'post.php' && $hook !== 'post-new.php') {
            return;
        }

        if ($this->is_supported_post_type($post) && $this->is_in_enforcement_window($post)) {
            wp_register_script('rfi-admin-js', plugins_url('/require-featured-image-on-edit.js', __FILE__), ['jquery']);
            wp_enqueue_script('rfi-admin-js');

            $minimum_size = get_option('rfi_minimum_size', $this->default_minimum_size);

            wp_localize_script(
                'rfi-admin-js',
                'passedFromServer',
                [
                    'jsWarningHtml' => __('<strong>This entry has no featured image.</strong> Please set one. You need to set a featured image before publishing.', 'require-featured-image'),
                    'jsSmallHtml' => sprintf(
                        __('<strong>This entry has a featured image that is too small.</strong> Please use an image that is at least %s x %s pixels.', 'require-featured-image'),
                        $minimum_size['width'],
                        $minimum_size['height']
                    ),
                    'width' => $minimum_size['width'],
                    'height' => $minimum_size['height'],
                ]
            );
        }
    }

    /**
     * Loads the plugin's text domain for translations.
     */
    public function load_textdomain() {
        load_plugin_textdomain('require-featured-image', false, dirname(plugin_basename(__FILE__)) . '/lang');
    }

    /**
     * Sets default options on plugin activation.
     */
    public function set_default_on_activation() {
        add_option('rfi_post_types', ['post']);
        add_option('rfi_enforcement_start', time() - 86400);
    }

    /**
     * Determines if publishing should be stopped due to missing or insufficient featured image.
     */
    private function should_stop_post_publishing($post) {
        return $this->is_supported_post_type($post) &&
               $this->is_in_enforcement_window($post) &&
               !$this->post_has_large_enough_image($post);
    }

    /**
     * Checks if the post type is in the supported list.
     */
    private function is_supported_post_type($post) {
        $supported_types = get_option('rfi_post_types', ['post']);
        return in_array($post->post_type, $supported_types, true);
    }

    /**
     * Checks if the post date is within the enforcement window.
     */
    private function is_in_enforcement_window($post) {
        return strtotime($post->post_date) > $this->get_enforcement_start_time();
    }

    /**
     * Returns the enforcement start time.
     */
    private function get_enforcement_start_time() {
        return (int) get_option('rfi_enforcement_start', time() - 86400 * 14);
    }

    /**
     * Checks if the post has a featured image large enough based on minimum size requirements.
     */
    private function post_has_large_enough_image($post) {
        $image_id = get_post_thumbnail_id($post->ID);
        if (!$image_id) {
            return false;
        }

        $image_meta = wp_get_attachment_image_src($image_id, 'full');
        if (!$image_meta) {
            return false;
        }

        $width = $image_meta[1];
        $height = $image_meta[2];
        $minimum_size = get_option('rfi_minimum_size', $this->default_minimum_size);

        return $width >= $minimum_size['width'] && $height >= $minimum_size['height'];
    }

    /**
     * Generates the warning message when publishing is blocked.
     */
    private function get_warning_message() {
        $minimum_size = get_option('rfi_minimum_size', $this->default_minimum_size);

        if ($minimum_size['width'] === 0 && $minimum_size['height'] === 0) {
            return __('You cannot publish without a featured image.', 'require-featured-image');
        }

        return sprintf(
            __('You cannot publish without a featured image that is at least %s x %s pixels.', 'require-featured-image'),
            $minimum_size['width'],
            $minimum_size['height']
        );
    }
}

// Initialize the plugin
new RequireFeaturedImage();