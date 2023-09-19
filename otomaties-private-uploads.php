<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              http://example.com
 * @since             1.0.0
 * @package           Otomaties_Private_Uploads
 *
 * @wordpress-plugin
 * Plugin Name:       Private uploads
 * Description:       Set a default plugin directory and add a .htaccess file to it. Add move-actions to uploads
 * Version:           1.1.0
 * Author:            Tom Broucke
 * Author URI:        https://tombroucke.be
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       otomaties-private-uploads
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'OTOMATIES_PRIVAT_UPLOADS_VERSION', '1.0.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-otomaties-private-uploads-activator.php
 */
function activate_otomaties_private_uploads() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-otomaties-private-uploads-activator.php';
	Otomaties_Private_Uploads_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-otomaties-private-uploads-deactivator.php
 */
function deactivate_otomaties_private_uploads() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-otomaties-private-uploads-deactivator.php';
	Otomaties_Private_Uploads_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_otomaties_private_uploads' );
register_deactivation_hook( __FILE__, 'deactivate_otomaties_private_uploads' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-otomaties-private-uploads.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_otomaties_private_uploads() {

	$plugin = new Otomaties_Private_Uploads();
	$plugin->run();

}
run_otomaties_private_uploads();
