<?php
// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

class Modula_Photoblocks_Importer {

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
        add_action('wp_ajax_modula_importer_photoblocks', array($this, 'photoblocks_gallery_import'));
        add_action('wp_ajax_modula_importer_photoblocks_update_imported', array($this, 'update_imported'));

    }

    /**
     * Get all Gallery PhotoBlocks galleries
     *
     * @return mixed
     *
     * @since 1.0.0
     */
    public function get_galleries() {

        global $wpdb;

        if(!$wpdb->get_var("SHOW TABLES LIKE '".$wpdb->prefix . "photoblocks'")){
            return false;
        }
        $galleries = $wpdb->get_results(" SELECT * FROM " . $wpdb->prefix . "photoblocks");
        if (count($galleries) != 0) {
            return $galleries;
        }

        return false;
    }

    /**
     * Imports a gallery from PhotoBlocks into Modula
     *
     * @since 1.0.0
     */
    public function photoblocks_gallery_import($gallery_id = '') {

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

        // Get gallery
        $sql     = $wpdb->prepare("SELECT * FROM " . $wpdb->prefix . "photoblocks
    						WHERE id = %d LIMIT 1",
            $gallery_id);
        $gallery = $wpdb->get_row($sql);

        $gallery_blocks = json_decode($gallery->blocks);
        $gallery_data   = json_decode($gallery->data);
        $images         = array();

        foreach ($gallery_blocks as $block) {

            if (NULL != $block->image->id) {

                $images[] = array(
                    'id'          => $block->image->id,
                    'description' => (NULL != $block->caption->description->text) ? $block->caption->description->text : '',
                    'title'       => (NULL != $block->caption->title->text) ? $block->caption->title->text : '',
                    'alt'         => (NULL != $block->image->alt) ? $block->image->alt : '',
                    'link'        => (NULL != $block->click->link) ? $block->click->link : ''
            );
            }
        }

        // Build Modula Gallery modula-images metadata
        $modula_images = array();
        if (is_array($images) && count($images) > 0) {
            // Add each image to Media Library
            foreach ($images as $image) {
                $image_src = wp_get_attachment_image_src($image['id'],'full');
                $modula_images[] = array(
                    'id'          => $image['id'],
                    'alt'         => $image['alt'],
                    'title'       => $image['title'],
                    'description' => $image['description'],
                    'halign'      => 'center',
                    'valign'      => 'middle',
                    'link'        => $image['link'],
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
            'post_title'  => $gallery_data->name,
        ));


        // Attach meta modula-settings to Modula CPT
        update_post_meta($modula_gallery_id, 'modula-settings', $modula_settings);

        // Attach meta modula-images to Modula CPT
        update_post_meta($modula_gallery_id, 'modula-images', $modula_images);

        $ftg_shortcode    = '[photoblocks id=' . $gallery_id . ']';
        $modula_shortcode = '[modula id="' . $modula_gallery_id . '"]';

        // Replace Gallery PhotoBlocks shortcode with Modula Shortcode in Posts, Pages and CPTs
        $sql = $wpdb->prepare("UPDATE " . $wpdb->prefix . "posts SET post_content = REPLACE(post_content, '%s', '%s')",
            $ftg_shortcode, $modula_shortcode);
        $wpdb->query($sql);

        if('delete' == $_POST['clean']){
            $this->clean_entries($gallery_id);
        }

        $this->modula_import_result(true, __('Migrated!', 'modula-importer'));
    }

    /**
     * Update imported galleries
     *
     * @since 1.0.0
     * @param array $galleries
     */
    public function update_imported() {

        check_ajax_referer('modula-importer', 'nonce');
        $galleries         = $_POST['galleries'];
        $importer_settings = get_option('modula_importer');

        if(!is_array($importer_settings)){
            $importer_settings = array();
        }

        if (!isset($importer_settings['galleries']['photoblocks'])) {
            $importer_settings['galleries']['photoblocks'] = array();
        }

        $galleries = array_merge($importer_settings['galleries']['photoblocks'], $galleries);

        // Remember that this gallery has been imported
        $importer_settings['galleries']['photoblocks'] = $galleries;
        update_option('modula_importer', $importer_settings);

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

        if (!isset(self::$instance) && !(self::$instance instanceof Modula_Photoblocks_Importer)) {
            self::$instance = new Modula_Photoblocks_Importer();
        }

        return self::$instance;

    }


    /**
     * Delete old entries from database
     *
     * @since 1.0.0
     * @param $gallery_id
     */
    public function clean_entries($gallery_id){
        global $wpdb;
        $sql      = $wpdb->prepare( "DELETE FROM  ".$wpdb->prefix ."photoblocks WHERE id = $gallery_id" );
        $wpdb->query( $sql );
        $wpdb->query( $sql_meta );
    }

}

// Load the class.
$modula_photoblocks_importer = Modula_Photoblocks_Importer::get_instance();