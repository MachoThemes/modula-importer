<?php
/**
 * Plugin Name: Modula Migrate
 * Plugin URI: https://wp-modula.com/
 * Description: Plugin used to migrate other galleries into Modula Gallery.
 * Author: Macho Themes
 * Version: 1.0.0
 * URI: https://www.machothemes.com/
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

define('MODULA_IMPORTER_VERSION', '1.0.0');
define('MODULA_IMPORTER_PATH', plugin_dir_path(__FILE__));
define('MODULA_IMPORTER_URL', plugin_dir_url(__FILE__));
define('MODULA_IMPORTER_FILE', __FILE__);

require_once MODULA_IMPORTER_PATH . 'includes/class-modula-importer.php';

// Load the main plugin class.
$modula_importer = Modula_Importer::get_instance();
