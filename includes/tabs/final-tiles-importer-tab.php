<?php
// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}
$final_tiles           = Modula_Final_Tiles_Importer::get_instance();
$final_tiles_galleries = $final_tiles->get_galleries();
?>
<div class="row">
    <form id="modula_importer_final_tiles" method="post">

        <table class="form-table">
            <tbody>
            <?php if ('inactive' != $final_tiles_galleries && false != $final_tiles_galleries) {
                $import_settings = get_option('modula_importer');
                ?>
                <!-- If Final Tiles gallery plugin is installed and active and there are galleries created -->
                <tr valign="top">
                    <th scope="row" valign="top">
                        <?php esc_html_e('Final Tiles Grid galleries', 'modula-importer'); ?>
                    </th>
                    <td>

                        <?php foreach ($final_tiles_galleries as $ftg_gallery) {

                            $ftg_config = json_decode($ftg_gallery->configuration);
                            $imported   = ((isset($import_settings['galleries']['final_tiles']) && isset($import_settings['galleries']['final_tiles'][$ftg_gallery->Id])) ? true : false);
                            ?>
                            <div>
                                <label for="galleries-<?php echo esc_attr($ftg_gallery->Id); ?>"
                                       data-id="<?php echo esc_attr($ftg_gallery->Id); ?>"<?php echo($imported ? ' class="imported"' : ''); ?>>
                                    <input type="checkbox" name="gallery"
                                           id="galleries-<?php echo esc_attr($ftg_gallery->Id); ?>"
                                           value="<?php echo esc_attr($ftg_gallery->Id); ?>"/>
                                    <?php echo esc_html($ftg_config->name); ?>
                                    <span style="color:blue;">
                                    <?php if ($imported) {
                                        esc_html_e('Imported', 'modula-importer');
                                    } ?>
                                </span>
                            </div>

                        <?php } ?>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row" valign="top">
                        <?php esc_html_e('Import Final Tiles Grid galleries', 'modula-importer'); ?>
                    </th>
                    <td>
                        <div>
                            <?php submit_button(__('Import Galleries', 'modula-importer'), 'primary', 'modula-importer-submit', false); ?>
                        </div>
                    </td>
                </tr>
            <?php } else if (false == $final_tiles_galleries) { ?>
                <!-- If Final Tiles gallery plugin is installed and active but there are no galleries created -->
                <tr valign="top">
                    <th scope="row" valign="top">
                        <?php esc_html_e('There are no Final Tiles Grid galleries', 'modula-importer'); ?>
                    </th>
                </tr>
            <?php } else { ?>
                <!-- If Final Tiles gallery plugin is not installed or is inactive -->
                <tr valign="top">
                    <th scope="row" valign="top">
                        <?php esc_html_e('Final Tiles Grid Gallery plugin is not active', 'modula-importer'); ?>
                    </th>
                </tr>
            <?php } ?>
            </tbody>
        </table>
    </form>
</div>