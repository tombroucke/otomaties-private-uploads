<?php
class Otomaties_Path_Name {

	private $name;

	public function __construct( $name ) {
		$this->name = $name;
	}

	public function is_valid() {

		// Check if path is year
		if( is_numeric( $this->name ) && strlen( $this->name ) == 4 && $this->name > 1970 && $this->name < 3000 ) {
			return false;
		}

		// Check if path is in array
		$forbidden = array( '',  ' ', 'cache', 'woocommerce_uploads', 'wc-logs' );
		if( in_array( $this->name, $forbidden) ) {
			return false;
		}

		// Check if path is within uploads folder
		if( strpos( $this->name, '../' ) !== false ) {
			return false;
		}

		return true;
	}

	public function __toString() {
		return $this->name;
	}

}
