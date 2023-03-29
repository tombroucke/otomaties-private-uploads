<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Otomaties_Private_Uploads
 * @subpackage Otomaties_Private_Uploads/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Otomaties_Private_Uploads
 * @subpackage Otomaties_Private_Uploads/admin
 * @author     Tom Broucke <tom@tombroucke.be>
 */
class Otomaties_Private_Uploads_Admin {

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
	 * @param      string    $otomaties_private_uploads       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $otomaties_private_uploads, $version ) {

		$this->otomaties_private_uploads = $otomaties_private_uploads;
		$this->version = $version;

	}

	public function admin_menu() {

		//create new top-level menu
		add_submenu_page('upload.php', __('Private uploads', 'otomaties-private-uploads'), __('Private uploads', 'otomaties-private-uploads'), 'manage_options', 'private-uploads', array( $this, 'settings_page' ) );

		//call register settings function
		add_action( 'admin_init', array( $this, 'register_settings' ) );
	}

	public function register_settings() {
		register_setting( 'private-uploads-settings-group', 'otomaties_private_uploads_default_private' );
	}

	public function settings_page() {
		?>
		<div class="wrap">
			<h1><?php _e('Private uploads settings', 'otomaties-private-uploads'); ?></h1>

			<form method="post" action="options.php">
				<?php 
				settings_fields( 'private-uploads-settings-group' );
				do_settings_sections( 'private-uploads-settings-group' ); 
				$default_upload_to_private_directory = get_option('otomaties_private_uploads_default_private');
				?>
				<table class="form-table">
					<tr valign="top">
						<th scope="row"><?php _e('Default upload to private directory', 'otomaties-private-uploads'); ?></th>
						<td>
							<fieldset>
								<label>
									<input name="otomaties_private_uploads_default_private" type="checkbox" value="1" <?php checked( $default_upload_to_private_directory, 1 ); ?>><?php _e( 'Move new uploads to private directory by default.', 'otomaties-private-uploads' ) ?></label>
							</fieldset>
						</td>
					</tr>
				</table>

				<?php submit_button(); ?>

			</form>
		</div>
	<?php }

	public function save_settings( $old_value, $new_value, $option ) {
		$uploads_dir = new Otomaties_Upload_Directory;
		$uploads_dir->create_private_dir_if_not_exists();
	}

	public function validate_settings( $new_value, $old_value, $option ) {
		$return = $new_value;
		$path_name = new Otomaties_Path_Name( $new_value );
		if( !$path_name->is_valid() ){
			$return = $old_value;
		}
		return $return;
	}

	public function move_attachment_action( $actions, $post ) {
		$upload = new Otomaties_Upload( $post->ID );
		$url = admin_url( 'post.php?post=' . $post->ID );

		if( !$upload->is_private() ) {
			$url = add_query_arg( array( 'action' => 'move_media', 'to' => 'private' ), $url );
			$actions['duplicate'] = sprintf( '<a href="%s" rel="permalink">%s</a>', $url ,__( 'Move to private directory', 'otomaties-private-uploads' ) );
		}
		else {
			$url = add_query_arg( array( 'action' => 'move_media', 'to' => 'public' ), $url );
			$actions['duplicate'] = sprintf( '<a href="%s" rel="permalink">%s</a>', $url ,__( 'Move to public directory', 'otomaties-private-uploads' ) );
		}

		return $actions;
	}

	public function no_redirect_notification() {
		$path_to_wp = new Otomaties_Path( get_home_path() );
		$file = $path_to_wp . '.htaccess';
		$rule = 'RewriteRule ^wp-content/uploads/private-uploads(.*)$ private-uploads?file=/$1 [R=301,NC,L]';
		$htaccess = file_get_contents($file);
		if( strpos($htaccess, $rule) === false ) {
			?>
			<div class="notice notice-error">
				<h2><?php _e( 'Critical error', 'otomaties-private-uploads' ); ?></h2>
				<p>
					<?php printf( __( 'Your private upload directory is not protected. You need to add <code>%s</code> to your .htacces file.', 'otomaties-private-uploads' ), $rule ); ?>
				</p>
			</div>
			<?php
		}
	}

	public function file_moved_notification() {
		$made_private 	= filter_input(INPUT_GET, 'made_media_private', FILTER_VALIDATE_BOOLEAN);
		$made_public 	= filter_input(INPUT_GET, 'made_media_public', FILTER_VALIDATE_BOOLEAN);
		$ids 			= filter_input(INPUT_GET, 'ids', FILTER_SANITIZE_STRING);
		$files 			= array();

		if( $ids ) {
			$ids_array = explode(',', $ids);
			foreach ($ids_array as $key => $id) {
				$files[] = get_the_title($id);
			}
		}

		if( $made_private ) {
			?>
			<div class="notice notice-success">
				<p><?php printf( __( 'Moved %s to private directory', 'otomaties-private-uploads' ), implode(', ', $files)); ?></p>
			</div>
			<?php
		}

		if( $made_public ) {
			?>
			<div class="notice notice-success">
				<p><?php printf( __( 'Moved %s to public directory', 'otomaties-private-uploads' ), implode(', ', $files)); ?></p>
			</div>
			<?php
		}
	}

	public function move_media() {

		$to = filter_input( INPUT_GET, 'to', FILTER_SANITIZE_STRING );

		if ( isset( $_GET['post'] ) && isset( $_POST['post_ID'] ) && (int) $_GET['post'] !== (int) $_POST['post_ID'] ) {
			wp_die( __( 'A post ID mismatch has been detected.' ), __( 'Sorry, you are not allowed to edit this item.' ), 400 );
		} elseif ( isset( $_GET['post'] ) ) {
			$post_id = (int) $_GET['post'];
		} elseif ( isset( $_POST['post_ID'] ) ) {
			$post_id = (int) $_POST['post_ID'];
		} else {
			$post_id = 0;
		}

		$sendback 	= wp_get_referer();

		if ( ! $sendback ||
			false !== strpos( $sendback, 'post.php' ) ||
			false !== strpos( $sendback, 'post-new.php' ) ) {
			if ( 'attachment' == $post_type ) {
				$sendback = admin_url( 'upload.php' );
			} else {
				$sendback = admin_url( 'edit.php' );
				if ( ! empty( $post_type ) ) {
					$sendback = add_query_arg( 'post_type', $post_type, $sendback );
				}
			}
		} else {
			$sendback = remove_query_arg( array( 'trashed', 'untrashed', 'deleted', 'ids', 'made_media_private', 'made_media_public' ), $sendback );
		}

		$upload = new Otomaties_Upload( $post_id );
		if( $to == 'private' ) {
			$upload->make_private();
			$sendback = add_query_arg(
				array(
					'made_media_private' => 1,
					'ids'     => $post_id,
				),
				$sendback
			);
		}
		else{
			$upload->make_public();
			$sendback = add_query_arg(
				array(
					'made_media_public' => 1,
					'ids'     => $post_id,
				),
				$sendback
			);
		}

		wp_redirect( $sendback );
		exit();
	}

	public function private_upload_dir( $upload_dir ) {
		$private_upload_dir = get_option('otomaties_private_uploads_directory') . '/' . date('Y') . '/' . date('m');
		if( get_option('otomaties_private_uploads_default_private') && $private_upload_dir ) {
	        $upload_dir['subdir'] = $private_upload_dir;
	        $upload_dir['path'] = $upload_dir['basedir'] . '/' . $private_upload_dir;
	        $upload_dir['url']  = $upload_dir['baseurl'] . '/' . $private_upload_dir;
		}
		return $upload_dir;
	}

	public function bulk_make_private_action($bulk_actions) {
		$bulk_actions['make_media_private'] = __( 'Make media private', 'otomaties-private-uploads');
		return $bulk_actions;
	}

	public function bulk_make_public_action($bulk_actions) {
		$bulk_actions['make_media_public'] = __( 'Make media public', 'otomaties-private-uploads');
		return $bulk_actions;
	}

	public function bulk_toggle_private( $redirect_to, $doaction, $post_ids ) {
		if ( $doaction === 'make_media_private' ) {
			$redirect_to = remove_query_arg( array( 'trashed', 'untrashed', 'deleted', 'ids', 'made_media_private', 'made_media_public' ), $redirect_to );
			foreach ($post_ids as $key => $post_id) {
				$upload = new Otomaties_Upload( $post_id );
				$upload->make_private();
				$redirect_to = add_query_arg(
					array(
						'made_media_private' => 1,
						'ids'     => $post_id,
					),
					$redirect_to
				);
			}
		}
		elseif ( $doaction === 'make_media_public' ) {
			$redirect_to = remove_query_arg( array( 'trashed', 'untrashed', 'deleted', 'ids', 'made_media_private', 'made_media_public' ), $redirect_to );
			foreach ($post_ids as $key => $post_id) {
				$upload = new Otomaties_Upload( $post_id );
				$upload->make_public();
				$redirect_to = add_query_arg(
					array(
						'made_media_public' => 1,
						'ids'     => $post_id,
					),
					$redirect_to
				);
			}
		}
		return $redirect_to;
	}

	public function show_private( $title ) {
		global $post;
		if( is_admin() && $post->post_type == 'attachment' ) {
			$upload = new Otomaties_Upload( $post->ID );
			if( $upload->is_private() ) {
				$title = '(Private)' . $title;
			}
		}
		return $title;
	}

	public function visibility_column( $columns ) {
		$columns['visibility'] = __('Private / Public', 'otomaties-private-uploads');
		return $columns;
	}

	public function populate_visibility_column($column_name, $id) {
		if($column_name == 'visibility') {
			$upload = new Otomaties_Upload( $id );
			if($upload->is_private()) {
				echo "<span class=\"dashicons dashicons-lock\"></span>";
			}
		}
		return $column_name;
	}

}
