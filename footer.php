		<?php do_atomic( 'after_container' ); ?>

	</div><!-- #container -->

	<div id="footer-container">

		<?php do_atomic( 'before_footer' ); ?>

		<div id="footer">

			<?php do_atomic( 'footer' ); ?>

		</div>

		<?php do_atomic( 'after_footer' ); ?>

	</div>

</div><!-- #body-container -->


<?php do_atomic( 'after_html' ); ?>
<?php wp_footer(); ?>

</body>
</html>