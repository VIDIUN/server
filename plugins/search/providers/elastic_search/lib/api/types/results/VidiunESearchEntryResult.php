<?php
/**
 * @package plugins.elasticSearch
 * @subpackage api.objects
 */
class VidiunESearchEntryResult extends VidiunESearchResult
{
	/**
	 * @var VidiunBaseEntry
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
		$isAdmin = vCurrentContext::$vs_object->isAdmin();
		$object = VidiunEntryFactory::getInstanceByType($srcObj->getObject()->getType(), $isAdmin);
		$object->fromObject($srcObj->getObject(), $responseProfile);
		$this->object = $object;
		return parent::doFromObject($srcObj, $responseProfile);
	}

}
