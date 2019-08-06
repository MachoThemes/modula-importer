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

        // first check if plugin is active ( pro or lite version )
        if (is_plugin_active('envira-gallery/envira-gallery.php') || is_plugin_active('envira-gallery-lite/envira-gallery-lite.php')) {

            $galleries = $wpdb->get_results(" SELECT * FROM " . $wpdb->prefix . "posts WHERE post_type ='envira'");
            if (count($galleries) == 0) {
                return false;
            }

            return $galleries;
        }

        return 'inactive';
    }

    /**
     * Imports a gallery from Envira gallery into Modula gallery
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
        $images = get_attached_media('image', $gallery_id);

        $attachments = array();

        if (is_array($images) && count($images) > 0) {
            // Add each image to Media Library
            foreach ($images as $image) {

                // get gallery data so we can get title, description and alt from envira
                $envira_gallery_data = get_post_meta($gallery_id, '_eg_gallery_data', true);

                $path     = get_attached_file($image->ID);
                $filename = basename($path);

                $envira_image_title = (!isset($envira_gallery_data['gallery'][$image->ID]['title']) || '' != $envira_gallery_data['gallery'][$image->ID]['title']) ? $envira_gallery_data['gallery'][$image->ID]['title'] : '';

                $envira_image_caption = (!isset($envira_gallery_data['gallery'][$image->ID]['caption']) || '' != $envira_gallery_data['gallery'][$image->ID]['caption']) ? $envira_gallery_data['gallery'][$image->ID]['caption'] : wp_get_attachment_caption($image->ID);

                $envira_image_alt = (!isset($envira_gallery_data['gallery'][$image->ID]['alt']) || '' != $envira_gallery_data['gallery'][$image->ID]['alt']) ? $envira_gallery_data['gallery'][$image->ID]['alt'] : get_post_meta($image->ID, '_wp_attachment_image_alt', TRUE);


                // Store image in WordPress Media Library
                $attachment = $this->add_image_to_library($path, $filename,$envira_image_title, $envira_image_caption, $envira_image_alt);

                if ($attachment !== false) {

                    // Add to array of attachments
                    $attachments[] = $attachment;
                }
            }
        }

        if (count($attachments) == 0) {
            $this->modula_import_result(false, __('No images found in gallery. Skipping gallery...', 'modula-importer'));
        }

        // Get Modula Gallery defaults, used to set modula-settings metadata
        $modula_settings = Modula_CPT_Fields_Helper::get_defaults();

        // Build Modula Gallery modula-images metadata
        $modula_images = array();
        foreach ($attachments as $attachment) {
            $modula_images[] = array(
                'id'          => $attachment['ID'],
                'alt'         => $attachment['alt'],
                'title'       => $attachment['title'],
                'description' => $attachment['caption'],
                'halign'      => 'center',
                'valign'      => 'middle',
                'link'        => $attachment['src'],
                'target'      => '',
                'width'       => 2,
                'height'      => 2,
                'filters'     => ''
            );
        }

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

        $importer_settings = get_option('modula_importer');

        if (!isset($importer_settings['galleries'])) {
            $importer_settings['galleries'] = array();
        }

        // Remember that this gallery has been imported
        $importer_settings['galleries'][$gallery_id] = $modula_gallery_id;
        update_option('modula_importer', $importer_settings);

        $envira_shortcodes = '[envira-gallery id="' . $gallery_id . '"]';
        $modula_shortcode  = '[modula id="' . $modula_gallery_id . '"]';

        // Replace Envira shortcode with Modula Shortcode in Posts, Pages and CPTs
        $sql = $wpdb->prepare("UPDATE " . $wpdb->prefix . "posts SET post_content = REPLACE(post_content, '%s', '%s')",
            $envira_shortcodes, $modula_shortcode);
        $wpdb->query($sql);

        $this->modula_import_result(true, __('Imported!', 'modula-importer'));
    }

    /**
     * Add image to library
     *
     * @param $source_path
     * @param $source_file
     * @param $description
     * @param $title
     * @param $alt
     * @return mixed
     *
     * @since 1.0.0
     */
    public function add_image_to_library($source_file_path, $source_file, $title, $description, $alt) {

        // Get WP upload dir
        $uploadDir = wp_upload_dir();

        // Create destination file paths and URLs
        $destination_file      = wp_unique_filename($uploadDir['path'], $source_file);
        $destination_file_path = $uploadDir['path'] . '/' . $destination_file;
        $destination_url       = $uploadDir['url'] . '/' . $destination_file;

        // Check file is valid
        $wp_filetype = wp_check_filetype($source_file, null);
        extract($wp_filetype);

        if ((!$type || !$ext) && !current_user_can('unfiltered_upload')) {
            return false;
        }

        $result = copy($source_file_path, $destination_file_path);

        if (!$result) {

            return false;
        }

        // Set file permissions
        $stat  = stat($destination_file_path);
        $perms = $stat['mode'] & 0000666;
        chmod($destination_file_path, $perms);

        // Apply upload filters
        $return = apply_filters('wp_handle_upload', array(
            'file' => $destination_file_path,
            'url'  => $destination_url,
            'type' => $type,
        ));

        // Construct the attachment array
        $attachment = array(
            'post_mime_type' => $type,
            'guid'           => $destination_url,
            'post_title'     => $title,
            'post_name'      => $alt,
            'post_content'   => $description,
        );

        // Save as attachment
        $attachmentID = wp_insert_attachment($attachment, $destination_file_path);

        // Update attachment metadata
        if (!is_wp_error($attachmentID)) {
            $metadata = wp_generate_attachment_metadata($attachmentID, $destination_file_path);
            wp_update_attachment_metadata($attachmentID, wp_generate_attachment_metadata($attachmentID, $destination_file_path));
        }

        update_post_meta($attachmentID, '_wp_attachment_image_alt', $alt);
        $attachment               = get_post($attachmentID);
        $attachment->post_excerpt = $description;
        wp_update_post($attachment);


        // Return attachment data
        return array(
            'ID'      => $attachmentID,
            'src'     => $destination_url,
            'title'   => $title,
            'alt'     => $alt,
            'caption' => $description,
        );

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

}

// Load the class.
$modula_envira_importer = Modula_Envira_Importer::get_instance();