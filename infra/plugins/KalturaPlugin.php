<?php
/**
 * @package infra
 * @subpackage Plugins
 */
abstract class VidiunPlugin implements IVidiunPlugin
{
	public function getInstance($interface)
	{
		if($this instanceof $interface)
			return $this;
			
		return null;
	}
}