<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Otomaties_Private_Uploads
 * @subpackage Otomaties_Private_Uploads/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Otomaties_Private_Uploads
 * @subpackage Otomaties_Private_Uploads/public
 * @author     Tom Broucke <tom@tombroucke.be>
 */
class Otomaties_Private_Uploads_Frontend {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $otomaties_private_uploads    The ID of this plugin.
	 */
	private $otomaties_private_uploads;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $otomaties_private_uploads       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $otomaties_private_uploads, $version ) {

		$this->otomaties_private_uploads = $otomaties_private_uploads;
		$this->version = $version;

	}

	public function rewrite_private_upload() {
		add_rewrite_rule('^private-upload', 'index.php?private-upload=true', 'top');
	}

	public function custom_query_vars( $vars ) {
	    $vars[] = 'private-upload';
	    return $vars;
	}

	public function parse_request( $wp ) {
		if( array_key_exists('private-upload', $wp->query_vars) ) {
			$private_upload_dir = new Otomaties_Upload_Directory;
			$upload_dir 		= wp_upload_dir();
			$file 				= '/' . ltrim( filter_input(INPUT_GET, 'file', FILTER_SANITIZE_STRING), '/' );
			$file_path			= $private_upload_dir->private_dir() . $file;
			$forbidden 			= plugins_url( '../assets/images/forbidden.png', __FILE__ );
			$extension 			= pathinfo( $file, PATHINFO_EXTENSION );

			// Check if filename contains forbidden characters
			if( strpos($file, '../') !== false ) {
				$file_path = $forbidden;
			}

			// Check if extension is allowed
			if( !array_key_exists($extension, $this->allowed_file_mimes()) || $extension == "" ){
				$file_path = $forbidden;
			}

			// Check if file exists
			if( !file_exists($file_path) ) {
				$file_path = str_replace('@2x', '', $file_path);
				if( !file_exists($file_path) ) {
					$file_path = $forbidden;
				}
			}

			// Check if user has access to this file
			if( !is_user_logged_in() ) {
				$file_path = $forbidden;
			}
			header( 'Cache-Control: public' );
			if( !in_array($extension, ['jpg', 'png', 'jpeg', 'bmp', 'gif']) ) {
				header( 'Content-Disposition: attachment; filename="'. basename($file) .'"');
			}
			header( 'Content-Type: ' . $this->get_content_type( $extension ) );
			readfile( $file_path );

			die();
		}
		return $wp;
	}

	public function allowed_file_mimes() {
		return array(
			'pdf'  	=> 'application/pdf',
			'gif'  	=> 'image/gif',
			'jpeg' 	=> 'image/jpeg',
			'jpg'  	=> 'image/jpeg',
			'png'  	=> 'image/png',
			'bmp'  	=> 'image/bmp',
			'txt'  	=> 'text/plain',
			'ppt'  	=> 'application/vnd.ms-powerpoint',
			'pptx' 	=> 'application/vnd.ms-powerpoint',
			'doc' 	=> 'application/msword',
			'docx' 	=> 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
			'xls' 	=> 'application/vnd.ms-excel',
			'xlsx' 	=> 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
		);
	}

	public function get_content_type( $extension ){

		$mimes = $this->allowed_file_mimes();
		return isset($mimes[$extension]) ? $mimes[$extension] : 'text/plain';

	}

	public function replace_private_file_url($url) {
		$upload_dir  = wp_get_upload_dir();
		return str_replace($upload_dir['baseurl'] . '/private-uploads/', home_url() . '/private-uploads?file=/', $url);
	}

}
