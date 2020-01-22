<?php
// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

$import_settings = get_option('modula_importer');
$import_settings = wp_parse_args($import_settings, array('galleries' => array()));
$def_galleries   = array();
$sources         = array();
$galleries       = array();

$envira           = Modula_Envira_Importer::get_instance();
$envira_galleries = $envira->get_galleries();
if ($envira_galleries) {
    $def_galleries['envira'] = array(
        'label'     => 'Envira',
        'galleries' => $envira_galleries
    );
}

$final_tiles           = Modula_Final_Tiles_Importer::get_instance();
$final_tiles_galleries = $final_tiles->get_galleries();
if ($final_tiles_galleries) {
    $def_galleries['final_tiles'] = array(
        'label'     => 'Final Tiles',
        'galleries' => $final_tiles_galleries
    );
}

$nextgen           = Modula_Nextgen_Importer::get_instance();
$nextgen_galleries = $nextgen->get_galleries();
if ($nextgen_galleries) {
    $def_galleries['nextgen'] = array(
        'label'     => 'Nextgen',
        'galleries' => $nextgen_galleries
    );
}

$photoblocks           = Modula_Photoblocks_Importer::get_instance();
$photoblocks_galleries = $photoblocks->get_galleries();
if ($photoblocks_galleries) {
    $def_galleries['photoblocks'] = array(
        'label'     => 'Photoblocks',
        'galleries' => $photoblocks_galleries
    );
}

$wp_core           = Modula_WP_Core_Gallery_Importer::get_instance();
$wp_core_galleries = $wp_core->get_galleries();
if ($wp_core_galleries) {
    $def_galleries['wp_core'] = array(
        'label'     => 'WP Core Galleries',
        'galleries' => $wp_core_galleries
    );
}

$galleries = apply_filters('modula_importable_galleries', $def_galleries);
?>

    <div class="row">
        <table class="form-table">
            <tbody>
            <tr valign="top">
                <th scope="row" valign="top">
                    <?php esc_html_e('Gallery source', 'modula-importer'); ?>
                    <div class="tab-header-tooltip-container modula-tooltip"><span>[?]</span>
                        <div class="tab-header-description modula-tooltip-content">
                            <?php esc_html_e('Select from which source would you like to migrate the gallery.', 'modula-importer') ?>
                            <?php esc_html_e('Migrating galleries will also replace the shortcode of the gallery with the new Modula shortcode in pages and posts.', 'modula-importer') ?>
                        </div>
                    </div>
                </th>
                <td>
                    <select name="modula_select_gallery_source" id="modula_select_gallery_source">
                        <option value="none"><?php echo (count($galleries) > 0) ? esc_html('Select gallery source', 'modula-importer') : esc_html('No galleries detected', 'modula-importer'); ?></option>
                        <?php
                        foreach ($galleries as $source => $gallery) {
                            if ($gallery['galleries']) {
                                echo '<option value="' . $source . '"> ' . $gallery['label'] . '</option>';
                            }
                        }
                        ?>
                    </select>
                </td>
            </tr>
            </tbody>
        </table>
    </div>
    <!-- Select all checkbox-->
    <div class="row select-all-wrapper hide">
        <table class="form-table">
            <tbody>
            <tr valign="top">
                <th scope="row" valign="top">
                </th>
                <td>
                    <div>
                        <label for="select-galleries"
                               data-id="select-all">
                            <input type="checkbox" name="select-all"
                                   id="select-all"
                                   value=""/>
                            <?php echo esc_html__('Check to select all galleries.', 'modula-importer'); ?>
                        </label>
                    </div>
                </td>
            </tr>
            </tbody>
        </table>
    </div>
<?php
if ($galleries) {
    foreach ($galleries as $source => $source_galleries) {
        if ($source_galleries['galleries']) {
            ?>

            <div id="modula-<?php echo esc_attr($source); ?>-importer" class="row modula-importer-row hide">
                <form id="modula_importer_<?php echo esc_attr($source); ?>" method="post">
                    <table class="form-table">
                        <tbody>
                        <tr valign="top">
                            <th scope="row" valign="top">
                                <?php echo esc_html($source_galleries['label']) . esc_html__(' galleries', 'modula-importer'); ?>
                            </th>
                            <td>
                                <?php
                                foreach ($source_galleries['galleries'] as $gallery) {
                                    $imported = false;
                                    switch ($source) {
                                        case 'envira':
                                            if (isset($import_settings['galleries']['envira']) && isset($import_settings['galleries']['envira'][$gallery->ID])) {
                                                $imported = true;
                                            }
                                            $id    = $gallery->ID;
                                            $title = $gallery->post_title;
                                            break;
                                        case 'final_tiles' :
                                            if (isset($import_settings['galleries']['final_tiles']) && isset($import_settings['galleries']['final_tiles'][$gallery->Id])) {
                                                $imported = true;
                                            }
                                            $id         = $gallery->Id;
                                            $ftg_config = json_decode($gallery->configuration);
                                            $title      = $ftg_config->name;
                                            break;
                                        case 'nextgen':
                                            if (isset($import_settings['galleries']['nextgen']) && isset($import_settings['galleries']['nextgen'][$gallery->gid])) {
                                                $imported = true;
                                            }
                                            $id    = $gallery->gid;
                                            $title = $gallery->title;
                                            break;
                                        case
                                        'photoblocks':
                                            if (isset($import_settings['galleries']['photoblocks']) && isset($import_settings['galleries']['photoblocks'][$gallery->id])) {
                                                $imported = true;
                                            }
                                            $id    = $gallery->id;
                                            $title = $gallery->name;
                                            break;
                                        case 'wp_core':
                                            $id    = $gallery->ID;
                                            $title = $gallery->post_title;
                                            break;
                                        default:
                                            if (isset($import_settings['galleries'][$source]) && isset($import_settings['galleries'][$source]['id'])) {
                                                $imported = true;
                                            }
                                            $id    = $gallery->ID;
                                            $title = $gallery->post_title;

                                    }
                                    ?>

                                    <div>
                                        <label for="<?php echo esc_attr($source); ?>-galleries-<?php echo esc_attr($id); ?>"
                                               data-id="<?php echo esc_attr($id); ?>"<?php echo($imported ? ' class="imported"' : ''); ?>>
                                            <input type="checkbox" name="gallery"
                                                   id="envira-galleries-<?php echo esc_attr($id); ?>"
                                                   value="<?php echo esc_attr($id); ?>"/>
                                            <?php echo esc_html($title); ?>
                                            <span style="color:blue;">
                                    <?php if ($imported) {
                                        esc_html_e('Migrated', 'modula-importer');
                                    } ?>
                                    </span>
                                        </label>
                                    </div>

                                <?php }
                                ?>
                            </td>
                        </tr>
                        <tr valign="top">
                            <th scope="row" valign="top">
                            </th>
                            <td>
                                <div>
                                    <?php submit_button(__('Migrate', 'modula-importer'), 'primary', 'modula-importer-submit-' . $source, false); ?>
                                </div>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </form>
            </div>
            <?php
        }
    }
}
?>