<?php
/**
 * @package infra
 * @subpackage Plugins
 */
interface IVidiunBase
{
	/**
	 * Return an instance implementing the interface
	 * @param string $interface
	 * @return IVidiunBase
	 */
	public function getInstance($interface);
}