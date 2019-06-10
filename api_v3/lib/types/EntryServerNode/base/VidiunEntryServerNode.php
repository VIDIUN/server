<?php
/**
 * @package api
 * @subpackage objects
 * @abstract
 */
abstract class VidiunEntryServerNode extends VidiunObject implements IFilterable, IApiObjectFactory
{
	/**
	 * unique auto-generated identifier
	 * @var int
	 * @readonly
	 */
	public $id;

	/**
	 * @var string
	 * @readonly
	 * @filter eq,in
	 */
	public $entryId;

	/**
	 * @var int
	 * @readonly
	 * @filter eq
	 */
	public $serverNodeId;

	/**
	 * @var int
	 * @readonly
	 */
	public $partnerId;

	/**
	 * @var time
	 * @readonly
	 * @filter lte,gte,order
	 */
	public $createdAt;

	/**
	 * @var time
	 * @readonly
	 * @filter gte,lte,order
	 */
	public $updatedAt;

	/**
	 * @var VidiunEntryServerNodeStatus
	 * @readonly
	 * @filter eq,in
	 */
	public $status;

	/**
	 * @var VidiunEntryServerNodeType
	 * @readonly
	 * @filter eq,in
	 */
	public $serverType;

	private static $map_between_objects = array
	(
		"id",
		"entryId",
		"serverNodeId",
		"partnerId",
		"createdAt",
		"updatedAt",
		"status",
		"serverType"
	);

	public function getMapBetweenObjects ( )
	{
		return array_merge ( parent::getMapBetweenObjects() , self::$map_between_objects );
	}

	/* (non-PHPdoc)
	 * @see VidiunObject::toInsertableObject()
	 */
	public function toInsertableObject ( $object_to_fill = null , $props_to_skip = array() )
	{
		$object_to_fill = parent::toInsertableObject($object_to_fill, $props_to_skip);
		$object_to_fill->setPartnerId(vCurrentContext::getCurrentPartnerId());
		return $object_to_fill;
	}

	/**
	 * Should return the extra filters that are using more than one field
	 * On inherited classes, do not merge the array with the parent class
	 *
	 * @return array
	 */
	function getExtraFilters()
	{
		return array();
	}

	/**
	 * Should return the filter documentation texts
	 *
	 */
	function getFilterDocs()
	{
		return array();
	}

	/**
	 * Function returns EntryServerNode sub-type according to protocol
	 * @param $sourceObject
	 * @param VidiunDetachedResponseProfile $responseProfile
	 * @return VidiunEntryServerNode
	 */
	public static function getInstance ($sourceObject, VidiunDetachedResponseProfile $responseProfile = null)
	{
		$type = $sourceObject->getServerType();

		switch ($type)
		{
			case VidiunEntryServerNodeType::LIVE_BACKUP:
			case VidiunEntryServerNodeType::LIVE_PRIMARY:
				$object = new VidiunLiveEntryServerNode();
				break;

			case VidiunEntryServerNodeType::LIVE_CLIPPING_TASK:
				$object = new VidiunClippingTaskEntryServerNode();
				break;

			default:
				$object = VidiunPluginManager::loadObject('VidiunEntryServerNode', $type);
				if(!$object)
					VidiunLog::err("Did not expect source object to be of type ".$type);
		}

		if (!$object)
			return null;

		$object->fromObject($sourceObject, $responseProfile);
		return $object;
	}

}