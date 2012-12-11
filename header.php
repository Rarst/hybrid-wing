<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title><?php hybrid_document_title(); ?></title>

	<link rel="profile" href="http://gmpg.org/xfn/11" />
	<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>" />

	<?php wp_head(); ?>
</head>

<body class="<?php hybrid_body_class(); ?>">

<?php do_atomic( 'before_html' ); ?>

<div id="body-container" class="<?php echo apply_atomic( 'body_container_class', '' ); ?>">

	<?php do_atomic( 'before_header' ); ?>

	<div id="header-container">

		<div id="header" class="<?php echo apply_atomic( 'header_class', '' ); ?>">

			<?php do_atomic( 'header' ); ?>

		</div><!-- #header -->

	</div><!-- #header-container -->

	<?php do_atomic( 'after_header' ); ?>

	<div id="container" class="<?php echo apply_atomic( 'container_class', '' ); ?>">

		<?php do_atomic( 'before_container' ); ?>