<?php 


use Teil\Core\Facade;


class Checkout extends Facade {


	/**
	 * Get the registered name of the component.
	 *
	 * @return string
	 */
	protected static function getFacadeAccessor() { return 'checkout'; }


}