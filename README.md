# Require Featured Image

A straightforward WordPress plugin that enforces the addition of a featured image to posts before they can be published.

## Description

This plugin ensures that posts have appropriately sized featured images before they can be published. It provides both server-side validation and real-time JavaScript checks in the post editor.

### Features

- Prevents publishing posts without featured images
- Supports minimum image size requirements
- Works with both Classic and Gutenberg editors
- Configurable post type support
- Customizable minimum image dimensions
- Real-time validation in the post editor

## Installation

1. Upload the plugin files to the `/wp-content/plugins/require-featured-image` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Configure the plugin settings under Settings > Req Featured Image

## Configuration

### Post Types

By default, the plugin works only with Posts. You can enable it for other post types that support featured images through the settings page.

### Minimum Image Size

You can set minimum dimensions for featured images:
- Default width: 800px
- Default height: 600px

Set both values to 0 to accept any image size.

## Usage

1. Create or edit a post
2. Attempt to publish without a featured image
3. The plugin will:
   - Display a warning message
   - Disable the publish button
   - Prevent publishing until a valid featured image is set

## Technical Details

The plugin consists of three main components:

- `require-featured-image.php`: Core plugin functionality
- `admin-options.php`: Settings page implementation
- `require-featured-image-on-edit.js`: Client-side validation

## Requirements

- WordPress 5.0 or higher
- PHP 7.0 or higher

## License

This project is licensed under the GPL v2 or later.

## Author

Mike Kipruto
- Website: [kipmyk.co.ke](https://kipmyk.co.ke/)
- GitHub: [kipmyk](https://github.com/kipmyk)

## Support

Please use the [GitHub issues page](https://github.com/kipmyk/require-featured-image/issues) to report bugs or request features.
