<?php
/**
 * Interface which allows plugin to add its own Exceptions handler
 * @package infra
 * @subpackage Plugins
 */
interface IVidiunExceptionHandler extends IVidiunBase
{

	/**
	 * get Exception map - exceptionClass => array(exceptionClass , callback)
	 * @return array
	 */
	public function getExceptionMap();

}
