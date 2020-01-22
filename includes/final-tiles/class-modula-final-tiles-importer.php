<?php
// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

class Modula_Final_Tiles_Importer {

    /**
     * Holds the class object.
     *
     * @var object
     *
     * @since 1.0.0
     */
    public static $instance;

    /**
     * Primary class constructor.
     *
     * @since 1.0.0
     */
    public function __construct() {

        // Add AJAX
        add_action('wp_ajax_modula_importer_final_tiles', array($this, 'final_tiles_gallery_import'));

    }

    /**
     * Get all Final Tiles Galleries
     *
     * @return mixed
     *
     * @since 1.0.0
     */
    public function get_galleries() {

        global $wpdb;
        if(!$wpdb->get_var("SHOW TABLES LIKE '".$wpdb->prefix."finaltiles_gallery'")){
            return false;
        }

        $galleries = $wpdb->get_results(" SELECT * FROM " . $wpdb->prefix . "finaltiles_gallery");
        if (count($galleries) != 0) {
            return $galleries;
        }

        return false;
    }

    /**
     * Imports a gallery from Final Tiles into Modula
     *
     * @since 1.0.0
     */
    public function final_tiles_gallery_import($gallery_id = '') {

        global $wpdb;

        // Set max execution time so we don't timeout
        ini_set('max_execution_time', 0);
        set_time_limit(0);

        // If no gallery ID, get from AJAX request
        if (empty($gallery_id)) {

            // Run a security check first.
            check_ajax_referer('modula-importer', 'nonce');

            if (!defined('ABSPATH')) {
                define('ABSPATH', dirname(__FILE__) . '/');
            }

            if (!isset($_POST['id'])) {
                $this->modula_import_result(false, __('No gallery was selected', 'modula-importer'));
            }

            $gallery_id = absint($_POST['id']);

        }

        // Get images from Final Tiles
        $sql    = $wpdb->prepare("SELECT * FROM " . $wpdb->prefix . "finaltiles_gallery_images
    						WHERE gid = %d
    						ORDER BY 'setOrder' ASC",
            $gallery_id);
        $images = $wpdb->get_results($sql);

        // Get gallery configuration
        $sql     = $wpdb->prepare("SELECT * FROM " . $wpdb->prefix . "finaltiles_gallery
    						WHERE id = %d",
            $gallery_id);
        $gallery = $wpdb->get_row($sql);

        $gallery_config = json_decode($gallery->configuration);

        // Build Modula Gallery modula-images metadata
        $modula_images = array();
        if (is_array($images) && count($images) > 0) {
            // Add each image to Media Library
            foreach ($images as $image) {

                $modula_images[] = array(
                    'id'          => $image->imageId,
                    'alt'         => $image->alt,
                    'title'       => $image->title,
                    'description' => $image->description,
                    'halign'      => 'center',
                    'valign'      => 'middle',
                    'link'        => $image->link,
                    'target'      => '',
                    'width'       => 2,
                    'height'      => 2,
                    'filters'     => ''
                );
            }
        }

        if (count($modula_images) == 0) {
            $this->modula_import_result(false, __('No images found in gallery. Skipping gallery...', 'modula-importer'));
        }

        // Get Modula Gallery defaults, used to set modula-settings metadata
        $modula_settings = Modula_CPT_Fields_Helper::get_defaults();


        // Create Modula CPT
        $modula_gallery_id = wp_insert_post(array(
            'post_type'   => 'modula-gallery',
            'post_status' => 'publish',
            'post_title'  => $gallery_config->name,
        ));

        // Attach meta modula-settings to Modula CPT
        update_post_meta($modula_gallery_id, 'modula-settings', $modula_settings);

        // Attach meta modula-images to Modula CPT
        update_post_meta($modula_gallery_id, 'modula-images', $modula_images);

        $importer_settings = get_option('modula_importer');

        if (!isset($importer_settings['galleries'])) {
            $importer_settings['galleries'] = array();
        }

        // Remember that this gallery has been imported
        $importer_settings['galleries']['final_tiles'][$gallery_id] = $modula_gallery_id;
        update_option('modula_importer', $importer_settings);

        $ftg_shortcode    = '[FinalTilesGallery id="' . $gallery_id . '"]';
        $modula_shortcode = '[modula id="' . $modula_gallery_id . '"]';

        // Replace Final Tiles Grid Gallery shortcode with Modula Shortcode in Posts, Pages and CPTs
        $sql = $wpdb->prepare("UPDATE " . $wpdb->prefix . "posts SET post_content = REPLACE(post_content, '%s', '%s')",
            $ftg_shortcode, $modula_shortcode);
        $wpdb->query($sql);

        $this->modula_import_result(true, __('Migrated!', 'modula-importer'));
    }


    /**
     * Returns result
     *
     * @param $success
     * @param $message
     *
     * @since 1.0.0
     */
    public function modula_import_result($success, $message) {
        echo json_encode(array(
            'success' => (bool)$success,
            'message' => (string)$message,
        ));
        die;
    }


    /**
     * Returns the singleton instance of the class.
     *
     * @since 1.0.0
     */
    public static function get_instance() {

        if (!isset(self::$instance) && !(self::$instance instanceof Modula_Final_Tiles_Importer)) {
            self::$instance = new Modula_Final_Tiles_Importer();
        }

        return self::$instance;

    }

}

// Load the class.
$modula_final_tiles_importer = Modula_Final_Tiles_Importer::get_instance();