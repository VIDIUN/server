<?php
/**
 * @package plugins.elasticSearch
 * @subpackage api.objects
 */
class VidiunESearchUserResult extends VidiunESearchResult
{
	/**
	 * @var VidiunUser
	 */
	public $object;

	private static $map_between_objects = array(
		'object',
	);

	protected function getMapBetweenObjects()
	{
		return array_merge(parent::getMapBetweenObjects(), self::$map_between_objects);
	}

	protected function doFromObject($srcObj, VidiunDetachedResponseProfile $responseProfile = null)
	{
		$object = new VidiunUser();
		$object->fromObject($srcObj->getObject(), $responseProfile);
		$this->object = $object;
		return parent::doFromObject($srcObj, $responseProfile);
	}

}
