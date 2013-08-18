<?php
namespace Rarst\Hybrid_Wing;

/**
 * Theme's loader and container class
 */
class Theme extends \Pimple {

	/**
	 * @param array $options
	 */
	public function __construct( $options = array() ) {

		$this['core'] = $this->share(
			function() {
				return new Core();
			}
		);

		$this['navbar'] = $this->share(
			function () {
				return new Navbar();
			}
		);

		$this['post-pagination'] = $this->share(
			function () {
				return new Post_Pagination();
			}
		);

		$this['breadcrumb'] = $this->share(
			function () {
				return new Breadcrumb();
			}
		);

		foreach ( $options as $key => $value ) {
			$this[$key] = $value;
		}
	}

	public function load() {

		add_action( 'after_setup_theme', array( $this, 'after_setup_theme' ), 0 );
	}

	public function unload() {

		remove_action( 'after_setup_theme', array( $this, 'after_setup_theme' ), 0 );
	}

	public function after_setup_theme(  ) {

		$this['core']; // instance Core

		add_action( 'after_setup_theme', array( $this, 'extensions' ), 14 );
	}

	public function extensions(  ) {

		if ( isset( $this['navbar'] ) )
			$this['navbar'];

		if ( isset( $this['post-pagination'] ) )
			$this['post-pagination'];

		if ( isset( $this['breadcrumb'] ) )
			$this['breadcrumb']->enable();
	}
}