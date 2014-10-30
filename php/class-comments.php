<?php
namespace Rarst\Hybrid_Wing;

/**
 * Adjusts markup of comment section
 */
class Comments {

	public function enable() {

		add_filter( 'hw_list_comments_args', array( $this, 'list_comments_args' ) );
		add_action( 'comment_form_defaults', array( $this, 'comment_form_defaults' ) );
		add_action( 'comment_form_top', array( $this, 'comment_form_top' ) );
		add_action( 'comment_form', array( $this, 'comment_form' ) );
	}

	public function disable() {

		remove_filter( 'hw_list_comments_args', array( $this, 'list_comments_args' ) );
		remove_action( 'comment_form_defaults', array( $this, 'comment_form_defaults' ) );
		remove_action( 'comment_form_top', array( $this, 'comment_form_top' ) );
		remove_action( 'comment_form', array( $this, 'comment_form' ) );
	}

	/**
	 * Adjust arguments for comments presentation.
	 *
	 * @param array $args
	 *
	 * @return array
	 */
	function list_comments_args( $args ) {

		$args['style']        = 'div';
		$args['avatar_size']  = 60;
		$args['end-callback'] = array( $this, 'comment_end_callback' );

		return $args;
	}

	/**
	 * Load closing template for comment.
	 */
	function comment_end_callback() {

		static $template;

		if ( empty( $template ) )
			$template = locate_template( 'comment-end.php' );

		require $template;
	}

	/**
	 * Adjust comment data for Bootstrap markup.
	 *
	 * @param array $defaults
	 *
	 * @return array
	 */
	function comment_form_defaults( $defaults ) {

		foreach ( $defaults['fields'] as $key => $field ) {
			$defaults['fields'][$key] = $this->make_comment_field_horizontal( $field );
		}

		$defaults['comment_field']        = $this->make_comment_field_horizontal( $defaults['comment_field'] );
		$defaults['logged_in_as']         = $this->make_comment_notes_help_block( $defaults['logged_in_as'] );
		$defaults['comment_notes_before'] = $this->make_comment_notes_help_block( $defaults['comment_notes_before'] );
		$defaults['comment_notes_after']  = $this->make_comment_notes_help_block( $defaults['comment_notes_after'] );

		return $defaults;
	}

	/**
	 * Rewrite markup to strip paragraph and wrap in horizontal form block markup.
	 *
	 * @param string $field
	 *
	 * @return string
	 */
	function make_comment_field_horizontal( $field ) {

		$field = preg_replace( '|<p class="(.*?)">|', '<div class="$1 control-group">', $field );

		$field = strtr(
			$field,
			array(
				'<label'    => '<label class="control-label"',
				'<input'    => '<div class="controls"><input',
				'<textarea' => '<div class="controls"><textarea',
				'</p>'      => '</div>',
			)
		);

		$field .= '</div>';

		return $field;
	}

	/**
	 * Rewrite markup to wrap into horizontal form help block.
	 *
	 * @param string $note
	 *
	 * @return string
	 */
	function make_comment_notes_help_block( $note ) {

		$note = '<div class="control-group"><div class="controls">' . str_replace( '<p class="', '<p class="help-block ', $note ) . '</div></div>';

		return $note;
	}

	function comment_form_top() {
		echo '<div class="form-horizontal">';
	}

	function comment_form() {
		echo '</div>';
	}
}