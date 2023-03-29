<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Otomaties_Private_Uploads
 * @subpackage Otomaties_Private_Uploads/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Otomaties_Private_Uploads
 * @subpackage Otomaties_Private_Uploads/includes
 * @author     Tom Broucke <tom@tombroucke.be>
 */
class Otomaties_Private_Uploads {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Otomaties_Private_Uploads_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $otomaties_private_uploads    The string used to uniquely identify this plugin.
	 */
	protected $otomaties_private_uploads;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( defined( 'OTOMATIES_PRIVAT_UPLOADS_VERSION' ) ) {
			$this->version = OTOMATIES_PRIVAT_UPLOADS_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->otomaties_private_uploads = 'otomaties-private-uploads';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Otomaties_Private_Uploads_Loader. Orchestrates the hooks of the plugin.
	 * - Otomaties_Private_Uploads_i18n. Defines internationalization functionality.
	 * - Otomaties_Private_Uploads_Admin. Defines all hooks for the admin area.
	 * - Otomaties_Private_Uploads_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		require plugin_dir_path( dirname( __FILE__ ) ) . 'vendor/autoload.php';

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-otomaties-private-uploads-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-otomaties-private-uploads-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-otomaties-private-uploads-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'frontend/class-otomaties-private-uploads-frontend.php';

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-otomaties-path.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-otomaties-path-name.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-otomaties-upload-directory.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-otomaties-upload.php';

		$this->loader = new Otomaties_Private_Uploads_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Otomaties_Private_Uploads_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Otomaties_Private_Uploads_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Otomaties_Private_Uploads_Admin( $this->get_otomaties_private_uploads(), $this->get_version() );

		$this->loader->add_action( 'admin_menu', $plugin_admin, 'admin_menu' );
		$this->loader->add_action( 'update_option_otomaties_private_uploads_directory', $plugin_admin, 'save_settings', 10, 3 );
		$this->loader->add_action( 'pre_update_option_otomaties_private_uploads_directory', $plugin_admin, 'validate_settings', 10, 3 );

		$this->loader->add_action( 'media_row_actions', $plugin_admin, 'move_attachment_action', 10, 2 );

		$this->loader->add_action( 'admin_notices', $plugin_admin, 'no_redirect_notification' );
		$this->loader->add_action( 'admin_notices', $plugin_admin, 'file_moved_notification' );

		$this->loader->add_action( 'post_action_move_media', $plugin_admin, 'move_media' );

		$this->loader->add_action( 'upload_dir', $plugin_admin, 'private_upload_dir' );

		$this->loader->add_action( 'bulk_actions-upload', $plugin_admin, 'bulk_make_private_action' );
		$this->loader->add_action( 'bulk_actions-upload', $plugin_admin, 'bulk_make_public_action' );

		$this->loader->add_filter( 'handle_bulk_actions-upload', $plugin_admin, 'bulk_toggle_private', 10, 3 );

		$this->loader->add_filter( 'manage_media_columns', $plugin_admin, 'visibility_column');
		$this->loader->add_filter( 'manage_media_custom_column', $plugin_admin, 'populate_visibility_column', 10, 2);


	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_frontend = new Otomaties_Private_Uploads_Frontend( $this->get_otomaties_private_uploads(), $this->get_version() );

		$this->loader->add_action( 'init', $plugin_frontend, 'rewrite_private_upload' );
		$this->loader->add_filter( 'query_vars', $plugin_frontend, 'custom_query_vars' );
		$this->loader->add_action( 'parse_request', $plugin_frontend, 'parse_request' );
		$this->loader->add_filter( 'wp_get_attachment_image_src', $plugin_frontend, 'replace_private_file_url', 10, 4);
		$this->loader->add_filter( 'the_content', $plugin_frontend, 'replace_private_file_url', 10, 4);
		$this->loader->add_filter( 'wp_get_attachment_url', $plugin_frontend, 'replace_private_file_url', 10, 2);

	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_otomaties_private_uploads() {
		return $this->otomaties_private_uploads;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Otomaties_Private_Uploads_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

}
