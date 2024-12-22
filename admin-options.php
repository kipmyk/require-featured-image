<?php
class RequireFeaturedImageAdminOptions {

    public function __construct() {
        // Hook actions
        add_action('admin_menu', [$this, 'add_admin_page']);
        add_action('admin_init', [$this, 'initialize_admin_settings']);
    }

    /**
     * Adds the options page to the WordPress admin menu.
     */
    public function add_admin_page() {
        add_options_page(
            __('Require Featured Image Settings', 'require-featured-image'),
            __('Req Featured Image', 'require-featured-image'),
            'manage_options',
            'rfi',
            [$this, 'render_options_page']
        );
    }

    /**
     * Renders the options page content.
     */
    public function render_options_page() {
        ?>
        <div class="wrap">
            <h2><?php _e('Require Featured Image', 'require-featured-image'); ?></h2>
            <form action="options.php" method="post">
                <?php
                settings_fields('rfi');
                do_settings_sections('rfi');
                ?>
                <input name="Submit" type="submit" value="<?php esc_attr_e('Save Changes', 'require-featured-image'); ?>" class="button button-primary" />
            </form>
        </div>
        <?php
    }

    /**
     * Initializes the admin settings.
     */
    public function initialize_admin_settings() {
        // Register settings
        register_setting('rfi', 'rfi_post_types');
        register_setting('rfi', 'rfi_minimum_size');

        // Add sections
        add_settings_section('rfi_main', __('Post Types', 'require-featured-image'), [$this, 'main_section_text'], 'rfi');
        add_settings_section('rfi_size', __('Image Size', 'require-featured-image'), [$this, 'size_section_text'], 'rfi');

        // Add fields
        add_settings_field('rfi_post_types', __('Post Types that require featured images', 'require-featured-image'), [$this, 'post_types_field'], 'rfi', 'rfi_main');
        add_settings_field('rfi_minimum_size', __('Minimum size of the featured images', 'require-featured-image'), [$this, 'size_option_field'], 'rfi', 'rfi_size');
    }

    /**
     * Output for the main section.
     */
    public function main_section_text() {
        _e('<p>You can specify the post type for Require Featured Image to work on. By default, it works on Posts only.</p><p>If you\'re not seeing a post type here that you think should be, it probably does not have support for featured images. Only post types that support featured images will appear on this list.</p>', 'require-featured-image');
    }

    /**
     * Output for the size section.
     */
    public function size_section_text() {
        _e('<p>The minimum acceptable size can be set for featured images. This size means that posts with images smaller than the specified dimensions cannot be published. By default, the sizes are zero, so any image size will be accepted.</p>', 'require-featured-image');
    }

    /**
     * Renders the post types input field.
     */
    public function post_types_field() {
        $option = get_option('rfi_post_types', []);
        $post_types = $this->get_post_types_with_featured_image_support();

        foreach ($post_types as $type => $obj) {
            $checked = in_array($type, $option) ? 'checked="checked"' : '';
            echo '<label><input type="checkbox" name="rfi_post_types[]" value="' . esc_attr($type) . '" ' . $checked . '> ' . esc_html($obj->label) . '</label><br>';
        }
    }

    /**
     * Renders the size option input fields.
     */
    public function size_option_field() {
        $dimensions = $this->get_min_dimensions();
        ?>
        <label>
            <input type="number" name="rfi_minimum_size[width]" value="<?php echo esc_attr($dimensions['width']); ?>"> <?php _e('width (px)', 'require-featured-image'); ?>
        </label><br>
        <label>
            <input type="number" name="rfi_minimum_size[height]" value="<?php echo esc_attr($dimensions['height']); ?>"> <?php _e('height (px)', 'require-featured-image'); ?>
        </label><br>
        <?php
    }

    /**
     * Retrieves post types that support featured images.
     */
    private function get_post_types_with_featured_image_support() {
        $post_types = get_post_types(['public' => true], 'objects');
        $supported_post_types = [];

        foreach ($post_types as $type => $obj) {
            if (post_type_supports($type, 'thumbnail')) {
                $supported_post_types[$type] = $obj;
            }
        }

        return $supported_post_types;
    }

    /**
     * Retrieves the minimum dimensions from the settings.
     */
    private function get_min_dimensions() {
        $minimum_size = get_option('rfi_minimum_size', ['width' => 0, 'height' => 0]);

        return [
            'width' => isset($minimum_size['width']) ? (int) $minimum_size['width'] : 0,
            'height' => isset($minimum_size['height']) ? (int) $minimum_size['height'] : 0,
        ];
    }
}

// Initialize the admin options
new RequireFeaturedImageAdminOptions();