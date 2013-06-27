<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Ihar_Peshkou
 * Date: 6/26/13
 * Time: 2:10 PM
 * To change this template use File | Settings | File Templates.
 */
/*
  Plugin Name:NextGEN Gallery Archivator
  Plugin URI: http://gocbprof.by
  Description: Moga Filter Content Platform
  Version: 1.0
  Author: Igor Peshkov
  Author URI: http://www.facebook.com/DARKDIESEL
 */

if (!class_exists('nggarchivator')) {

    class nggArchivator
    {

        function nggArchivator()
        {
            add_action('admin_menu', array(&$this, 'add_admin_menu'));
            add_action('admin_enqueue_scripts', array(&$this, 'add_load_scripts'));
            add_action('admin_enqueue_', array(&$this, 'add_load_scripts'));
            add_action('wp_ajax_update_nggachive', array(&$this, 'update_nggallery_achive_ajax'));
            add_action('wp_ajax_remove_nggachive', array(&$this, 'remove_nggallery_achive_ajax'));
        }

        function add_admin_menu()
        {
            global $archivator_tool;
            //add_options_page(__('LetterBox Thumbnails Settings', 'default'), __('Letterbox Thumbnails', 'Letterbox Thumbnails'), 'manage_options', 'letterbox_thumbnails.php', array(&$this, 'letterboxing_settings_interface'));
            $archivator_tool = add_management_page(
                __('NextGEN Archivator', 'nggarchivator'), __('NextGEN Archivator', 'nggarchivator'), 8,
                'nggarchivator', array(&$this, 'nggarchivator_tool_interface')
            );
        }

        function nggarchivator_tool_interface()
        {
            ?>
            <h2><?php echo __('NextGEN Archivator Tool', 'nggarchivator') ?></h2>
            <?php
            global $wpdb;

            $nggpictures = $wpdb->prefix . 'ngg_pictures';
            $nggallery = $wpdb->prefix . 'ngg_gallery';

            $nggalleryes = $wpdb->get_results(
                $wpdb->prepare(
                    "
                    SELECT $nggallery.gid, $nggallery.name, $nggallery.path, COUNT($nggpictures.pid) AS count_pic_gal
                    FROM $nggallery
                    LEFT JOIN $nggpictures
                    ON $nggallery.gid=$nggpictures.galleryid
                    GROUP BY $nggallery.gid"
                )
            );
            ?>
            <table id="nggallery_archives" class="widefat importers" cellspacing="0">
                <thead>
                <tr>
                    <th><?php echo __('Gallery Id', 'nggarchivator') ?></th>
                    <th><?php echo __('Gallery Name', 'nggarchivator') ?></th>
                    <th><?php echo __('Gallery Path', 'nggarchivator') ?></th>
                    <th><?php echo __('Count Gallery Pictures', 'nggarchivator') ?></th>
                    <th><?php echo __('Link to archive', 'nggarchivator') ?></th>
                    <th><?php echo __('Action', 'nggarchivator') ?></th>
                    <th><?php echo __('Remove archive', 'nggarchivator') ?></th>
                </tr>
                </thead>
                <tbody>
                <?php
                foreach ($nggalleryes as $nggallery) {
                    ?>
                    <tr>
                        <td><?php echo $nggallery->gid ?></td>
                        <td><strong><?php echo $nggallery->name ?></strong></td>
                        <td><i><?php echo $nggallery->path ?></i></td>
                        <td><?php echo $nggallery->count_pic_gal ?></td>
                        <td class="ngg-gallery-link">
                            <?php
                            $gallery_path = str_replace('wp-content', $nggallery->path, WP_CONTENT_DIR);

                            if (file_exists($gallery_path . '/archive/' . 'archive.zip')) {
                            $galery_url = str_replace(
                                'wp-content', $nggallery->path . '/archive/' . 'archive.zip', content_url()
                            )
                            ?>
                            <a class="dwonload_archive"
                               href="<?php echo $galery_url ?>"><?php echo __('Download', 'nggachivator') ?></a>
                        </td>
                        <?php
                        } else {
                            echo __('No archive', 'nggachivator');
                            ?>
                        <?php
                        }
                        ?>
                        <td>
                            <a class="update_archive" data-gallery-id="<?php echo $nggallery->gid ?>"
                               href="#"><?php echo __('Create / Update archive', 'nggachivator') ?></a>
                            <span class="ajax_loader" style="display:none"><img
                                    src="<?php echo plugins_url('img/ajax-loader.gif', __FILE__) ?>"></span
                        </td>
                        <td>
                            <a class="remove_archive" data-gallery-id="<?php echo $nggallery->gid ?>"
                               href="#"><?php echo __('Delete archive', 'nggachivator') ?></a>
                            <span class="ajax_loader" style="display:none"><img
                                    src="<?php echo plugins_url('img/ajax-loader.gif', __FILE__) ?>"></span
                        </td>
                    </tr>
                <?php
                }
                ?>
                </tbody>
            </table>
        <?php
        }

        function add_load_scripts($hook)
        {
            global $archivator_tool;

            if ($hook != $archivator_tool) {
                return;
            }

            wp_enqueue_script(
                'nggachivator_ajax', plugin_dir_url(__FILE__) . "js/nggachivator_ajax.js", array('jquery')
            );

            wp_localize_script(
                'nggachivator_ajax', 'nggachivator_vars',
                array('nggachivator_nonce' => wp_create_nonce('nggachivator-nonce'))
            );

            wp_enqueue_style('nggachivator_style', plugin_dir_url(__FILE__) . "css/style.css");
        }

        function update_nggallery_achive_ajax()
        {
            if(!isset($_POST['nggachivator_nonce']) || !wp_verify_nonce($_POST['nggachivator_nonce'], "nggachivator-nonce"))
                die(__('Permission check failed', 'nggarchivator'));

            global $wpdb;

            $nggpictures = $wpdb->prefix . 'ngg_pictures';
            $nggallery = $wpdb->prefix . 'ngg_gallery';

            // requested gallery id
            $galery_id = $_REQUEST['galery_id'];

            $nggalleryes = $wpdb->get_results(
                $wpdb->prepare(
                    "
                    SELECT $nggpictures.filename, $nggallery.gid, $nggallery.path
                    FROM $nggpictures
                    LEFT JOIN $nggallery
                    ON $nggpictures.galleryid=$nggallery.gid
                    WHERE wp_ngg_pictures.galleryid = %d", $galery_id
                )
            );

            $gallery_path = str_replace('wp-content', $nggalleryes[0]->path, WP_CONTENT_DIR);

            if (!file_exists($gallery_path . '/archive')) {
                mkdir($gallery_path . '/archive');
            } else {
                $files = glob($gallery_path . '/archive/*'); // get all file names
                foreach ($files as $file) { // iterate files
                    if (is_file($file)) {
                        unlink($file);
                    } // delete file
                }
            }

            //create the archive
            $zip = new ZipArchive();

            if ($zip->open($gallery_path . '/archive/' . 'archive.zip', ZIPARCHIVE::CREATE) !== true) {
                return false;
            }

            foreach ($nggalleryes as $picture) {
                if (file_exists($gallery_path . '/' . $picture->filename)) {
                    $zip->addFile($gallery_path . '/' . $picture->filename, $picture->filename);
                }
            }

            $zip->close();
            $gallery_url = str_replace(
                'wp-content', $nggalleryes[0]->path . '/archive/' . 'archive.zip', content_url()
            );
            echo $gallery_url;

            die();
        }

        function remove_nggallery_achive_ajax()
        {
            if(!isset($_POST['nggachivator_nonce']) || !wp_verify_nonce($_POST['nggachivator_nonce'], "nggachivator-nonce"))
                die(__('Permission check failed', 'nggarchivator'));

            // requested gallery id
            $galery_id = $_REQUEST['galery_id'];

            global $wpdb;

            $nggallery = $wpdb->prefix . 'ngg_gallery';

            $nggalleryes = $wpdb->get_results(
                $wpdb->prepare(
                    "
                    SELECT $nggallery.path
                    FROM $nggallery
                    WHERE $nggallery.gid = %d", $galery_id
                )
            );

            $gallery_path = str_replace('wp-content', $nggalleryes[0]->path, WP_CONTENT_DIR);

            if (file_exists($gallery_path . '/archive/archive.zip')) {
                unlink($gallery_path . '/archive/archive.zip');
            }

            die();
        }

    }

} else {
    exit("Class nggarchivator already declared!");
}

// create new instance of the class
global $nggArchivator;
$nggArchivator = new nggArchivator();