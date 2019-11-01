<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
$wp_core           = Modula_WP_Core_Gallery_Importer::get_instance();
$wp_core_galleries = $wp_core->get_galleries();
?>
<div class="row">
    <form id="modula_importer_wp_core_gallery" method="post">

        <table class="form-table">
            <tbody>
            <?php if (false != $wp_core_galleries) {
                $import_settings = get_option('modula_importer');
                ?>
                <tr valign="top">
                    <th scope="row" valign="top">
                        <?php esc_html_e('Pages/Posts with WP Core Galleries', 'modula-importer'); ?>
                        <p class="description"> <?php esc_html_e('These are specific to [gallery ids="x,x,x"] shortcodes that come from WP core and not Gallery Blocks from Gutenberg', 'modula-importer'); ?></p>
                    </th>
                    <td>

                        <?php foreach ($wp_core_galleries as $wp_core_gallery) {
                            ?>

                            <div>
                                <label for="galleries-<?php echo esc_attr($wp_core_gallery->ID); ?>"
                                       data-id="<?php echo esc_attr($wp_core_gallery->ID); ?>">
                                    <input type="checkbox" name="gallery"
                                           id="galleries-<?php echo esc_attr($wp_core_gallery->ID); ?>"
                                           value="<?php echo esc_attr($wp_core_gallery->ID); ?>"/>
                                    <?php echo esc_html($wp_core_gallery->post_title); ?>
                                    <span style="color:blue;">
                                </span>
                            </div>

                        <?php } ?>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row" valign="top">
                        <?php esc_html_e('Convert and create gallery', 'modula-importer'); ?>
                    </th>
                    <td>
                        <div>
                            <?php submit_button(__('Convert and create gallery', 'modula-importer'), 'primary', 'modula-importer-submit', false); ?>
                        </div>
                    </td>
                </tr>
            <?php } else if (false == $wp_core_galleries) { ?>
                <tr valign="top">
                    <th scope="row" valign="top">
                        <?php esc_html_e('There are no WP core galleries.', 'modula-importer'); ?>
                        <p class="description"> <?php esc_html_e('These are specific to [gallery ids="x,x,x"] shortcodes that come from WP core and not Gallery Blocks from Gutenberg', 'modula-importer'); ?></p>
                    </th>
                </tr>
            <?php } ?>
            </tbody>
        </table>
    </form>
</div>