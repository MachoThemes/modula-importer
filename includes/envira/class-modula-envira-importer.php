<?php
// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

class Modula_Envira_Importer {

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
        add_action('wp_ajax_modula_importer_envira_gallery_import', array($this, 'envira_gallery_import'));
        add_action('wp_ajax_modula_importer_envira_gallery_imported_update', array($this, 'update_imported'));

    }


    /**
     * Get all Envira Galleries
     *
     * @return mixed
     *
     * @since 1.0.0
     */
    public function get_galleries() {

        global $wpdb;

        $galleries = $wpdb->get_results(" SELECT * FROM " . $wpdb->prefix . "posts WHERE post_type = 'envira' AND post_status = 'publish'");

        if (count($galleries) != 0) {
            return $galleries;
        }

        return false;
    }

    /**
     * Imports a gallery from Envira into Modula
     *
     * @since 1.0.0
     */
    public function envira_gallery_import($gallery_id = '') {

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

        // Get all images attached to the gallery
        $modula_images = array();

        // get gallery data so we can get title, description and alt from envira
        $envira_gallery_data = get_post_meta($gallery_id, '_eg_gallery_data', true);
        if (isset($envira_gallery_data['gallery']) && count($envira_gallery_data['gallery']) > 0) {
            foreach ($envira_gallery_data['gallery'] as $imageID => $image) {

                $envira_image_title = (!isset($image['title']) || '' != $image['title']) ? $image['title'] : '';

                $envira_image_caption = (!isset($image['caption']) || '' != $image['caption']) ? $image['caption'] : wp_get_attachment_caption($imageID);

                $envira_image_alt = (!isset($image['alt']) || '' != $image['alt']) ? $image['alt'] : get_post_meta($imageID, '_wp_attachment_image_alt', TRUE);
                $envira_image_url = (!isset($image['link']) || '' != $image['link']) ? $image['link'] : '';


                $modula_images[] = array(
                    'id'          => $imageID,
                    'alt'         => $envira_image_alt,
                    'title'       => $envira_image_title,
                    'description' => $envira_image_caption,
                    'halign'      => 'center',
                    'valign'      => 'middle',
                    'link'        => $envira_image_url,
                    'target'      => '',
                    'width'       => 2,
                    'height'      => 2,
                    'filters'     => ''
                );

            }
        }

        if (count($modula_images) == 0) {
            // Trigger delete function if option is set to delete
            if('delete' == $_POST['clean']){
                $this->clean_entries($gallery_id);
            }
            $this->modula_import_result(false, __('No images found in gallery. Skipping gallery...', 'modula-importer'));
        }

        // Get Modula Gallery defaults, used to set modula-settings metadata
        $modula_settings = Modula_CPT_Fields_Helper::get_defaults();

        // Create Modula CPT
        $modula_gallery_id = wp_insert_post(array(
            'post_type'   => 'modula-gallery',
            'post_status' => 'publish',
            'post_title'  => get_the_title($gallery_id),
        ));

        // Attach meta modula-settings to Modula CPT
        update_post_meta($modula_gallery_id, 'modula-settings', $modula_settings);

        // Attach meta modula-images to Modula CPT
        update_post_meta($modula_gallery_id, 'modula-images', $modula_images);

        $envira_shortcodes = '[envira-gallery id="' . $gallery_id . '"]';
        $modula_shortcode  = '[modula id="' . $modula_gallery_id . '"]';

        // Replace Envira shortcode with Modula Shortcode in Posts, Pages and CPTs
        $sql = $wpdb->prepare("UPDATE " . $wpdb->prefix . "posts SET post_content = REPLACE(post_content, '%s', '%s')",
            $envira_shortcodes, $modula_shortcode);
        $wpdb->query($sql);

        // Trigger delete function if option is set to delete
        if('delete' == $_POST['clean']){
            $this->clean_entries($gallery_id);
        }

        $this->modula_import_result(true, wp_kses_post('<i class="imported-check dashicons dashicons-yes"></i>'));
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

        // first check if array
        if(!is_array($importer_settings)){
            $importer_settings = array();
        }

        if (!isset($importer_settings['galleries']['envira'])) {
            $importer_settings['galleries']['envira'] = array();
        }

        $galleries = array_merge($importer_settings['galleries']['envira'], $galleries);

        // Remember that this gallery has been imported
        $importer_settings['galleries']['envira'] = $galleries;
        update_option('modula_importer', $importer_settings);

        // Set url for migration complete
        $url = admin_url('edit.php?post_type=modula-gallery&page=modula&modula-tab=importer&migration=complete');

        if('delete' == $_POST['clean']){
            // Set url for migration and cleaning complete
            $url = admin_url('edit.php?post_type=modula-gallery&page=modula&modula-tab=importer&migration=complete&delete=complete');
        }

        echo $url;
        wp_die();
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

        if (!isset(self::$instance) && !(self::$instance instanceof Modula_Envira_Importer)) {
            self::$instance = new Modula_Envira_Importer();
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
        $sql      = $wpdb->prepare( "DELETE FROM  $wpdb->posts WHERE ID = $gallery_id" );
        $sql_meta = $wpdb->prepare( "DELETE FROM  $wpdb->postmeta WHERE post_id = $gallery_id" );
        $wpdb->query( $sql );
        $wpdb->query( $sql_meta );
    }

}

// Load the class.
$modula_envira_importer = Modula_Envira_Importer::get_instance();