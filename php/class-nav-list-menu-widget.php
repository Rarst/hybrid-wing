<?php

/**
 * Menu widget with Bootstrap nav list markup.
 */
class Nav_List_Menu_Widget extends WP_Nav_Menu_Widget {

	function __construct() {

		WP_Widget::__construct( 'nav_list_menu', 'Nav List Custom Menu' );
	}

	function widget( $args, $instance ) {

		add_filter( 'wp_nav_menu_args', array( $this, 'wp_nav_menu_args' ) );
		add_filter( 'wp_nav_menu', array( $this, 'wp_nav_menu' ) );
		parent::widget( $args, $instance );
		remove_filter( 'wp_nav_menu_args', array( $this, 'wp_nav_menu_args' ) );
		remove_filter( 'wp_nav_menu', array( $this, 'wp_nav_menu' ) );
	}

	function wp_nav_menu_args( $args ) {

		$args['container_class'] = 'well';
		$args['menu_class']      = 'menu nav nav-list';

		return apply_filters( 'nav_list_menu_args', $args );;
	}

	function wp_nav_menu( $nav_menu ) {

		$nav_menu = str_replace( '"sub-menu"', '"sub-menu nav nav-list"', $nav_menu );
		$nav_menu = str_replace( 'current-menu-item', 'current-menu-item active', $nav_menu );

		return $nav_menu;
	}
}