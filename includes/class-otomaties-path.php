<?php
class Otomaties_Path {

	/**
	 * The path
	 * @var String
	 */
	private $path;

	/**
	 * Add trailing slash on construction
	 * @param String $path
	 */
	public function __construct( $path ) {
		$this->path = $this->add_trailing_slash( $path );
	}

	/**
	 * Add trailing slash
	 * @param String $path
	 * @return String
	 */
	private function add_trailing_slash( $path ) {
		return rtrim($path, '/') . '/';
	}

	/**
	 * Append directory to path
	 * @param  String $append
	 * @return Otomaties_Path
	 */
	public function append( $append ) {
		$path = $this->path . ltrim( $append, '/' );
		return new Otomaties_Path( $path );
	}

	/**
	 * Return path
	 * @return String
	 */
	public function __toString() {
		return $this->path;
	}

	/**
	 * Mkdir
	 * @return Void
	 */
	public function create() {
		if( !file_exists( $this->path ) ) {
			mkdir( $this->path, 0755, true );
		}
	}

	/**
	 * Remove part of path
	 *
	 * @param [type] $string
	 * @return Otomaties_Path
	 */
	public function remove( $string ) {
		$path = str_replace('/' . $string, '', $this->path);
		return new Otomaties_Path( $path );
	}

}
