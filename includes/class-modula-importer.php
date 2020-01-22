<?php

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

class Modula_Importer {

    /**
     * Holds the class object.
     *
     * @since 1.0.0
     *
     * @var object
     */
    public static $instance;

    /**
     * The name of the plugin.
     *
     * @since 1.0.0
     *
     * @var string
     */
    public $plugin_name = 'Modula Importer';

    /**
     * Unique plugin slug identifier.
     *
     * @since 1.0.0
     *
     * @var string
     */
    public $plugin_slug = 'modula-importer';

    /**
     * Primary class constructor.
     *
     * @since 1.0.0
     */
    public function __construct() {

        // Load the plugin textdomain.
        add_action('plugins_loaded', array($this, 'load_plugin_textdomain'));

        // Add Importer Tab
        add_filter('modula_admin_page_tabs', array($this, 'add_importer_tab'));

       // Render Importer tab
        add_action('modula_admin_tab_importer', array($this, 'render_importer_tab'));

        // Include required scripts for import
        add_action('admin_enqueue_scripts', array($this, 'admin_importer_scripts'));

        // Required files
        require_once MODULA_IMPORTER_PATH . 'includes/nextgen/class-modula-nextgen-importer.php';
        require_once MODULA_IMPORTER_PATH . 'includes/envira/class-modula-envira-importer.php';
        require_once MODULA_IMPORTER_PATH . 'includes/final-tiles/class-modula-final-tiles-importer.php';
        require_once MODULA_IMPORTER_PATH . 'includes/photoblocks/class-modula-photoblocks-importer.php';
        require_once MODULA_IMPORTER_PATH . 'includes/wp-core-gallery/class-modula-wp-core-gallery-importer.php';

        // Load the plugin.
        $this->init();

    }

    /**
     * Loads the plugin textdomain for translation.
     *
     * @since 1.0.0
     */
    public function load_plugin_textdomain() {

        load_plugin_textdomain($this->plugin_slug, false, MODULA_IMPORTER_PATH . '/languages/');
    }

    /**
     * Loads the plugin into WordPress.
     *
     * @since 1.0.0
     */
    public function init() {

        // Load admin only components.
        if (is_admin()) {
            add_action('modula_pro_updater', array($this, 'addon_updater'), 15, 2);
        }

    }

    /**
     * Updater
     *
     * @param $license_key
     * @param $store_url
     *
     * @since 1.0.0
     */
    public function addon_updater($license_key, $store_url) {

        if (class_exists('Modula_Pro_Base_Updater')) {
            $modula_addon_updater = new Modula_Pro_Base_Updater($store_url, MODULA_IMPORTER_FILE,
                array(
                    'version' => MODULA_IMPORTER_VERSION,        // current version number
                    'license' => $license_key,               // license key (used get_option above to retrieve from DB)
                    'item_id' => 0,                      // ID of the product
                    'author'  => 'MachoThemes',            // author of this plugin
                    'beta'    => false,
                )
            );
        }
    }

