<?php
// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}
$envira           = Modula_Envira_Importer::get_instance();
$envira_galleries = $envira->get_galleries();
?>
<div class="row">
    <form id="modula_importer_envira" method="post">

        <table class="form-table">
            <tbody>
            <?php if ('inactive' != $envira_galleries && false != $envira_galleries) {
                $import_settings = get_option( 'modula_importer' );
                $import_settings = wp_parse_args( $import_settings, array( 'galleries' => array() ) );
                ?>
                <!-- If Envira gallery plugin is installed and active and there are galleries created -->
                <tr valign="top">
                    <th scope="row" valign="top">
                        <?php esc_html_e('Envira galleries', 'modula-importer'); ?>
                    </th>
                    <td>

                        <?php foreach ($envira_galleries as $envira_gallery) {
                            ?>

                            <div>
                                <label for="galleries-<?php echo esc_attr($envira_gallery->ID); ?>"
                                       data-id="<?php echo esc_attr($envira_gallery->ID); ?>"<?php echo($imported ? ' class="imported"' : ''); ?>>
                                    <input type="checkbox" name="gallery"
                                           id="galleries-<?php echo esc_attr($envira_gallery->ID); ?>"
                                           value="<?php echo esc_attr($envira_gallery->ID); ?>"/>
                                    <?php echo esc_html($envira_gallery->post_title); ?>
                                    <span style="color:blue;">
                                    <?php if ( isset($import_settings['galleries'][$envira_gallery->ID]) ) {
                                        //esc_html_e('Imported', 'modula-importer');
                                    } ?>
                                    </span>
                                </label>
                            </div>

                        <?php } ?>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row" valign="top">
                        <?php esc_html_e('Import Envira galleries', 'modula-importer'); ?>
                    </th>
                    <td>
                        <div>
                            <?php submit_button(__('Import Galleries', 'modula-importer'), 'primary', 'modula-importer-submit', false); ?>
                        </div>
                    </td>
                </tr>
            <?php } else if (false == $envira_galleries) { ?>
                <!-- If Envira gallery plugin is installed and active but there are no galleries created -->
                <tr valign="top">
                    <th scope="row" valign="top">
                        <?php esc_html_e('There are no Envira galleries', 'modula-importer'); ?>
                    </th>
                </tr>
            <?php } else { ?>
                <!-- If Envira gallery plugin is not installed or is inactive -->
                <tr valign="top">
                    <th scope="row" valign="top">
                        <?php esc_html_e('Envira Gallery plugin is not active', 'modula-importer'); ?>
                    </th>
                </tr>
            <?php } ?>
            </tbody>
        </table>
    </form>
</div>