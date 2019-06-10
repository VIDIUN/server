<?php
/**
 * @package api
 * @subpackage objects
 */
abstract class VidiunBaseResponseProfile extends VidiunObject implements IApiObjectFactory
{
	public static function getInstance($sourceObject, VidiunDetachedResponseProfile $responseProfile = null)
	{
		$object = null;
		
		if($sourceObject instanceof ResponseProfile)
		{
			$object = new VidiunResponseProfile();
		}
		elseif($sourceObject instanceof vResponseProfile)
		{
			$object = new VidiunDetachedResponseProfile();
		}
		
		if($object)
		{
			$object->fromObject($sourceObject, $responseProfile);
		}
		
		return $object;
	}
}