    /**
     * Enqueue import script
     *
     * @since 1.0.0
     */
    public function admin_importer_scripts() {

        $screen = get_current_screen();

        // only enqueue script if we are in Modula Settings page on modula importer tab
        if ('modula-gallery' == $screen->post_type && 'modula-gallery_page_modula' == $screen->base && isset($_GET['modula-tab']) && 'importer' == $_GET['modula-tab']) {

            $ajax_url = admin_url('admin-ajax.php');
            $nonce = wp_create_nonce('modula-importer');

            wp_enqueue_style('modula-importer',MODULA_IMPORTER_URL.'assets/css/modula-importer.css',array(),MODULA_IMPORTER_VERSION);
            wp_enqueue_script('modula-importer',MODULA_IMPORTER_URL.'assets/js/modula-importer.js',array('jquery'),MODULA_IMPORTER_VERSION,true);
            // only enqueue if nextGEN gallery plugin is active
            if (is_plugin_active('nextgen-gallery/nggallery.php')) {
                // scripts required for nextGEN importer
                wp_register_script('modula-nextgen-importer', MODULA_IMPORTER_URL . 'assets/js/modula-nextgen-importer.js', '', MODULA_IMPORTER_VERSION, true);
                wp_enqueue_script('modula-nextgen-importer');

                // Strings added to js are used for translation
                wp_localize_script(
                    'modula-nextgen-importer',
                    'modula_nextgen_importer_settings',
                    array(
                        'ajax'                    => $ajax_url,
                        'nonce'                   => $nonce,
                        'importing'               => '<span style="color:green">' . esc_html__('Migration started...', 'modula-importer') . '</span>',
                        'empty_gallery_selection' => esc_html__('Please choose at least one NextGEN Gallery to migrate.', 'modula-importer'),
                    )
                );
            }

            // only enqueue if Envira gallery plugin is active
            if (is_plugin_active('envira-gallery/envira-gallery.php') || is_plugin_active('envira-gallery-lite/envira-gallery-lite.php')) {
                // scripts required for Envira importer
                wp_register_script('modula-envira-importer', MODULA_IMPORTER_URL . 'assets/js/modula-envira-importer.js', '', MODULA_IMPORTER_VERSION, true);
                wp_enqueue_script('modula-envira-importer');

                // Strings added to js are used for translation
                wp_localize_script(
                    'modula-envira-importer',
                    'modula_envira_importer_settings',
                    array(
                        'ajax'                    => $ajax_url,
                        'nonce'                   => $nonce,
                        'importing'               => '<span style="color:green">' . esc_html__('Migration started...', 'modula-importer') . '</span>',
                        'empty_gallery_selection' => esc_html__('Please choose at least one Envira Gallery to migrate.', 'modula-importer'),
                    )
                );
            }

            // only enqueue if Final Tiles gallery plugin is active
            if (is_plugin_active('final-tiles-grid-gallery-lite/FinalTilesGalleryLite.php')) {
                // scripts required for final tiles importer
                wp_register_script('modula-final-tiles-importer', MODULA_IMPORTER_URL . 'assets/js/modula-final-tiles-importer.js', '', MODULA_IMPORTER_VERSION, true);
                wp_enqueue_script('modula-final-tiles-importer');

                // Strings added to js are used for translation
                wp_localize_script(
                    'modula-final-tiles-importer',
                    'modula_ftg_importer_settings',
                    array(
                        'ajax'                    => $ajax_url,
                        'nonce'                   => $nonce,
                        'importing'               => '<span style="color:green">' . esc_html__('Migration started...', 'modula-importer') . '</span>',
                        'empty_gallery_selection' => esc_html__('Please choose at least one Final Tiles Grid Gallery to migrate.', 'modula-importer'),
                    )
                );
            }

            // only enqueue if Gallery PhotoBlocks plugin is active
            if (is_plugin_active('photoblocks-grid-gallery/photoblocks.php')) {
                // scripts required for final tiles importer
                wp_register_script('modula-photoblocks-importer', MODULA_IMPORTER_URL . 'assets/js/modula-photoblocks-importer.js', '', MODULA_IMPORTER_VERSION, true);
                wp_enqueue_script('modula-photoblocks-importer');

                // Strings added to js are used for translation
                wp_localize_script(
                    'modula-photoblocks-importer',
                    'modula_pb_importer_settings',
                    array(
                        'ajax'                    => $ajax_url,
                        'nonce'                   => $nonce,
                        'importing'               => '<span style="color:green">' . esc_html__('Migration started...', 'modula-importer') . '</span>',
                        'empty_gallery_selection' => esc_html__('Please choose at least one PhotoBlocks gallery to migrate.', 'modula-importer'),
                    )
                );
            }

            // scripts required for wp core importer
            wp_register_script('modula-wp-core-gallery-importer', MODULA_IMPORTER_URL . 'assets/js/modula-wp-core-gallery-importer.js', '', MODULA_IMPORTER_VERSION, true);
            wp_enqueue_script('modula-wp-core-gallery-importer');

            // Strings added to js are used for translation
            wp_localize_script(
                'modula-wp-core-gallery-importer',
                'modula_wp_core_gallery_importer_settings',
                array(
                    'ajax'                    => $ajax_url,
                    'nonce'                   => $nonce,
                    'importing'               => '<span style="color:green">' . esc_html__('Migration started...', 'modula-importer') . '</span>',
                    'empty_gallery_selection' => esc_html__('Please choose at least one gallery.', 'modula-importer'),
                )
            );
        }
    }


    /**
     * Add Importer tab
     *
     * @param $tabs
     * @return mixed
     *
     * @since 1.0.0
     */
    public function add_importer_tab($tabs) {
        if (class_exists('Modula_PRO')) {
            $tabs['importer'] = array(
                'label'    => esc_html__('Migrate galleries', 'modula-importer'),
                'priority' => 50,
            );
        }

        return $tabs;
    }


    /**
     * Render Importer tab
     *
     * @since 1.0.0
     */
    public function render_importer_tab() {
        if (class_exists('Modula_PRO')) {
            include 'tabs/modula-importer-tab.php';
        }
    }


    /**
     * Returns the singleton instance of the class.
     *
     * @return object The Modula_Importer object.
     *
     * @since 1.0.0
     */
    public static function get_instance() {

        if (!isset(self::$instance) && !(self::$instance instanceof Modula_Importer)) {
            self::$instance = new Modula_Importer();
        }

        return self::$instance;
    }
}