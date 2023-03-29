<?php
class Otomaties_Upload_Directory {
	public function create_private_dir_if_not_exists() {
		$private_dir = $this->private_dir();
		if( !file_exists( $private_dir ) ) {
			mkdir( $private_dir );
		}
	}

	public function private_dir() {
		$private_path_name 	= get_option('otomaties_private_uploads_directory');
		$upload_dir 		= wp_get_upload_dir();
		$base_dir 			= new Otomaties_Path( $upload_dir['basedir'] );
		$private_dir 		= $base_dir->append( $private_path_name );
		return $private_dir;
	}

	public function private_url() {
		$private_path_name 	= get_option('otomaties_private_uploads_directory');
		$upload_dir 		= wp_get_upload_dir();
		$base_url			= new Otomaties_Path( $upload_dir['baseurl'] );
		$private_url 		= $base_url->append( $private_path_name );
		return $private_url;
	}
}
