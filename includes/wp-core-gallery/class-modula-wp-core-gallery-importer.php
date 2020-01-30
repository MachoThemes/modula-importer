<?php
// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

class Modula_WP_Core_Gallery_Importer {

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
        add_action('wp_ajax_modula_importer_wp_core_gallery_import', array($this, 'wp_core_gallery_import'));

    }

    /**
     * Get all posts/pages that have wp core galleries
     *
     * @return mixed
     *
     * @since 1.0.0
     */
    public function get_galleries() {

        global $wpdb;

        $post_in   = "'post','page'";
        $post_types = get_post_types(array('show_in_menu' => true,'public'=>true));

        foreach($post_types as $post_type){
            // exclude previous set and attachment from sql query
            if($post_type != 'post' && $post_type != 'page' && $post_type != 'attachment'){
                $post_in .= ",'".$post_type."'";
            }
        }

        $sql       = "SELECT * FROM " . $wpdb->prefix . "posts WHERE `post_content` LIKE '%[galler%' AND `post_type` IN ($post_in)";
        $galleries = $wpdb->get_results($sql);

        if (count($galleries) == 0) {
            return false;
        }

        return $galleries;
    }


    /**
     * Get gallery image count
     *
     * @since 1.0.0
     * @param $id
     * @return int
     */
    public function images_count($id){

        $post          = get_post($page_id);
        $content       = $post->post_content;
        $search_string = '[gallery';
        $pattern       = '/\\' . $search_string . '[\s\S]*?\]/';
        $result        = preg_match_all($pattern, $content, $matches);

        if ($result && $result > 0) {
            foreach ($matches[0] as $sc) {
                $pattern           = '/ids\s*=\s*\"([\s\S]*?)\"/';
                //$result            = preg_match($pattern, $sc, $gallery_ids);
                $image_ids         = $modula_importer->prepare_images('wp_core', $gallery_ids[1]);
            }
        }

        $count = count($image_ids);

        return $count;
    }


    /**
     * Replace WP Core gallery and create Modula gallery
     *
     * @since 1.0.0
     */
    public function wp_core_gallery_import($page_id = '') {

        global $wpdb,$modula_importer;

        // Set max execution time so we don't timeout
        ini_set('max_execution_time', 0);
        set_time_limit(0);

        // If no gallery ID, get from AJAX request
        if (empty($page_id)) {

            // Run a security check first.
            check_ajax_referer('modula-importer', 'nonce');

            if (!defined('ABSPATH')) {
                define('ABSPATH', dirname(__FILE__) . '/');
            }

            if (!isset($_POST['id'])) {
                $this->modula_import_result(false, __('No gallery was selected', 'modula-importer'));
            }

            $page_id = absint($_POST['id']);

        }

        // Get gallery
        $post          = get_post($page_id);
        $content       = $post->post_content;
        $search_string = '[gallery';
        $pattern       = '/\\' . $search_string . '[\s\S]*?\]/';
        $result        = preg_match_all($pattern, $content, $matches);

        if ($result && $result > 0) {
            foreach ($matches[0] as $sc) {
                $modula_images = array();
                $pattern           = '/ids\s*=\s*\"([\s\S]*?)\"/';
                $result            = preg_match($pattern, $sc, $gallery_ids);
                $image_ids = $modula_importer->prepare_images('wp_core',$gallery_ids[1]);
                $gallery_image_ids = $gallery_ids[0];

                foreach ($image_ids as $image) {

                    $img = get_post($image);
                    if ($img) {
                        // Build Modula Gallery modula-images metadata
                        $modula_images[] = array(
                            'id'          => $image,
                            'alt'         => get_post_meta( $image, '_wp_attachment_image_alt', true ),
                            'title'       => $img->post_title,
                            'description' => $img->post_content,
                            'halign'      => 'center',
                            'valign'      => 'middle',
                            'link'        => $img->guid,
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
                    'post_title'  => 'Gallery from ' . $post->post_title,
                ));


                // Attach meta modula-settings to Modula CPT
                update_post_meta($modula_gallery_id, 'modula-settings', $modula_settings);

                // Attach meta modula-images to Modula CPT
                update_post_meta($modula_gallery_id, 'modula-images', $modula_images);

                $wp_core_shortcode    = '[gallery ' . $gallery_image_ids . ']';
                $modula_shortcode = '[modula id="' . $modula_gallery_id . '"]';

                // Replace Gallery PhotoBlocks shortcode with Modula Shortcode in Posts, Pages and CPTs
                $sql = $wpdb->prepare("UPDATE " . $wpdb->prefix . "posts SET post_content = REPLACE(post_content, '%s', '%s')",
                    $wp_core_shortcode, $modula_shortcode);
                $wpdb->query($sql);
            }
        }

        $this->modula_import_result(true, wp_kses_post('<i class="imported-check dashicons dashicons-yes"></i>'));
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

        if (!isset(self::$instance) && !(self::$instance instanceof Modula_WP_Core_Gallery_Importer)) {
            self::$instance = new Modula_WP_Core_Gallery_Importer();
        }

        return self::$instance;

    }

}

// Load the class.
$wp_core_importer = Modula_WP_Core_Gallery_Importer::get_instance();