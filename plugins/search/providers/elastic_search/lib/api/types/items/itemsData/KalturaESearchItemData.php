<?php
/**
 * @package plugins.elasticSearch
 * @subpackage api.objects
 */
abstract class VidiunESearchItemData extends VidiunObject
{
	/**
	 * @var VidiunESearchHighlightArray
	 */
	public $highlight;

	private static $map_between_objects = array(
		'highlight',
	);

	protected function getMapBetweenObjects()
	{
		return array_merge(parent::getMapBetweenObjects(), self::$map_between_objects);
	}
}
