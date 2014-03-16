<?php
namespace Rarst\Hybrid_Wing;

/**
 * Theme's loader and container class
 */
class Theme extends \Pimple {

	/**
	 * @param array $values
	 */
	public function __construct( $values = array() ) {

		$defaults  = array();
		$class_map = array(
			'core'            => 'Core',
			'navbar'          => 'Navbar',
			'post-pagination' => 'Post_Pagination',
			'loop-pagination' => 'Loop_Pagination',
			'breadcrumb'      => 'Breadcrumb',
			'comments'        => 'Comments',
			'gallery'         => 'Gallery',
		);

		foreach ( $class_map as $key => $name ) {
			$defaults[$key] = function () use ( $name ) {
				$class_name = __NAMESPACE__ . '\\' . $name;

				return new $class_name();
			};
		}

		parent::__construct( array_merge( $defaults, $values ) );
	}

	public function load() {

		$this['core']; // instance Core early or bad things happen, see https://core.trac.wordpress.org/ticket/27428

		add_action( 'after_setup_theme', array( $this, 'after_setup_theme' ), 0 );
	}

	public function unload() {

		remove_action( 'after_setup_theme', array( $this, 'after_setup_theme' ), 0 );
	}

	public function after_setup_theme(  ) {

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