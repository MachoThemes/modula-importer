<?php
// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}
$photoblocks           = Modula_Photoblocks_Importer::get_instance();
$photoblocks_galleries = $photoblocks->get_galleries();
?>
<div class="row">
    <form id="modula_importer_photoblocks" method="post">

        <table class="form-table">
            <tbody>
            <?php if ('inactive' != $photoblocks_galleries && false != $photoblocks_galleries) {
                $import_settings = get_option('modula_importer');
                ?>
                <!-- If Gallery PhotoBlocks plugin is installed and active and there are galleries created -->
                <tr valign="top">
                    <th scope="row" valign="top">
                        <?php esc_html_e('Gallery PhotoBlocks galleries', 'modula-importer'); ?>
                    </th>
                    <td>

                        <?php foreach ($photoblocks_galleries as $photoblocks_gallery) {

                            $photoblock_blocks = json_decode($photoblocks_gallery->blocks);
                            $imported   = ((isset($import_settings['galleries']) && isset($import_settings['galleries'][$photoblocks_gallery->id])) ? true : false);
                            ?>
                            <div>
                                <label for="galleries-<?php echo esc_attr($photoblocks_gallery->id); ?>"
                                       data-id="<?php echo esc_attr($photoblocks_gallery->id); ?>"<?php echo($imported ? ' class="imported"' : ''); ?>>
                                    <input type="checkbox" name="gallery"
                                           id="galleries-<?php echo esc_attr($photoblocks_gallery->Id); ?>"
                                           value="<?php echo esc_attr($photoblocks_gallery->id); ?>"/>
                                    <?php echo esc_html($photoblocks_gallery->name); ?>
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
                        <?php esc_html_e('Import PhotoBlocks galleries', 'modula-importer'); ?>
                    </th>
                    <td>
                        <div>
                            <?php submit_button(__('Import Galleries', 'modula-importer'), 'primary', 'modula-importer-submit', false); ?>
                        </div>
                    </td>
                </tr>
            <?php } else if (false == $photoblocks_galleries) { ?>
                <!-- If Gallery PhotoBlocks plugin is installed and active but there are no galleries created -->
                <tr valign="top">
                    <th scope="row" valign="top">
                        <?php esc_html_e('There are no PhotoBlocks galleries', 'modula-importer'); ?>
                    </th>
                </tr>
            <?php } else { ?>
                <!-- If Gallery PhotoBlocks plugin is not installed or is inactive -->
                <tr valign="top">
                    <th scope="row" valign="top">
                        <?php esc_html_e('Gallery PhotoBlocks plugin is not active', 'modula-importer'); ?>
                    </th>
                </tr>
            <?php } ?>
            </tbody>
        </table>
    </form>
</div>