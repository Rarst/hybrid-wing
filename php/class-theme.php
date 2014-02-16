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

		$this['core'] = function () {
			return new Core();
		};

		$this['navbar'] = function () {
			return new Navbar();
		};

		$this['post-pagination'] = function () {
			return new Post_Pagination();
		};

		$this['loop-pagination'] = function () {
			return new Loop_Pagination();
		};

		$this['breadcrumb'] = function () {
			return new Breadcrumb();
		};

		$this['comments'] = function () {
			return new Comments();
		};

		$this['gallery'] = function () {
			return new Gallery();
		};

		parent::__construct( $options );
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

		if ( isset( $this['loop-pagination'] ) )
			$this['loop-pagination']->enable();

		if ( isset( $this['breadcrumb'] ) )
			$this['breadcrumb']->enable();

		if ( isset( $this['comments'] ) )
			$this['comments']->enable();

		if ( isset( $this['gallery'] ) )
			$this['gallery']->enable();
	}
}