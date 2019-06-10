<?php
/**
 * @package plugins.caption
 * @relatedService ignore
 * @subpackage api.filters.base
 * @abstract
 */
abstract class VidiunCaptionParamsBaseFilter extends VidiunAssetParamsFilter
{
	static private $map_between_objects = array
	(
		"formatEqual" => "_eq_format",
		"formatIn" => "_in_format",
	);

	static private $order_by_map = array
	(
	);

	public function getMapBetweenObjects()
	{
		return array_merge(parent::getMapBetweenObjects(), self::$map_between_objects);
	}

	public function getOrderByMap()
	{
		return array_merge(parent::getOrderByMap(), self::$order_by_map);
	}

	/**
	 * @var VidiunCaptionType
	 */
	public $formatEqual;

	/**
	 * @dynamicType VidiunCaptionType
	 * @var string
	 */
	public $formatIn;
}
