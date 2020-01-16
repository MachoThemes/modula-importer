<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
$nextgen           = Modula_Nextgen_Importer::get_instance();
$nextgen_galleries = $nextgen->get_galleries();

?>
<div class="row">
    <form id="modula_importer_nextgen" method="post">

        <table class="form-table">
            <tbody>
            <?php if (false != $nextgen_galleries) {
                $import_settings = get_option( 'modula_importer' );
                $import_settings = wp_parse_args( $import_settings, array( 'galleries' => array() ) );
                ?>
                <!-- If NextGen gallery plugin is installed and active and there are galleries created -->
                <tr valign="top">
                    <th scope="row" valign="top">
                        <?php esc_html_e('NextGEN galleries', 'modula-importer'); ?>
                    </th>
                    <td>

                        <?php foreach ($nextgen_galleries as $ng_gallery) {
                            $imported = ((isset($import_settings['galleries']['nextgen']) && isset($import_settings['galleries']['nextgen'][$ng_gallery->gid])) ? true : false);
                            ?>

                            <div>
                                <label for="galleries-<?php echo esc_attr($ng_gallery->gid); ?>" data-id="<?php echo esc_attr($ng_gallery->gid); ?>">
                                    <input type="checkbox" name="gallery" id="galleries-<?php echo esc_attr($ng_gallery->gid); ?>" value="<?php echo esc_attr($ng_gallery->gid); ?>"/>
                                    <?php echo esc_html($ng_gallery->title); ?>
                                    <span style="color:blue;">
                                    <?php if ( $imported ) {
                                        esc_html_e('Imported', 'modula-importer');
                                    } ?>
                                    </span>
                                </label>
                            </div>

                        <?php } ?>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row" valign="top">
                        <?php esc_html_e('Import NextGEN galleries', 'modula-importer'); ?>
                    </th>
                    <td>
                        <div>
                            <?php submit_button(__('Import Galleries', 'modula-importer'), 'primary', 'modula-importer-submit', false); ?>
                        </div>
                    </td>
                </tr>
            <?php } else if (false == $nextgen_galleries) { ?>
                <!-- If NextGEN gallery plugin there are no galleries created -->
                <tr valign="top">
                    <th scope="row" valign="top">
                        <?php esc_html_e('There are no NextGEN galleries', 'modula-importer'); ?>
                    </th>
                </tr>
            <?php  } ?>
            </tbody>
        </table>
    </form>
</div>