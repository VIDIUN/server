<?php
/**
 * @package plugins.elasticSearch
 * @subpackage api.objects
 */
abstract class VidiunESearchAbstractUserItem extends VidiunESearchUserBaseItem
{
	/**
	 * @var string
	 */
	public $searchTerm;
	
	/**
	 * @var VidiunESearchItemType
	 */
	public $itemType;
	
	/**
	 * @var VidiunESearchRange
	 */
	public $range;

	/**
	 * @var bool
	 */
	public $addHighlight;

	private static $map_between_objects = array(
		'searchTerm',
		'itemType',
		'range',
		'addHighlight',
	);
	
	protected function getMapBetweenObjects()
	{
		return array_merge(parent::getMapBetweenObjects(), self::$map_between_objects);
	}
	
	abstract protected function getItemFieldName();
	
	abstract protected function getDynamicEnumMap();
	
	abstract protected function getFieldEnumMap();
	
	public function toObject($object_to_fill = null, $props_to_skip = array())
	{
		list($object_to_fill, $props_to_skip) =
			VidiunESearchItemImpl::eSearchItemToObjectImpl($this, $this->getDynamicEnumMap(), $this->getItemFieldName(), $this->getFieldEnumMap(), $object_to_fill, $props_to_skip);
		
		return parent::toObject($object_to_fill, $props_to_skip);
	}
}