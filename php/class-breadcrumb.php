<?php
namespace Rarst\Hybrid_Wing;

/**
 * Adjusts markup for Breadcrumb Trail
 */
class Breadcrumb {

	public function enable() {

		add_filter( 'breadcrumb_trail_args', array( $this, 'breadcrumb_trail_args' ) );
		add_filter( 'breadcrumb_trail_items', array( $this, 'breadcrumb_trail_items' ) );
		add_filter( 'breadcrumb_trail', array( $this, 'breadcrumb_trail' ) );
	}

	public function disable() {

		remove_filter( 'breadcrumb_trail_args', array( $this, 'breadcrumb_trail_args' ) );
		remove_filter( 'breadcrumb_trail_items', array( $this, 'breadcrumb_trail_items' ) );
		remove_filter( 'breadcrumb_trail', array( $this, 'breadcrumb_trail' ) );
	}

	public function breadcrumb_trail_args( $args ) {

		$args['container']     = 'ol';
		$args['separator']     = false;
		$args['show_on_front'] = false;
		$args['show_browse']   = false;

		return $args;
	}

	public function breadcrumb_trail_items( $items ) {

		if ( empty( $items ) )
			return $items;

		$last = array_pop( $items );

		foreach ( $items as $key => $item ) {
			$items[$key] = '<li>' . $item . '</li>';
		}

		$items[] = '<li class="active">' . $last . '</li>';

		return $items;
	}

	public function breadcrumb_trail( $breadcrumb ) {

		$breadcrumb = strtr(
			$breadcrumb,
			array(
				'class="breadcrumb-trail '   => 'class="breadcrumb-trail breadcrumb ',
				'<span class="sep">/</span>' => '',
				'<span class="trail-begin">' => '',
				'<span class="trail-end">'   => '',
				'</span>'                    => '',
			)
		);

		return $breadcrumb;
	}
}