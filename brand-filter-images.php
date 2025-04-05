<?php
/*
 * Plugin Name: Nokytech Brand Filter Images
 * Plugin URI:  https://nokytech.com/
 * Description: Replaces brand text labels with images in the product filter.
 * Version:     1.0
 * Author:      walid nokytech
 * Author URI:  https://nokytech.com/
 * License:     GPL2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: brand-filter-images
 * Domain Path: /languages
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Brand_Filter_Images_Plugin {

    private $taxonomy = 'pa_marque'; // Update this if your taxonomy slug is different

    public function __construct() {
        // Hook into WordPress
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        add_action('init', array($this, 'add_term_meta_fields'));
    }

    public function enqueue_scripts() {
        // Only enqueue on shop and product archive pages
        if (!is_shop() && !is_product_taxonomy() && !is_product_category()) {
            return;
        }

        // Enqueue the JavaScript file
        wp_enqueue_script(
            'brand-filter-images-js',
            plugin_dir_url(__FILE__) . 'js/brand-filter-images.js',
            array('jquery'),
            '1.0',
            true
        );

        // Enqueue the CSS file
        wp_enqueue_style(
            'brand-filter-images-css',
            plugin_dir_url(__FILE__) . 'css/style.css',
            array(),
            '1.0'
        );

        // Get all terms in the taxonomy
        $terms = get_terms(array(
            'taxonomy' => $this->taxonomy,
            'hide_empty' => false,
        ));

        $brand_images = array();

        if (!empty($terms) && !is_wp_error($terms)) {
            foreach ($terms as $term) {
                $brand_name = $term->name;

                // Get the image URL from term meta (assumes 'brand_image' meta key)
                $image_url = get_term_meta($term->term_id, 'brand_image', true);

                if ($image_url) {
                    $brand_images[$brand_name] = $image_url;
                }
            }
        }

        // Pass data to JavaScript
        wp_localize_script(
            'brand-filter-images-js',
            'brandFilterData',
            array('brandImages' => $brand_images)
        );
    }

    public function enqueue_admin_scripts($hook_suffix) {
        // Enqueue scripts only on term add/edit pages
        if ('edit-tags.php' !== $hook_suffix && 'term.php' !== $hook_suffix) {
            return;
        }

        // Enqueue WordPress media scripts for the media uploader
        wp_enqueue_media();

        // Enqueue admin script for media uploader
        wp_enqueue_script(
            'brand-filter-images-admin-js',
            plugin_dir_url(__FILE__) . 'js/brand-filter-images-admin.js',
            array('jquery'),
            '1.0',
            true
        );
    }

    public function add_term_meta_fields() {
        // Add fields to add/edit term pages
        add_action("{$this->taxonomy}_edit_form_fields", array($this, 'edit_brand_image_field'), 10, 2);
        add_action("{$this->taxonomy}_add_form_fields", array($this, 'add_brand_image_field'), 10, 2);

        // Save term meta data
        add_action("edited_{$this->taxonomy}", array($this, 'save_brand_image_field'), 10, 2);
        add_action("created_{$this->taxonomy}", array($this, 'save_brand_image_field'), 10, 2);
    }

    public function add_brand_image_field($taxonomy) {
        // Display the field on the add term page
        ?>
        <div class="form-field">
            <label for="brand_image"><?php _e('Brand Image', 'brand-filter-images'); ?></label>
            <input type="text" name="brand_image" id="brand_image" value="" />
            <button class="button brand-image-upload"><?php _e('Upload Image', 'brand-filter-images'); ?></button>
            <p class="description"><?php _e('Upload or enter the URL of the brand image.', 'brand-filter-images'); ?></p>
        </div>
        <?php
    }

    public function edit_brand_image_field($term, $taxonomy) {
        // Display the field on the edit term page
        $value = get_term_meta($term->term_id, 'brand_image', true);
        ?>
        <tr class="form-field">
            <th scope="row"><label for="brand_image"><?php _e('Brand Image', 'brand-filter-images'); ?></label></th>
            <td>
                <input type="text" name="brand_image" id="brand_image" value="<?php echo esc_attr($value); ?>" />
                <button class="button brand-image-upload"><?php _e('Upload Image', 'brand-filter-images'); ?></button>
                <p class="description"><?php _e('Upload or enter the URL of the brand image.', 'brand-filter-images'); ?></p>
            </td>
        </tr>
        <?php
    }

    public function save_brand_image_field($term_id, $tt_id) {
        if (isset($_POST['brand_image'])) {
            update_term_meta($term_id, 'brand_image', esc_url_raw($_POST['brand_image']));
        }
    }
}

new Brand_Filter_Images_Plugin();
