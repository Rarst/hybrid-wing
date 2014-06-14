<?php

/**
 * This is project's console commands configuration for Robo task runner.
 *
 * @see http://robo.li/
 */
class RoboFile extends \Robo\Tasks {

	/**
	 * Watches for changes.
	 */
	public function watch() {

		$this->taskWatch()->monitor( 'style.less', function() {
			$this->less();
		} )->run();
	}

	/**
	 * Compiles stylesheet from less into css.
	 */
	public function less() {

		$this->taskExec('lessc style.less style.css')->run();
	}
}