<?php

/**
 * Fired during plugin activation
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Otomaties_Private_Uploads
 * @subpackage Otomaties_Private_Uploads/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Otomaties_Private_Uploads
 * @subpackage Otomaties_Private_Uploads/includes
 * @author     Tom Broucke <tom@tombroucke.be>
 */
class Otomaties_Private_Uploads_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
		update_option('otomaties_private_uploads_directory', 'private-uploads');

		$uploads_dir = new Otomaties_Upload_Directory;
		$uploads_dir->create_private_dir_if_not_exists();

		$htaccess = get_home_path() . '.htaccess';
		$lines[] = 'RewriteRule ^wp-content/uploads/private-uploads(.*)$ private-uploads?file=/$1 [R=301,NC,L]';
		insert_with_markers($htaccess, 'Otomaties Private Uploads', $lines);
	}

}
