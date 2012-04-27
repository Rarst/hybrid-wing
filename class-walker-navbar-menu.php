<?php

class Walker_Navbar_Menu extends Walker_Nav_Menu {

	public $dropdown_enqueued;

	function __construct() {

		$this->dropdown_enqueued = wp_script_is( 'bootstrap-dropdown', 'queue' );
	}

	function display_element( $element, &$children_elements, $max_depth, $depth = 0, $args, &$output ) {

		if ( $element->current )
			$element->classes[] = 'active';

		$element->is_dropdown = ( 0 == $depth ) && ! empty( $children_elements[$element->ID] );

		if ( $element->is_dropdown )
			$element->classes[] = 'dropdown';

		parent::display_element( $element, &$children_elements, $max_depth, $depth, $args, &$output );
	}

	function start_lvl( &$output, $depth ) {

		if ( ! $this->dropdown_enqueued ) {
			wp_enqueue_script( 'bootstrap-dropdown' );
			$this->dropdown_enqueued = true;
		}

		$indent  = str_repeat( "\t", $depth );
		$class   = ( 0 == $depth ) ? 'dropdown-menu' : 'unstyled';
		$output .= "\n{$indent}<ul class='{$class}'>\n";
	}

	function start_el( &$output, $item, $depth, $args ) {

		$item_html = '';
		parent::start_el( &$item_html, $item, $depth, $args );

		if ( $item->is_dropdown && ( 1 != $args->depth ) ) {
			$item_html = str_replace( '<a', '<a class="dropdown-toggle" data-toggle="dropdown"', $item_html );
			$item_html = str_replace( '</a>', '<b class="caret"></b></a>', $item_html );
			$item_html = str_replace( esc_attr( $item->url ), '#', $item_html );
		}

		$output .= $item_html;
	}
}