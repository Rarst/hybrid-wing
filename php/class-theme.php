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

		foreach ( $options as $key => $value ) {
			$this[$key] = $value;
		}
	}

	public function load() {

		add_filter( 'after_setup_theme', array( $this, 'after_setup_theme' ), 0 );
	}

	public function unload() {

		remove_filter( 'after_setup_theme', array( $this, 'after_setup_theme' ), 0 );
	}

	public function after_setup_theme(  ) {

		$this['core']; // instance Core
	}
}